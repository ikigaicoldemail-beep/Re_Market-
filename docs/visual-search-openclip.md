# Visual Search With OpenCLIP

## Overview

Visual search uses:

- OpenCLIP `ViT-B-32`
- `openai` pretrained weights
- FAISS inner-product index over normalized image embeddings
- Laravel `listing_visual_index` rows to map FAISS IDs back to product images/products

This is local/free and does not require an API key.

## Environment

```env
VISUAL_SEARCH_PYTHON=python3
VISUAL_SEARCH_MODEL=ViT-B-32
VISUAL_SEARCH_PRETRAINED=openai
VISUAL_SEARCH_DEVICE=cpu
VISUAL_SEARCH_TIMEOUT=900
```

Docker Compose also sets:

```env
HF_HOME=/var/www/.cache/huggingface
TORCH_HOME=/var/www/.cache/torch
```

Production Dockerfile defaults:

```env
HF_HOME=/app/storage/app/model-cache/huggingface
TORCH_HOME=/app/storage/app/model-cache/torch
```

Persist those cache folders when possible.

## First Run

OpenCLIP downloads weights the first time the model loads. Warm it explicitly:

```bash
docker compose exec app python3 -c "import open_clip; m, _, _ = open_clip.create_model_and_transforms('ViT-B-32', pretrained='openai', device='cpu'); print(m.visual.output_dim)"
```

Expected output includes:

```text
512
```

## Rebuild Index

```bash
docker compose exec app php artisan visual-search:generate-embeddings
```

Run after:

- `migrate:fresh --seed`
- bulk product import
- changing product images externally
- changing `VISUAL_SEARCH_MODEL`
- changing `VISUAL_SEARCH_PRETRAINED`

## Runtime Notes

CPU OpenCLIP is accurate but slower than the old local color/edge features.

Expected behavior:

- First search in a fresh container is slow because the model loads.
- Rebuilding the index is CPU-heavy.
- Queue worker timeout should be at least `900` seconds.

## False Match Guard

The Python script returns CLIP concept labels for the query image. Laravel then filters obvious cross-type mistakes using product title/category/brand keywords.

Example:

- phone image: keeps iPhone/Galaxy/Redmi-style products
- phone image: filters laptop/fridge/speaker products

If a new category is added, update the concept prompts in `scripts/visual_search.py` and the keyword map in `app/Services/VisualSearchService.php`.

## Troubleshooting

No results:

- confirm product images exist and are public/readable
- rebuild the FAISS index
- check `storage/app/visual-search/faiss.index`
- check queue logs if indexing happens from product upload

Slow first run:

- expected if model weights are not cached
- persist `HF_HOME` and `TORCH_HOME`

Import errors:

```bash
docker compose exec app python3 -c "import open_clip, torch, faiss; print(open_clip.__version__, torch.__version__)"
```

Bad product matches:

- make sure demo/product images actually show the product
- rebuild the index after image fixes
- add or tune concept keywords
