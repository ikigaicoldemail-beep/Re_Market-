# Phase 6: Product Management

## What this phase builds

This phase upgrades the catalog layer from a prototype product table into a proper marketplace product module with:

- create product
- update product
- delete product
- list public products
- product details
- upload multiple product images
- set product condition
- set price, stock, location, and status
- seller own product listing
- public seller store page with products
- product share endpoint

## Main design choices

- product logic now lives in a dedicated `ProductService`
- validation uses Form Requests
- responses use API Resources
- product ownership is enforced with `ProductPolicy`
- product images are stored on the `product-images` filesystem disk
- public listings only expose `published` and `public` products

## Schema added in this phase

New tables:

- `categories`
- `product_conditions`
- `product_images`

Product table enhancements:

- `store_id`
- `category_id`
- `product_condition_id`
- `slug`
- `sku`
- `price_amount`
- `currency`
- `stock_quantity`
- `location_country_code`
- `location_state`
- `location_city`
- `status`
- `moderation_status`
- `visibility`
- `allow_offers`
- `published_at`
- soft deletes

## Endpoints added or upgraded

### Public product endpoints

- `GET /api/v1/products`
- `GET /api/v1/products/{product}`
- `GET /api/v1/products/{product}/share`
- `GET /api/v1/stores/{store}/products`

### Authenticated seller endpoints

- `GET /api/v1/me/products`
- `POST /api/v1/products`
- `PUT /api/v1/products/{product}`
- `DELETE /api/v1/products/{product}`
- `POST /api/v1/products/{product}/images`

## Seed data added

Basic seeders were added for:

- categories
- product conditions

This gives the API usable lookup data for early development and testing.

## Main implementation files

### Controller

- `app/Http/Controllers/Api/V1/ProductController.php`

### Service

- `app/Services/ProductService.php`

### Requests

- `app/Http/Requests/Product/*`

### Resources

- `app/Http/Resources/ProductResource.php`
- `app/Http/Resources/ProductImageResource.php`
- `app/Http/Resources/CategoryResource.php`
- `app/Http/Resources/ProductConditionResource.php`

### Models

- `app/Models/Product.php`
- `app/Models/ProductImage.php`
- `app/Models/Category.php`
- `app/Models/ProductCondition.php`

## Behavior notes

- sellers can only create products in their own stores
- only product owners can update or delete their products
- the first uploaded product image becomes the primary image automatically
- share responses include both a frontend share URL and the API detail URL
- public store pages only return published public products

## Current limits

- image resizing and optimization are not implemented yet
- category and condition management endpoints are not implemented yet
- product sharing is a response helper now; social platform publishing comes in later phases
- inventory reservation is not implemented yet because checkout belongs to Phase 7

## Outcome

At the end of Phase 6, the backend has a real product catalog API that is ready to feed the next commerce phase.
