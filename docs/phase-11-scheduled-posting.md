# Phase 11: Scheduled Posting

## What this phase builds

This phase extends social posting with delayed publishing support.

Implemented features:

- schedule social post for later
- list scheduled posts
- view scheduled post details
- edit scheduled time
- cancel scheduled post
- queue/job execution for due posts
- scheduled status tracking
- retry and failed job handling

## Main design choices

- scheduled execution state lives in a dedicated `scheduled_posts` table
- `social_posts` keeps the business intent/content
- `scheduled_posts` keeps deferred execution lifecycle and retry metadata
- due post dispatch is driven by Laravel scheduler plus queue jobs

## Schema added

New table:

- `scheduled_posts`

Key fields include:

- `scheduled_for`
- `status`
- `attempts`
- `last_attempt_at`
- `processed_at`
- `cancelled_at`
- `failure_reason`

## Main implementation files

### Model

- `app/Models/ScheduledPost.php`

### Service

- `app/Services/ScheduledPostService.php`

### Requests

- `app/Http/Requests/Social/ScheduleSocialPostRequest.php`
- `app/Http/Requests/Social/UpdateScheduledPostRequest.php`

### Resource

- `app/Http/Resources/ScheduledPostResource.php`

### Policy

- `app/Policies/ScheduledPostPolicy.php`

### Controller

- `app/Http/Controllers/Api/V1/ScheduledPostController.php`

### Jobs and command

- `app/Jobs/PublishScheduledPostJob.php`
- `app/Jobs/PublishSocialPostJob.php`
- `app/Console/Commands/DispatchScheduledPostsCommand.php`

## Endpoints added

- `GET /api/v1/scheduled-posts`
- `POST /api/v1/scheduled-posts`
- `GET /api/v1/scheduled-posts/{scheduledPost}`
- `PUT /api/v1/scheduled-posts/{scheduledPost}`
- `DELETE /api/v1/scheduled-posts/{scheduledPost}`

## How the workflow now works

### Post now

1. User creates a social post with `publish_now=true`
2. The post is queued immediately
3. `PublishSocialPostJob` publishes it through the provider client

### Schedule for later

1. User creates a social post draft or queued post
2. User schedules it with `scheduled_for`
3. A `scheduled_posts` record is created
4. Laravel scheduler runs `social:dispatch-scheduled-posts` every minute
5. Due posts are pushed to `PublishScheduledPostJob`
6. That job calls the normal social publishing service
7. Final state is stored on both `scheduled_posts` and `social_posts`

## Status model

### Scheduled post statuses

- `scheduled`
- `queued`
- `processing`
- `posted`
- `failed`
- `cancelled`

### Social post statuses touched here

- `draft`
- `queued`
- `processing`
- `posted`
- `failed`
- `cancelled`

## Retry and failed job handling

- `PublishScheduledPostJob` has `tries = 3`
- `PublishSocialPostJob` now also has `tries = 3`
- both jobs implement `failed()` to persist final error state after retries are exhausted
- each scheduled execution attempt increments `attempts`
- `failure_reason` and `error_message` persist the latest failure cause

## Authorization behavior

- users can only view/update/cancel their own scheduled posts
- ownership is resolved through the linked `social_post`

## Operational note

The scheduler hook was added in:

- `routes/console.php`

It schedules:

- `social:dispatch-scheduled-posts` every minute

## Current limits

- no recurrence rules yet
- no bulk schedule management yet
- no provider webhook reconciliation yet
- scheduled publishing depends on queue workers and Laravel scheduler running in deployment

## Outcome

At the end of Phase 11, the backend supports both immediate and delayed social publishing with a durable, queue-backed scheduling workflow and explicit retry/failure tracking.
