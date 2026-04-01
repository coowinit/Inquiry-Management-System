#!/usr/bin/env bash
curl -X POST "http://localhost/your-project/public/api/v1/inquiries/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "site_key": "SITE_KEY_HERE",
    "api_token": "API_TOKEN_HERE",
    "form_key": "contact_form",
    "name": "John Doe",
    "email": "john@example.com",
    "content": "Need a quotation for decking products.",
    "country": "United States",
    "phone": "+1-555-0100"
  }'
