# Phase 14: Final Project Structure

## Goal

This phase defines the recommended long-term folder and module structure for the Laravel marketplace backend.

The purpose is to make the codebase easier to:

- extend
- onboard into
- test
- refactor safely
- maintain as the project grows

This is the target structure the repository should continue moving toward.

## Recommended top-level application structure

```text
app/
  Console/
    Commands/
  Contracts/
  Enums/
  Events/
  Exceptions/
  Helpers/
  Http/
    Controllers/
      Api/
        V1/
    Middleware/
    Requests/
      Address/
      Ai/
      Auth/
      Cart/
      Chat/
      Checkout/
      Product/
      Profile/
      Social/
      Store/
      Wishlist/
    Resources/
  Integrations/
    Ai/
    Social/
    Payments/
    Notifications/
  Jobs/
  Listeners/
  Models/
  Notifications/
  Policies/
  Providers/
  Services/
  Traits/
```

## Recommended responsibility by folder

## `app/Http/Controllers/Api/V1`

Use for:

- thin REST controllers only
- request/response orchestration
- policy calls
- delegating business logic into services

Controllers should not contain:

- heavy business rules
- provider-specific integration logic
- large query-building branches

Current examples already aligned with this:

- auth
- profile
- product
- checkout
- chat
- social
- scheduling

## `app/Http/Requests`

Use for:

- validation rules
- request authorization when simple
- normalized endpoint input

Recommended subfolders:

- `Auth`
- `Profile`
- `Store`
- `Product`
- `Address`
- `Cart`
- `Checkout`
- `Wishlist`
- `Chat`
- `Ai`
- `Social`

This structure is already in good shape and should be preserved.

## `app/Http/Resources`

Use for:

- JSON response transformation
- pagination item shaping
- consistent API contracts

Recommendation:

- keep one resource per domain object
- avoid embedding business logic here
- use resource collections for nested responses where needed

## `app/Models`

Use for:

- Eloquent persistence models
- relationships
- casts
- small persistence-related helpers

Avoid placing:

- orchestration logic
- large domain workflows

As the codebase grows, this folder may later be grouped by domain subdirectories, but the current flat model structure is still acceptable.

## `app/Services`

Use for:

- business workflows
- orchestration across models
- transaction management
- provider-manager composition

Current service organization is already the main backbone of the backend.

Recommended domain grouping if this grows further:

```text
app/Services/
  Auth/
  Catalog/
  Commerce/
  Chat/
  Ai/
  Social/
  Shared/
```

Right now the flat service structure is fine, but domain subfolders will become helpful as more services appear.

## `app/Contracts`

Use for:

- provider-facing interfaces
- replaceable abstractions

Good current examples:

- AI embedding client interface
- social platform client interface

Future additions likely:

- payment gateway contract
- notification sender contract
- shipping provider contract

## `app/Integrations`

Use for:

- vendor-specific implementations
- HTTP client wrappers
- payload translation for external platforms

Recommended subfolders:

- `Ai`
- `Social`
- later `Payments`, `Shipping`, `Notifications`

This is where Facebook, TikTok, and future provider clients belong.

## `app/Jobs`

Use for:

- asynchronous background execution
- queued side effects
- retryable integration work

Current good examples:

- AI embedding generation
- social publishing
- scheduled post publishing

Keep jobs focused and small:

- fetch record
- call service
- update failure state if needed

## `app/Policies`

Use for:

- ownership and access-control rules

Each user-owned or sensitive aggregate should have a policy.

The current codebase already has strong policy coverage and should keep extending that pattern rather than shifting authorization into controllers or services.

## `app/Notifications`

Use for:

- in-app and email notification classes
- order updates
- chat notifications
- social publish results

This folder is not heavily used yet, but it should become the home for notification delivery in later iterations.

## `app/Events` and `app/Listeners`

Use for:

- decoupled side effects
- internal domain events

Examples for future use:

- `OrderPlaced`
- `ProductPublished`
- `ConversationStarted`
- `SocialPostPublished`

