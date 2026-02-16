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
