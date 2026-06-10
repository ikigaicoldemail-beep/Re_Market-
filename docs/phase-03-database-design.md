# Phase 3: Database Design

## 1. Database Design Goal

This phase defines the production-ready MySQL schema for the marketplace backend. The purpose is to establish:

- core transactional tables
- social and discovery tables
- chat tables
- AI logging tables
- status fields and indexes
- clear relationship rules

This schema is the target design. It is intentionally more complete than the current prototype migrations in the repository.

## 2. General Database Conventions

### 2.1 Primary Keys

- Use `bigint unsigned` auto-increment `id` as the primary key for most tables.

### 2.2 Foreign Keys

- Use `foreignId()` with constrained references where possible.
- Use `nullOnDelete()` for optional relationships.
- Use `cascadeOnDelete()` only for dependent child records that should never survive parent deletion.

### 2.3 Timestamps

- Most tables should include:
  - `created_at`
  - `updated_at`

### 2.4 Soft Deletes

Use `softDeletes()` on business-critical user-facing tables where recovery or audit is useful:

- users
- stores
- products
- addresses

Optional later:

- social_posts
- scheduled_posts

### 2.5 Status Fields

Prefer string-based status columns with application enums over MySQL native enum for flexibility.

Examples:

- `status`
- `payment_status`
- `order_status`
- `publish_status`

### 2.6 Money Fields

Use integer minor units for prices:

- `price_amount`
- `subtotal_amount`
- `shipping_amount`

This avoids floating-point issues.

### 2.7 Common Audit Fields

Where useful, include:

- `created_by`
- `updated_by`
- `published_at`
- `deleted_at`

## 3. Entity Relationship Summary

High-level relationships:

- A `user` has one `user_profile`
- A `user` has many `addresses`
- A `user` has many `stores`
- A `store` has many `products`
- A `product` has many `product_images`
- A `product` belongs to one `category`
- A `product` belongs to one `product_condition`
- A `user` has one active `cart`
- A `cart` has many `cart_items`
- A `user` has many `orders`
- An `order` has many `order_items`
- An `order` has many `payments`
- A `conversation` has many `chat_messages`
- A `conversation` has many participants
- A `user` has many `social_accounts`
- A `product` can produce many `social_posts`
- A `scheduled_post` belongs to a `social_post`

## 4. Detailed Table Design

## 4.1 `users`

### Purpose

Stores account-level identity and authentication data.

### Columns

- `id` PK
- `name` string
- `email` string unique
- `phone` string nullable unique
- `password` string
- `email_verified_at` timestamp nullable
- `phone_verified_at` timestamp nullable
- `status` string default `active`
- `role` string default `user`
- `last_login_at` timestamp nullable
- `remember_token` string nullable
- `created_at`
- `updated_at`
- `deleted_at` nullable

### Primary key

- `id`

### Foreign keys

- none

### Indexes

- unique: `email`
- unique: `phone`
- index: `status`
- index: `role`

### Status values

- `active`
- `suspended`
- `pending_verification`
- `banned`

### Role values

- `user`
- `seller`
- `admin`
- `super_admin`

## 4.2 `user_profiles`

### Purpose

Stores extended profile and seller-facing public profile information.

### Columns

- `id` PK
- `user_id` FK unique
- `username` string nullable unique
- `avatar_path` string nullable
- `cover_path` string nullable
- `bio` text nullable
- `gender` string nullable
- `date_of_birth` date nullable
- `country_code` string nullable
- `city` string nullable
- `state` string nullable
- `default_store_id` FK nullable
- `is_seller` boolean default false
- `profile_visibility` string default `public`
- `created_at`
- `updated_at`

### Primary key

- `id`

### Foreign keys

- `user_id` references `users.id`
- `default_store_id` references `stores.id` nullable

### Indexes

- unique: `user_id`
- unique: `username`
- index: `is_seller`
- index: `profile_visibility`

