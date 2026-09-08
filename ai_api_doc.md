# 🚀 AI Chatbot & Native Ollama API Documentation

Welcome to the official API documentation for **Happy Bangladesh AI**. You can integrate local AI intelligence powered by `qwen3.5:0.8b` directly into any website, web application, mobile app, WordPress site, or Python script.

---

## 🌐 API Base URLs

| API Type | Base URL |
|---|---|
| **Chatbot Session API** (Auto-stores history in SQLite) | `https://ai.happybangladesh.com/api` |
| **Native Ollama Direct API** (Stateless, Ollama format) | `https://ai.happybangladesh.com/api/ollama` |

---

## 📚 Table of Contents
1. [Session Chat API](#1-session-chat-api)
2. [Native Ollama Direct API](#2-native-ollama-direct-api)
3. [Code Examples](#3-code-examples)
   - [JavaScript (Web / React / Vue / HTML)](#javascript-web--react--vue--html)
   - [Node.js](#nodejs)
   - [Python](#python)
   - [PHP (WordPress / Custom)](#php-wordpress--custom)
   - [cURL](#curl)
4. [CORS & Security](#4-cors--security)

---

## 1. Session Chat API
Use these endpoints if you want built-in chat history storage in SQLite and automatic conversation title generation.

### 1.1 Health Check
Verify API status and check if Ollama and `qwen3.5:0.8b` model are online.

- **Method**: `GET`
- **Endpoint**: `/api/health`
- **Response**:
```json
{
  "status": "ok",
  "ollama": "connected",
  "model": "qwen3.5:0.8b",
  "modelReady": true,
  "availableModels": ["qwen3.5:0.8b"]
}
```

---

### 1.2 Create New Conversation
Creates a new chat session ID.

- **Method**: `POST`
- **Endpoint**: `/api/conversations`
- **Headers**: `Content-Type: application/json`
- **Body**:
```json
{
  "title": "Customer Support Session"
}
```
- **Response**:
```json
{
  "conversation": {
    "id": 1,
    "title": "Customer Support Session",
    "created_at": "2026-09-08 04:00:00",
    "updated_at": "2026-09-08 04:00:00"
  }
}
```

---

### 1.3 Send Message & Stream Response (SSE)
Sends a message to an existing conversation and streams AI tokens in real-time.

- **Method**: `POST`
- **Endpoint**: `/api/conversations/:id/messages`
- **Headers**: `Content-Type: application/json`
- **Body**:
```json
{
  "content": "What is the latest weather forecast in Dhaka today?",
  "webSearch": true
}
```
> 💡 **Live Internet Access**: Setting `"webSearch": true` automatically performs a real-time DuckDuckGo web search on your server, fetches live web snippets, and feeds up-to-date internet data to `qwen3.5:0.8b`. Setting `"webSearch": false` forces offline mode. If omitted, queries containing words like `today`, `news`, `weather`, `latest`, `2026`, etc., automatically enable web search!
- **Response**: `text/event-stream`
```http
data: {"token":" Quantum"}

data: {"token":" computing"}

data: {"token":" uses"}

data: {"done":true,"conversationId":"1"}
```

---

### 1.4 Get Conversation History
Retrieves all messages for a specific conversation session.

- **Method**: `GET`
- **Endpoint**: `/api/conversations/:id/messages`
- **Response**:
```json
{
  "messages": [
    {
      "id": 1,
      "conversation_id": 1,
      "role": "user",
      "content": "Hello!",
      "created_at": "2026-09-08 04:00:00"
    },
    {
      "id": 2,
      "conversation_id": 1,
      "role": "assistant",
      "content": "Hello! How can I help you today?",
      "created_at": "2026-09-08 04:00:02"
    }
  ]
}
```

---

### 1.5 List All Conversations
- **Method**: `GET`
- **Endpoint**: `/api/conversations`

---

### 1.6 Delete Conversation
- **Method**: `DELETE`
- **Endpoint**: `/api/conversations/:id`

---

## 2. Native Ollama Direct API
Use these endpoints if you want raw, direct access to the Ollama AI engine in standard Ollama/OpenAI format.

### 2.1 Service Check
- **Method**: `GET`
- **Endpoint**: `/api/ollama`
- **Response**: `Ollama is running`

---

### 2.2 List Installed AI Models
- **Method**: `GET`
- **Endpoint**: `/api/ollama/api/tags`
- **Response**:
```json
{
  "models": [
    {
      "name": "qwen3.5:0.8b",
      "model": "qwen3.5:0.8b",
      "size": 1321098329
    }
  ]
}
```

---

### 2.3 Chat Completion (`/api/ollama/api/chat`)
- **Method**: `POST`
- **Endpoint**: `/api/ollama/api/chat`
- **Headers**: `Content-Type: application/json`
- **Body**:
```json
{
  "model": "qwen3.5:0.8b",
  "messages": [
    { "role": "system", "content": "You are a helpful customer service assistant." },
    { "role": "user", "content": "What are your business hours?" }
  ],
  "stream": false
}
```
- **Response (Non-streaming)**:
```json
{
  "model": "qwen3.5:0.8b",
  "created_at": "2026-09-08T04:00:00Z",
  "message": {
    "role": "assistant",
    "content": "We are open 24/7 online!"
  },
  "done": true
}
```

---

### 2.4 Text Generation (`/api/ollama/api/generate`)
- **Method**: `POST`
- **Endpoint**: `/api/ollama/api/generate`
- **Headers**: `Content-Type: application/json`
- **Body**:
```json
{
  "model": "qwen3.5:0.8b",
  "prompt": "Write a catchy tagline for a web agency.",
  "stream": false
}
```

---

## 3. Code Examples

### JavaScript (Web / React / Vue / HTML)

#### Streaming Chat Component Example:
```javascript
async function askAI(userQuestion) {
  // 1. Create session
  const sessionRes = await fetch('https://ai.happybangladesh.com/api/conversations', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title: 'Web Chat' })
  });
  const session = await sessionRes.json();
  const convId = session.conversation.id;

  // 2. Stream message response
  const response = await fetch(`https://ai.happybangladesh.com/api/conversations/${convId}/messages`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ content: userQuestion })
  });

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let aiReply = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    const lines = decoder.decode(value).split('\n');
    for (const line of lines) {
      if (line.startsWith('data: ')) {
        try {
          const data = JSON.parse(line.slice(6));
          if (data.token) {
            aiReply += data.token;
            console.log(data.token); // Update UI element in real-time
          }
        } catch (e) {}
      }
    }
  }

  return aiReply;
}
```

---

### Node.js

```javascript
const response = await fetch('https://ai.happybangladesh.com/api/ollama/api/chat', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    model: 'qwen3.5:0.8b',
    messages: [{ role: 'user', content: 'What is Node.js?' }],
    stream: false
  })
});

const data = await response.json();
console.log(data.message.content);
```

---

### Python

```python
import requests

url = "https://ai.happybangladesh.com/api/ollama/api/chat"

payload = {
    "model": "qwen3.5:0.8b",
    "messages": [
        {"role": "user", "content": "Explain machine learning in 2 sentences."}
    ],
    "stream": False
}

response = requests.post(url, json=payload)
result = response.json()

print(result["message"]["content"])
```

---

### PHP (WordPress / Custom)

```php
<?php
function get_ai_response($prompt) {
    $url = 'https://ai.happybangladesh.com/api/ollama/api/chat';
    
    $body = json_encode([
        'model' => 'qwen3.5:0.8b',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'stream' => false
    ]);

    $response = wp_remote_post($url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => $body,
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        return 'Error connecting to AI service.';
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    return $data['message']['content'] ?? 'No response';
}

// Example usage:
// echo get_ai_response("Summarize our company values.");
?>
```

---

### cURL

```bash
curl -X POST https://ai.happybangladesh.com/api/ollama/api/chat \
  -H "Content-Type: application/json" \
  -d '{
    "model": "qwen3.5:0.8b",
    "messages": [{"role": "user", "content": "Hello!"}],
    "stream": false
  }'
```

---

## 4. CORS & Security
- **CORS Allowed**: The API permits requests from any domain (`Access-Control-Allow-Origin: *`).
- **HTTPS Encrypted**: All data transferred is encrypted via SSL/TLS certificates.
- **Local AI Privacy**: Prompts and responses are processed 100% locally on your VPS server. No third-party APIs (OpenAI, Anthropic, Google) are called.