Listeners can then trigger:

- notifications
- analytics
- audit enrichment
- integration side effects

## `app/Traits`

Use sparingly for:

- small cross-cutting reusable behavior

Only introduce traits when they genuinely reduce duplication and do not hide important behavior.

Examples that may be reasonable later:

- slug generation helper trait
- activity-log helper trait

## `app/Helpers`

Use sparingly for:

- pure helper functions or utility classes with clear cross-domain value

Do not let this become a dumping ground.

Prefer services or dedicated value objects first.

## `app/Enums`

Recommended next improvement:

introduce PHP enums for repeated status fields, especially:

- product status
- order status
- payment status
- social post status
- scheduled post status
- user role
- user status

This will reduce string duplication and improve safety across requests, services, and resources.

## Recommended routes structure

Current:

- `routes/api.php`
- `routes/api/v1.php`

Recommended later if the API surface keeps growing:

```text
routes/
  api.php
  api/
    v1/
      auth.php
      profile.php
      stores.php
      products.php
      commerce.php
      chat.php
      ai.php
      social.php
```

That split is not required immediately, but it will improve maintainability as the route file gets larger.

## Recommended docs structure

Current docs are already phase-based and helpful.

Recommended long-term docs structure:

```text
docs/
  architecture/
  api/
  modules/
  operations/
  testing/
  phase-*.md
```

Suggestion:

- keep the phase docs as a project history
- add stable module docs later for ongoing maintenance

## Recommended tests structure

```text
tests/
  Feature/
    Api/
      V1/
        Auth/
        Catalog/
        Commerce/
        Chat/
        Ai/
        Social/
  Unit/
    Services/
    Policies/
    Integrations/
```

Current tests are a good start, but domain subfolders will scale better than a flat test layout.

## Prototype cleanup that should happen next

The repository still contains some prototype leftovers that should be retired when convenient:

- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Controllers/Api/ProductController.php`
- `app/Http/Controllers/Api/MessageController.php`
- `app/Models/Message.php`
- `app/Http/Controllers/AuthController.php`

These were part of the earlier prototype and are no longer the intended API surface.

Recommendation:

1. keep them only until you confirm no routes reference them
2. remove them in a cleanup pass
3. keep all new work inside `Api/V1`

## Recommended module map

For this backend, the clean conceptual module structure is:

### Identity

- auth controllers
- auth requests
- auth service
- user model
- profile model/resource

### Seller and store

- store controller
- store request
- store service
- store policy

### Catalog

- product controller
- product requests
- product service
- category/product-condition models
- product resources

### Commerce

- address/cart/checkout/order/payment controllers
- commerce services
- commerce models
- order/payment policies

### Chat

- conversation controller
- chat requests/resources
- chat service
- conversation policy

### AI

- AI search controller
- AI requests/resources
- AI embedding/search services
- AI contracts/integrations
- AI jobs

### Social

- social account/post/scheduled post controllers
- social requests/resources
- social services
- provider contracts/integrations
- social jobs

### Shared infrastructure

- activity logging
- middleware
- providers
- queue config
- exception handling

## Final recommended structure for this project

If continuing from the current codebase, the most practical “final structure” is:

```text
app/
  Console/
    Commands/
  Contracts/
  Http/
    Controllers/
      Api/
        V1/
    Middleware/
    Requests/
      Address/
      Ai/
      Auth/
      Cart/
      Chat/
      Checkout/
      Product/
      Profile/
      Social/
      Store/
      Wishlist/
    Resources/
  Integrations/
    Ai/
    Social/
  Jobs/
  Models/
  Policies/
  Providers/
  Services/
```

This is the best balance between:

- Laravel familiarity
- clean architecture
- growth readiness
- avoiding unnecessary complexity

## Outcome

At the end of Phase 14, the project now has a clear recommended long-term structure for controllers, requests, resources, models, services, jobs, integrations, policies, notifications, helpers, and tests, along with identified cleanup targets from the earlier prototype stage.
