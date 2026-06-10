#!/usr/bin/env python3
import argparse
import json
import os
import sys
from functools import lru_cache
from pathlib import Path

import faiss
import numpy as np
import open_clip
import torch
from PIL import Image


CONCEPT_PROMPTS = {
    "smartphone": [
        "a photo of a smartphone",
        "a photo of an iphone",
        "a photo of a mobile phone",
    ],
    "laptop": [
        "a photo of a laptop computer",
        "a photo of a notebook computer",
    ],
    "speaker": ["a photo of a bluetooth speaker", "a photo of a portable speaker"],
    "power_bank": ["a photo of a power bank battery", "a photo of a portable charger"],
    "television": ["a photo of a television", "a photo of a tv screen"],
    "motorbike": ["a photo of a motorbike", "a photo of a scooter motorcycle"],
    "bicycle": ["a photo of a bicycle", "a photo of a mountain bike"],
    "helmet": ["a photo of a motorcycle helmet", "a photo of a helmet"],
    "shoes": ["a photo of running shoes", "a photo of sneakers"],
    "clothing": ["a photo of clothing", "a photo of a shirt or dress"],
    "book": ["a photo of books", "a photo of a textbook"],
    "rice_cooker": ["a photo of a rice cooker", "a photo of a kitchen appliance"],
    "fan": ["a photo of an electric fan", "a photo of a standing fan"],
    "refrigerator": ["a photo of a refrigerator", "a photo of a fridge"],
    "washing_machine": ["a photo of a washing machine", "a photo of a laundry machine"],
    "sofa": ["a photo of a sofa", "a photo of living room furniture"],
    "dishes": ["a photo of ceramic dishes", "a photo of a dinnerware set"],
    "sports": ["a photo of sports equipment", "a photo of fitness gear"],
}


def read_manifest(path):
    if not path:
        return []

    rows = []
    with open(path, "r", encoding="utf-8") as handle:
        for line in handle:
            line = line.strip()
            if line:
                rows.append(json.loads(line))
    return rows


def resolve_device(device):
    if device == "cuda" and not torch.cuda.is_available():
        return "cpu"
    if device == "mps" and not torch.backends.mps.is_available():
        return "cpu"
    return device


@lru_cache(maxsize=4)
def load_model(model_name, pretrained, device):
    device = resolve_device(device)
    model, _, preprocess = open_clip.create_model_and_transforms(
        model_name,
        pretrained=pretrained or "openai",
        device=device,
    )
    model.eval()
    return model, preprocess, device


@lru_cache(maxsize=4)
def concept_text_features(model_name, pretrained, device):
    model, _, resolved_device = load_model(model_name, pretrained, device)
    tokenizer = open_clip.get_tokenizer(model_name)
    labels = []
    features = []

    for label, prompts in CONCEPT_PROMPTS.items():
        text = tokenizer(prompts).to(resolved_device)
        with torch.no_grad():
            encoded = model.encode_text(text)
            encoded = encoded / encoded.norm(dim=-1, keepdim=True)
            averaged = encoded.mean(dim=0, keepdim=True)
            averaged = averaged / averaged.norm(dim=-1, keepdim=True)
        labels.append(label)
        features.append(averaged.cpu())

    return labels, torch.cat(features, dim=0).numpy().astype("float32")


def image_vector(path, model_name, pretrained, device):
    model, preprocess, resolved_device = load_model(model_name, pretrained, device)
    image = Image.open(path).convert("RGB")
    tensor = preprocess(image).unsqueeze(0).to(resolved_device)

    with torch.no_grad():
        features = model.encode_image(tensor)
        features = features / features.norm(dim=-1, keepdim=True)

    return features.cpu().numpy().astype("float32")[0]


def embed_paths(paths, batch_size, model_name, pretrained, device):
    model, preprocess, resolved_device = load_model(model_name, pretrained, device)
    vectors = []
    valid_paths = []
    pending_tensors = []
    pending_paths = []

    def flush():
        if not pending_tensors:
            return

        batch = torch.stack(pending_tensors).to(resolved_device)
        with torch.no_grad():
            features = model.encode_image(batch)
            features = features / features.norm(dim=-1, keepdim=True)

        vectors.extend(features.cpu().numpy().astype("float32"))
        valid_paths.extend(pending_paths)
        pending_tensors.clear()
        pending_paths.clear()

    for path in paths:
        try:
            image = Image.open(path).convert("RGB")
            pending_tensors.append(preprocess(image))
            pending_paths.append(path)
            if len(pending_tensors) >= batch_size:
                flush()
        except Exception:
            continue

    flush()

    if not vectors:
        return np.empty((0, model.visual.output_dim), dtype="float32"), []

    return np.vstack(vectors), valid_paths


