const endpoint = 'https://your-domain.com/api/v1/inquiries/submit';

const payload = {
  site_key: 'a_main',
  api_token: 'token_a_main_2026',
  form_key: 'sample_form',
  name: 'Jane Doe',
  email: 'jane@example.com',
  title: 'Need sample pack',
  content: 'Please send me a sample pack for decking colours.',
  source_url: window.location.href,
  submitted_at: new Date().toISOString(),
  extra_data: {
    preferred_colour: 'Teak',
    project_type: 'Residential',
  },
};

fetch(endpoint, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(payload),
})
  .then((res) => res.json())
  .then((data) => console.log('API response:', data))
  .catch((err) => console.error('Submit failed:', err));
