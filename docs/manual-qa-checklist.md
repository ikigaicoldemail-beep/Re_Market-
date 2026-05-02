# Manual QA Checklist

## Base setup

- API base URL is `http://localhost:8000/api/v1`
- Docker containers are running:
  - `app`
  - `mysql`
  - `queue`
  - `scheduler`
- Postman collection and environment are imported

## 1. Health

- Call `GET /health`
- Expect `200 OK`
- Expect JSON with `message` and `version`

## 2. Auth

- Register seller user
- Expect token in response
- Login with same user
- Expect token in response
- Call `GET /me`
- Expect authenticated user payload
- Logout
- Expect success message

## 3. Store

- Create store as seller
- Expect `201`
- Save `store_id`
- Update store
- Expect updated fields returned

## 4. Product

- Create product with valid `store_id`
- Expect `201`
- Save `product_id`
- Upload one or more product images
- Expect image list in product response
- Call public `GET /products`
- Expect created product visible when `published`
- Call `GET /products/{product_id}`
- Expect product detail
- Call `GET /products/{product_id}/share`
- Expect share URL payload

## 5. Buyer setup

- Register buyer user
- Expect token
- Login as buyer
- Expect buyer token
- Replace Postman token with buyer token

## 6. Address

- Create shipping address
- Expect `201`
- Save `address_id`
- List addresses
- Expect created address in collection
- Update address
- Expect updated fields returned

## 7. Wishlist

- Add product to wishlist
- Expect success
- List wishlist
- Expect product present
- Remove product from wishlist
- Expect success

## 8. Cart

- Add product to cart
- Expect `201`
- Get cart
- Expect item in cart
- Update cart item quantity
- Expect totals updated
- Remove cart item
- Expect item removed
- Add again for checkout flow

## 9. Checkout and orders

- Checkout using valid `address_id`
- Expect `201`
- Save `order_id`
- List orders
- Expect created order
- Get order details
- Expect order items and payment status
- Get `/orders/{order_id}/payment-status`
- Expect payment payload

## 10. Chat

- Login as buyer
- Start conversation with seller
- Save `conversation_id`
- Send message
- Expect `201`
- List conversations
- Expect conversation present
- Get conversation messages
- Expect sent message present
- Get unread count
- Mark conversation as seen

## 11. AI similarity

- Call `POST /ai/similarity-search` with:
  - `product_id`
  - or uploaded image
- Expect search log payload
- Expect ranked products array

## 12. Social accounts

- Login as seller
- Connect Facebook placeholder account
- Save `social_account_id`
- List social accounts
- Expect connected account present

## 13. Social posts

- Create social post for product
- Save `social_post_id`
- Get social post details
- Expect draft or queued status
- Publish social post
- Expect `posted` or success response

## 14. Scheduled posts

- Create scheduled post for future datetime
- Save `scheduled_post_id`
- List scheduled posts
- Expect scheduled post present
- Update scheduled datetime
- Expect updated timestamp
- Cancel scheduled post
- Expect `cancelled` status

## 15. Security checks

- Call protected endpoint without token
- Expect `401`
- Try updating another user's store/product/order if possible
- Expect `403`
- Call invalid endpoint
- Expect `404`
- Submit invalid payload
- Expect `422`

## 16. Background processing checks

- Watch queue logs:
  - AI embedding job should process after image upload
  - social publish job should process after publish request
  - scheduled post dispatch should appear when due

## 17. Final sanity check

- No unexpected `500` responses during core flows
- Queue worker stays running
- Scheduler stays running
- MySQL container remains healthy
