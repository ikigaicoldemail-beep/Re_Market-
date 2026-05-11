# Marketplace Backend API - Endpoints Documentation

**Last Updated:** May 11, 2026  
**API Version:** v1  
**Base URL:** `{base_url}/api`

---

## Table of Contents
1. [Authentication](#authentication)
2. [Address Management](#address-management)
3. [Product Management](#product-management)
4. [Profile Management](#profile-management)
5. [Headers & Authorization](#headers--authorization)

---

## Authentication

### 1. Register as Admin
**Endpoint:** `POST /register`

**Body (JSON):**
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "SecureP@ssw0rd",
  "password_confirmation": "SecureP@ssw0rd",
  "role": "admin"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully.",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "status": "active"
  }
}
```

---

### 2. Login
**Endpoint:** `POST /login`

**Body (JSON):**
```json
{
  "email": "admin@example.com",
  "password": "SecureP@ssw0rd"
}
```

**Response (200):**
```json
{
  "message": "Login successful.",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

---

### 3. Logout
**Endpoint:** `POST /auth/logout`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
  "message": "Logout successful."
}
```

---

## Address Management

### 1. Create Address
**Endpoint:** `POST /addresses`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_jwt_token>
```

**Body (JSON):**
```json
{
  "label": "Home",
  "recipient_name": "John Doe",
  "phone": "+1234567890",
  "country_code": "US",
  "state": "California",
  "city": "Los Angeles",
  "postal_code": "90001",
  "address_line_1": "123 Main Street",
  "address_line_2": "Apt 4B",
  "landmark": "Near the park",
  "type": "shipping",
  "is_default": true
}
```

**Response (201):**
```json
{
  "message": "Address created successfully.",
  "address": {
    "id": 1,
    "user_id": 1,
    "label": "Home",
    "recipient_name": "John Doe",
    "phone": "+1234567890",
    "country_code": "US",
    "state": "California",
    "city": "Los Angeles",
    "postal_code": "90001",
    "address_line_1": "123 Main Street",
    "address_line_2": "Apt 4B",
    "landmark": "Near the park",
    "type": "shipping",
    "is_default": true,
    "created_at": "2026-05-11T10:00:00Z"
  }
}
```

---

### 2. Get All Addresses
**Endpoint:** `GET /addresses`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
  "addresses": [
    {
      "id": 1,
      "label": "Home",
      "recipient_name": "John Doe",
      "type": "shipping",
      "is_default": true
    }
  ]
}
```

---

### 3. Update Address
**Endpoint:** `PUT /addresses/{address_id}`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_jwt_token>
```

**Body (JSON):**
```json
{
  "label": "Home Updated",
  "recipient_name": "John Doe",
  "phone": "+1234567890",
  "address_line_1": "123 Main Street Updated"
}
```

**Response (200):**
```json
{
  "message": "Address updated successfully.",
  "address": { ... }
}
```

---

### 4. Delete Address
**Endpoint:** `DELETE /addresses/{address_id}`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
  "message": "Address deleted successfully."
}
```

---

## Product Management

### 1. Create Product
**Endpoint:** `POST /products`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_jwt_token>
```

**Body (JSON - Full):**
```json
{
  "store_id": 1,
  "category_id": 5,
  "product_condition_id": 2,
  "title": "Vintage Leather Jacket",
  "slug": "vintage-leather-jacket",
  "sku": "JACKET-001",
  "description": "Beautiful vintage leather jacket in excellent condition. Well-maintained, no tears or stains. Perfect for casual or formal occasions.",
  "price_amount": 4599,
  "currency": "USD",
  "stock_quantity": 3,
  "location_country_code": "US",
  "location_state": "California",
  "location_city": "Los Angeles",
  "status": "draft",
  "visibility": "public",
  "allow_offers": true,
  "schedule_at": "2026-05-15T10:00:00Z",
  "auto_post": "facebook,instagram"
}
```

**Body (JSON - Minimal Required Fields):**
```json
{
  "store_id": 1,
  "title": "Vintage Leather Jacket",
  "description": "Beautiful vintage leather jacket in excellent condition",
  "price_amount": 4599
}
```

**Response (201):**
```json
{
  "message": "Product created successfully.",
  "product": {
    "id": 1,
    "title": "Vintage Leather Jacket",
    "slug": "vintage-leather-jacket",
    "price_amount": 4599,
    "currency": "USD",
    "status": "draft",
    "visibility": "public",
    "store_id": 1,
    "user_id": 1,
    "created_at": "2026-05-11T10:00:00Z"
  }
}
```

---

### 2. Upload Product Images
**Endpoint:** `POST /products/{product_id}/images`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Body:** (form-data - NOT JSON)
- **Key:** `images[]` (type: File) - Select multiple image files
- Max 10 images
- Supported formats: JPG, JPEG, PNG, WebP
- Max 5MB per image

**Postman Setup:**
1. Select `Body` → `form-data`
2. Add key `images[]` → Type: File
3. Click "Select Files" and pick your images
4. Add more `images[]` keys for additional photos
5. Click Send

**Response (200):**
```json
{
  "message": "Product images uploaded successfully.",
  "product": {
    "id": 1,
    "title": "Vintage Leather Jacket",
    "images": [
      {
        "id": 1,
        "product_id": 1,
        "path": "products/image1.jpg",
        "sort_order": 1,
        "is_primary": true
      },
      {
        "id": 2,
        "product_id": 1,
        "path": "products/image2.jpg",
        "sort_order": 2,
        "is_primary": false
      }
    ]
  }
}
```

---

### 3. Get All Products (Public)
**Endpoint:** `GET /products`

**Query Parameters:**
- `search` - Search by title or description
- `category_id` - Filter by category
- `min_price` - Minimum price in cents
- `max_price` - Maximum price in cents
- `location_city` - Filter by city
- `sort` - Sort by: `latest` (default), `oldest`, `price_asc`, `price_desc`
- `per_page` - Items per page (default: 15)
- `page` - Page number

**Example:**
```
GET /products?search=jacket&sort=price_asc&per_page=20
```

**Response (200):**
```json
{
  "products": [
    {
      "id": 1,
      "title": "Vintage Leather Jacket",
      "price_amount": 4599,
      "status": "published",
      "images": [...]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### 4. Get Product Details
**Endpoint:** `GET /products/{product_id}`

**Headers:**
```
Authorization: Bearer <your_jwt_token> (optional, required if unpublished)
```

**Response (200):**
```json
{
  "product": {
    "id": 1,
    "title": "Vintage Leather Jacket",
    "description": "Beautiful vintage leather jacket...",
    "price_amount": 4599,
    "currency": "USD",
    "stock_quantity": 3,
    "status": "published",
    "visibility": "public",
    "images": [...]
  }
}
```

---

### 5. Update Product
**Endpoint:** `PUT /products/{product_id}`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_jwt_token>
```

**Body (JSON - Partial Update):**
```json
{
  "title": "Updated Jacket Title",
  "price_amount": 3999,
  "stock_quantity": 2,
  "status": "published",
  "visibility": "public"
}
```

**Response (200):**
```json
{
  "message": "Product updated successfully.",
  "product": { ... }
}
```

---

### 6. Delete Product
**Endpoint:** `DELETE /products/{product_id}`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
  "message": "Product deleted successfully."
}
```

---

### 7. Get My Products (Owner Only)
**Endpoint:** `GET /my-products`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
  "products": [
    {
      "id": 1,
      "title": "Vintage Leather Jacket",
      "status": "draft",
      "user_id": 1
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

---

## Profile Management

### 1. Get My Profile
**Endpoint:** `GET /me`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "user",
    "status": "active",
    "profile": {
      "id": 1,
      "username": "johndoe",
      "avatar_path": "avatars/xyz123.jpg",
      "cover_path": "covers/abc456.jpg",
      "bio": "Vintage clothing collector | Second-hand enthusiast",
      "gender": "Male",
      "date_of_birth": "1990-01-15",
      "country_code": "US",
      "state": "California",
      "city": "Los Angeles",
      "profile_visibility": "public",
      "is_seller": false
    }
  }
}
```

