# AGENTS.md — Guide for AI Agents & Developers

This document orients future agents (human or AI) working on this codebase. It captures **why** decisions were made, not just **what** the code does.

---

## What This Project Is

A cultural organization website for "Sanggar Pelita Budaya" — an Indonesian traditional art studio. The site has two main areas:

- **Public website** — Home, About, Organization, Portfolio (list + detail), Contact, 404 — with museum-inspired editorial design, scroll-reveal animations, and full SEO metadata
- **CMS** — Admin dashboard with Portfolio CRUD (incl. RichTextEditor + image upload + gallery management), Organization member management, Hero/Footer/Settings editors

Built as **Svelte 5 SPA + PHP REST API + SQLite**, target deployment to **shared hosting cPanel** (Rumahweb). No Node.js in production.

---

## Core Architectural Decisions

### 1. **Frontend Svelte SPA (not SvelteKit)**
- Plain Vite + Svelte 5 + TypeScript. Output is static HTML + CSS + JS.
- Custom History API router (`$lib/router.svelte.ts`) with `:param` support.
- Admin pages are **code-split** via dynamic `import()` — only loaded when admin route is accessed.

### 2. **Native PHP, no framework**
- Layered architecture: Controllers → Services → Repositories → SQLite.
- No Composer; autoloader in `helpers.php` maps class suffix to directory.
- All endpoints return JSON `{success, message, data}` or `{success: false, message, errors}`.

### 3. **SQLite, not MySQL**
- Single file at `site/data/sanggar.sqlite` (outside Document Root).
- WAL mode + busy_timeout 5s for concurrent access.
- 5 tables: users, portfolio, portfolio_media, organization_members, settings.
- Hero and Footer are **static frontend content** (i18n + `lib/constants/siteContent.ts`) — not in the DB.

### 4. **CSRF via double-submit token**
- `GET /api/csrf-token` returns token, client sends in `X-CSRF-Token` header on non-GET requests.
- Frontend `client.ts` auto-fetches + caches token, clears on 401.

### 5. **Session cookie with SameSite=Lax**
- Native PHP sessions, `session_regenerate_id` on login.
- Session keys: `admin_logged_in`, `admin_user_id`, `admin_username`, `admin_full_name`.

