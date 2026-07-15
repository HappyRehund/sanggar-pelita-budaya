# Deployment Guide — Sanggar Pelita Budaya

Target: Shared Hosting cPanel (Rumahweb, Niagahoster, dll). Aplikasi di-deploy sebagai static ZIP — tidak perlu Node.js di server.

---

## Arsitektur Deploy

```
Document Root (public_html/ di cPanel)
├── index.html           ← dari site/public/index.html
├── assets/              ← JS, CSS (built)
├── uploads/             ← (persistent, for future use)
└── api/                 ← PHP entry points
    ├── index.php
    ├── .htaccess
    └── ...

Di PARENT directory (di luar Document Root):
├── config/              ← config/*.php (paths, constants)
├── data/                ← sanggar.sqlite (database)
└── seed.php             ← CLI seeder (tidak dipakai di production)
```

⚠️ **Atau** lebih aman: set Document Root ke `public_html/site/public/` sehingga `config/` dan `data/` benar-benar di luar web access.

---

## Source Project Structure

### `frontend/` — Svelte 5 SPA source

```
frontend/
├── index.html                      # Vite entry
├── package.json                    # Dependencies + scripts
├── .env.example                    # VITE_API_BASE
├── scripts/
│   └── sync-to-site.js             # Copies dist/ → site/public/
└── src/
    ├── main.ts                     # Mounts <App>
    ├── App.svelte                  # Router shell, auth gate, GSAP fade-in
    ├── lib/
    │   ├── api/
    │   │   ├── client.ts           # Fetch wrapper + CSRF
    │   │   └── index.ts            # authApi, healthApi
    │   ├── i18n/
    │   │   ├── index.svelte.ts     # i18n store + t() function
    │   │   ├── en.json             # English (base)
    │   │   └── id.json             # Indonesian
    │   ├── stores/
    │   │   ├── auth.svelte.ts      # Auth state
    │   │   └── lang.svelte.ts      # Language proxy
    │   ├── types/index.ts          # TypeScript types
    │   └── router.svelte.ts        # History API SPA router
    ├── routes/
    │   ├── Home.svelte             # Plain landing page
    │   ├── NotFound.svelte         # 404
    │   └── admin/
    │       ├── Login.svelte        # Login form
    │       └── Admin.svelte        # "(this is admin)" page
    └── assets/styles/
        ├── tokens.css              # Design system
        └── public.css              # Page styles
```

### `site/` — PHP API + SPA shell (yang di-deploy)

```
site/
├── .htaccess                       # Denies *.sqlite
├── seed.php                        # CLI seeder (admin user)
├── config/
│   ├── app.php                     # Constants: paths, env, CORS
│   ├── database.php                # PDO + initSchema + seedDatabase
│   └── response.php                # JSON helpers
├── data/                           # SQLite database (outside Document Root)
│   └── sanggar.sqlite              # Auto-created on first boot
└── public/                         # Document Root
    ├── index.html                  # Built SPA
    ├── .htaccess                   # SPA fallback
    ├── assets/                     # Built JS/CSS
    ├── uploads/                    # (for future use)
    └── api/
        ├── .htaccess               # Rewrite /api/* → index.php
        ├── index.php               # Front controller
        ├── routes.php              # Route table
        ├── helpers.php             # Request body parser
        ├── middleware/
        │   ├── auth.php            # require_auth()
        │   └── csrf.php            # require_csrf()
        └── handlers/
            ├── csrf.php            # GET /api/csrf-token
            ├── auth.php            # login, logout, me
            └── admin_index.php     # GET /api/admin
```

### `docker/` — Development container (TIDAK di-deploy)

```
docker/
├── Dockerfile                      # php:8.3-apache + pdo_sqlite + mod_rewrite
├── apache.conf                     # VirtualHost + Directory rules
├── entrypoint.sh                   # chown → seed.php → apache2-foreground
└── php.ini                         # PHP config
```

---

## Pre-Deploy Checklist

- [ ] Frontend sudah di-built: `pnpm build` di `frontend/`
- [ ] Sync sudah dijalankan: `pnpm run deploy:sync`
- [ ] `site/public/index.html` ada
- [ ] `site/public/assets/` ada (JS, CSS)
- [ ] Deployment ZIP sudah di-generate:
  - UI-only: `./scripts/package-assets.sh --with-index` → `deployment/deploy-assets.zip`
  - Full: `./scripts/package-deploy.sh` → `deploy-sanggar-pelita.zip`
- [ ] Ganti password admin sebelum go-live (TODO: belum ada UI, manual via SQLite)