### Visibility values

- `public`
- `private`

## 4.3 `stores`

### Purpose

Represents a seller page/storefront.

### Columns

- `id` PK
- `user_id` FK
- `name` string
- `slug` string unique
- `logo_path` string nullable
- `banner_path` string nullable
- `description` text nullable
- `contact_email` string nullable
- `contact_phone` string nullable
- `country_code` string nullable
- `state` string nullable
- `city` string nullable
- `address_line` string nullable
- `status` string default `active`
- `is_verified` boolean default false
- `followers_count` unsigned integer default 0
- `published_at` timestamp nullable
- `created_at`
- `updated_at`
- `deleted_at` nullable

### Foreign keys

- `user_id` references `users.id`

### Indexes

- unique: `slug`
- index: `user_id`
- index: `status`
- index: `is_verified`
- index: `city`

### Status values

- `draft`
- `active`
- `suspended`
- `archived`

## 4.4 `categories`

### Purpose

Product classification.

### Columns

- `id` PK
- `parent_id` FK nullable
- `name` string
- `slug` string unique
- `description` text nullable
- `icon_path` string nullable
- `status` string default `active`
- `sort_order` integer default 0
- `created_at`
- `updated_at`

### Foreign keys

- `parent_id` self-reference to `categories.id`

### Indexes

- unique: `slug`
- index: `parent_id`
- index: `status`
- index: `sort_order`

### Status values

- `active`
- `inactive`

## 4.5 `product_conditions`

### Purpose

Normalizes reusable second-hand item conditions.

### Columns

- `id` PK
- `name` string unique
- `slug` string unique
- `description` text nullable
- `rank` unsigned tiny integer
- `created_at`
- `updated_at`

### Indexes

- unique: `name`
- unique: `slug`
- index: `rank`

### Example values

- `new`
- `like_new`
- `good`
- `fair`
- `poor`

## 4.6 `products`

### Purpose

Stores product listings.

### Columns

- `id` PK
- `user_id` FK
- `store_id` FK
- `category_id` FK nullable
- `product_condition_id` FK nullable
- `title` string
- `slug` string unique
- `sku` string nullable unique
- `description` longText
- `price_amount` bigint unsigned
- `currency` char(3) default `USD` or chosen platform currency
- `stock_quantity` unsigned integer default 1
- `location_country_code` string nullable
- `location_state` string nullable
- `location_city` string nullable
- `address_visibility` string default `city_only`
- `status` string default `draft`
- `moderation_status` string default `approved`
- `visibility` string default `public`
- `is_featured` boolean default false
- `allow_offers` boolean default true
- `published_at` timestamp nullable
- `expires_at` timestamp nullable
- `meta_title` string nullable
- `meta_description` text nullable
- `created_at`
- `updated_at`
- `deleted_at` nullable

### Foreign keys

- `user_id` references `users.id`
- `store_id` references `stores.id`
- `category_id` references `categories.id`
- `product_condition_id` references `product_conditions.id`

### Indexes

- unique: `slug`
- unique: `sku`
- index: `user_id`
- index: `store_id`
- index: `category_id`
- index: `product_condition_id`
- index: `status`
- index: `moderation_status`
- index: `visibility`
- index: `published_at`
- composite: `(status, moderation_status, published_at)`
- composite: `(category_id, status, price_amount)`
- composite: `(store_id, status, published_at)`

### Status values

- `draft`
- `pending`
- `published`
- `sold`
- `inactive`
- `archived`

### Moderation values

- `pending_review`
- `approved`
- `rejected`

### Visibility values

- `public`
- `followers_only`
- `private`

### Address visibility values

- `city_only`
- `state_only`
- `full`
- `hidden`

## 4.7 `product_images`

### Purpose

Stores multiple images per product.

### Columns