### 6. **i18n custom (EN/ID bilingual)**
- Dictionaries in `src/lib/i18n/{en,id}.json` (297 keys each).
- `t(key, vars?)` function + `i18n` store with persistent localStorage.
- Both files must be updated together (AGENTS.md pattern #2).

### 7. **Design: Cultural museum-inspired**
- Cormorant Garamond (serif headings), Inter (sans body), Great Vibes (script accent).
- Color palette: deep red (#9e2a2b), warm gold (#c9a227), ivory (#fbf7f0), cream, dark brown.
- Spacing scale 4–128px, radii 12–32px, soft shadows.
- GSAP scroll-reveal animations via `lib/utils/animations.ts`.

### 8. **Feature-based module architecture**
- `modules/home/`, `modules/about/`, `modules/organization/`, `modules/portfolio/`, `modules/contact/`, `modules/admin/`
- Each module owns its components + pages.
- `lib/components/` contains only cross-module reusable components (24 components).
- `layouts/` contains PublicLayout, AdminLayout, Navbar, Footer.

### 9. **Image upload flow**
- Files stored in `site/public/uploads/{portfolio,organization,settings,documents}/`.
- Unique filenames: `YYYYMMDD_<8hex>.<ext>`.
- SQLite stores metadata only (filename, mime, dimensions, size, alt_text, sort_order).
- Deleting a portfolio unlinks all associated files (orphan cleanup in transaction).
- Hero background and Navbar/Footer logos are **static frontend assets** under `frontend/src/assets/` (not uploaded).

### 10. **Code-splitting for performance**
- Public pages are statically imported (critical path).
- Admin pages use dynamic `import()` — loaded on-demand when admin route is accessed.
- Vite manualChunks: `vendor` (svelte+gsap), `sanitizer` (dompurify+marked).

---

## File-by-File Mental Model

### Backend (PHP API)

| File | Mental model |
|---|---|
| `site/public/api/index.php` | Front controller. Session, multipart, CORS, OPTIONS, bootstrap DB, dispatch. |
| `site/public/api/routes.php` | Route table + controller instantiation. All routes registered here. |
| `site/public/api/helpers.php` | Autoloader, `get_json_input()`, `get_uploaded_file()`, multipart parsing. |
| `site/public/api/helpers/formatters.php` | `formatPortfolioRow()`, `formatMediaRow()`, `formatOrganizationRow()`, `buildPaginationMeta()`. |
| `site/public/api/controllers/` | Thin: AuthController, CsrfController, PortfolioController, OrganizationController, SettingsController, DashboardController. |
| `site/public/api/services/` | Business logic: AuthService, ValidationService, UploadService, PortfolioService, PortfolioMediaService, OrganizationService, SettingsService, DashboardService. |
| `site/public/api/repositories/` | PDO-only: User, Portfolio, PortfolioMedia, Organization, Settings repositories. |
| `site/public/api/middleware/` | `auth.php` (require_auth, is_authenticated, get_current_*), `csrf.php` (generate, validate, require). |
| `site/config/app.php` | Constants: paths, CORS, upload limits, per-page limits. |
| `site/config/database.php` | PDO singleton, `initSchema()` (5 tables + indexes + FKs + DROP of legacy hero/footer), `migrateLegacyUsersTable()`, seed functions. |
| `site/config/response.php` | `success_response()`, `error_response()`, `validation_error_response()`, `not_found_response()`, `unauthorized_response()`, `forbidden_response()`. |
| `site/seed.php` | CLI seeder: admin user + default settings. Idempotent. |

### Frontend

| File | Mental model |
|---|---|
| `frontend/src/App.svelte` | Router shell. 3-way layout: PublicLayout (public), AdminLayout (admin), bare (login). Static imports for public pages, dynamic `import()` for admin pages. GSAP page transitions. |
| `frontend/src/lib/router.svelte.ts` | History API SPA router. `defineRoute()` + `matchRoute()` + `navigate()` + `router.go()`. Supports `:param` capture. |
| `frontend/src/lib/api/client.ts` | `api` object: get/post/put/delete. Auto CSRF token, `ApiError` on non-2xx. |
| `frontend/src/lib/api/endpoints.ts` | Centralized API path constants. |
| `frontend/src/lib/api/index.ts` | Domain modules: authApi, healthApi, portfolioApi, organizationApi, settingsApi, dashboardApi. |
| `frontend/src/lib/stores/` | auth, lang, notification (toast), loading, portfolio, organization, settings. |
| `frontend/src/lib/hooks/` | usePagination, useSearch, useDebounce, useIntersection, useLightbox. |
| `frontend/src/lib/components/` | 24 reusable components: Button, Badge, Card, Input, Textarea, Select, Checkbox, Radio, Modal, Drawer, Accordion, Carousel, Toast, Pagination, Lightbox, Tabs, Dropdown, Skeleton, EmptyState, Spinner, SectionTitle, Image, Icon, FileUpload, RichTextEditor. |
| `frontend/src/lib/utils/` | dateFormatter, slugify, imageUrl, fileSizeFormatter, debounce, animations (GSAP reveal/parallax/stagger). |
| `frontend/src/lib/constants/` | routes, categories, uploadLimits, fileTypes, languages, siteContent (static Hero/Footer URLs + socials). |
| `frontend/src/lib/types/index.ts` | All entity types: User, Portfolio, PortfolioMedia, OrganizationMember, Settings, DashboardData, PaginationMeta, PaginatedResponse, PortfolioListQuery, etc. (Hero/Footer removed — static frontend content). |
| `frontend/src/layouts/` | PublicLayout (Navbar + slot + Footer), AdminLayout (sidebar + topbar + slot), Navbar, Footer. |
| `frontend/src/modules/` | Feature modules: home (8 section components + HomePage), about, organization, portfolio (list + detail), contact, admin (Dashboard, PortfolioAdmin, OrganizationAdmin, SettingsAdmin, ConfirmDialog). |
| `frontend/src/routes/` | Thin route assemblers: Home, NotFound, admin/Login. |

---

## API Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/api/health` | No | Health check |
| GET | `/api/info` | No | App info |
| GET | `/api/csrf-token` | No | Get CSRF token |
| POST | `/api/login` | CSRF | Login |
| POST | `/api/logout` | CSRF | Logout |
| GET | `/api/session` | Session | Current user |
| GET | `/api/portfolio` | No | List (pagination, filters, search, sort) |
| GET | `/api/portfolio/featured` | No | Featured portfolio |
| GET | `/api/portfolio/gallery` | No | All gallery images |
| GET | `/api/portfolio/slug/{slug}` | No | Get by slug (with media + related) |
| GET | `/api/portfolio/{id}` | No | Get by ID |
| POST | `/api/portfolio` | Auth+CSRF | Create |
| PUT | `/api/portfolio/{id}` | Auth+CSRF | Update |
| DELETE | `/api/portfolio/{id}` | Auth+CSRF | Delete (+ orphan cleanup) |
| POST | `/api/portfolio/{id}/media` | Auth+CSRF | Upload media |
| DELETE | `/api/portfolio/media/{id}` | Auth+CSRF | Delete media |
| PUT | `/api/portfolio/media/reorder` | Auth+CSRF | Reorder gallery |
| GET | `/api/organization` | No | List members |
| GET | `/api/organization/tree` | No | Hierarchy tree |
| POST | `/api/organization` | Auth+CSRF | Create member |
| PUT | `/api/organization/{id}` | Auth+CSRF | Update member |
| DELETE | `/api/organization/{id}` | Auth+CSRF | Delete member |
| PUT | `/api/organization/reorder` | Auth+CSRF | Reorder members |
| POST | `/api/organization/{id}/photo` | Auth+CSRF | Upload photo |
| GET | `/api/settings` | No | Get settings |
| PUT | `/api/settings` | Auth+CSRF | Update settings |
| GET | `/api/dashboard` | Auth | Dashboard stats + recent uploads |

> **Hero and Footer are static frontend content** — driven by i18n keys + `frontend/src/lib/constants/siteContent.ts`. No `/api/hero` or `/api/footer` endpoints exist.

---

## Critical Patterns (DO NOT BREAK)

### 1. **Form components use `value` + `oninput`, NOT `bind:value`**
```svelte
<input value={username} oninput={(e) => (username = e.target.value)} />
```

### 2. **Bilingual i18n — always add to BOTH files**
Update `en.json` AND `id.json` together.

### 3. **Admin route gating in App.svelte**
`showPage` derived prevents onMount race. Admin pages use dynamic `import()`.

### 4. **CSRF in client.ts**
Token cached, auto-attached to non-GET, cleared on 401.

### 5. **Sync script preserves uploads**
`pnpm run deploy:sync` wipes `assets/` but never touches `uploads/` or `data/`.

### 6. **Prepared statements only**
All repositories use PDO prepared statements. Never concatenate SQL.

### 7. **Transactions for multi-step operations**
Portfolio create/delete, media reorder, organization reorder — all wrapped in transactions with rollback.

### 8. **Hero & Footer are static frontend content**
- Hero and Footer are **not** CMS-managed. No DB tables, no API endpoints, no admin pages.
- Translatable text lives in i18n (`hero_*`, `footer_*` keys in `en.json` / `id.json`).
- Non-translatable URLs (maps URL, social URLs, hero CTA target) live in `frontend/src/lib/constants/siteContent.ts`.
- Hero background image: `frontend/src/assets/hero-bg.webp` (imported directly).
- Navbar + Footer logo: `frontend/src/assets/logo-sanggar-pelita-budaya.svg` (imported directly).
- Only **Settings, Organization, and Portfolio** remain CMS-managed via the Admin.

---

## Testing Strategy

### Local development
```bash
docker compose up -d     # Backend (PHP API + SQLite)
cd frontend && pnpm dev  # Frontend (Vite dev server)
```

### Before commit
```bash
cd frontend && pnpm check  # svelte-check (0 errors required)
```

### Before deploy
```bash
cd frontend && pnpm build && pnpm run deploy:sync
./scripts/package-deploy.sh
```

---

## License & Credits

- **Code**: MIT