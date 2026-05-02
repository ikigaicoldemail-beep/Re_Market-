# Phase 9: AI Image Similarity Search

## What this phase builds

This phase adds the backend foundation for image-based product similarity search.

Implemented pieces:

- similarity search request logging
- product image embedding storage
- AI embedding provider contract
- queue job for product image embedding generation
- similarity search service
- API endpoint for similarity lookup

## Recommended implementation options

### Option 1: External embedding API + MySQL metadata

Use an external provider to generate embeddings, store vectors in MySQL JSON, and compute similarity in Laravel for early-stage catalogs.

Pros:

- fastest MVP
- minimal infrastructure
- easy to replace later

Cons:

- not ideal for very large catalogs
- similarity search gets expensive as volume grows

### Option 2: External embedding API + vector database

Generate embeddings externally but store/query vectors in a vector database.

Pros:

- scalable nearest-neighbor search
- better latency at scale

Cons:

- more infrastructure
- more moving parts during MVP

### Option 3: Self-hosted embeddings pipeline

Run your own model pipeline and vector search infra.

Pros:

- maximum control
- no per-call vendor dependency

Cons:

- significantly higher engineering and ops cost

## Best MVP option

For this project, the best MVP option is:

- external embedding service
- Laravel orchestration
- MySQL vector metadata storage first
- vector DB later when catalog volume justifies it

That is exactly how this phase is structured.

## What was implemented in code

### Schema

New tables:

- `ai_search_logs`
- `product_image_embeddings`

### Models

- `app/Models/AiSearchLog.php`
- `app/Models/ProductImageEmbedding.php`

### Provider abstraction

- `app/Contracts/AiImageEmbeddingClientInterface.php`

### Current MVP provider

- `app/Integrations/Ai/FakeImageEmbeddingClient.php`

This is a deterministic placeholder provider that keeps the architecture working until a real AI vendor is connected.

### Services and jobs

- `app/Services/AiEmbeddingService.php`
- `app/Services/AiSimilaritySearchService.php`
- `app/Jobs/GenerateProductImageEmbeddingJob.php`

### Controller and request

- `app/Http/Controllers/Api/V1/AiSimilaritySearchController.php`
- `app/Http/Requests/Ai/ImageSimilaritySearchRequest.php`

## How the current Laravel integration works

### Product image indexing

1. Seller uploads product images
2. `ProductService` dispatches `GenerateProductImageEmbeddingJob`
3. The job calls `AiEmbeddingService`
4. The embedding service uses the configured provider contract
5. The resulting vector is stored in `product_image_embeddings`
6. `product_images.ai_embedding_status` is updated

### Similarity search

1. User calls `POST /api/v1/ai/similarity-search`
2. Request can use:
   - uploaded image
   - `product_image_id`
   - `product_id`
3. Laravel resolves the query vector
4. Candidate products are loaded
5. Stored embeddings are compared using cosine similarity
6. If a source product is known, heuristic boosts are added for:
   - same category
   - same condition
   - nearby price range
7. Results are ranked and returned
8. Request/response metadata is written to `ai_search_logs`

## Current endpoint

- `POST /api/v1/ai/similarity-search`

## Request options

### Upload a query image

```json
{
  "top_k": 8
}
```

Use multipart form-data with:

- `image`
- optional `top_k`

### Search using an indexed product image

```json
{
  "product_image_id": 12,
  "top_k": 8
}
```

### Search using a product’s primary image

```json
{
  "product_id": 55,
  "top_k": 8
}
```

## Example response shape

```json
{
  "message": "Similarity search completed.",
  "log": {
    "id": 1,
    "status": "completed",
    "provider": "fake-image-embedding",
    "top_k": 8,
    "result_count": 8
  },
  "products": [
    {
      "id": 99,
      "title": "Used iPhone 13",
      "similarity_score": 0.9132
    }
  ]
}
```

## Operational notes

- the current provider is intentionally a placeholder
- this architecture is ready for a real provider swap via the contract binding in `AppServiceProvider`
- AI search requests are audit-friendly because logs persist even when failures happen
- the queue name used for embedding generation is `ai`

## How to upgrade this to a real AI provider

Replace the placeholder provider with a real implementation of:

- `AiImageEmbeddingClientInterface`

Examples of what a real client would do:

1. upload or reference the image to an AI API
2. receive an embedding vector
3. return provider name, model, version, and vector

Then update the container binding in `AppServiceProvider`.

## Production recommendation

When product volume grows:

1. keep Laravel as orchestrator
2. keep `ai_search_logs` in MySQL
3. move heavy vector search to a vector database
4. retain `product_image_embeddings` either as a cache/reference layer or shrink it to metadata only

## Outcome

At the end of Phase 9, the backend has a real AI similarity-search architecture with logs, indexing, search orchestration, and a replaceable provider boundary, ready for a real embedding vendor in a later integration pass.
