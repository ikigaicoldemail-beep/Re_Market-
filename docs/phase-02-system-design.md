# Phase 2: System Design

## 1. Objective of the System Design

This phase defines the target backend architecture for a production-ready Laravel REST API marketplace. The goal is to keep the design clean enough for fast delivery, while making the system scalable for chat, e-commerce, AI similarity search, and social integrations.

This design intentionally moves beyond the current prototype routes/controllers in the repository and establishes the structure we will implement in later phases.

## 2. Architecture Style

### Recommended approach

Use a layered Laravel architecture with domain-oriented modules:

1. Transport layer
2. Application layer
3. Domain/business layer
4. Infrastructure layer
5. Persistence layer

In Laravel terms:

- Controllers handle HTTP only
- Form Requests handle validation
- API Resources shape responses
- Services/Actions handle business workflows
- Models represent persistence
- Policies handle authorization
- Jobs handle asynchronous workloads
- Integrations live behind contracts/interfaces

This is not strict hexagonal architecture, but it follows the same important principle: external concerns should not leak deeply into business logic.

## 3. High-Level Component Overview

### 3.1 Client Applications

Separate frontend clients can consume the API:

- Web frontend
- Mobile app
- Admin frontend
- Partner or internal tools

### 3.2 Laravel API Backend

The Laravel backend is the central orchestrator for:

- Authentication and authorization
- Marketplace business rules
- Product and store management
- Order and checkout logic
- Chat messaging persistence
- Social posting orchestration
- AI similarity request orchestration
- Notifications and background jobs

### 3.3 Core Infrastructure

- MySQL for transactional data
- Queue backend for async jobs
- File storage for product images
- Cache for hot reads and throttling
- Optional Redis for queues/cache/realtime support

### 3.4 External Services

- Social platform APIs
- Payment provider API
- Email service
- AI image embedding/vision API
- Optional vector database or search service

## 4. Recommended Module Breakdown

Organize the backend by business capability.

### 4.1 Identity Module

- Registration
- Login/logout
- Password reset
- Token refresh strategy if needed
- Roles and permissions

### 4.2 User Profile Module

- User profile data
- Avatar and bio
- Contact details
- Seller flag and profile completeness

### 4.3 Store Module

- Seller page/store creation
- Store branding and metadata
- Store public view
- Store status management

### 4.4 Catalog Module

- Products
- Product images
- Categories
- Conditions
- Product lifecycle statuses
- Moderation support

### 4.5 Discovery Module

- Search/filter/sort
- Seller page listings
- Related products
- Wishlist/favorites
- Follows

### 4.6 Commerce Module

- Carts
- Cart items
- Checkout
- Orders
- Order items
- Payments
- Shipping addresses
- Order tracking statuses

### 4.7 Chat Module

- Conversations
- Conversation participants
- Messages
- Seen status
- Unread counters

### 4.8 Social Integration Module

- Social account connection
- OAuth callback handling
- Social post composition
- Manual sharing
- Auto-posting
- Provider response/error logs

### 4.9 Scheduling Module

- Scheduled post records
- Publish windows
- Queue dispatch
- Retry/failure recording

### 4.10 AI Similarity Module

- Image upload intake
- Feature extraction / embedding
- Similarity lookup
- Ranking and result storage
- AI request logging

### 4.11 Notification Module

- In-app notifications
- Event-triggered notifications
- Optional email hooks

### 4.12 Admin and Audit Module

- Activity log support
- Moderation statuses
- Reportability
- Operational observability

## 5. Suggested Request Lifecycle

Typical API flow:

1. Request enters versioned API route
2. Middleware checks auth, throttling, and context
3. Controller delegates to Form Request and Service
4. Service executes business logic
5. Service uses models/repositories/integrations as needed
6. Transaction wraps critical write workflows
7. Events/jobs/notifications dispatched after success
8. API Resource returns normalized JSON response

This keeps controllers thin and testable.

## 6. Proposed Laravel Structure

Recommended application structure:

- `app/Http/Controllers/Api/V1/...`
- `app/Http/Requests/...`
- `app/Http/Resources/...`
- `app/Models/...`
- `app/Services/...`
- `app/Actions/...` for focused business operations
- `app/Contracts/...` for integration interfaces
- `app/Integrations/...` for external providers
- `app/Jobs/...`
- `app/Policies/...`
- `app/Events/...`
- `app/Listeners/...`
- `app/Notifications/...`
- `app/Enums/...`

