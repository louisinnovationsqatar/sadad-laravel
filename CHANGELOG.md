# Changelog

All notable changes to `sadad-laravel` will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [1.0.0] - 2024-01-01

### Added
- `SadadServiceProvider` with config publishing, migration publishing, route registration, and service container binding.
- `Sadad` Facade for `SadadClient` methods: `checkout`, `refund`, `createInvoice`, `shareInvoice`, `listInvoices`, `getTransaction`, `handleWebhook`, `handleCallback`.
- `config/sadad.php` publishable configuration with full `.env` support.
- `SadadWebhookController` – handles POST `/sadad/webhook`, fires `SadadWebhookReceived` + `SadadPaymentSucceeded` / `SadadPaymentFailed` events, always returns `200 {"status":"success"}`.
- `SadadCallbackController` – handles POST `/sadad/callback`, fires events, redirects to configured success/failure URL.
- `VerifySadadWebhook` middleware – verifies SHA-256 checksumhash before webhook reaches controller.
- `SadadWebhookReceived`, `SadadPaymentSucceeded`, `SadadPaymentFailed`, `SadadRefundProcessed` events with typed public properties.
- `sadad:test` Artisan command – validates config and tests live API authentication.
- Built-in routes: `POST /sadad/callback` and `POST /sadad/webhook` (CSRF-exempt).
- Optional `sadad_transactions` migration for database-level transaction logging.
- Full README with installation, `.env` reference, Facade examples, event docs, webhook setup, and artisan command usage.

---

Built by [Louis Innovations](https://www.louis-innovations.com)
