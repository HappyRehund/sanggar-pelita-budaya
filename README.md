# Sanggar Pelita Budaya

Website for Sanggar Pelita Budaya — a cultural studio. Built as Svelte SPA + PHP REST API + SQLite, deployed to shared hosting cPanel (Rumahweb).

---

## About

A production-ready cultural organization website with a lightweight Content Management System (CMS). Museum-inspired editorial design with bilingual (EN/ID) support.

### Public Website
- **Home** — Hero, About preview, Vision & Mission, Organization preview, Services carousel, Featured portfolio, Gallery, Contact CTA
- **About** — History, background, vision, mission, core values (editorial long-form layout)
- **Organization** — Hierarchical org tree with member cards
- **Portfolio** — Filterable/searchable grid with pagination + detail pages (rich content, gallery, YouTube embed, related)
- **Contact** — Contact info cards + Google Maps embed
- **404** — Premium not-found page

### CMS
- **Dashboard** — 6 stat cards, recent uploads, quick actions
- **Portfolio** — Full CRUD with RichTextEditor, cover/gallery image upload, draft/publish, featured toggle, SEO fields
- **Organization** — Member CRUD with photo upload, parent assignment, display order
- **Hero** — Single-record editor with background image upload
- **Footer** — Single-record editor with logo upload, social links, maps URL
- **Settings** — Site name, description, language, logo/favicon/OG image uploads

### Languages
- **English (en)** — base language
- **Indonesian (id)** — secondary language

---

## Tech Stack

- **Frontend**: Svelte 5 + Vite 6 + TypeScript, GSAP for animations, @lucide/svelte icons
- **Backend**: Native PHP 8.3 (no framework), SQLite database, layered architecture (Controllers → Services → Repositories)
- **Deploy**: Shared hosting cPanel (Rumahweb) — no Node.js in production

---

## Development

```bash
# Start backend (PHP API + SQLite)
docker compose up -d

# Start frontend (Vite dev server)
cd frontend && pnpm install && pnpm dev
```

Frontend runs at `http://localhost:5173`, backend at `http://localhost:8080`.

### Default admin
- Username: `admin`
- Password: `admin123`
- **⚠️ Change before go-live** (manual via SQLite or future UI)

---

## Build & Deploy

```bash
# Build frontend
cd frontend && pnpm build

# Sync to site/public
pnpm run deploy:sync

# Package for deployment
cd ..
./scripts/package-deploy.sh         # Full ZIP → deploy-sanggar-pelita.zip
./scripts/package-assets.sh --with-index  # UI-only → deployment/deploy-assets.zip
```

See [DEPLOYMENT.md](./DEPLOYMENT.md) for detailed cPanel deployment instructions.

---

## Project Structure

```
frontend/src/
├── App.svelte              # Router shell (3-way layout, code-split admin)
├── assets/styles/          # tokens.css, global.css
├── layouts/                # PublicLayout, AdminLayout, Navbar, Footer
├── lib/
│   ├── api/                # client.ts, endpoints.ts, domain modules
│   ├── components/          # 24 reusable UI components
│   ├── constants/           # routes, categories, uploadLimits, fileTypes
│   ├── hooks/               # usePagination, useSearch, useDebounce, useIntersection, useLightbox
│   ├── i18n/                # en.json, id.json, t() function
│   ├── router.svelte.ts     # History API SPA router
│   ├── stores/              # auth, lang, notification, loading, portfolio, organization, settings
│   ├── types/               # All entity TypeScript interfaces
│   └── utils/               # dateFormatter, slugify, imageUrl, animations, etc.
├── modules/
│   ├── home/                # 8 section components + HomePage
│   ├── about/               # AboutPage
│   ├── organization/        # OrganizationPage
│   ├── portfolio/           # PortfolioListPage, PortfolioDetailPage
│   ├── contact/             # ContactPage
│   └── admin/               # Dashboard, PortfolioAdmin, OrganizationAdmin, HeroAdmin, FooterAdmin, SettingsAdmin
└── routes/                  # Home, NotFound, admin/Login

site/
├── config/                  # app.php, database.php, response.php
├── data/                    # sanggar.sqlite (outside Document Root)
├── public/
│   ├── api/
│   │   ├── controllers/     # 8 thin controllers
│   │   ├── services/        # 10 service classes (business logic)
│   │   ├── repositories/    # 7 repository classes (PDO only)
│   │   ├── middleware/      # auth.php, csrf.php
│   │   └── helpers/         # formatters.php
│   └── uploads/             # hero/, portfolio/, organization/, settings/, documents/
└── seed.php                 # CLI seeder (admin + default hero/footer/settings)
```

---

## Documentation

- **[DEPLOYMENT.md](./DEPLOYMENT.md)** — Deployment guide for cPanel shared hosting
- **[.opencode/AGENTS.md](./.opencode/AGENTS.md)** — Technical guide for developers and AI agents

---

## License

MIT