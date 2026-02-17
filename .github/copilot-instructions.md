# Copilot instructions for this repository

Purpose: quick reference for Copilot sessions to operate effectively in this repo (build/test/lint, architecture, and conventions).

---

## Quick build / test / lint commands

Backend (PHP / Laravel)
- Install dependencies: `composer install`
- Run local dev environment (recommended): `composer run dev` (starts PHP server, queue worker, pail, and frontend dev server via npm/bun)
- Run full test suite: `composer test` (invokes `php artisan test`) or `./vendor/bin/pest`
- Run a single Pest test file: `./vendor/bin/pest tests/Feature/ExampleTest.php`
- Run a single test method (Pest / PHPUnit): `./vendor/bin/pest --filter "ExampleTest::test_example"` or `php artisan test --filter ExampleTest::test_example`

Frontend (Vue 3 / Vite)
- Install JS deps: `bun install` (preferred) or `npm install`
- Run frontend dev server: `bun run dev` or `npm run dev`
- Build assets: `bun run build` or `npm run build`
- Lint: `bun run lint` (uses bunx oxlint + eslint) — auto-fix: `bun run lint:fix`
- Format frontend files: `npm run format` (Prettier)

Formatting / code style
- Backend format (Pint + Rector): `composer run format`

CI notes
- GitHub Actions run on PHP 8.4 and Bun 1.3 (see `.github/workflows/tests.yml` and `lint.yml`). Use `./vendor/bin/pest` in CI to mirror runner.

---

## High-level architecture (big picture)
- Laravel 12 backend (PHP 8.4+) serves APIs and pages via Inertia; authentication via Sanctum and background jobs using `php artisan queue`/Pail. Prism PHP SDK is used for AI streaming and real-time responses (SSE).
- Frontend is Inertia + Vue 3, built with Vite and styled with Tailwind CSS (v4) and Shadcn components; Vite/Bun are used for dev/build flows and optional SSR via composer script `dev:ssr`.
- Real-time streaming: server-sent events (SSE) endpoints stream AI responses (Prism), with streaming UI handled in Vue components under `resources/js/components`.
- Tests: Pest (Pest/Laravel) for PHP; phpunit.xml config uses an in-memory SQLite DB for tests (DB_CONNECTION=sqlite, DB_DATABASE=:memory:).

---

## Key conventions and repo-specific patterns
- JS tooling prefers Bun. CI uses `bun install`/`bun run build`; local `npm` is supported but Bun is the primary target for performance parity.
- Fonts & RTL: Arabic UI and RTL layout are supported. The Tajawal font is the canonical Arabic font for the app: it's loaded in `resources/views/app.blade.php` (fonts.bunny.net) and set as the default `--font-sans` in `resources/css/app.css`. Copilot should prefer generating HTML/CSS that uses `font-family: 'Tajawal', sans-serif` or `class="font-sans"` in templates.
- Translations: plural setup is mixed — some UI strings are inline in Vue components and some live in `resources/lang/ar`. The assistant/system prompt is at `resources/views/prompts/system.blade.php` and already instructs responses in Arabic and to prefer Tajawal; respect that prompt when producing content for UI or user-facing text.
- AI model config: `app/Enums/ModelName.php` centralizes supported model identifiers — add new models by adding enum cases and implementing the required matching methods (getName/getDescription/getProvider).
- Dev script `composer run dev` uses `npx concurrently` to run multiple processes; use that for local full-stack dev. For debugging a single piece, run each process separately (e.g., `php artisan serve`, `php artisan queue:listen`, `npm run dev`).
- Test expectations: unit/feature tests assume in-memory sqlite DB; when running integration tests that need persistent DB, update `.env` or run migrations/seeders explicitly.
- Linting: frontend lint task runs `bunx oxlint@latest` then `eslint .` — when proposing ESLint rule changes, update `eslint.config.mjs`/`.eslintrc` and ensure `@antfu/eslint-config` compatibility.

---

## Where to look for more context
- Project README: `README.md` (setup and features)
- CI workflows: `.github/workflows/tests.yml` and `.github/workflows/lint.yml` (mirrors how CI installs and builds)
- System assistant prompt: `resources/views/prompts/system.blade.php` (contains explicit Arabic/Tajawal guidance)
- Frontend entrypoints: `resources/js`, `resources/js/pages`, `resources/css/app.css`
- Backend entrypoints and model config: `app/`, especially `app/Enums/ModelName.php`
- Translations: `resources/lang/ar/`

---

If you'd like, Copilot sessions can be configured to run Playwright or other MCP servers for end-to-end tests; ask if you want that enabled.

---

What I created: a concise set of Copilot instructions tailored to this repo (build/test/lint commands, architecture summary, and repo-specific conventions). If you want this file adjusted (more examples, expanded single-test examples, or extraction guidance for inline Arabic strings), say which area to expand.