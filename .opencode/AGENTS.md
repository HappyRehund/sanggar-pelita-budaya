# AGENTS.md — Guide for AI Agents & Developers

This document orients future agents (human or AI) working on this codebase. It captures **why** decisions were made, not just **what** the code does.

---

## What This Project Is

A website for "Sanggar Pelita Budaya" — a cultural studio/gathering. Two audiences:

- **Public visitors** — see a plain landing page with a language toggle (EN/ID)
- **Admins** — login to a simple admin page that shows "(this is admin)"

Built as **Svelte SPA + PHP REST API + SQLite**, target deployment to **shared hosting cPanel** (Rumahweb). No Node.js in production.

The frontend is intentionally plain — a starter scaffold with GSAP fade-in, bilingual i18n, and admin login. The backend only has health check + auth endpoints.

---

## Core Architectural Decisions

### 1. **Frontend Svelte SPA (not SvelteKit)**
- **Why SPA**: Simplest possible build — output is HTML + CSS + JS, no SSR runtime needed
- **Why not SvelteKit**: SvelteKit adds complexity (adapter-node, SSR, etc.). For shared hosting, plain Vite + Svelte is ideal
- **Tradeoff**: Initial page load fetches the SPA shell + JS bundle. SEO penalty but acceptable for this use case

### 2. **Native PHP, no framework**
- **Why**: Deployment simplicity. No Composer install, no `vendor/` to upload
- **Tradeoff**: Reinventing some wheels (routing, middleware), but minimal PHP files — manageable
- **Convention**: All endpoints return JSON `{success, message, data}` or `{success: false, message, errors}`

### 3. **SQLite, not MySQL**
- **Why**: Single file, no server to configure, perfect for shared hosting
- **Tradeoff**: Concurrent writes limited, but with WAL mode + busy_timeout 5s, sufficient for ~100 concurrent users
- **Where DB lives**: `site/data/sanggar.sqlite` — **outside Document Root** so it can't be downloaded via web

### 4. **CSRF via double-submit token**
- **Why**: Standard defense against CSRF for cookie-based session auth
- **How**: `GET /api/csrf-token` returns token, client sends in `X-CSRF-Token` header on every POST/PUT/DELETE
- **Frontend pattern**: Auto-fetch + cache in `src/lib/api/client.ts`, retry on 401

### 5. **Session cookie with SameSite=Lax**
- **Why**: Native browser protection against CSRF (Lax = cookies sent on top-level navigations, not on cross-site POSTs)
- **Why not JWT**: JWT adds complexity (refresh tokens, storage). For shared hosting with PHP sessions, native sessions are simpler and equally secure

### 6. **i18n custom (not Paraglide)**
- **Why not Paraglide**: Paraglide v2 had a known issue where the SDK couldn't resolve the message-format plugin path. Custom solution is simpler.
- **Custom solution**: Bilingual (en, id) dictionaries in `src/lib/i18n/{en,id}.json`, loaded by simple `t(key, vars?)` function
- **Base language**: `en` (English) — reference structure for integrity/validation checks
- **Reactivity**: `$state`-based store, language switchable via `langStore.toggle()` or `i18n.set('id')`
- **Tradeoff**: No compile-time message extraction, but build is simpler