- `id` PK
- `product_id` FK
- `path` string
- `disk` string default `public`
- `mime_type` string nullable
- `file_size` unsigned bigint nullable
- `width` unsigned integer nullable
- `height` unsigned integer nullable
- `sort_order` unsigned integer default 0
- `is_primary` boolean default false
- `ai_embedding_status` string default `pending`
- `created_at`
- `updated_at`

### Foreign keys

- `product_id` references `products.id`

### Indexes

- index: `product_id`
- index: `is_primary`
- index: `sort_order`
- index: `ai_embedding_status`
- composite: `(product_id, sort_order)`

### AI embedding status values

- `pending`
- `processing`
- `completed`
- `failed`

## 4.8 `wishlists`

### Purpose

Represents saved/favorited products by user.

### Columns

- `id` PK
- `user_id` FK
- `product_id` FK
- `created_at`
- `updated_at`

### Foreign keys

- `user_id` references `users.id`
- `product_id` references `products.id`

### Indexes

- unique composite: `(user_id, product_id)`
- index: `product_id`

## 4.9 `carts`

### Purpose

Stores active shopping carts.

### Columns

- `id` PK
- `user_id` FK unique
- `status` string default `active`
- `currency` char(3)
- `subtotal_amount` bigint unsigned default 0
- `discount_amount` bigint unsigned default 0
- `shipping_amount` bigint unsigned default 0
- `total_amount` bigint unsigned default 0
- `checked_out_at` timestamp nullable
- `expires_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `user_id` references `users.id`

### Indexes

- unique: `user_id`
- index: `status`
- index: `expires_at`

### Status values

- `active`
- `checked_out`
- `abandoned`

## 4.10 `cart_items`

### Purpose

Stores products inside a cart.

### Columns

- `id` PK
- `cart_id` FK
- `product_id` FK
- `quantity` unsigned integer default 1
- `unit_price_amount` bigint unsigned
- `line_total_amount` bigint unsigned
- `created_at`
- `updated_at`

### Foreign keys

- `cart_id` references `carts.id`
- `product_id` references `products.id`

### Indexes

- unique composite: `(cart_id, product_id)`
- index: `product_id`

## 4.11 `addresses`

### Purpose

Stores shipping and billing addresses for users.

### Columns

- `id` PK
- `user_id` FK
- `label` string nullable
- `recipient_name` string
- `phone` string
- `country_code` string
- `state` string
- `city` string
- `postal_code` string nullable
- `address_line_1` string
- `address_line_2` string nullable
- `landmark` string nullable
- `type` string default `shipping`
- `is_default` boolean default false
- `created_at`
- `updated_at`
- `deleted_at` nullable

### Foreign keys

- `user_id` references `users.id`

### Indexes

- index: `user_id`
- index: `type`
- index: `is_default`

### Type values

- `shipping`
- `billing`

## 4.12 `orders`

### Purpose

Stores completed checkout records.

### Columns

- `id` PK
- `order_number` string unique
- `buyer_id` FK
- `store_id` FK nullable
- `address_id` FK nullable
- `status` string default `pending`
- `payment_status` string default `pending`
- `currency` char(3)
- `subtotal_amount` bigint unsigned
- `discount_amount` bigint unsigned default 0
- `shipping_amount` bigint unsigned default 0
- `total_amount` bigint unsigned
- `notes` text nullable
- `placed_at` timestamp nullable
- `paid_at` timestamp nullable
- `cancelled_at` timestamp nullable
- `completed_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `buyer_id` references `users.id`
- `store_id` references `stores.id` nullable
- `address_id` references `addresses.id` nullable

### Indexes

- unique: `order_number`
- index: `buyer_id`
- index: `store_id`
- index: `status`
- index: `payment_status`
- index: `placed_at`
- composite: `(buyer_id, created_at)`

### Order status values

- `pending`
- `awaiting_payment`
- `paid`
- `processing`
- `shipped`
- `delivered`
- `cancelled`
- `refunded`

### Payment status values