---

### 2. Update Profile
**Endpoint:** `PUT /me`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer <your_jwt_token>
```

**Body (JSON):**
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+1234567890",
  "username": "johndoe",
  "bio": "Vintage clothing collector | Second-hand enthusiast",
  "gender": "Male",
  "date_of_birth": "1990-01-15",
  "country_code": "US",
  "state": "California",
  "city": "Los Angeles",
  "profile_visibility": "public"
}
```

**Response (200):**
```json
{
  "message": "Profile updated successfully.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "profile": { ... }
  }
}
```

---

### 3. Upload Avatar
**Endpoint:** `POST /me/avatar`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Body:** (form-data - NOT JSON)
- **Key:** `image` (type: File)
- Select one image file
- Supported formats: JPG, JPEG, PNG, WebP
- Max 5MB

**Postman Setup:**
1. Select `Body` → `form-data`
2. Add key `image` → Type: File
3. Click "Select File" and pick your avatar image
4. Click Send

**Response (200):**
```json
{
  "message": "Avatar uploaded successfully.",
  "user": {
    "id": 1,
    "profile": {
      "avatar_path": "avatars/xyz123.jpg"
    }
  }
}
```

---

### 4. Upload Cover Image
**Endpoint:** `POST /me/cover`

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Body:** (form-data - NOT JSON)
- **Key:** `image` (type: File)
- Select one image file
- Supported formats: JPG, JPEG, PNG, WebP
- Max 5MB

