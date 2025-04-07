# DHBW Identity Provider (IDP) Project

A simple, educational identity provider (IDP) system built with PHP and CodeIgniter. This project is designed as a teaching tool for the Distributed Systems course at DHBW, helping students understand how authentication systems work in practice and how they can be used as external services in distributed applications.

---

## ✨ Project Overview

This IDP allows users to:
- Log in with a username and password
- Receive a signed access token
- Use that token to access protected API endpoints

The system simulates real-world token-based authentication (inspired by JWT) and is designed to integrate easily into other distributed apps.

---

## 🌐 How It Works

### Login & Token Issuance
1. User logs in via `/auth/login` with valid credentials.
2. If valid, the server generates a signed token:
   - `token = base64(payload) + '.' + signature`
   - `signature = HMAC_SHA256(base64(payload), SECRET_KEY)`
3. Token is returned to the frontend and stored in `localStorage`.

### Token Usage
- All protected API requests must include:
  ```http
  Authorization: Bearer <token>
  ```
- The server verifies the signature using the same secret key.
- If valid, access is granted.

### Token Decoding (client-side)
```bash
echo "<base64payload>" | base64 -d
```

---

## 🔍 Project Structure

```
app/
  Controllers/        --> API and Web controllers (login, users, chatbot)
  Services/           --> UserManager, OpenAiService, TokenHelper, storage backends
  Views/              --> UI for login and user management
  Config/             --> Routing and environment configuration
public/
  css/                --> Shared styles
  images/             --> Logo or static assets (optional)
writable/
  users.json          --> User storage file (file backend)
.env                  --> Environment settings and secrets
```

---

## ⚙️ Getting Started

### Requirements
- PHP >= 8.2
- Composer

### Run the project
For the LLM integration, an openai api key is required.

```bash
git clone <repo-url>
cd idp-app
cp env .env
composer install
php spark serve --host 0.0.0.0 --port 8080
```

Visit: [http://localhost:8080](http://localhost:8080)

---

## 🎓 Classroom Use

### Overview
This project is broken into **6 parts** for collaborative group work:

| Group | Responsibility                           |
|-------|--------------------------------------------|
| 1     | User management API (CRUD endpoints)       |
| 2     | File-based storage (salted password hashing) |
| 3     | Login/authentication and token generation  |
| 4     | Token validation logic (HMAC)              |
| 5     | Frontend logic (login + token handling)    |
| 6     | System bootstrap (.env, default admin)     |

Each group implements their part in isolation and merges it into a shared repository. After integration, the full IDP system is available to all teams for use in their own distributed apps.

TODO: Ensure the interaction / contract between components by using unittests.

### Constraints
- UI is fixed (shared login and user management screen)
- Each group must work independently and use Git
- The final integrated system becomes a shared external IDP

---

## ✅ Using the IDP in Other Projects

### 1. Get a Token
```bash
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin"}'
```

The token will have the following format: `<base64_payload>.<hex_signature>`

### 2. Decode Payload
bash:
```bash
TOKEN="PASTE_YOUR_TOKEN_HERE"
PAYLOAD=$(echo "$TOKEN" | cut -d. -f1)

# Decode the payload (regular base64)
echo "$PAYLOAD" | base64 -d
```

PHP:
```php
$token = 'PASTE_YOUR_TOKEN_HERE';
$secret = 'your_shared_secret';

// Split token into payload and signature
list($encodedPayload, $encodedSignature) = explode('.', $token, 2);

// Decode the payload
$payloadJson = base64_decode($encodedPayload);
```

### 3. Validate Token
bash:
```bash
SECRET="your_super_secret_key"
TOKEN="PASTE_YOUR_TOKEN_HERE"
PAYLOAD=$(echo "$TOKEN" | cut -d. -f1)
SIGNATURE=$(echo "$TOKEN" | cut -d. -f2)

# Recreate the signature using HMAC-SHA256
GENERATED_SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | cut -d" " -f2)
# GENERATED_SIGNATURE must be equal to SIGNATURE, otherwise the token was tampered.
```

PHP:
```php
$token = 'PASTE_YOUR_TOKEN_HERE';
$secret = 'your_shared_secret';

// Split token into payload and signature
list($encodedPayload, $encodedSignature) = explode('.', $token, 2);

// Decode the payload
$payloadJson = base64_decode($encodedPayload);

// Recreate signature
$expectedSignature = hash_hmac('sha256', $encodedPayload, $secret);
$expectedSignatureEncoded = base64_encode($expectedSignature);

// Validate
if ($expectedSignatureEncoded === $encodedSignature) {
    echo "Valid signature ✅\n";
} else {
    echo "Invalid signature ❌\n";
}
```
If signature matches, the token is valid.

---

## 💬 Bonus: AI Chatbot for User Management

This project includes an optional chatbot interface powered by OpenAI. It allows users to manage the system using natural language commands.

### Features:
- Chat UI integrated into the user management screen
- Users can type requests like:
  - "Create a user named Alice with password 1234 and role admin"
  - "Update user 2 to be named Bob with role user"
  - "Delete user 3"
- OpenAI interprets the intent and triggers the correct backend function
- On success, the users table automatically refreshes

### How it Works:
- Messages are sent to OpenAI using function calling
- If all parameters are provided, OpenAI returns the function name and arguments. If not, there is a follow up with the user
- The system validates and executes the command via `UserManager` class

> This feature showcases how AI can be used to enhance system interactions and provides a fun, practical example of integrating external APIs.

---

Happy coding ✨

