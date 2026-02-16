# Commit Guidelines

Use concise, scope-based commit messages so history is understandable file/folder-wise.

## Format

```text
<type>(<scope>): <summary>
```

- `type`: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `security`
- `scope`: top-level area affected (`api`, `includes`, `php`, `js`, `sandbox`, `docs`, `migrations`)
- `summary`: imperative mood, ~50-72 chars

## Examples

- `feat(api): add auth middleware for JSON endpoints`
- `security(includes): enforce session cookie hardening`
- `fix(php): block dashboard access for guests`
- `docs(docs): update project structure map`
- `test(tests): add auth and CRUD smoke script`

## File/Folder-Based Scopes

Use the folder that contains the changed file as the commit `scope`.

| File or folder changed | Recommended scope | Example commit message |
| --- | --- | --- |
| `api/*` | `api` | `fix(api): validate file_name before loading file` |
| `includes/*` | `includes` | `security(includes): regenerate session id on login` |
| `php/*` | `php` | `feat(php): add empty-state UI for dashboard` |
| `js/*` | `js` | `fix(js): debounce preview rendering` |
| `css/*` | `css` | `refactor(css): normalize editor panel spacing` |
| `docs/*` | `docs` | `docs(docs): clarify workspace storage path` |
| `migrations/*` | `migrations` | `feat(migrations): add index for users.email` |
| `tests/*` | `tests` | `test(tests): cover save and rename flow` |
| root files (`README.md`, `.gitignore`, etc.) | `repo` | `chore(repo): refresh contributor setup steps` |

### If multiple files/folders changed

- Prefer splitting into one commit per folder when practical.
- If you keep a single commit, choose the dominant folder as `scope`.
- In the commit body, list per-folder changes as bullets for traceability.

## Multi-area Changes

If one commit spans multiple folders, choose the dominant scope:

- `security(auth): add lockout + rate limiting`

Or split into separate commits when practical:

1. `feat(includes): add workspace helper`
2. `feat(api): integrate workspace helper in file routes`
3. `docs(readme): document workspace behavior`

## Commit Body (Recommended)

Add bullets when context is useful:

- what changed
- why it changed
- migration or rollout note (if any)