- `pending`
- `authorized`
- `paid`
- `failed`
- `refunded`
- `expired`

## 4.13 `order_items`

### Purpose

Stores order line items with price snapshot data.

### Columns

- `id` PK
- `order_id` FK
- `product_id` FK nullable
- `seller_id` FK nullable
- `product_title` string
- `product_slug` string nullable
- `product_image_path` string nullable
- `product_condition_label` string nullable
- `quantity` unsigned integer
- `unit_price_amount` bigint unsigned
- `line_total_amount` bigint unsigned
- `fulfillment_status` string default `pending`
- `created_at`
- `updated_at`

### Foreign keys

- `order_id` references `orders.id`
- `product_id` references `products.id` nullable
- `seller_id` references `users.id` nullable

### Indexes

- index: `order_id`
- index: `product_id`
- index: `seller_id`
- index: `fulfillment_status`

### Fulfillment status values

- `pending`
- `processing`
- `shipped`
- `delivered`
- `cancelled`
- `returned`

## 4.14 `payments`

### Purpose

Stores payment attempts and payment provider state transitions.

### Columns

- `id` PK
- `order_id` FK
- `user_id` FK
- `provider` string
- `provider_reference` string nullable
- `payment_method` string nullable
- `status` string default `pending`
- `currency` char(3)
- `amount` bigint unsigned
- `provider_payload` json nullable
- `failure_code` string nullable
- `failure_message` text nullable
- `paid_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `order_id` references `orders.id`
- `user_id` references `users.id`

### Indexes

- index: `order_id`
- index: `user_id`
- index: `provider`
- index: `status`
- index: `provider_reference`

### Status values

- `pending`
- `authorized`
- `paid`
- `failed`
- `refunded`
- `expired`

## 4.15 `conversations`

### Purpose

Stores chat threads between users, optionally linked to a product.

### Columns

- `id` PK
- `product_id` FK nullable
- `created_by` FK
- `type` string default `private`
- `last_message_id` FK nullable
- `last_message_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `product_id` references `products.id` nullable
- `created_by` references `users.id`
- `last_message_id` references `chat_messages.id` nullable

### Indexes

- index: `product_id`
- index: `created_by`
- index: `type`
- index: `last_message_at`

### Type values

- `private`
- `support`

## 4.16 `conversation_participants`

### Purpose

Maps users to conversations and stores read state.

### Columns

- `id` PK
- `conversation_id` FK
- `user_id` FK
- `joined_at` timestamp nullable
- `last_read_message_id` FK nullable
- `last_read_at` timestamp nullable
- `is_muted` boolean default false
- `created_at`
- `updated_at`

### Foreign keys

- `conversation_id` references `conversations.id`
- `user_id` references `users.id`
- `last_read_message_id` references `chat_messages.id` nullable

### Indexes

- unique composite: `(conversation_id, user_id)`
- index: `user_id`
- index: `last_read_at`

## 4.17 `chat_messages`

### Purpose

Stores conversation messages.

### Columns

- `id` PK
- `conversation_id` FK
- `sender_id` FK
- `type` string default `text`
- `body` text
- `attachment_path` string nullable
- `sent_at` timestamp nullable
- `edited_at` timestamp nullable
- `deleted_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `conversation_id` references `conversations.id`
- `sender_id` references `users.id`

### Indexes

- index: `conversation_id`
- index: `sender_id`
- index: `sent_at`
- composite: `(conversation_id, created_at)`

### Type values

- `text`
- `image`
- `system`

## 4.18 `social_accounts`

### Purpose

Stores connected social provider accounts for a user.

### Columns

- `id` PK
- `user_id` FK
- `platform` string
- `provider_user_id` string
- `provider_account_name` string nullable
- `access_token` text
- `refresh_token` text nullable
- `token_expires_at` timestamp nullable
- `scopes` json nullable
- `status` string default `active`
- `last_synced_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `user_id` references `users.id`

