#!/usr/bin/env bash
curl -X POST "http://localhost/your-project/public/api/v1/inquiries/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "site_key": "SITE_KEY_HERE",
    "api_token": "wrong-token",
    "name": "Bad Token",
    "email": "bad@example.com",
    "content": "This should fail."
  }'
