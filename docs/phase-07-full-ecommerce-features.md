# Phase 7: Full E-commerce Features

## What this phase builds

This phase adds the marketplace commerce core:

- shipping address management
- wishlist/favorites
- cart management
- checkout
- order creation
- order history
- payment status lookup
- order tracking status exposure
- product search/filter/sort support on the listing API

## Main design choices

- carts, orders, and payments are separated cleanly
- checkout uses a transaction to protect stock and order creation
- order items snapshot product title, image, condition, and price at purchase time
- payment handling is provider-agnostic and starts as status tracking
- one cart is maintained per user and reused after checkout

## Schema added in this phase

New tables:

- `addresses`
- `wishlists`
- `carts`
- `cart_items`
- `orders`
- `order_items`
- `payments`

## Endpoints added

### Addresses

- `GET /api/v1/addresses`
- `POST /api/v1/addresses`
- `PUT /api/v1/addresses/{address}`
- `DELETE /api/v1/addresses/{address}`

### Wishlist

- `GET /api/v1/wishlist`
- `POST /api/v1/wishlist`
- `DELETE /api/v1/wishlist/{product}`

### Cart

- `GET /api/v1/cart`
- `POST /api/v1/cart/items`
- `PUT /api/v1/cart/items/{cartItem}`
- `DELETE /api/v1/cart/items/{cartItem}`
- `DELETE /api/v1/cart`

### Checkout and orders

- `POST /api/v1/checkout`
- `GET /api/v1/orders`
- `GET /api/v1/orders/{order}`
- `GET /api/v1/orders/{order}/payment-status`

## Main implementation files

### Controllers

- `app/Http/Controllers/Api/V1/AddressController.php`
- `app/Http/Controllers/Api/V1/WishlistController.php`
- `app/Http/Controllers/Api/V1/CartController.php`
- `app/Http/Controllers/Api/V1/CheckoutController.php`
- `app/Http/Controllers/Api/V1/OrderController.php`
- `app/Http/Controllers/Api/V1/PaymentController.php`

### Services

- `app/Services/AddressService.php`
- `app/Services/WishlistService.php`
- `app/Services/CartService.php`
- `app/Services/CheckoutService.php`

### Models

- `app/Models/Address.php`
- `app/Models/Wishlist.php`
- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/Payment.php`

### Policies

- `app/Policies/AddressPolicy.php`
- `app/Policies/OrderPolicy.php`
- `app/Policies/PaymentPolicy.php`

## Behavior notes

- only published products can be wishlisted or added to cart
- cart quantity cannot exceed current stock
- checkout validates address ownership and stock availability
- checkout decrements stock and marks products as `sold` when stock reaches zero
- the order keeps item snapshot fields so later product edits do not rewrite purchase history
- payment status is exposed through the latest payment record for the order

## Search, filter, and sort

Product discovery remains on:

- `GET /api/v1/products`

Supported filters already in the API:

- `search`
- `store_id`
- `category_id`
- `product_condition_id`
- `min_price`
- `max_price`
- `location_city`
- `sort=latest|oldest|price_asc|price_desc`
- `per_page`

## Current limits

- payment processing is still a placeholder status model, not a live gateway integration
- shipping fees, coupons, taxes, and refunds are not implemented yet
- seller-side fulfillment status updates are not implemented yet
- wishlist is product-based only for now

## Outcome

At the end of Phase 7, the backend supports the core buyer commerce journey from saving items and managing a cart to placing an order and checking payment status.