### Indexes

- index: `user_id`
- index: `platform`
- index: `status`
- unique composite: `(platform, provider_user_id)`

### Platform values

- `facebook`
- `tiktok`
- future providers later

### Status values

- `active`
- `expired`
- `revoked`
- `disconnected`

### Security note

Tokens should be encrypted at rest in the application layer.

## 4.19 `social_posts`

### Purpose

Represents outgoing product-based social publishing attempts or drafts.

### Columns

- `id` PK
- `user_id` FK
- `product_id` FK nullable
- `social_account_id` FK nullable
- `platform` string
- `caption` text nullable
- `media_payload` json nullable
- `status` string default `draft`
- `provider_post_id` string nullable
- `provider_response` json nullable
- `error_message` text nullable
- `posted_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `user_id` references `users.id`
- `product_id` references `products.id` nullable
- `social_account_id` references `social_accounts.id` nullable

### Indexes

- index: `user_id`
- index: `product_id`
- index: `social_account_id`
- index: `platform`
- index: `status`
- index: `posted_at`

### Status values

- `draft`
- `queued`
- `processing`
- `posted`
- `failed`
- `cancelled`

## 4.20 `scheduled_posts`

### Purpose

Stores scheduled publish jobs for social posts.

### Columns

- `id` PK
- `social_post_id` FK
- `scheduled_for` timestamp
- `status` string default `scheduled`
- `attempts` unsigned integer default 0
- `last_attempt_at` timestamp nullable
- `processed_at` timestamp nullable
- `cancelled_at` timestamp nullable
- `failure_reason` text nullable
- `created_at`
- `updated_at`

### Foreign keys

- `social_post_id` references `social_posts.id`

### Indexes

- index: `social_post_id`
- index: `scheduled_for`
- index: `status`
- composite: `(status, scheduled_for)`

### Status values

- `scheduled`
- `queued`
- `processing`
- `posted`
- `failed`
- `cancelled`

## 4.21 `shared_products`

### Purpose

Tracks manual marketplace share actions initiated by users.

### Columns

- `id` PK
- `user_id` FK
- `product_id` FK
- `platform` string nullable
- `destination` string nullable
- `status` string default `shared`
- `metadata` json nullable
- `shared_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `user_id` references `users.id`
- `product_id` references `products.id`

### Indexes

- index: `user_id`
- index: `product_id`
- index: `platform`
- index: `shared_at`

### Status values

- `shared`
- `failed`

## 4.22 `follows`

### Purpose

Tracks follow relationships for stores or sellers.

### Columns

- `id` PK
- `follower_user_id` FK
- `followable_type` string
- `followable_id` unsigned bigint
- `created_at`
- `updated_at`

### Foreign keys

- `follower_user_id` references `users.id`

### Indexes

- index: `follower_user_id`
- composite: `(followable_type, followable_id)`
- unique composite: `(follower_user_id, followable_type, followable_id)`

### Design note

This polymorphic design supports following:

- stores
- sellers

## 4.23 `notifications`

### Purpose

Stores in-app notifications.

### Columns

- `id` uuid or string PK if using Laravel default notifications table
- `type` string
- `notifiable_type` string
- `notifiable_id` unsigned bigint
- `data` json
- `read_at` timestamp nullable
- `created_at`
- `updated_at`

### Primary key

- `id`

### Indexes

- composite: `(notifiable_type, notifiable_id)`
- index: `read_at`
- index: `type`

### Recommendation

Use Laravel’s standard notifications table format.

## 4.24 `ai_search_logs`

### Purpose

Stores AI similarity search requests and result metadata.

### Columns

