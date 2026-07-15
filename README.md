# 🌴 PCA Hybridization Portal System

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com/)
[![Filament Version](https://img.shields.io/badge/Filament-3.2-D97706?style=flat-square&logo=filament)](https://filamentphp.com/)
[![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)](LICENSE)

The **PCA Hybridization Portal System** is a secure, enterprise-grade web application designed for the **Philippine Coconut Authority (PCA)**. It streamlines and centralizes the management of coconut hybridization activities across multiple field sites (Loay and Balilihan Farms), ensuring data integrity, traceability, and professional reporting.

Originally conceptualized as a Django-based system, this modern implementation leverages the **Laravel 11** ecosystem and **Filament v3** for a high-performance, real-time administrative experience.

---

## ✨ Key Features

### 🚜 Field Data Modules
- **Hybrid Seedling Distribution**: Tracks the distribution of seedlings to farmers with detailed location and variety data.
- **Monthly Seednut Harvest**: Manages on-farm hybrid seednut production with automated carry-forward logic.
- **Nursery Operations**: Full lifecycle tracking of communal nurseries, from sowing to dispatch.
- **Pollen Production & Inventory**: Monitors pollen collection, receipt, and weekly utilization across centers.
- **Terminal Reports**: Specialized end-of-cycle reporting for nursery activities.

### 🔐 Advanced Security & RBAC
- **Role-Based Access Control (RBAC)** via Spatie Laravel Permission + Filament Shield: distinct permissions for **Supervisors** (field entry), **Managers** (Senior Agriculturist review), **Admins** (Division Chief validation), and **Super Admins** (system governance, unrestricted access).
- **Field-Site Data Isolation**: Supervisors are scoped to their assigned farm (Loay or Balilihan) and can only manage records tied to that site.
- **Draft-Locked Editing**: Field records can only be edited or deleted by their owning supervisor while status is `draft`. Once prepared, reviewed, or noted, records lock and can only be reopened via an explicit "Return to Draft" action from a manager or admin.
- **Audit Logging**: Tracking of key actions for accountability, viewable by Admin and Super Admin roles.
- **Submission Workflow**: Multi-stage validation for field-data records (Draft → Prepared → Reviewed → Noted).

### 📊 Reporting & Analytics
- **Branded Excel Exports**: Generate official PCA-formatted `.xlsx` reports with logos, headers, and signature footers.
- **PDF Report Generation**: Landscape/portrait-selectable reports for field activities and consolidated summaries, generated server-side for consistent output across browsers.
- **Interactive Dashboards**: Real-time stats, trend charts, and activity feeds tailored to each user role.

---

## 🛠️ Tech Stack
- **Framework**: [Laravel 11](https://laravel.com/)
- **Admin Panel**: [Filament v3](https://filamentphp.com/) + [Filament Shield](https://github.com/bezhanSalleh/filament-shield) (permission/policy generation)
- **Authorization**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Database**: SQLite (local development) / PostgreSQL (production)
- **Frontend Build**: Vite `^6.0.0` (pinned — see note below)
- **Styling**: Tailwind CSS
- **Reporting**: PDF via `barryvdh/laravel-dompdf`, Excel via Laravel Excel
- **Base Starter**: [Kaido Kit](https://github.com/siubie/kaido-kit)

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite / MySQL / PostgreSQL

### Step-by-Step Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/MARCAAAAARRON/PCA-Hybrid-Laravel.git
   cd PCA-Hybrid-Laravel
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**

   > ⚠️ **Important:** This project requires **Vite `^6.0.0`**. `laravel-vite-plugin` in this repo does not yet support Vite 7 — if `npm install` throws an `ERESOLVE` peer dependency error, confirm `package.json` pins `"vite": "^6.0.0"` under `devDependencies` before installing.

   ```bash
   npm install
   npm run build
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `DB_CONNECTION` and other credentials in your `.env` file as needed.

5. **Run Migrations (without seeding yet)**
   ```bash
   php artisan migrate
   ```
   > Run migrations *before* generating Shield permissions — Shield needs the base tables (and the Spatie permission tables) to already exist.

6. **Generate Shield Permissions & Policies**
   ```bash
   php artisan shield:generate --all
   ```
   When prompted **"Which panel do you want to generate permissions/policies for?"**, select the `admin` panel (option `0`).

   > This project does **not** use `shield:super-admin`. Super admin role creation and assignment is handled automatically by `RolePermissionSeeder` in the next step — running `shield:super-admin` separately will create a conflicting `super_admin` role (underscore) alongside the app's own `superadmin` role and is unnecessary here.

7. **Seed the Database**
   ```bash
   php artisan db:seed
   ```
   This seeds, in order: field sites → roles & permissions (`RolePermissionSeeder`, which must run before any user is created) → default users for each role (admin, manager, supervisors, superadmin).

   Alternatively, for a full reset at any point during development:
   ```bash
   php artisan migrate:fresh --seed
   ```

8. **Serve the Application**
   ```bash
   php artisan serve
   ```

### Verifying the Setup

```bash
php artisan tinker
```
```php
// Should return 4 roles: superadmin, admin, manager, supervisor
Spatie\Permission\Models\Role::all(['name']);

// Should return a non-zero count (117 in the reference build)
Spatie\Permission\Models\Permission::count();

// Superadmin should hold ALL permissions
App\Models\User::where('role', 'superadmin')->first()->getAllPermissions()->count();
```
---

## 🩺 Troubleshooting

**`RoleDoesNotExist` error during seeding**
Roles must exist before any `User` is created, since the `User` model automatically syncs Spatie roles on creation. Confirm `DatabaseSeeder` calls `RolePermissionSeeder` *before* creating any users, and confirm `shield:generate --all` was run (and fully completed) before seeding.

**`npm install` fails with `ERESOLVE`**
See step 3 above — pin `vite` to `^6.0.0` in `package.json`, delete `node_modules` and `package-lock.json`, and reinstall.

**A role has zero permissions after seeding**
Run `Spatie\Permission\Models\Permission::count()` in Tinker. If it returns `0`, `shield:generate --all` did not complete before `db:seed` ran. Re-run `shield:generate --all` on its own, then re-run `php artisan db:seed --class=RolePermissionSeeder`.

---

## 📸 Screenshots
*(Add your screenshots here to showcase the beautiful Filament UI)*
- **Dashboard**: High-level overview with efficiency stats.
- **Data Entry**: Clean, responsive forms with batch entry support.
- **Reports**: Examples of the PCA-branded Excel/PDF outputs.

---

## 🤝 Contributing
This project was developed by **Marc Arron** as part of an undergraduate thesis/capstone project. For inquiries or contributions, please contact the repository owner.

---

## 🙏 Acknowledgments
- **Philippine Coconut Authority (PCA)** for providing the domain expertise and requirements.
- **Kaido Kit** for the robust FilamentPHP starter foundation.
- **The Laravel & Filament Communities** for the amazing tools.

---

⭐ *Give a star if this project helped you!*
