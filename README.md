# Sanggar Pelita Budaya

Website for Sanggar Pelita Budaya — a cultural studio. Built as Svelte SPA + PHP REST API + SQLite, deployed to shared hosting cPanel (Rumahweb).

---

## About

This is a starter project with:
- **Plain frontend** — landing page with bilingual (EN/ID) language toggle and GSAP fade-in animation
- **Admin login** — session-based auth with CSRF protection
- **Admin page** — simple text showing "(this is admin)" after login
- **Health check API** — backend ready with `/api/health` and `/api/info` endpoints

### Languages
- **English (en)** — base language
- **Indonesian (id)** — secondary language

### Pages

| Page | Content |
|------|---------|
| **Home** (`/`) | Hello message with language toggle |
| **Admin Login** (`/admin/login`) | Login form (admin/admin123) |
| **Admin** (`/admin`) | "(this is admin)" text + logout |
| **404** | Not found page |

---

## Tech Stack

- **Frontend**: Svelte 5 + Vite 6 + TypeScript, GSAP for animations
- **Backend**: Native PHP 8.3 (no framework), SQLite database
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

## Documentation

- **[DEPLOYMENT.md](./DEPLOYMENT.md)** — Deployment guide for cPanel shared hosting
- **[.opencode/AGENTS.md](./.opencode/AGENTS.md)** — Technical guide for developers and AI agents

---

## License

MIT