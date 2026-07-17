---
name: feature-commit
description: "Commit proactively after a feature is developed. Triggers on: 'feature developed', 'done with task', 'commit this', 'selesai', after completing a unit of work the user requested. WAJIB: ask user confirmation before each commit, compose conventional-commits message (feat/fix/refactor/...) with DETAILED bullet-list body so the user can understand what feature was developed, verify svelte-check passes first, stage intentionally. NEVER push — the user handles pushing. Complements git-review-before-commit (safety checklist) and verify-svelte-check (type-check)."
---

# Skill: Feature commit

Commit a completed feature with a **detailed** conventional-commits message. Proactive, but **asks the user before each commit** and **never pushes**.

## When to trigger

After the user's requested work is **complete and verified** for a single feature/task. Do NOT auto-commit mid-task or between sub-steps of one task.

Examples that trigger:
- User says "done", "feature developed", "commit this", "selesai"
- A unit of work the user requested is fully implemented end-to-end and type-checks pass

Examples that do NOT trigger:
- Mid-task partial edits
- Read-only research / exploration
- When the user is still iterating on the design

## Workflow

### 1. Verify with `svelte-check` FIRST

Delegate to the `verify-svelte-check` skill. **Never commit before type-check passes.**

```bash
cd frontend && npx svelte-check --threshold error 2>&1 | tail -30
```

If there are errors introduced by this task → fix them, don't commit yet.
If only pre-existing errors remain in unrelated files → note them and proceed.

### 2. Inventory the change

Run in parallel:

```
git status
git diff --staged
git diff
git log --oneline -10
```

Apply the safety checklist from `git-review-before-commit`:
- Classify each diff block: intended / side-effect of refactor / pre-existing / stranger
- Revert strangers with `git checkout -- <file>` or surface to user
- Scan for secrets (tokens, keys, `.env`, absolute paths exposing username) — **do not commit if found**, ask user

### 3. Stage intentionally

Use `git add <specific-files>` — **never** `git add .` / `git add -A`.
Separate unrelated changes into separate commits.

### 4. Ask the user before committing

Present a short preview and ask for confirmation:

```
Ready to commit:
  Type: feat
  Scope: admin
  Subject: add gallery management page
  Files: 6 changed (frontend + backend)
  Body: 6 bullets covering new files, API methods, i18n keys, backend routes

Proceed with this commit?
```

Abort cleanly if the user declines or wants to adjust the message.

### 5. Compose the commit message — conventional-commits + DETAILED body

**Subject line** (≤50 chars, lowercase, imperative mood):

```
<type>(<scope>): <summary>
```

**Types** (pick the one that fits best):

| Type | When |
|---|---|
| `feat` | New feature or capability for the user |
| `fix` | Bug fix |
| `refactor` | Code restructuring without behavior change |
| `perf` | Performance improvement |
| `docs` | Documentation only |
| `test` | Tests only |
| `style` | Formatting/whitespace, no logic change |
| `chore` | Maintenance, tooling, deps, build config |
| `build` | Build system / external dependencies |
| `ci` | CI pipeline changes |

**Scope** (optional, encouraged): the area of the codebase, e.g. `(admin)`, `(i18n)`, `(api)`, `(auth)`, `(router)`, `(deploy)`.

**Breaking change** marker: `feat!:` or `feat(scope)!:` — and add a `BREAKING CHANGE:` footer explaining what breaks and the migration path.

**Body** — blank line after subject, then a **detailed bullet list** so the user can understand what was developed weeks later. Each bullet should answer: what was added/changed, in which file, and why (if non-obvious). Cover:

