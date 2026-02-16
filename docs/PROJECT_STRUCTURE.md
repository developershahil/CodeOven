# Project Structure

This document describes the current source layout and clean conventions for future changes.

## Repository Layout

```text
CodeOven/
├── .github/workflows/            # CI workflows
├── api/                          # HTTP API routes (auth-protected JSON endpoints)
│   └── execute/                  # Route shim for /api/execute
├── css/                          # Page-level stylesheets
├── docs/                         # Documentation and contribution guides
├── includes/                     # Shared PHP modules (db, auth, csrf, session, workspace)
├── js/                           # Client-side scripts
├── migrations/                   # SQL migrations for schema evolution
├── php/                          # Server-rendered pages (login/signup/dashboard)
├── sandbox/                      # Docker sandbox runtime and image definitions
│   ├── config/                   # Sandbox runtime environment config
│   ├── docker/                   # Language-specific Dockerfiles
│   ├── runner/                   # Sandbox execution runner scripts
│   └── scripts/                  # Sandbox utility scripts
├── storage/workspaces/           # Per-user workspace directories
├── tests/                        # Simple integration validation scripts
├── codemirror/                   # Third-party vendor code (do not edit casually)
├── editor_db.sql                 # Base SQL schema dump
├── docker-compose.sandbox.yml    # Sandbox image build orchestration
├── index.html                    # Landing page
└── README.md                     # Main project guide
```

## Folder Responsibilities

- `api/`: keep handlers thin; delegate auth/session/workspace logic to `includes/`.
- `includes/`: reusable backend modules only; no page rendering here.
- `php/`: UI entrypoints and views; enforce auth checks before loading protected pages.
- `sandbox/`: isolated code execution infra only.
- `storage/workspaces/`: user-owned data directories (`{user_id}/...`) with strict permissions.
- `tests/`: smoke/integration scripts (safe to run locally).

## Structure Rules

1. Avoid adding app logic to vendor folders (`codemirror/`).
2. Prefer adding new shared logic to `includes/` instead of duplicating across routes.
3. Keep migrations additive and timestamped in `migrations/`.
4. Keep write APIs behind auth + CSRF.
