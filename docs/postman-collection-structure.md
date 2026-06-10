# Postman Collection Structure

## Collection name

- `Marketplace Backend API`

## Variables

- `base_url`
- `jwt_token`
- `user_id`
- `store_id`
- `product_id`
- `address_id`
- `order_id`
- `conversation_id`
- `social_account_id`
- `social_post_id`
- `scheduled_post_id`

## Folder structure

### `00 Health`

- `GET /health`

### `01 Auth`

- Register
- Login
- Logout
- Forgot Password
- Reset Password

### `02 Profile`

- Get Me
- Update Me

### `03 Stores`

- Create Store
- Update Store
- Public Store Products

### `04 Products`

- List Products
- Product Detail
- Create Product
- Update Product
- Delete Product
- Upload Product Images
- My Products
- Share Link

### `05 Commerce - Addresses`

- List Addresses
- Create Address
- Update Address
- Delete Address

### `06 Commerce - Wishlist`

- List Wishlist
- Add Wishlist Product
- Remove Wishlist Product

### `07 Commerce - Cart`

- Get Cart
- Add Cart Item
- Update Cart Item
- Remove Cart Item
- Clear Cart

### `08 Commerce - Checkout`

- Checkout
- Order History
- Order Detail
- Payment Status

### `09 Chat`

- List Conversations
- Start Conversation
- Unread Count
- List Messages
- Send Message
- Mark Seen

### `10 AI Similarity`

- Similarity Search by Upload
- Similarity Search by Product
- Similarity Search by Product Image

### `11 Social Accounts`

- List Social Accounts
- Connect Social Account
- Disconnect Social Account

### `12 Social Posts`

- List Social Posts
- Create Social Post
- View Social Post
- Publish Social Post
- Share Product

### `13 Scheduled Posts`

- List Scheduled Posts
- Create Scheduled Post
- View Scheduled Post
- Update Scheduled Post
- Cancel Scheduled Post

## Suggested auth setup

- Use `Bearer Token` auth at collection or folder level
- Save login token into `jwt_token`

## Suggested tests in Postman

- save IDs from create responses into variables
- assert response status
- assert required fields exist
- assert token exists after login
- assert pagination meta exists on list endpoints
