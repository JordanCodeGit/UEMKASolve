# 🔍 UEMKASolve Website

A Laravel-based financial management website for business cash tracking, reporting, team roles, and AI-assisted receipt scanning.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-5-646CFF?logo=vite&logoColor=white)
![Status](https://img.shields.io/badge/Status-Financial%20Web%20App-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## 📌 Overview

UEMKASolve Website is a financial management application built with Laravel. It supports business setup, authentication, role-based dashboards, categories, cashbook transactions, members, reports, profile settings, and PDF financial report generation.

The system uses Laravel controllers, services, models, migrations, Blade views, API routes, Sanctum authentication, Google login support, and a Gemini OCR service for receipt extraction. The dashboard aggregates income, expense, profit, balance, recent transactions, line chart data, and category breakdowns.

The target users are small businesses or student project reviewers who need a structured cash management system with digital transaction reporting.

## 🧠 Model & Methodology

| Method | Output | Notes |
|---|---|---|
| **Dashboard aggregation** | Balance, income, expense, profit, recent transactions | Implemented in `DashboardService`. |
| **Category-based transaction grouping** | Line chart and doughnut chart data | Groups transactions by type, date range, and category. |
| **Gemini OCR extraction** | Receipt transaction JSON | Extracts items, total, date, store name, and category from receipt images. |
| **GradeService** | A/B/C score label | Simple score-to-grade helper with tests. |

No model accuracy metric is provided in the project files.

## ✨ Features

- ✅ Login, register, password reset, email verification, and Google authentication routes.
- 💼 Business/company setup and profile management.
- 📒 Cashbook transaction CRUD for income and expenses.
- 🏷️ Category management with income/expense types.
- 📊 Dashboard summary with line and doughnut chart data.
- 👥 Member invitation and role-oriented pages.
- 🧾 PDF financial report generation using DOMPDF.
- 🤖 Gemini OCR receipt scanning endpoint.

## 🛠️ Tech Stack

**Core:** PHP 8.2+, Laravel 11  
**ML/AI:** Google Gemini OCR  
**Data:** Eloquent models, Laravel migrations, database seeders  
**Visualization:** Dashboard chart data from Laravel services  
**API/Backend:** Laravel routes, controllers, Sanctum  
**Frontend:** Blade, Vite, Axios  
**Tools:** Composer, npm, PHPUnit, Larastan/PHPStan, Laravel Pint

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-Laravel%20Config-4479A1?style=for-the-badge)

## ⚡ Strengths & Limitations

**Strengths:**

- Uses service classes for dashboard aggregation and OCR extraction.
- Includes API and web routes for core business workflows.
- Provides migrations for users, businesses, categories, transactions, roles, and members.
- Includes testing and analysis tooling in Composer scripts.

**Limitations:**

- README and deployment details were previously framework-default.
- OCR depends on a configured Gemini API key and quota availability.
- Generated coverage/build artifacts are present in the project folder.

Future improvements may include documenting environment variables and adding screenshots of dashboard, cashbook, and report pages.

## 📁 Project Structure

```text
UEMKASolve_Website/
├── app/
│   ├── Http/Controllers/        # Web, API, auth, profile, dashboard controllers
│   ├── Models/                  # Business, Category, Transaction, User, Member models
│   └── Services/                # DashboardService, GeminiOcrService, GradeService
├── database/
│   ├── migrations/              # Database schema
│   └── seeders/                 # Database seeders
├── resources/
│   ├── views/                   # Blade pages, auth views, PDF/report views
│   └── js/                      # Frontend JavaScript entry
├── routes/
│   ├── web.php                  # Web routes
│   ├── api.php                  # API routes
│   └── auth.php                 # Auth routes
├── tests/                       # Feature and unit tests
├── composer.json                # PHP dependencies and scripts
├── package.json                 # Vite tooling
└── README.md                    # Project documentation
```

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- Database supported by Laravel config
- Gemini API key for OCR features

### Installation

```bash
git clone <repository-url>
cd UEMKASolve_Website
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### How to Run

```bash
npm run dev
php artisan serve
```

## 📊 Results & Performance

No ML accuracy metric is provided. Available outputs include dashboard summaries, category chart data, transaction reports, PDF financial reports, and OCR-extracted transaction JSON.

## 👨‍💻 Author

**Axandio**  
LinkedIn: Not provided in project files  
GitHub: Not provided in project files

Open to collaborations and feedback — feel free to reach out!

---
> ⭐ If you find this project useful,
> please give it a star!

## 💡 Portfolio Suggestions

1. Add dashboard and PDF report screenshots.
2. Document required `.env` keys for database, Google auth, mail, and Gemini.
3. Add API examples for transaction, category, report, and OCR endpoints.