### 7. **Design: System fonts, minimal styling**
- **Why**: No external font requests, fast load, clean starter aesthetic
- **Tokens**: System font stack (San Francisco, Segoe UI, Roboto, etc.)
- **Palette**: Dark surface (--color-bg: #1a1a2e) + accent (--color-accent: #e8a87c)
- **Animations**: GSAP fade-in on app mount (`gsap.fromTo` with `power2.out` easing)

### 8. **Sync script preserves user uploads**
- **The `pnpm run deploy:sync` script wipes `site/public/assets/` and copies from `dist/`** — this is intentional to avoid stale JS chunks
- **User uploads** (`site/public/uploads/`) are **never** touched by sync or build

### 9. **Deployment scripts (two modes)**
- **`scripts/package-deploy.sh`** — Full deployment ZIP of `site/` (excludes SQLite DB, uploads, PHP backups). Use for initial deploy or when backend code changes.
- **`scripts/package-assets.sh`** — UI-only ZIP of `site/public/assets/` (+ optional `index.html` via `--with-index` flag). Strips `site/public/` prefix so files extract directly into `public_html/`. Use when only the frontend changes. Output goes to `deployment/` folder.

### 10. **`overflow-x: hidden` on body**
- Added to `tokens.css` body rule to prevent horizontal scroll artifacts on mobile.

---

## File-by-File Mental Model

### Server (PHP API + SPA)

| File | Mental model |
|---|---|
| `site/public/api/index.php` | Front controller. Parses `PATH_INFO`, dispatches to handler. The ONLY entry point for API. |
| `site/public/api/routes.php` | Route table. `route('GET', '/api/...', 'handler_name')`. Match via regex with `{id}` capture groups. |
| `site/public/api/.htaccess` | Rewrites `/api/*` → `index.php/$1`. |
| `site/config/app.php` | Constants: paths, env flags, CORS origins. Loaded everywhere. |
| `site/config/database.php` | PDO singleton (static), PRAGMA settings, `initSchema()` (CREATE TABLE users), `seedDatabase()` (idempotent — admin user only), `bootstrapDatabase()`. |
| `site/config/response.php` | `success_response($data)`, `error_response($msg, $status, $errors)`. Always JSON. |
| `site/public/api/middleware/auth.php` | `require_auth()` — abort 401 if no session. `is_authenticated()` getter. |
| `site/public/api/middleware/csrf.php` | `require_csrf()` — abort 403 if no token match. `generate_csrf_token()` (session-persistent). |
| `site/public/api/helpers.php` | `parse_request_body()` — for PUT multipart, PHP doesn't auto-parse `$_POST` so we read `php://input`. |
| `site/public/api/handlers/csrf.php` | `get_csrf_token()` — GET /api/csrf-token. |
| `site/public/api/handlers/auth.php` | `login_handler()`, `logout_handler()`, `me_handler()` — session-based auth. |
| `site/public/api/handlers/admin_index.php` | `admin_index_handler()` — GET /api/admin, returns `{message: "this is admin"}`. |
| `site/seed.php` | CLI seeder. `php seed.php` is idempotent — runs on Docker container boot. Seeds admin user only. |
| `docker/Dockerfile` | `php:8.3-apache` + `pdo_sqlite` + `mod_rewrite` + custom `apache.conf`. |
| `docker/entrypoint.sh` | `chown data/uploads` → `php seed.php` → `apache2-foreground`. |
| `docker/apache.conf` | `<Directory>` rules: deny `data/`, allow `public/`, `Options -Indexes` for uploads. |

### Frontend

| File | Mental model |
|---|---|
| `frontend/src/App.svelte` | Router shell. Defines routes, mounts correct page. GATES admin pages until auth verified. GSAP fade-in on mount. |
| `frontend/src/main.ts` | Mounts App. |
| `frontend/src/lib/api/client.ts` | `api` object: `get`, `post`, `put`, `delete`. Auto-fetches CSRF token, attaches as header. Throws `ApiError` on non-2xx. CSRF cleared on 401. |
| `frontend/src/lib/api/index.ts` | Domain modules: `authApi` (login, logout, me), `healthApi` (check). |
| `frontend/src/lib/stores/auth.svelte.ts` | Auth state with `$state`. `init()` checks `/api/auth/me`, `login()`, `logout()`. |
| `frontend/src/lib/stores/lang.svelte.ts` | Thin proxy to i18n store. `langStore.toggle()` delegates to `i18n.toggle()`. |
| `frontend/src/lib/router.svelte.ts` | History API SPA router. `defineRoute()` + `matchRoute()` + `navigate()` + `router.go()`. Intercepts `<a>` clicks for SPA navigation. |
| `frontend/src/lib/i18n/index.svelte.ts` | `t(key, vars?)` function + `i18n` store with persistent localStorage. Supports en, id. Base: en. |
| `frontend/src/lib/types/index.ts` | TypeScript types: `ApiResponse`, `User`, `Lang`. |
| `frontend/src/routes/Home.svelte` | Plain landing page with header (lang toggle + admin link), hello message, footer. |
| `frontend/src/routes/admin/Login.svelte` | Login form. Uses `value=` + `oninput` (NOT `bind:value`). |
| `frontend/src/routes/admin/Admin.svelte` | Simple admin page showing "(this is admin)" + logout link. |
| `frontend/src/routes/NotFound.svelte` | 404 page. |
| `frontend/src/assets/styles/tokens.css` | Design system: colors, fonts, spacing. CSS variables only. |
| `frontend/src/assets/styles/public.css` | All page styles (header, main, footer, login, admin, not-found). |

---

## API Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/api/health` | No | Health check — returns status, time, env, DB tables |
| GET | `/api/info` | No | App info — name, version, PHP version |
| GET | `/api/csrf-token` | No | Get CSRF token (session-persistent) |
| POST | `/api/auth/login` | CSRF | Login with username + password → sets session |
| POST | `/api/auth/logout` | CSRF | Destroy session |
| GET | `/api/auth/me` | Session | Get current user info |
| GET | `/api/admin` | Session | Admin endpoint — returns `{message: "this is admin"}` |

---

## Common Tasks

### Add a new API endpoint
1. **Server (PHP)** — add function in appropriate handler file (`site/public/api/handlers/*.php`)
2. **Routes** — add `route('GET', '/api/...', 'handler_name')` in `routes.php`
3. **Frontend API** — add method in `frontend/src/lib/api/index.ts`
4. **Frontend page** — call from your Svelte component, handle loading/error states

### Add a new public page
1. **Frontend** — create page in `frontend/src/routes/MyPage.svelte`
2. **Register route** — in `App.svelte`, add `defineRoute('/mypage')` and add case in `getPageFromPath()`
3. **i18n** — add new keys to `frontend/src/lib/i18n/{en,id}.json` (BOTH files)
4. **Build** — `pnpm build && pnpm run deploy:sync`

---

## Critical Patterns (DO NOT BREAK)

### 1. **Form components use `value` + `oninput`, NOT `bind:value`**
```svelte
<!-- CORRECT -->
<input value={username} oninput={(e) => (username = e.target.value)} />

<!-- WRONG — silently fails when parent value is undefined -->
<input bind:value={username} />
```
Reason: `$bindable` causes `props_invalid_value` when parent value is undefined.

### 2. **Bilingual i18n — always add to BOTH files**
When adding a new translation key, update `en.json` AND `id.json`:
```json
// en.json
"my_key": "My text"
// id.json
"my_key": "Teks saya"
```
The `t()` function falls back to the key name if missing.

### 3. **Language switch in UI**
```svelte
<button onclick={() => langStore.toggle()}>
  {t('lang_toggle')}
</button>
```
`langStore` proxies to `i18n.toggle()` which updates `document.documentElement.lang` and persists to localStorage.

### 4. **Admin route gating in App.svelte**
```svelte
const isAdminProtected = $derived(
  router.current.path.startsWith('/admin') && router.current.path !== '/admin/login'
);
const showPage = $derived(
  !isAdminProtected || (authStore.initialized && authStore.isAuthenticated)
);
```
This prevents race conditions where child `onMount` fires before auth check completes.

### 5. **CSRF in client.ts**
- Token cached after first `GET /api/csrf-token`
- Auto-attached to all non-GET requests
- Cleared on 401 (forces re-fetch)
- Server validates with `hash_equals()` (timing-safe)

### 6. **Sync script preserves uploads**
`pnpm run deploy:sync`:
- Wipes `site/public/assets/` and copies `dist/assets/`
- Copies `dist/index.html` → `site/public/index.html`
- **Never touches** `site/public/uploads/`
- **Never touches** `site/data/sanggar.sqlite`

### 7. **Two deployment ZIP modes**
- **Full deploy** (`scripts/package-deploy.sh`): ZIPs entire `site/` excluding SQLite DB, uploads, PHP backups.
- **UI-only deploy** (`scripts/package-assets.sh`): ZIPs only `site/public/assets/` (+ optional `index.html`). Output to `deployment/` folder.
- Both scripts are idempotent and print sanity-check results.

---

## Known Issues & TODOs

### TODO (not yet implemented)
- [ ] **Change password UI** — admin still uses `admin/admin123`. Manual via SQLite:
  ```bash
  php -r "echo password_hash('NEW_PASSWORD', PASSWORD_DEFAULT);" > /tmp/hash
  sqlite3 site/data/sanggar.sqlite "UPDATE users SET password=$(cat /tmp/hash) WHERE username='admin';"
  ```
- [ ] **Rate limiting on login** — currently unlimited attempts. Add token bucket per IP.
- [ ] **CSRF rotation on login** — current token persists across logins. Best practice: regenerate on auth state change.
- [ ] **Frontend tests** — no unit/e2e tests.
- [ ] **Server tests** — no PHP unit tests.

---

## Testing Strategy

### Local development
```bash
# Server (PHP API + SPA)
docker compose up -d

# Frontend
cd frontend && pnpm dev
```

### Before commit
```bash
cd frontend && pnpm check
```

### Before deploy
```bash
cd frontend && pnpm build && pnpm run deploy:sync
./scripts/package-deploy.sh
```

---

## License & Credits

- **Code**: MIT