For a project of this size, a hybrid structure works well:

- Domain-oriented folders for major capabilities
- Shared Laravel framework folders for common concerns

## 7. Main Entities and Relationships

### 7.1 Identity and User Area

- `users`
- `user_profiles`
- `addresses`

Relationships:

- One user has one profile
- One user has many addresses
- One user can own many stores
- One user can own many products

### 7.2 Seller/Store Area

- `stores`
- `store_followers` or `follows`

Relationships:

- One store belongs to one user
- One store has many products
- Many users can follow many stores

### 7.3 Catalog Area

- `products`
- `product_images`
- `categories`
- `product_conditions`
- `shared_products`

Relationships:

- One product belongs to one seller user
- One product belongs to one store
- One product belongs to one category
- One product belongs to one condition
- One product has many images

### 7.4 Discovery/Social Graph Area

- `wishlists`
- `favorites`
- `follows`

Relationships:

- Users can favorite many products
- Users can follow many stores or sellers

### 7.5 Commerce Area

- `carts`
- `cart_items`
- `orders`
- `order_items`
- `payments`

Relationships:

- One user has one active cart
- One cart has many cart items
- One order belongs to one buyer
- One order has many order items
- One order may have one or many payment attempts

### 7.6 Chat Area

- `conversations`
- `conversation_participants`
- `chat_messages`

Relationships:

- One conversation has many participants
- One conversation has many messages
- One message belongs to one sender

### 7.7 Social Publishing Area

- `social_accounts`
- `social_posts`
- `scheduled_posts`

Relationships:

- One user can connect many social accounts
- One product can generate many social posts
- One scheduled post can reference one social post draft

### 7.8 AI Area

- `ai_search_logs`
- optional `product_image_embeddings`

Relationships:

- AI logs belong to user and optionally product/image
- Product images can have derived embedding records

## 8. High-Level Design for Each Critical Feature

## 8.1 Chat System

### Goal

Allow buyers and sellers to exchange private messages safely around products and marketplace interactions.

### Recommended first version

Implement database-backed REST chat first:

- Create or fetch conversation between participants
- Send message into conversation
- List user conversations
- List messages in a conversation
- Mark messages as seen
- Return unread counts

### Core rules

- Only conversation participants can view/send messages
- Product context can be attached when conversation starts from a product page
- Messages are stored durably in MySQL
- Unread counts are computed or cached

### Future enhancement

Add Laravel broadcasting with Reverb or Pusher for:

- New message events
- Seen receipts
- Typing indicators

### Recommended model

- `conversations`
- `conversation_participants`
- `chat_messages`

This is better than a direct two-user message table because it scales to future system messages and optional group/admin support.

## 8.2 AI Image Similarity Search

### Goal

When a user uploads a product image, the platform should return visually similar marketplace items.

### High-level flow

1. User uploads image
2. Laravel stores the image
3. Laravel dispatches a job to extract image features or request embedding from an AI service
4. System compares the embedding with existing product image embeddings
5. Similar products are ranked and returned
6. Search request and result metadata are logged

### MVP recommendation

Use an external image embedding service, then store returned embeddings or derived similarity metadata.

Why this is best for MVP:

- Fastest to market
- No ML training pipeline required
- Keeps Laravel as orchestration layer
- Easy to swap providers later

### Storage options

Option A:
- Store embeddings in MySQL as JSON for early MVP
- Perform approximate or narrowed similarity checks in app/service layer

Option B:
- Store embedding references in MySQL and raw vectors in a vector database

Recommendation:

- Start with MySQL + external AI service if dataset is still small
- Move to vector DB once product volume grows

## 8.3 Social Platform Posting

### Goal

Allow users to connect social accounts and publish marketplace products to external channels.

### High-level flow

1. User connects social account via OAuth
2. Access/refresh tokens are stored securely
3. User creates a social post draft from product data
4. User chooses post now or schedule later
5. Laravel dispatches a job to publish to provider
6. Provider result is stored with status and response logs

### Design principle

Do not place provider-specific logic directly in controllers.

Use:

- `SocialPlatformClientInterface`
- `FacebookClient`
- `TikTokClient`
- `SocialPostingService`

This allows adding more providers later without rewriting core business logic.

### Important note

Different platforms have different permissions and API limitations. The design must support:

- account type validation
- permission scope validation
- provider-specific payload transformations
- webhook/result logging later if needed

