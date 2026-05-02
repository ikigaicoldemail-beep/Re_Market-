# API Reference V1

## Base URL

- `/api/v1`

## Auth module

### `POST /auth/register`

Request:

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "1234567890",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

Validation:

- `name` required
- `email` required, unique
- `phone` optional, unique
- `password` required, confirmed, min 8 with letters and numbers
- `role` optional, defaults to `user`

Success response:

```json
{
  "message": "Registration completed successfully.",
  "token": "jwt-token",
  "token_type": "bearer",
  "user": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

Admin registration can use the same endpoint by sending `role` and `admin_key`:

```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "phone": "1234567891",
  "password": "Password123",
  "password_confirmation": "Password123",
  "role": "admin"
}
```

### `POST /auth/login`

Request:

```json
{
  "email": "jane@example.com",
  "password": "Password123"
}
```

Error cases:

- `422` invalid credentials

### `POST /auth/logout`

Auth required.

## Admin module

### `GET /admin/stores`

Admin auth required. Lists seller stores with seller details.

Optional query parameters:

- `seller_id`
- `status`
- `is_verified`
- `search`
- `per_page`

Example:

```text
GET /api/v1/admin/stores?seller_id=1&status=active
```

### `POST /auth/forgot-password`

Request:

```json
{
  "email": "jane@example.com"
}
```

### `POST /auth/reset-password`

Request:

```json
{
  "email": "jane@example.com",
  "token": "reset-token",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

## Profile and store module

### `GET /me`

Auth required.

### `PUT /me`

Request:

```json
{
  "name": "Jane Updated",
  "username": "jane-updated",
  "bio": "Seller of second-hand tech",
  "country_code": "US",
  "city": "Seattle",
  "profile_visibility": "public"
}
```

### `POST /stores`

Request:

```json
{
  "name": "Jane Resale Store",
  "slug": "jane-resale-store",
  "description": "Curated second-hand items",
  "contact_email": "shop@example.com",
  "city": "Seattle",
  "status": "active"
}
```

### `PUT /stores/{store}`

Auth required, owner only.

## Product module

### `GET /products`

Query params:

- `search`
- `store_id`
- `category_id`
- `product_condition_id`
- `min_price`
- `max_price`
- `location_city`
- `sort=latest|oldest|price_asc|price_desc`
- `per_page`

Response:

```json
{
  "products": [
    {
      "id": 1,
      "title": "Used Camera",
      "price_amount": 45000,
      "currency": "USD",
      "status": "published"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### `POST /products`

Auth required.

Request:

```json
{
  "store_id": 1,
  "category_id": 1,
  "product_condition_id": 1,
  "title": "Used Camera",
  "description": "Mirrorless camera in good condition",
  "price_amount": 45000,
  "currency": "USD",
  "stock_quantity": 1,
  "location_country_code": "US",
  "location_state": "California",
  "location_city": "Los Angeles",
  "status": "published",
  "visibility": "public",
  "allow_offers": true
}
```

Validation:

- must use own store
- valid category/condition if supplied
- `status` in allowed set
- `visibility` in allowed set

### `PUT /products/{product}`

Auth required, owner only.

### `DELETE /products/{product}`

Auth required, owner only.

### `POST /products/{product}/images`

Multipart form-data:

- `images[]` one to ten image files

### `GET /me/products`

Auth required.

### `GET /stores/{store}/products`

Public seller store page products.

### `GET /products/{product}/share`

Returns frontend/API share URLs.

## Commerce module

### Addresses

- `GET /addresses`
- `POST /addresses`
- `PUT /addresses/{address}`
- `DELETE /addresses/{address}`

Create request:

```json
{
  "label": "Home",
  "recipient_name": "Jane Doe",
  "phone": "1234567890",
  "country_code": "US",
  "state": "California",
  "city": "Los Angeles",
  "postal_code": "90001",
  "address_line_1": "123 Main Street",
  "type": "shipping",
  "is_default": true
}
```

### Wishlist

- `GET /wishlist`
- `POST /wishlist`
- `DELETE /wishlist/{product}`

Add request:

```json
{
  "product_id": 5
}
```

### Cart

- `GET /cart`
- `POST /cart/items`
- `PUT /cart/items/{cartItem}`
- `DELETE /cart/items/{cartItem}`
- `DELETE /cart`

Add request:

```json
{
  "product_id": 5,
  "quantity": 1
}
```

Error cases:

- adding unpublished product
- requested quantity exceeds stock

### Checkout

`POST /checkout`

Request:

```json
{
  "address_id": 1,
  "provider": "manual",
  "payment_method": "cash_on_delivery",
  "notes": "Please call before delivery"
}
```

Response:

```json
{
  "message": "Checkout completed successfully.",
  "order": {
    "id": 1,
    "order_number": "ORD-ABC123",
    "payment_status": "pending"
  }
}
```

### Orders

- `GET /orders`
- `GET /orders/{order}`
- `GET /orders/{order}/payment-status`

## Chat module

- `GET /conversations`
- `POST /conversations`
- `GET /conversations/unread-count`
- `GET /conversations/{conversation}/messages`
- `POST /conversations/{conversation}/messages`
- `POST /conversations/{conversation}/seen`

Start conversation request:

```json
{
  "recipient_user_id": 2,
  "product_id": 10
}
```

Send message request:

```json
{
  "body": "Is this item still available?",
  "type": "text"
}
```

## AI module

### `POST /ai/similarity-search`

Auth required.

Request options:

- multipart upload with `image`
- JSON with `product_image_id`
- JSON with `product_id`

Example:

```json
{
  "product_id": 12,
  "top_k": 8
}
```

Response includes:

- search log metadata
- ranked product results
- `similarity_score` when available

## Social module

### Social accounts

- `GET /social/accounts`
- `POST /social/accounts`
- `DELETE /social/accounts/{socialAccount}`

Connect request:

```json
{
  "platform": "facebook",
  "provider_user_id": "fb_user_123",
  "provider_account_name": "Jane Shop",
  "access_token": "token",
  "refresh_token": "refresh-token",
  "scopes": ["pages_manage_posts"]
}
```

### Social posts

- `GET /social/posts`
- `POST /social/posts`
- `GET /social/posts/{socialPost}`
- `POST /social/posts/{socialPost}/publish`

Create request:

```json
{
  "platform": "facebook",
  "product_id": 10,
  "social_account_id": 3,
  "caption": "Fresh listing now available",
  "publish_now": false
}
```

### Manual share tracking

`POST /products/share`

Request:

```json
{
  "product_id": 10,
  "platform": "facebook",
  "destination": "story"
}
```

## Scheduling module

- `GET /scheduled-posts`
- `POST /scheduled-posts`
- `GET /scheduled-posts/{scheduledPost}`
- `PUT /scheduled-posts/{scheduledPost}`
- `DELETE /scheduled-posts/{scheduledPost}`

Create request:

```json
{
  "social_post_id": 5,
  "scheduled_for": "2026-04-30 15:00:00"
}
```

Update request:

```json
{
  "scheduled_for": "2026-05-01 10:00:00"
}
```

## Common error responses

### Validation error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### Unauthenticated

```json
{
  "message": "Unauthenticated."
}
```

### Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

### Not found

```json
{
  "message": "Resource not found."
}
```
