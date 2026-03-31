// For token-only sites.
// Do not use this pattern for signed sites, because the secret should stay on your backend.

fetch('https://your-central-domain.com/api/v1/inquiries/submit', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    site_key: 'a_main',
    api_token: 'token_a_main_2026',
    form_key: 'contact_form',
    name: 'John Smith',
    email: 'john@example.com',
    content: 'I want more information about your products.',
    extra_data: {
      product_interest: 'Decking'
    }
  })
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error(error));