def make_index(dim):
    return faiss.IndexIDMap2(faiss.IndexFlatIP(dim))


def read_index(path, dim):
    if os.path.exists(path):
        index = faiss.read_index(path)
        if index.d == dim:
            return index
    return make_index(dim)


def write_index(index, path):
    Path(path).parent.mkdir(parents=True, exist_ok=True)
    faiss.write_index(index, path)


def command_add(args):
    rows = read_manifest(args.manifest)
    vectors, valid_paths = embed_paths(
        [row["path"] for row in rows],
        args.batch_size,
        args.model,
        args.pretrained,
        args.device,
    )
    by_path = {row["path"]: row for row in rows}
    indexed_rows = [by_path[path] for path in valid_paths]

    if vectors.shape[0] > 0:
        index = read_index(args.index, vectors.shape[1])
        ids = np.array([int(row["faiss_id"]) for row in indexed_rows], dtype="int64")
        index.add_with_ids(vectors, ids)
        write_index(index, args.index)

    print(json.dumps({"items": indexed_rows, "failed": len(rows) - len(indexed_rows)}))


def command_rebuild(args):
    rows = read_manifest(args.manifest)
    vectors, valid_paths = embed_paths(
        [row["path"] for row in rows],
        args.batch_size,
        args.model,
        args.pretrained,
        args.device,
    )
    by_path = {row["path"]: row for row in rows}
    indexed_rows = [by_path[path] for path in valid_paths]

    if vectors.shape[0] > 0:
        index = make_index(vectors.shape[1])
        ids = np.array([int(row["faiss_id"]) for row in indexed_rows], dtype="int64")
        index.add_with_ids(vectors, ids)
    else:
        _, _, resolved_device = load_model(args.model, args.pretrained, args.device)
        empty_vector = image_vector_from_blank(args.model, args.pretrained, resolved_device)
        index = make_index(empty_vector.shape[0])

    write_index(index, args.index)
    print(json.dumps({"items": indexed_rows, "failed": len(rows) - len(indexed_rows)}))


def image_vector_from_blank(model_name, pretrained, device):
    blank_path = Path("/tmp/remarket-blank-clip-image.jpg")
    if not blank_path.exists():
        Image.new("RGB", (224, 224), color=(255, 255, 255)).save(blank_path)
    return image_vector(str(blank_path), model_name, pretrained, device)


def command_search(args):
    if not os.path.exists(args.index):
        print(json.dumps({"matches": []}))
        return

    vectors, _ = embed_paths([args.image], 1, args.model, args.pretrained, args.device)
    if vectors.shape[0] == 0:
        print(json.dumps({"matches": []}))
        return

    index = faiss.read_index(args.index)
    if index.d != vectors.shape[1] or index.ntotal == 0:
        print(json.dumps({"matches": []}))
        return

    scores, ids = index.search(vectors, args.limit)
    query_labels = classify_query(vectors[0], args.model, args.pretrained, args.device)
    matches = [
        {"faiss_id": int(faiss_id), "score": float(score)}
        for faiss_id, score in zip(ids[0], scores[0])
        if int(faiss_id) >= 0
    ]
    print(json.dumps({"matches": matches, "query_labels": query_labels}))


def classify_query(vector, model_name, pretrained, device):
    labels, text_features = concept_text_features(model_name, pretrained, device)
    scores = text_features @ vector
    ranked = sorted(
        [{"label": label, "score": float(score)} for label, score in zip(labels, scores)],
        key=lambda item: item["score"],
        reverse=True,
    )
    return ranked[:5]


def parse_args():
    parser = argparse.ArgumentParser(description="OpenCLIP + FAISS visual search helper")
    parser.add_argument("command", choices=["add", "rebuild", "search"])
    parser.add_argument("--index", required=True)
    parser.add_argument("--manifest")
    parser.add_argument("--image")
    parser.add_argument("--limit", type=int, default=24)
    parser.add_argument("--batch-size", type=int, default=16)
    parser.add_argument("--model", default="ViT-B-32")
    parser.add_argument("--pretrained", default="openai")
    parser.add_argument("--device", default="cpu")
    return parser.parse_args()


def main():
    args = parse_args()
    if args.command in {"add", "rebuild"} and not args.manifest:
        print("--manifest is required", file=sys.stderr)
        return 2
    if args.command == "search" and not args.image:
        print("--image is required", file=sys.stderr)
        return 2

    try:
        if args.command == "add":
            command_add(args)
        elif args.command == "rebuild":
            command_rebuild(args)
        else:
            command_search(args)
    except Exception as exc:
        print(f"visual_search.py failed: {exc}", file=sys.stderr)
        return 1

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
