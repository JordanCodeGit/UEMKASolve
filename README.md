<p align="center">
  <img src="logo-uemkasolve.png" alt="UEMKASolve logo" width="120"/>
</p>

<h1 align="center">UEMKASolve</h1>

<p align="center"><b>From receipts to reports.</b></p>

<p align="center">
  The digital financial ledger &amp; cash-management platform for Indonesian MSMEs (UMKM) —<br/>
  buku kas digital dengan pemindaian struk berbasis AI, laporan keuangan, dan manajemen tim.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11"/>
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+"/>
  <img src="https://img.shields.io/badge/Vite-5-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 5"/>
  <img src="https://img.shields.io/badge/Gemini%20AI-OCR-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Gemini AI"/>
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT"/>
</p>

<p align="center">🌐 <b>Live:</b> <a href="https://uemkasolve.my.id">uemkasolve.my.id</a></p>

<p align="center">
  <img src="uemkasolve.my.id_dashboard.png" alt="UEMKASolve dashboard" width="900"/>
  <br/><em>Dashboard — balance, income/expense/profit cards, cash-flow chart, and category breakdown.</em>
</p>

---

## 📌 Overview

**UEMKASolve** helps small and micro businesses replace paper cash books with a structured digital ledger. Owners and staff record income and expenses, categorize transactions, scan physical receipts with AI, monitor cash flow through a live dashboard, and export professional PDF financial reports.

Built on **Laravel 11**, the system exposes both a **Blade web application** and a **token-authenticated REST API** (Laravel Sanctum) that powers a companion **mobile app** — so the same business data is accessible on desktop and on the go.

> 💼 **Impact:** deployed to real MSME clients, contributing to a reported **12.8% efficiency gain** across 15 businesses.

---

## 📸 Screenshots

<p align="center">
  <img src="uemkasolve.my.id_.png" alt="UEMKASolve login page" width="800"/>
  <br/><em>Login — email/password or Google sign-in.</em>
</p>

---

## ✨ Key Features

| Area | Capability |
|------|-----------|
| 🔐 **Authentication** | Email/password login & registration, email verification, password reset, **Google OAuth** (Socialite), "Remember me", and configurable auto-logout on inactivity. |
| 🏢 **Business Setup** | Guided onboarding — create a business profile and select a role before accessing the workspace. |
| 👥 **Team & Roles** | Role-based access for **Owner**, **Sekretaris** (Secretary), and **Bendahara** (Treasurer). Invite members by email with an accept/reject invitation flow. |
| 📒 **Cash Book (Buku Kas)** | Full CRUD for income (*pemasukan*) and expense (*pengeluaran*) transactions with search & date filtering. |
| 🏷️ **Categories** | Manage income/expense categories with custom icons and drag-and-drop ordering. |
| 📊 **Live Dashboard** | Real-time **balance, income, expense, and profit** cards, a monthly cash-flow **line chart**, and a category **doughnut chart** (*Persentase Kas*). |
| 🤖 **AI Receipt Scanning** | Upload a receipt photo and let **Google Gemini** extract items, total, date, store name, and category into a ready-to-save transaction. |
| 🕵️ **Audit Workflow** | Flag transactions for re-audit, attach receipts, and leave audit notes for accountability. |
| 🧾 **PDF Reports** | Generate downloadable financial reports via DOMPDF. |
| 📱 **Mobile API** | Dedicated Sanctum-secured `/api/mobile/*` endpoints for the companion mobile client. |

---

## 🛠️ Tech Stack

**Backend**
- PHP 8.2+ · Laravel 11
- Laravel **Sanctum** — API token authentication
- Laravel **Socialite** — Google OAuth
- **barryvdh/laravel-dompdf** — PDF report generation

**AI / Integrations**
- **Google Gemini** (`gemini-2.5-flash` → `2.0-flash` fallback) for receipt OCR, with **multi-API-key rotation** to extend free-tier quota

**Frontend**
- Blade templating · Vite 5 · Axios · Chart.js-style dashboard rendering

**Data**
- Eloquent ORM · MySQL (SQLite supported for local dev) · migrations & seeders

**Quality & Tooling**
- PHPUnit 10 (feature + unit tests) · **Larastan / PHPStan** (static analysis) · **Laravel Pint** (code style) · Mockery · Laravel Sail

---

## 🏗️ Architecture

UEMKASolve follows a layered Laravel architecture with a dedicated **service layer** that keeps controllers thin:

```
Request ──► Route (web.php / api.php) ──► Middleware ──► Controller ──► Service ──► Model ──► DB
                                                                          │
                                              DashboardService · GeminiOcrService · GradeService
```

**Service layer**
- `DashboardService` — aggregates balance/income/expense/profit and builds line & doughnut chart datasets.
- `GeminiOcrService` — calls the Gemini API with automatic key rotation and model fallback, returns structured receipt JSON.
- `GradeService` — helper that maps scores to A/B/C grades (unit-tested).

**Middleware**
- `SecurityHeaders` (global) · `CheckCompanySetup` · `EnsureEmailIsVerified` · `PreventBackHistory` · `CheckUserActivity` (auto-logout).

**Domain models**
- `User`, `Business`, `BusinessMember`, `Category`, `Transaction`.

---

## 🗄️ Data Model (high level)

| Entity | Description |
|--------|-------------|
| **User** | Account with role (`owner` / `sekretaris` / `bendahara`), Google ID, profile photo. Owns one Business. |
| **Business** | A user's company/UMKM profile. |
| **BusinessMember** | Membership + invitation record (pending / accepted) linking staff to a business. |
| **Category** | Income/expense category with icon. |
| **Transaction** | Amount (`jumlah`, decimal), type, status, audit notes, receipt path — belongs to a Business and a Category. |

