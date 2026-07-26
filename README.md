# Santrix Archive

Monorepo arsip proyek **Santrix** — Platform Manajemen Pesantren Modern (All-in-One).

---

## Struktur Proyek

```
santrix-archive/
├── santrix/          # Backend Laravel 12 (Multi-Tenant SaaS)
├── santrix-launcher/ # Desktop App Tauri (Wrapper)
└── santrix-mobile/   # Mobile App Flutter
```

---

## santrix — Laravel Backend

Platform web utama berbasis **Laravel 12** dengan arsitektur **Multi-Tenant** (subdomain-based).

### Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12.x (PHP 8.2+) |
| Database | MySQL 8.0 |
| Frontend | Blade, Tailwind CSS v4, Alpine.js |
| Payment | Duitku / Midtrans |
| Notifikasi | WhatsApp via Fonnte |
| Desktop | Tauri (santrix-launcher) |
| Mobile | Flutter (santrix-mobile) |

### Fitur Utama

- **Multi-Tenant** — Setiap pesantren punya subdomain unik (`nama.santrix.my.id`)
- **Keuangan & SPP** — Tagihan syahriah otomatis, payment gateway, laporan PDF
- **Data Santri** — Database santri lengkap, asrama, mutasi, kartu digital
- **Akademik** — Nilai, e-rapor, kalender, jadwal pelajaran
- **Owner Dashboard** — Kelola semua tenant dari satu panel
- **Multi-Role** — Admin, Sekretaris, Bendahara, Pendidikan

### Quick Start (Development)

```bash
# 1. Clone & masuk ke folder santrix
cd santrix

# 2. Install dependencies
composer install
pnpm install   # atau npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi .env (database, dsb.)
# DB_CONNECTION=mysql
# DB_DATABASE=santrix
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Migrate & seed
php artisan migrate --seed

# 6. Jalankan server (1 perintah, semua service jalan)
composer run dev
```

### URL Development (Localhost)

| Portal | URL |
|--------|-----|
| Landing Page | `http://127.0.0.1:8000` |
| Login Owner | `http://127.0.0.1:8000/login` |
| Owner Dashboard | `http://127.0.0.1:8000/owner` |
| Login Tenant | `http://127.0.0.1:8000/tenant/login` |
| Dashboard Tenant | `http://127.0.0.1:8000/tenant/admin` |

### Akun Default (Setelah Seed)

| Role | Email | Password |
|------|-------|----------|
| Owner | `nawawimahinutsman@gmail.com` | `OwnerSantrix200601` |
| Admin | `admin@santrix.com` | `password` |
| Bendahara | `bendahara@santrix.com` | `password` |
| Sekretaris | `sekretaris@santrix.com` | `password` |
| Pendidikan | `pendidikan@santrix.com` | `password` |

### Arsitektur Multi-Tenant (Production)

```
santrix.my.id              → Landing Page
owner.santrix.my.id        → Owner Dashboard
pesantren1.santrix.my.id   → Tenant: Pesantren 1
pesantren2.santrix.my.id   → Tenant: Pesantren 2
```

### Deployment ke VPS

```bash
# Post-deploy commands
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## santrix-launcher — Desktop App (Tauri)

Wrapper desktop berbasis **Tauri v2** yang membungkus frontend Santrix.

### Jalankan

```bash
cd santrix-launcher
pnpm tauri dev
```

---

## santrix-mobile — Mobile App (Flutter)

Aplikasi mobile cross-platform berbasis **Flutter**.

### Jalankan

```bash
cd santrix-mobile
flutter pub get
flutter run
```

---

## Kontribusi

Dikembangkan oleh **Mahin Utsman Nawawi, S.H** & Tim **Velora**.

> Dedikasi untuk kemajuan digitalisasi Pesantren Indonesia. 🕌

---

© 2026 Santrix Project — All rights reserved.
