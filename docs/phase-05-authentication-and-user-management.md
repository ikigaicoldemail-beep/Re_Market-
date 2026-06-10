# Phase 5: Authentication and User Management

## What this phase builds

This phase replaces the prototype user auth flow with a production-oriented API module for:

- register
- login
- logout
- forgot password
- reset password
- get current user profile
- update profile
- create seller store/page
- update seller store/page

## Design choices

- JWT remains the API authentication mechanism
- controllers are thin and live in `App\Http\Controllers\Api\V1`
- validation is handled with Form Requests
- business logic is moved into services
- responses use API Resources
- store updates use authorization via `StorePolicy`
- user/profile/store creation flows use database transactions

## Endpoints added

### Auth

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`

### Profile

- `GET /api/v1/me`
- `PUT /api/v1/me`

### Store

- `POST /api/v1/stores`
- `PUT /api/v1/stores/{store}`

## Data added for this phase

New schema pieces introduced:

- enhanced `users` table fields for status, role, verification, last login, remember token, and soft deletes
- `user_profiles`
- `stores`

## Main implementation files

### Controllers

- `app/Http/Controllers/Api/V1/AuthController.php`
- `app/Http/Controllers/Api/V1/ProfileController.php`
- `app/Http/Controllers/Api/V1/StoreController.php`

### Requests

- `app/Http/Requests/Auth/*`
- `app/Http/Requests/Profile/UpdateProfileRequest.php`
- `app/Http/Requests/Store/StoreUpsertRequest.php`

### Resources

- `app/Http/Resources/UserResource.php`
- `app/Http/Resources/UserProfileResource.php`
- `app/Http/Resources/StoreResource.php`

### Services

- `app/Services/AuthService.php`
- `app/Services/ProfileService.php`
- `app/Services/StoreService.php`

### Policy

- `app/Policies/StorePolicy.php`

## Implementation notes

- registration now creates both `users` and `user_profiles`
- login updates `last_login_at`
- store creation automatically marks the profile as seller-ready and upgrades the user role from `user` to `seller`
- the first created store becomes the profile’s default store
- store slugs are generated uniquely if not provided

## Limits of this phase

- email delivery for password reset still depends on mail configuration
- profile/store avatar and banner uploads are not implemented yet
- admin role management is not implemented yet
- current product and chat modules still use earlier prototype controllers and will be upgraded in later phases

## Outcome

At the end of Phase 5, the project has a real API foundation for account and seller onboarding flows, which is enough to continue into product management in Phase 6.