---

## Step-by-Step: Rumahweb cPanel

### 1. Login ke cPanel
Buka https://[domain].com/cpanel atau https://[domain].com:2083

### 2. Buka File Manager
Sidebar → **Files** → **File Manager**

### 3. Navigate ke `public_html/`

### 4. Upload ZIP
- Drag file ZIP dari komputer ke area upload, atau click **Upload** button

### 5. Extract ZIP
- Right-click pada ZIP → **Extract**
- Pilih destination: `/public_html/`
- Delete ZIP setelah extract

### 6. Set Document Root

**Opsi A: Set ke `public_html/site/public/`** (recommended — paling aman)
- Buka **Domains** atau **Subdomains** di cPanel
- Ubah "Document Root" ke `/public_html/site/public/`

**Opsi B: Set tetap di `public_html/`**
- Pindahkan isi `site/public/*` ke `public_html/`
- Pindahkan `site/api/`, `site/config/`, `site/data/` ke parent directory

### 7. Set File Permissions
- `data/sanggar.sqlite` → 644 atau 664
- `data/` directory → 755
- `*.php` files → 644

### 8. Verify .htaccess
- `site/public/.htaccess` ada (SPA fallback)
- `site/public/api/.htaccess` ada (API rewrite)
- `site/.htaccess` ada (denies *.sqlite)

### 9. Test Website
- Buka `https://[domain].com` di browser
- Cek:
  - [ ] Homepage load dengan hello message
  - [ ] Click EN/ID toggle → bahasa berubah (bilingual)
  - [ ] `/admin/login` → login form
  - [ ] Login dengan `admin`/`admin123` → admin page "(this is admin)"
  - [ ] Logout works

### 10. Verify API
```bash
curl https://[domain].com/api/health
# Expected: {"success":true,"data":{"status":"ok",...}}

curl https://[domain].com/api/info
# Expected: {"success":true,"data":{"app":"Sanggar Pelita Budaya API",...}}
```

### 11. Force HTTPS
cPanel → **SSL/TLS Status** atau **Let's Encrypt SSL**:
- Aktifkan AutoSSL untuk domain
- Tambahkan ke `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Updating Website (Redeploy)

### Skenario A: Update frontend saja (umum)

```bash
cd frontend
pnpm build
pnpm run deploy:sync
cd ..
./scripts/package-assets.sh --with-index
```

Upload `deployment/deploy-assets.zip` ke cPanel `public_html/`, delete old `assets/`, extract ZIP.

⚠️ **JANGAN overwrite folder `uploads/`**.

### Skenario B: Update site code (jarang)

```bash
cd frontend
pnpm build
pnpm run deploy:sync
cd ..
./scripts/package-deploy.sh
```

Upload `deploy-sanggar-pelita.zip` ke cPanel, extract ke `/public_html/`.

⚠️ **JANGAN overwrite** `data/` dan `public/uploads/`.

---

## Backup & Restore

### Backup database
```bash
scp user@server:~/public_html/site/data/sanggar.sqlite ./backup-$(date +%Y%m%d).sqlite
```

### Automated backup (recommended)
cPanel → **Cron Jobs**:
```bash
0 2 * * * cp ~/public_html/site/data/sanggar.sqlite ~/backups/db-$(date +\%Y\%m\%d).sqlite
```

---

## Troubleshooting

| Problem | Solusi |
|---|---|
| **404 Not Found di semua route** | `.htaccess` tidak aktif. Pastikan `mod_rewrite` enabled |
| **API returns 500** | Cek `error_log` di cPanel. Biasanya PHP error |
| **Login gagal terus** | SQLite file corrupt atau permission salah. Cek perms 644 |
| **Form submit 400** | CSRF token mismatch. Coba hard refresh |
| **Blank page setelah extract** | Cek `index.html` ada di Document Root |

---

## Production Security Checklist

- [ ] Password admin diganti dari default
- [ ] HTTPS aktif (AutoSSL)
- [ ] `data/` di luar Document Root
- [ ] `.htaccess` aktif
- [ ] Tidak ada file `.sqlite` di Document Root
- [ ] CSRF token works (test login)
- [ ] File permissions: file 644, directory 755

---

## Catatan Tambahan

- **Default admin** (`admin`/`admin123`) — **HARUS diganti** sebelum go-live
- **PHP version** — pastikan cPanel pakai PHP 8.1+
- **SQLite** — biasanya sudah enabled di shared hosting modern

Lihat juga: [AGENTS.md](./.opencode/AGENTS.md) untuk development workflow, [README.md](./README.md) untuk overview.