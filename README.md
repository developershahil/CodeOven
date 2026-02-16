# CodeOven

CodeOven is a browser-based HTML/CSS/JavaScript editor with live preview, user authentication, and project file management backed by PHP + MySQL.

## Highlights

- Live HTML/CSS/JS editing with CodeMirror
- Browser preview panel for rapid iteration
- Signup/login flow for user access
- File save/load integration through API endpoints
- Offline-friendly local development workflow

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Editor Engine:** CodeMirror 5

## Repository Structure

```text
CodeOven/
├── .github/workflows/   # CI workflows
├── api/                 # PHP endpoints (file/preferences operations)
├── css/                 # Page-level styles
├── docs/                # Project documentation
├── includes/            # Shared backend helpers
├── js/                  # Frontend scripts
├── php/                 # Application pages/routes
├── codemirror/          # Third-party CodeMirror sources
├── editor_db.sql        # Database schema
└── index.html           # Landing page
```

For conventions and future refactoring guidelines, see `docs/PROJECT_STRUCTURE.md`.

## Quick Start

1. Place this repository under your local PHP server root (e.g. `htdocs/CodeOven`).
2. Start Apache and MySQL.
3. Create a database (example: `editor_db`).
4. Import `editor_db.sql`.
5. Update DB credentials in `includes/db.php` if needed.
6. Open `http://localhost/CodeOven`.

## Quality & Tooling

This repository now includes:

- `.editorconfig` for consistent formatting.
- `.gitignore` for local artifacts and dependency folders.
- GitHub Actions workflow (`.github/workflows/php-lint.yml`) for automated PHP lint checks.

## Local Health Checks

Run before opening a PR:

```bash
find php api includes -type f -name '*.php' -print0 | while IFS= read -r -d '' file; do php -l "$file"; done
```

Then verify manually:

- Landing page interactions render correctly.
- Dashboard scripts load without console errors.
- Save/load operations work from the dashboard.

## Secure Code Execution Sandbox

CodeOven exposes `POST /api/execute` to run user-submitted code safely in Docker sandboxes for:
- Python
- PHP
- C++

### Endpoint request
- `language`: `python | php | cpp` (also accepts `c++`)
- `code`: source code string
- `stdin`: optional standard input string

### Endpoint response
- `stdout`
- `stderr`
- `exit_code`
- `timed_out`

### Production setup
Prebuild runner images before serving traffic: `bash sandbox/scripts/prebuild_images.sh`


## Authentication & Workspaces

- Registration/Login/Logout are enabled with secure sessions.
- Password hashing uses bcrypt (`password_hash(..., PASSWORD_BCRYPT)`).
- CSRF protection is enforced on auth forms and write APIs.
- Per-user workspace directories are stored at:
  - `storage/workspaces/{user_id}`

### API Auth

All `/api/*` routes require an authenticated session.
Write routes additionally require CSRF token:
- header: `X-CSRF-Token`
- or form field: `_csrf_token`

### File CRUD APIs

- `GET /api/get_files.php`
- `GET /api/load_file.php?file_name={name}`
- `POST /api/save_file.php`
- `POST /api/rename_file.php`
- `POST /api/delete_file.php`

### DB Migration

Run: `migrations/2026_02_auth_workspace.sql`

### Quick verification script

Run a simple end-to-end auth + file CRUD check:

```bash
php tests/auth_file_crud_test.php http://localhost/CodeOven
```