- New files created and their purpose
- Modified files and what changed in each
- API endpoints / routes / handlers added (for backend work)
- i18n keys added to BOTH `en.json` and `id.json` (per AGENTS.md pattern #2)
- AGENTS.md "Critical Patterns (DO NOT BREAK)" referenced, if relevant (e.g. `value` + `oninput` instead of `bind:value`)
- Any non-obvious decision or tradeoff

**Footer** (optional): `BREAKING CHANGE: ...`, issue refs (`Closes #12`), or migration notes. Only add footers that are meaningful.

### 6. Commit (do NOT push)

```bash
git commit -m "<subject>" -m "<body>" -m "<footer>"
```

Or use a heredoc for multi-line bodies. Keep each `-m` block coherent.

**Forbidden always** (per repo rules + safe defaults):
- `git commit --amend` on already-pushed commits
- `git push` (any form) — **the user handles pushing themselves**
- `git push --force` / `--force-with-lease`
- `git config` modification
- Empty commits
- `--no-verify` (skipping hooks)
- Interactive `-i` flag

### 7. Report back

After commit:

```
Committed: <hash-snip> "<subject>"
Type: <type>(<scope>)
Files changed: <count>
Status: working tree clean (or: N files still unstaged — listed)
Push: NOT pushed (user handles pushing)
```

If the user then asks to push, **defer to the user's own action**. The skill's policy is to never invoke `git push`. You may show the user the exact command to run themselves:

```bash
git push origin <branch>
```

## Example commit messages

### Feature (frontend + backend + i18n)

```
feat(admin): add gallery management page

- add frontend/src/routes/admin/GalleryList.svelte with CRUD UI and image upload
- add galleryApi in frontend/src/lib/api/index.ts (list, create, update, delete)
- register /admin/gallery route in App.svelte behind auth gating
- add gallery.* keys to en.json AND id.json (per AGENTS.md pattern #2)
- backend: add handlers/gallery.php with GET/POST/PUT/DELETE handlers
- backend: register 4 routes in site/public/api/routes.php
- backend: extend database.php initSchema() with galleries table

Form inputs use value+oninput (not bind:value) per AGENTS.md pattern #1.
```

### Bug fix

```
fix(auth): preserve CSRF token across login

- client.ts: only clear csrfToken on 401 from /api/auth/login, not from any 401
- fixes infinite 401 loop when token expired but session still valid
```

### Refactor

```
refactor(i18n): extract lang.svelte.ts as thin proxy to i18n store

- langStore.toggle() now delegates to i18n.toggle() (single source of truth)
- removes duplicate localStorage writes from lang store
- no behavior change for callers of langStore.toggle()
```

### Breaking change

```
feat(api)!: switch auth from JWT to session cookies

- backend: replace jwt-verify middleware with require_auth() session check
- frontend: client.ts no longer attaches Authorization header
- all /api/* now require SameSite=Lax session cookie

BREAKING CHANGE: clients must obtain a session via POST /api/auth/login
before calling any authenticated endpoint. JWT bearer tokens no longer
accepted.
```

## Conflicts with repo style — noted decision

The existing repo commit (`210ee86 initial project setup...`) uses a plain lowercase sentence, NOT conventional-commits. The user explicitly chose conventional-commits for the `feature-commit` skill. **This skill overrides repo-style-matching for proactive feature commits.** The `git-review-before-commit` skill still says to match repo style for user-initiated commits — if the user runs that skill instead, follow repo style. When in doubt, ask the user which style they want for the current commit.

## Relationship to other skills

- **`verify-svelte-check`** — MUST run before this skill commits. Type-check failure blocks the commit.
- **`git-review-before-commit`** — shares the diff-review / secret-hygiene / staging checklist. This skill reuses those rules. `git-review-before-commit` is for when the user explicitly says "commit/push" and is more conservative on style; `feature-commit` is for proactive commits after a feature lands.
- **`i18n`** — when the feature adds i18n keys, ensure BOTH `en.json` and `id.json` are updated and staged together before commit (AGENTS.md pattern #2).

## Final reminder

- **Ask before committing.** Never surprise the user with a commit they didn't approve.
- **Detailed body.** The user should be able to read the commit message weeks later and understand exactly what feature was developed and why.
- **Never push.** Pushing is the user's job, always.
