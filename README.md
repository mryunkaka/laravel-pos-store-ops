# Laravel POS Store Ops

Laravel POS Store Ops is a Laravel 10 based Point of Sale application for retail store operations. The current application includes POS transactions, products, categories, customers, suppliers, orders, invoices, due payments, employee data, attendance, salary, role permissions, dashboard summaries, and database backup.

This repository continues development from the original Laravel Point of Sale project and adds a structured roadmap for operational features such as stock validation, stock movements, purchase receiving, cashier shifts, daily closing, stock opname, returns, reports, and audit logs.

## Current Status

The project is installed and documented for local development in `docs/`.

Important local setup used during this handoff:

- Project path: `D:\Project\Web\pos3`
- Local URL: `http://127.0.0.1:8084`
- PHP runtime used locally: `C:\php\php.exe`
- Database: MariaDB `127.0.0.1:3307`
- Database name: `point_of_sale`
- Default login: `admin` / `password`

## Features Available Now

- Authentication and dashboard.
- POS cart and checkout flow.
- Pending and complete orders.
- Invoice and receipt print views.
- Due payment tracking.
- Product, category, customer, supplier, employee, attendance, and salary modules.
- Product import and export.
- Product barcode display.
- Role and permission management using Spatie Permission.
- User management.
- Database backup.

## Planned Enhancements

The project roadmap is documented in `docs/03-TODO.md` and must be followed by phase order.

Main planned improvements:

- POS stock validation.
- Cancel and void transactions with reason and audit trail.
- Stock movement history.
- Purchase order and purchase receiving.
- Supplier returns.
- Cashier shift management.
- Cash in and cash out.
- Shift closing and daily closing.
- Multi payment and split payment.
- Stock opname.
- Sales returns.
- Discounts, promotions, tax, and barcode workflow improvements.
- Sales, profit, stock, due, and payment reports.
- Audit log viewer.
- Store settings and restore database flow.

## Documentation

Read the documentation before making changes:

- `docs/01-PRD.md`: product requirements, scope, rules, and constraints.
- `docs/02-PROGRESS.md`: current progress and risks.
- `docs/03-TODO.md`: phase-based checklist.
- `docs/04-HISTORY.md`: installation and change history.
- `docs/05-PROJECT_STRUCTURE.md`: project structure and implementation guidance.

Important rule: do not rewrite the project, change the framework, replace Blade with a SPA, rename existing tables, or restructure the application. New features must extend the current Laravel structure.

## Requirements

- PHP 8.1 to 8.4 recommended.
- Composer.
- Node.js and npm.
- MySQL or MariaDB.
- Laravel-compatible PHP extensions such as `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, and `intl`.

Note: during local setup, PHP 8.5 was not compatible with the locked `phpoffice/phpspreadsheet` version. PHP 8.4 was used successfully.

## Installation

Clone the repository:

```bash
git clone https://github.com/mryunkaka/laravel-pos-store-ops.git
cd laravel-pos-store-ops
```

Install PHP dependencies:

```bash
composer install
```

Install and build frontend assets:

```bash
npm install
npm run build
```

Create environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=point_of_sale
DB_USERNAME=root
DB_PASSWORD=
```

Run migration and seeder:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Start the development server:

```bash
php artisan serve --host=127.0.0.1 --port=8084
```

Open:

```text
http://127.0.0.1:8084
```

## Default Credentials

| Role | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `password` |

Change the default credentials before using this application outside local development.

## Development Workflow

Before implementing new work:

1. Read all files in `docs/`.
2. Check `docs/03-TODO.md`.
3. Work only on the first unchecked item in the active phase.
4. Keep the existing Laravel structure.
5. Update `docs/02-PROGRESS.md`, `docs/03-TODO.md`, and `docs/04-HISTORY.md` after finishing work.

Useful local commands:

```bash
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan test
npm run build
```

If using the local PHP runtime from the current machine:

```powershell
C:\php\php.exe artisan serve --host=127.0.0.1 --port=8084
C:\php\php.exe composer.phar install
```

## Original Project

This project is based on:

```text
https://github.com/fajarghifar/laravel-point-of-sale
```

The original project is licensed under the MIT License. This repository keeps the MIT license and continues development with additional documentation and operational roadmap.

## License

This project is open-source software licensed under the MIT License. See `LICENSE` for details.
