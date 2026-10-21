# Environment contract

The repository contains templates only. Real credentials belong in the
deployment host's private `.env` file or GitHub Environment secrets; they must
never be committed.

Required production values are grouped in `.env.production.example`:

- application: `APP_KEY`, `APP_DOMAIN`, `APP_URL`;
- database: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`;
- cache and queues: `REDIS_HOST`, `REDIS_PASSWORD`;
- search: `MEILISEARCH_HOST`, `MEILISEARCH_KEY`;
- realtime: `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`;
- authentication callbacks: Google and Facebook credentials;
- payments: Stripe credentials and webhook secret;
- AI: the selected provider's API key and model settings;
- email and object storage when enabled;
- observability: `SENTRY_LARAVEL_DSN` when Sentry is enabled.

CI uses `.env.testing`, disables external AI, and relies on fakes or HTTP
stubs. CI must never receive production credentials or call paid providers.
