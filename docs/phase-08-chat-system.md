# Phase 8: Chat System

## What this phase builds

This phase replaces the prototype direct-message table with a proper conversation-based chat system for buyer-seller messaging.

Implemented features:

- create or find private conversation
- list conversations
- list messages in a conversation
- send message
- unread count
- mark conversation as seen
- participant-based authorization

## Main design choices

- chat is now database-backed REST first
- conversations are separate from messages
- participants are stored explicitly for access control and read tracking
- unread counts are computed from participant read state and message ids
- product context is optional on a conversation so buyer-seller chat can be linked to a listing

## Schema added

New tables:

- `conversations`
- `conversation_participants`
- `chat_messages`

## Endpoints added

- `GET /api/v1/conversations`
- `POST /api/v1/conversations`
- `GET /api/v1/conversations/unread-count`
- `GET /api/v1/conversations/{conversation}/messages`
- `POST /api/v1/conversations/{conversation}/messages`
- `POST /api/v1/conversations/{conversation}/seen`

## Main implementation files

### Controller

- `app/Http/Controllers/Api/V1/ConversationController.php`

### Service

- `app/Services/ChatService.php`

### Requests

- `app/Http/Requests/Chat/StartConversationRequest.php`
- `app/Http/Requests/Chat/SendMessageRequest.php`

### Resources

- `app/Http/Resources/ConversationResource.php`
- `app/Http/Resources/ConversationParticipantResource.php`
- `app/Http/Resources/ChatMessageResource.php`

### Models

- `app/Models/Conversation.php`
- `app/Models/ConversationParticipant.php`
- `app/Models/ChatMessage.php`

### Policy

- `app/Policies/ConversationPolicy.php`

## Behavior notes

- a user cannot start a conversation with themselves
- for product-scoped chat, the recipient must be valid for that product context
- users can only view or send messages in conversations they participate in
- sending a message updates the conversation’s last message metadata
- when a user sends a message, their own participant record is marked as read up to that message
- marking a conversation as seen updates the participant’s last read message pointer

## Authorization rules

- `view`: only participants can open a conversation
- `sendMessage`: only participants can send messages

## REST-first strategy

This phase intentionally keeps chat REST + database backed. That is the safest MVP and already supports:

- inbox screens
- message history
- unread badges
- seen state

## Optional realtime enhancement later

The next upgrade path can use Laravel Reverb, Pusher, or Laravel broadcasting for:

- realtime message delivery
- unread badge refresh
- seen receipts
- typing indicators

That enhancement can be added without changing the core schema introduced here.

## Outcome

At the end of Phase 8, the backend has a proper private messaging foundation suitable for marketplace buyer-seller communication.
