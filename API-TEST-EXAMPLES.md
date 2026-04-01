# API Test Examples

Replace the placeholders below before running the examples.

- `https://your-domain.example/public`
- `SITE_KEY_HERE`
- `API_TOKEN_HERE`
- `SIGNATURE_SECRET_HERE`

## 1. Health check
```bash
curl -X GET "https://your-domain.example/public/api/v1/health"
```

## 2. Valid JSON submission
```bash
curl -X POST "https://your-domain.example/public/api/v1/inquiries/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "site_key": "SITE_KEY_HERE",
    "api_token": "API_TOKEN_HERE",
    "form_key": "contact_form",
    "name": "John Doe",
    "email": "john@example.com",
    "content": "Need a quotation for decking products.",
    "country": "United States",
    "phone": "+1-555-0100",
    "extra_data": {
      "product_name": "Decking Board",
      "quantity": "500 sqm"
    }
  }'
```

## 3. Invalid token example
```bash
curl -X POST "https://your-domain.example/public/api/v1/inquiries/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "site_key": "SITE_KEY_HERE",
    "api_token": "wrong-token",
    "name": "Bad Token",
    "email": "bad@example.com",
    "content": "This should fail."
  }'
```

## 4. Honeypot example
```bash
curl -X POST "https://your-domain.example/public/api/v1/inquiries/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "site_key": "SITE_KEY_HERE",
    "api_token": "API_TOKEN_HERE",
    "name": "Bot",
    "email": "bot@example.com",
    "content": "This should be flagged by honeypot.",
    "website": "https://spam.example"
  }'
```

## 5. Signed request example
Signature format:

```text
HMAC_SHA256(timestamp + "\n" + raw_body, signature_secret)
```

PHP helper example is already included here:
- `examples/php-signed-forwarder.php`

## 6. Verification targets after testing
After each request, verify:
- API response body and HTTP status
- `api_request_logs` contains the request
- `inquiries` contains the saved inquiry for successful requests
- spam-triggered requests are marked correctly
- `raw_payload` and `extra_data` were stored as expected
