#!/usr/bin/env python3
import argparse
import json
import os
import sys
from pathlib import Path

import faiss
import numpy as np
from PIL import Image, ImageFilter


VECTOR_DIM = 280


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


def image_vector(path):
    image = Image.open(path).convert("RGB")

    small = image.resize((16, 16), Image.Resampling.LANCZOS)
    gray = small.convert("L")
    gray_values = np.asarray(gray, dtype="float32").reshape(-1) / 255.0

    hist_values = []
    for channel in image.resize((96, 96), Image.Resampling.LANCZOS).split():
        hist = np.asarray(channel.histogram(), dtype="float32").reshape(8, 32).sum(axis=1)
        total = float(hist.sum()) or 1.0
        hist_values.extend((hist / total).tolist())

    edges = gray.filter(ImageFilter.FIND_EDGES)
    edge_values = np.asarray(edges, dtype="float32").reshape(-1) / 255.0

    vector = np.concatenate([
        np.asarray(hist_values, dtype="float32"),
        gray_values,
    ])

    # Blend in a tiny edge/shape signal without changing vector size.
    vector[24:] = (vector[24:] * 0.8) + (edge_values * 0.2)

    norm = np.linalg.norm(vector)
    if norm > 0:
        vector = vector / norm

    return vector.astype("float32")


def embed_paths(paths, batch_size):
    vectors = []
    valid_paths = []
    for path in paths:
        try:
            vectors.append(image_vector(path))
            valid_paths.append(path)
        except Exception:
            continue

    if not vectors:
        return np.empty((0, VECTOR_DIM), dtype="float32"), []

    return np.vstack(vectors), valid_paths


def make_index(dim=VECTOR_DIM):
    return faiss.IndexIDMap2(faiss.IndexFlatIP(dim))


def read_index(path, dim=VECTOR_DIM):
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
    vectors, valid_paths = embed_paths([row["path"] for row in rows], args.batch_size)
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
    vectors, valid_paths = embed_paths([row["path"] for row in rows], args.batch_size)
    by_path = {row["path"]: row for row in rows}
    indexed_rows = [by_path[path] for path in valid_paths]

    index = make_index(vectors.shape[1] if vectors.shape[0] > 0 else VECTOR_DIM)
    if vectors.shape[0] > 0:
        ids = np.array([int(row["faiss_id"]) for row in indexed_rows], dtype="int64")
        index.add_with_ids(vectors, ids)

    write_index(index, args.index)
    print(json.dumps({"items": indexed_rows, "failed": len(rows) - len(indexed_rows)}))


def command_search(args):
    if not os.path.exists(args.index):
        print(json.dumps({"matches": []}))
        return

    vectors, _ = embed_paths([args.image], 1)
    if vectors.shape[0] == 0:
        print(json.dumps({"matches": []}))
        return

    index = faiss.read_index(args.index)
    if index.d != vectors.shape[1] or index.ntotal == 0:
        print(json.dumps({"matches": []}))
        return

    scores, ids = index.search(vectors, args.limit)
    matches = [
        {"faiss_id": int(faiss_id), "score": float(score)}
        for faiss_id, score in zip(ids[0], scores[0])
        if int(faiss_id) >= 0
    ]
    print(json.dumps({"matches": matches}))


def parse_args():
    parser = argparse.ArgumentParser(description="Local image-feature + FAISS visual search helper")
    parser.add_argument("command", choices=["add", "rebuild", "search"])
    parser.add_argument("--index", required=True)
    parser.add_argument("--manifest")
    parser.add_argument("--image")
    parser.add_argument("--limit", type=int, default=24)
    parser.add_argument("--batch-size", type=int, default=64)
    parser.add_argument("--model", default="local-image-features")
    parser.add_argument("--pretrained", default="none")
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
