# TikTok Integration Guide

## Complete Setup Instructions

---

## **Step 1: Create TikTok Developer Account**

### 1.1 Register
- Go to: [https://developer.tiktok.com/](https://developer.tiktok.com/)
- Click **Sign Up**
- Email verification required
- Accept terms and complete verification

### 1.2 Complete Profile
- Full name (required)
- Country/Region
- Phone number
- Agree to terms

---

## **Step 2: Create a TikTok App**

### 2.1 Create New App
1. Go to **TikTok Developers Dashboard**
2. Click **Apps** → **Create an app**
3. Fill in:
   - **App name**: "Second-Hand Marketplace" (or your name)
   - **App category**: Select **E-commerce** or **Shopping**
   - **Use case**: Product listing/sharing
   - **Platform**: Web

### 2.2 Get Credentials
After app is created, you'll see:
- **Client Key** (keep safe)
- **Client Secret** (KEEP PRIVATE!)

**Example Credentials:**
```
Client Key: aw1234567890abcdefghijklmnop
Client Secret: 4a8c9d7e3f2b1a9c8d7e6f5g4h3i2j1k
```

---

## **Step 3: Configure OAuth**

### 3.1 Set Redirect URI
1. In your app settings, go to **Authorization settings**
2. Add **Redirect URIs** (both for testing and production):

**For Local Development:**
```
http://localhost:8000/auth/tiktok/callback
```

**For Production:**
```
https://yourdomain.com/auth/tiktok/callback
```

### 3.2 Request Scopes
Request access to these scopes:
- `video.upload` - Upload videos
- `video.publish` - Publish videos
- `user.info.basic` - Get user info

Apply for approval if needed.

---

## **Step 4: Update Environment Variables**

Edit `.env` file:

```env
TIKTOK_CLIENT_ID=aw1234567890abcdefghijklmnop
TIKTOK_CLIENT_SECRET=4a8c9d7e3f2b1a9c8d7e6f5g4h3i2j1k
TIKTOK_REDIRECT_URI=http://localhost:8000/auth/tiktok/callback
```

---

## **Step 5: Get User Access Token**

### 5.1 OAuth Flow (User Authorization)

When user connects their TikTok account:

1. **Step 1: Redirect to TikTok Authorization**
```
GET https://www.tiktok.com/v1/oauth/authorize/?
  client_key={CLIENT_KEY}
  &scope=video.upload,video.publish,user.info.basic
  &redirect_uri={REDIRECT_URI}
  &response_type=code
  &state={RANDOM_STATE}
```

2. **Step 2: User Authorizes**
   - User logs into TikTok
   - Sees permissions request
   - Clicks "Authorize"

3. **Step 3: Receive Authorization Code**
   - TikTok redirects to your callback URL with code
   ```
   http://localhost:8000/auth/tiktok/callback?code=abc123&state=xyz789
   ```

4. **Step 4: Exchange Code for Access Token**
```php
POST https://open.tiktokapis.com/v1/oauth/token/
Content-Type: application/x-www-form-urlencoded

client_key=YOUR_CLIENT_KEY
client_secret=YOUR_CLIENT_SECRET
code=abc123
grant_type=authorization_code
```

**Response:**
```json
{
  "access_token": "act_abc123xyz789...",
  "refresh_token": "rft_abc123xyz789...",
  "expires_in": 2592000,
  "token_type": "Bearer",
  "scope": "video.upload,video.publish,user.info.basic"
}
```

5. **Step 5: Store in Database**
```php
SocialAccount::create([
    'user_id' => $user->id,
    'platform' => 'tiktok',
    'provider_user_id' => $tiktokUserId,
    'provider_account_name' => $tiktokUsername,
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
    'token_expires_at' => now()->addSeconds(2592000),
    'status' => 'active'
]);
```

---

## **Step 6: Test Auto-Posting**

### 6.1 In Postman

**Connect TikTok Account:**
```
POST /social/accounts
{
  "platform": "tiktok",
  "provider_user_id": "123456789",
  "provider_account_name": "your_tiktok_username",
  "access_token": "act_abc123...",
  "refresh_token": "rft_abc123...",
  "token_expires_at": "2026-06-05T10:30:00Z"
}
```

**Create Product with Auto-Post:**
```
POST /products
{
  "store_id": 1,
  "title": "Used iPhone 13 Pro",
  "description": "Excellent condition",
  "category_id": 1,
  "product_condition_id": 1,
  "price_amount": 50000,
  "currency": "USD",
  "status": "published",
  "visibility": "public",
  "auto_post": "tiktok"
}
```

### 6.2 What Happens
1. Product created ✅
2. ProductCreated event fires ✅
3. AutoPostProductToSocial listener activated ✅
4. TikTok account found ✅
5. SocialPost created (status=queued) ✅
6. PublishSocialPostJob dispatched ✅
7. Queue worker processes ✅
8. TikTokSocialClient::publish() called ✅
9. Video uploaded to TikTok ✅
10. Post published ✅

---

## **API Implementation Details**

### TikTok Video Upload Process

```
1. Call POST /video/upload/init/
   ↓ Returns: upload_url, upload_id
2. Upload video to upload_url
   ↓ Returns: video_id
3. Call POST /video/publish/
   ↓ Returns: status=success
4. Video published on TikTok! ✅
```

### Our Implementation

The `TikTokSocialClient.php` handles:
- ✅ Initializing upload session
- ✅ Uploading video/image
- ✅ Publishing to timeline
- ✅ Token refresh if expired
- ✅ Error handling & retries

---

## **Token Refresh**

TikTok tokens expire after 30 days.

**Automatic refresh in our system:**
```php
// In TikTokSocialClient.php
if ($account->token_expires_at->isPast()) {
    $this->refreshAccessToken($account);  // Auto-refresh
}
```

**Manual refresh endpoint:**
```
POST https://open.tiktokapis.com/v1/oauth/token/refresh/
Content-Type: application/x-www-form-urlencoded

client_key=YOUR_CLIENT_KEY
client_secret=YOUR_CLIENT_SECRET
grant_type=refresh_token
refresh_token=rft_abc123...
```

---

## **Troubleshooting**

### Error: "Invalid access token"
- Token has expired → Refresh it
- Wrong client credentials → Check .env
- Token revoked by user → Ask user to re-connect

### Error: "Video upload failed"
- File size too large → Compress video
- Invalid format → Convert to MP4
- Network timeout → Retry

### Error: "Insufficient permissions"
- Scopes not approved → Request approval on developer.tiktok.com
- Account not authorized → Have user connect account again

---

## **Key Files**

| File | Purpose |
|------|---------|
| `app/Integrations/Social/TikTokSocialClient.php` | Real API integration |
| `app/Listeners/AutoPostProductToSocial.php` | Triggers auto-posting |
| `app/Events/ProductCreated.php` | Product creation event |
| `.env` | Stores credentials |

---

## **Testing Without Real Account**

While developing, you can:

1. **Use TikTok Sandbox** (if available)
   - Separate test environment
   - Doesn't publish to real accounts

2. **Mock the client**
   ```php
   // In config/services.php
   'tiktok' => [
       'mode' => 'test', // Use mock instead of real API
   ]
   ```

3. **Use test tokens**
   ```
   access_token=test_token_12345
   ```

---

## **Production Checklist**

Before launching:

- ✅ TikTok app approved by TikTok
- ✅ All scopes requested and approved
- ✅ Redirect URI set to production domain
- ✅ Client key & secret stored securely in .env
- ✅ HTTPS enforced (required by TikTok)
- ✅ Error logging enabled
- ✅ Rate limiting configured (300 calls/day limit)
- ✅ Token refresh working

---

## **Support Links**

- [TikTok Developer Docs](https://developer.tiktok.com/doc/login-kit)
- [Video Upload API](https://developer.tiktok.com/doc/login-kit-video/)
- [OAuth Reference](https://developer.tiktok.com/doc/login-kit-web/)
- [Rate Limits](https://developer.tiktok.com/doc/guide/rate-limit/)
