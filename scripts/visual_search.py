#!/usr/bin/env python3
import argparse
import json
import os
import sys
from pathlib import Path

import faiss
import numpy as np
import open_clip
import torch
from PIL import Image


def load_model(model_name, pretrained, device):
    model, _, preprocess = open_clip.create_model_and_transforms(
        model_name,
        pretrained=pretrained,
        device=device,
    )
    model.eval()
    return model, preprocess


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


def embed_paths(paths, model, preprocess, device, batch_size):
    vectors = []
    valid_paths = []
    for start in range(0, len(paths), batch_size):
        batch_paths = paths[start:start + batch_size]
        images = []
        kept = []
        for path in batch_paths:
            try:
                image = Image.open(path).convert("RGB")
                images.append(preprocess(image))
                kept.append(path)
            except Exception:
                continue

        if not images:
            continue

        tensor = torch.stack(images).to(device)
        with torch.no_grad():
            features = model.encode_image(tensor)
            features = features / features.norm(dim=-1, keepdim=True)
        vectors.append(features.cpu().numpy().astype("float32"))
        valid_paths.extend(kept)

    if not vectors:
        return np.empty((0, 512), dtype="float32"), []

    return np.vstack(vectors), valid_paths


def make_index(dim):
    return faiss.IndexIDMap2(faiss.IndexFlatIP(dim))


def read_index(path, dim):
    if os.path.exists(path):
        return faiss.read_index(path)
    return make_index(dim)


def write_index(index, path):
    Path(path).parent.mkdir(parents=True, exist_ok=True)
    faiss.write_index(index, path)


def command_add(args):
    rows = read_manifest(args.manifest)
    model, preprocess = load_model(args.model, args.pretrained, args.device)
    vectors, valid_paths = embed_paths([row["path"] for row in rows], model, preprocess, args.device, args.batch_size)
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
    model, preprocess = load_model(args.model, args.pretrained, args.device)
    vectors, valid_paths = embed_paths([row["path"] for row in rows], model, preprocess, args.device, args.batch_size)
    by_path = {row["path"]: row for row in rows}
    indexed_rows = [by_path[path] for path in valid_paths]

    if vectors.shape[0] > 0:
        index = make_index(vectors.shape[1])
        ids = np.array([int(row["faiss_id"]) for row in indexed_rows], dtype="int64")
        index.add_with_ids(vectors, ids)
    else:
        index = make_index(512)

    write_index(index, args.index)
    print(json.dumps({"items": indexed_rows, "failed": len(rows) - len(indexed_rows)}))


def command_search(args):
    if not os.path.exists(args.index):
        print(json.dumps({"matches": []}))
        return

    model, preprocess = load_model(args.model, args.pretrained, args.device)
    vectors, _ = embed_paths([args.image], model, preprocess, args.device, 1)
    if vectors.shape[0] == 0:
        print(json.dumps({"matches": []}))
        return

    index = faiss.read_index(args.index)
    scores, ids = index.search(vectors, args.limit)
    matches = [
        {"faiss_id": int(faiss_id), "score": float(score)}
        for faiss_id, score in zip(ids[0], scores[0])
        if int(faiss_id) >= 0
    ]
    print(json.dumps({"matches": matches}))


def parse_args():
    parser = argparse.ArgumentParser(description="Local OpenCLIP + FAISS visual search helper")
    parser.add_argument("command", choices=["add", "rebuild", "search"])
    parser.add_argument("--index", required=True)
    parser.add_argument("--manifest")
    parser.add_argument("--image")
    parser.add_argument("--limit", type=int, default=24)
    parser.add_argument("--batch-size", type=int, default=64)
    parser.add_argument("--model", default="ViT-B-32")
    parser.add_argument("--pretrained", default="laion2b_s34b_b79k")
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

    if args.command == "add":
        command_add(args)
    elif args.command == "rebuild":
        command_rebuild(args)
    else:
        command_search(args)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