**Postman Setup:**
1. Select `Body` → `form-data`
2. Add key `image` → Type: File
3. Click "Select File" and pick your cover image
4. Click Send

**Response (200):**
```json
{
  "message": "Cover image uploaded successfully.",
  "user": {
    "id": 1,
    "profile": {
      "cover_path": "covers/abc456.jpg"
    }
  }
}
```

---

## Headers & Authorization

### Standard Headers

**All Authenticated Endpoints Require:**
```
Content-Type: application/json
Authorization: Bearer <JWT_TOKEN>
```

### How to Get JWT Token

1. Call Login or Register endpoint
2. Copy the `token` from response
3. Use it in `Authorization` header for all authenticated requests

### Token Format
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

---

## Common Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### 404 Not Found
```json
{
  "message": "Resource not found."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

---

## Field Descriptions

### Product Fields
- `store_id` ✅ Required – Your store ID
- `title` ✅ Required – Product name (max 255 characters)
- `description` ✅ Required – Product details (max 10,000 characters)
- `price_amount` ✅ Required – Price in cents (e.g., 4599 = $45.99)
- `category_id` – Product category ID
- `product_condition_id` – Condition: new, like-new, excellent, good, fair, poor
- `sku` – Stock keeping unit (unique, max 255 characters)
- `slug` – URL-friendly name (unique, max 255 characters)
- `stock_quantity` – Number available (default: 1)
- `currency` – 3-letter code (USD, EUR, GBP, etc. default: USD)
- `location_country_code` – 2-letter country code (US, CA, UK, etc.)
- `location_state` – State/province name
- `location_city` – City name
- `status` – draft, pending, published, sold, inactive, archived
- `visibility` – public, followers_only, private
- `allow_offers` – Accept buyer offers (true/false)
- `schedule_at` – Schedule post date/time (ISO 8601 format)
- `auto_post` – Social platforms: facebook, instagram, tiktok (comma-separated)

### Address Fields
- `recipient_name` ✅ Required – Recipient name
- `phone` ✅ Required – Phone number
- `country_code` ✅ Required – 2-letter country code
- `state` ✅ Required – State/province
- `city` ✅ Required – City
- `address_line_1` ✅ Required – Street address
- `label` – Label for address (e.g., "Home", "Office", "Billing")
- `postal_code` – ZIP/postal code
- `address_line_2` – Apartment/suite number (optional)
- `landmark` – Nearby reference point
- `type` – "shipping" or "billing" (default: "shipping")
- `is_default` – Set as default address (true/false)

### Profile Fields
- `name` – Full name
- `email` – Email address
- `phone` – Phone number
- `username` – Unique username (max 100 characters)
- `bio` – Short biography (max 2,000 characters)
- `gender` – Gender value
- `date_of_birth` – Date in YYYY-MM-DD format
- `country_code` – 2-letter country code
- `state` – State/province
- `city` – City
- `profile_visibility` – "public" or "private"

---

## Base URL
- **Development:** `http://localhost:8000/api`
- **Production:** `https://api.marketplace.com/api`

---

## Support
For issues or questions, contact the backend development team.

**Last Updated:** May 11, 2026