- `id` PK
- `user_id` FK nullable
- `product_id` FK nullable
- `product_image_id` FK nullable
- `query_image_path` string nullable
- `provider` string nullable
- `status` string default `pending`
- `embedding_version` string nullable
- `top_k` unsigned integer default 10
- `result_count` unsigned integer default 0
- `request_payload` json nullable
- `response_payload` json nullable
- `error_message` text nullable
- `searched_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `user_id` references `users.id` nullable
- `product_id` references `products.id` nullable
- `product_image_id` references `product_images.id` nullable

### Indexes

- index: `user_id`
- index: `product_id`
- index: `product_image_id`
- index: `provider`
- index: `status`
- index: `searched_at`

### Status values

- `pending`
- `processing`
- `completed`
- `failed`

## 4.25 Optional `product_image_embeddings`

### Purpose

Stores derived embedding metadata for product images.

### Columns

- `id` PK
- `product_image_id` FK
- `provider` string
- `embedding_model` string nullable
- `embedding_vector` longText or json nullable
- `vector_hash` string nullable
- `status` string default `completed`
- `generated_at` timestamp nullable
- `created_at`
- `updated_at`

### Foreign keys

- `product_image_id` references `product_images.id`

### Indexes

- index: `product_image_id`
- index: `provider`
- index: `status`
- index: `vector_hash`

### Recommendation

Keep this optional for MVP. If vectors become large or query-heavy, migrate vector storage to a dedicated service and keep only references here.

## 5. Pivot and Support Tables Not Explicitly Requested but Recommended

## 5.1 `cart_items`

Required because `carts` alone is not enough for commerce behavior.

## 5.2 `conversation_participants`

Required because `conversations` should support participant-based authorization and unread tracking.

## 5.3 `product_image_embeddings`

Recommended for AI traceability.

## 6. Important Relationship Rules

### Product ownership

- A product belongs to exactly one seller user
- A product belongs to exactly one store

### Order snapshotting

- `order_items` must preserve product title, price, image, and condition label at purchase time

### Chat authorization

- Users can only see conversations where they exist in `conversation_participants`

### Social posting durability

- `social_posts` records business intent
- `scheduled_posts` records deferred execution state
- failures should update status, not delete records

### AI traceability

- `ai_search_logs` should always keep a durable request/response trail

## 7. Indexing Strategy Notes

The most important read paths are:

- public product listing
- seller page products
- user conversations
- conversation messages
- orders by buyer
- due scheduled posts
- AI search logs by user/product

Priority indexes:

- `products(status, moderation_status, published_at)`
- `products(category_id, status, price_amount)`
- `conversation_participants(user_id)`
- `chat_messages(conversation_id, created_at)`
- `orders(buyer_id, created_at)`
- `scheduled_posts(status, scheduled_for)`

## 8. Current Prototype vs Target Schema

The current repository migrations are an early prototype and differ from the target production design in a few important ways:

- `products` currently lacks category, store, condition, stock, status, slug, moderation, and pricing structure
- current chat uses a direct `messages` table instead of normalized `conversations` plus participants plus messages
- social posting fields currently live on `products`, but should move to dedicated `social_posts` and `scheduled_posts`
- users currently need richer status and verification fields

We should treat the current migrations as temporary and refactor toward this target structure in the implementation phases.

## 9. Recommended Migration Order for Implementation

When we start coding schema changes, implement in this order:

1. users and supporting auth tables adjustments
2. user_profiles
3. stores
4. categories
5. product_conditions
6. products
7. product_images
8. addresses
9. wishlists
10. carts
11. cart_items
12. orders
13. order_items
14. payments
15. conversations
16. conversation_participants
17. chat_messages
18. social_accounts
19. social_posts
20. scheduled_posts
21. shared_products
22. follows
23. notifications
24. ai_search_logs
25. optional product_image_embeddings

## 10. Phase 3 Outcome

At the end of Phase 3, we now have:

- a complete target schema blueprint
- all major marketplace entities defined
- keys, foreign keys, indexes, and statuses documented
- a clear migration order for implementation

This is the foundation for Phase 4, where we prepare the Laravel project structure and environment for building these modules properly.