## 8.4 Scheduled Posting

### Goal

Publish social content immediately or at a future time.

### High-level flow

1. User submits a social post request
2. If `post_now`, dispatch publish job immediately
3. If `scheduled_at`, create scheduled post record
4. Scheduler/queue worker picks up due records
5. Publish job runs
6. Status updated to queued, processing, posted, failed, or cancelled

### Laravel implementation approach

- `scheduled_posts` table stores intent and state
- `php artisan schedule:run` triggers periodic dispatcher command
- Dispatcher finds due items and sends queue jobs
- Jobs handle retries and provider errors

## 8.5 E-commerce Flow

### Goal

Support the full buyer purchase journey.

### High-level flow

1. Buyer browses and filters products
2. Buyer adds product to cart
3. Buyer provides or selects shipping address
4. Buyer checks out
5. Order is created transactionally
6. Payment record is created
7. Product stock/reservation is updated
8. Order status moves through fulfillment states

### Recommended order states

- pending
- awaiting_payment
- paid
- processing
- shipped
- delivered
- cancelled
- refunded

### Recommended payment states

- pending
- authorized
- paid
- failed
- refunded
- expired

### Important design choice

Orders should snapshot pricing and product details at purchase time in `order_items`. Do not rely on live product data after checkout.

## 9. Third-Party Services and APIs

## 9.1 Required or Likely Services

- JWT auth package
- Payment gateway
- Email provider
- Cloud/object file storage
- AI image embedding or computer vision API
- Social platform APIs

## 9.2 Recommended Integration Categories

### Authentication

- `tymon/jwt-auth` is already installed

### Social APIs

- Facebook Graph API
- TikTok developer APIs

### AI Services

Candidates depend on budget and quality requirements:

- OpenAI vision/embedding-adjacent workflow through custom pipeline
- Replicate-hosted image models
- AWS Rekognition or similar vision services
- Dedicated vector/search provider if scaling later

The final provider should be abstracted behind an internal contract.

### Payments

Examples:

- Stripe
- PayPal
- local provider for target market

### Storage

- Local storage for development
- S3-compatible storage for production

## 10. Data Ownership and Boundaries

To keep the system maintainable:

- Laravel owns the core marketplace state
- External providers are treated as side-effect systems
- Provider responses are stored for traceability
- Publish/payment/AI outcomes should not overwrite original business intent records

Examples:

- A `scheduled_posts` record should remain even if Facebook posting fails
- A `payments` record should capture provider status transitions instead of hiding failed attempts
- AI search logs should preserve the request even if the AI provider times out

## 11. Queue and Background Processing Strategy

Use queues from the beginning for:

- image processing
- AI embedding extraction
- social publishing
- scheduled post execution
- notification sending
- heavy audit/event processing

Recommended queue separation:

- `default`
- `media`
- `social`
- `ai`
- `notifications`

This is optional at first, but worth designing for now.

## 12. API Strategy

### Versioning

Use URI versioning from day one:

- `/api/v1/...`

### Response format

Standardize API responses:

- success payloads via API Resources
- consistent validation errors
- consistent unauthorized/forbidden/not found format
- pagination meta for collection endpoints

### Authorization

Use:

- JWT middleware for authentication
- Policies for product, store, order, and conversation access
- role or permission middleware later for admin

## 13. Non-Functional Requirements

The architecture should support:

- horizontal queue worker scaling
- safe retries for external integrations
- auditability
- observability
- pagination for large collections
- rate limiting on auth/chat/public search endpoints
- secure file upload handling
- token encryption for external accounts

## 14. Risks and Design Considerations

### Social API risk

Platform APIs can change and permissions may be limited. The system should expect partial capability per provider.

### AI similarity risk

True vector similarity search can outgrow MySQL. MVP should stay pluggable.

### Inventory risk

Checkout must prevent overselling. Product stock and order creation should be transaction-safe.

### Chat scale risk

Unread count and message pagination must be index-aware from the start.

### Security risk

OAuth tokens, payment identifiers, and uploaded files require strong protection and validation.

## 15. Phase 2 Outcome

At the end of Phase 2, we now have:

- A target production architecture
- A module map for implementation
- Core entity relationships at system level
- Integration boundaries for AI, social, and payments
- High-level flow design for chat, e-commerce, scheduling, and AI search

This provides the blueprint for Phase 3, where we will translate the design into detailed database schema definitions.
