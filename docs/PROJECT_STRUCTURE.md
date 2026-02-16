# Project Structure

This document describes the current source layout and a clean convention for future additions.

## Current Runtime Structure

```text
CodeOven/
├── api/                 # PHP endpoints for project and preference APIs
├── css/                 # Page-level stylesheets
├── includes/            # Shared backend helpers (db/auth)
├── js/                  # Client-side scripts
├── php/                 # UI routes/pages
├── codemirror/          # Third-party editor library (vendor)
├── editor_db.sql        # SQL schema/dump
└── index.html           # Landing page
```

## Conventions

- Keep **application code** in `api/`, `includes/`, `js/`, `php/`, and `css/`.
- Treat `codemirror/` as **vendor code**; avoid direct edits unless upgrading.
- Add new documentation in `docs/`.
- Keep reusable backend logic in `includes/` and thin route handlers in `api/`.
- Keep page-specific JS in `js/<page>.js`.

## Suggested Next Refactor (Safe, Incremental)

1. Create `config/` for central app/db configuration.
2. Split `js/api_integration.js` into smaller modules (`file-service`, `ui-bindings`, `storage`).
3. Add backend validation tests for API endpoints.
4. Add `public/` as a web root and move static assets under it for cleaner deployment.