📐 Full **ERD, Use Case, Class, Sequence, Activity, and DFD diagrams** live in the diagram folders of this repo. A complete **SKPL / DPPL / DUPL** and **User Manual** are included as PDFs.

<p align="center">
  <img src="UEMKASolve%20Use%20Case%20Diagram.png" alt="Use Case Diagram" width="600"/>
</p>

---

## 🔌 API Reference (selected)

All protected routes require a Sanctum bearer token.

**Auth (public)**
```
POST   /api/register
POST   /api/login
GET    /api/auth/google/redirect
```

**Web app (auth:sanctum)**
```
GET    /api/dashboard                    # summary + chart data
GET    /api/transactions                 # list (search + date filter)
POST   /api/transactions                 # create
PUT    /api/transactions/{id}            # update
DELETE /api/transactions/{id}            # delete
PATCH  /api/transactions/{id}/status     # audit status
GET    /api/categories                   # list
POST   /api/categories                   # create
GET    /api/report/download              # PDF report
POST   /api/ocr/scan                     # AI receipt scan
```

**Mobile (`/api/mobile/*`)**
```
POST   /api/mobile/register | login | google-login
GET    /api/mobile/user
GET    /api/mobile/members
POST   /api/mobile/members
```

---

## 🚀 Getting Started

### Prerequisites
- PHP **8.2+** with `ext-intl` and `ext-pdo_mysql`
- Composer · Node.js & npm
- MySQL (or SQLite for local dev)
- A **Google Gemini API key** (for OCR) and **Google OAuth** credentials (for social login)

### Installation

```bash
git clone https://github.com/JordanCodeGit/UEMKASolve.git
cd UEMKASolve

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### Run (development)

```bash
# Terminal 1 — Vite asset server
npm run dev

# Terminal 2 — Laravel app
php artisan serve
```

Then open `http://localhost:8000`.

### Required environment variables

```dotenv
# Database (MySQL example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=uemkasolve
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Mail (email verification / password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=

# Gemini AI — at least one key required; add numbered keys from
# separate Google projects to multiply free quota via auto-rotation
GEMINI_API_KEY=
GEMINI_API_KEY_1=
GEMINI_API_KEY_2=
```

---

## 🧪 Testing & Code Quality

```bash
php artisan test          # PHPUnit feature + unit tests
composer analyse          # PHPStan / Larastan static analysis
composer fix              # Laravel Pint code style
```

Test coverage includes authentication, email verification, password reset, role data sync, the OCR scan endpoint, and `GradeService`.

---

## 📁 Project Structure

```text
UEMKASolve/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Web, Auth, and Api/ (incl. Mobile*) controllers
│   │   └── Middleware/         # SecurityHeaders, CheckCompanySetup, ...
│   ├── Models/                 # User, Business, BusinessMember, Category, Transaction
│   └── Services/               # DashboardService, GeminiOcrService, GradeService
├── database/
│   ├── migrations/             # Schema (users, businesses, categories, transactions, members)
│   └── seeders/
├── resources/
│   ├── views/                  # Blade: dashboard, buku-kas, kategori, auth/, pdf/, reports/
│   ├── js/  └── css/           # Vite entry points
├── routes/
│   ├── web.php                 # Web (Blade) routes
│   ├── api.php                 # REST API + mobile routes
│   └── auth.php                # Auth scaffolding routes
├── tests/                      # Feature & Unit tests
├── Activity Diagrams/ · Sequence Diagram/ · Class Diagrams per Use Case/
├── DFD - UEMKASolve/ · Wireframe - UEMKASolve/
├── SKPL / DPPL / DUPL / User Manual (PDF)  # Software engineering documentation
└── composer.json · package.json · phpunit.xml · phpstan.neon
```

---

## 👥 Team

Developed as an academic software-engineering project at **Telkom University**.

| Member | Role | Links |
|--------|------|-------|
| **Jordan Angkawijaya** | Project Manager | [GitHub](https://github.com/JordanCodeGit) · [LinkedIn](https://www.linkedin.com/in/jordan-angkawijaya-776502254/) · [Portfolio](https://jordanaw.vercel.app/) |
| **Axandio Biyanatul Lizan** | Frontend Web Developer | [GitHub](https://github.com/xancodess) · [LinkedIn](https://www.linkedin.com/in/axandio-biyanatul-lizan-b79a29260/) |
| **Mahija Danadyaksa Sadtomo** | Backend Web Developer | [GitHub](https://github.com/AxxA3205) · [LinkedIn](https://www.linkedin.com/in/mahija-danadyaksa-84658829a/) |
| **Hizkia Kevin Octaviano** | Fullstack Mobile Developer | [GitHub](https://github.com/kepin7) |
| **Muhammad Daffa Al Faiz** | Assistant Mobile Developer | — |
| **Denny Budiansyach** | Designer | [GitHub](https://github.com/DennyBudiansyach) · [LinkedIn](https://www.linkedin.com/in/dennybudiansyach/) |
| **Dadya Vio Hendraksa** | QA / Tester | [GitHub](https://github.com/DadyaVio) · [LinkedIn](https://www.linkedin.com/in/dadyaviohendraksa/) |

---

## 📄 License

Released under the **MIT License**.

---

<p align="center"><i>⭐ If UEMKASolve helped you, please star the repo!</i></p>
