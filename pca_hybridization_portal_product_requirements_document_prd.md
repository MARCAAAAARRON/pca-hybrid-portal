# Product Requirements Document (PRD)

## Project Title
PCA Hybridization Portal with Role-Based Access and Sequential Data Propagation Algorithm

## Version
v3.0 (Laravel/Filament Migration)

## Prepared For
Undergraduate Thesis / Capstone Project

## Prepared By
Marc Arron

---

## 1. Purpose of the Document
This Product Requirements Document (PRD) specifies the complete functional and non-functional requirements of the PCA Hybridization Portal System. It serves as the authoritative reference for system design, development, testing, deployment, and thesis defense.

---

## 2. Project Overview

### 2.1 System Description
The **PCA Hybridization Portal with Role-Based Access and Sequential Data Propagation Algorithm** is a secure, web-based information system designed to manage coconut hybridization activities across multiple Philippine Coconut Authority (PCA) field sites in Bohol. It features a **Role-Based Access** system that governs data isolation, multi-stage approval workflows, and a **Sequential Data Propagation Algorithm** for automated carry-forward logic and reporting. Built with the Laravel and Filament framework, it supports structured data recording, real-time analytics, and official PCA-formatted exports.

### 2.2 Background and Rationale
Agricultural hybridization data at PCA field sites is often recorded manually, resulting in fragmented records, limited traceability, and weak reporting. This system centralizes all field data — hybrid seedling distribution, monthly seednut harvest, nursery operations, and pollen production — while enforcing strict access control via role-based logic to preserve data integrity and accountability.

### 2.3 Objectives
- Digitize and centralize PCA coconut hybridization records across field sites.
- Enforce a **Role-Based Algorithm System** for data isolation (Supervisor) and multi-level validation (Manager/Admin).
- Support multi-field operations (Loay, Balilihan, and Loon farms).
- Implement carry-forward algorithms for monthly seednut and pollen production.
- Generate official PCA-branded Excel and PDF reports with automated signature embedding.
- Maintain a comprehensive audit trail of all system-wide actions.
- Provide dynamic, role-specific dashboards with advanced data visualizations (Production Ranking, Operations Funnel, Efficiency Stats).

---

## 3. Scope

### 3.1 In Scope
- User authentication with multi-factor support via Filament Breezy.
- Role-specific dashboards with interactive charts and real-time statistics.
- Four field data modules: Hybrid Distribution, Monthly Harvest, Nursery Operations, Pollen Production.
- Hybridization record management with lifecycle tracking (Seedling → Harvested).

#### Admin / Manager Dashboard
- Multi-field overview with aggregate statistics.
- Per-farm record counts and comparisons.
- **Recent Portal Activity**: Real-time monitoring of user actions (Audit Trail) with role-based visibility.
- Comparative charts across field sites.
- Report generation controls.

#### Superadmin Dashboard
- Total user counts broken down by role.
- Active vs. inactive user summary.
- System-wide record counts across all data modules.
- **Full Audit Logs**: Complete historical view of all system-wide actions.
- User management quick links.

- Multi-stage approval workflow: Draft → Prepared → Reviewed → Noted.
- PCA-formatted Excel export center with brand-consistent styling.
- PDF report generation with landscape layouts and digital signatures.
- Comprehensive audit logging of all CRUD and system events.
- User profile management including digital signature uploads.

### 3.2 Out of Scope
- Native mobile applications (iOS/Android).
- Public-facing APIs or guest access.
- Real-time IoT sensor integration for farm monitoring.

---

## 4. Stakeholders

| Stakeholder | Role Title | Responsibility |
|------------|------------|----------------|
| Farm Supervisor | COS / Agriculturist | Field-level data entry, monitoring, and site-specific exports. |
| Manager | Senior Agriculturist | Data review, validation, and multi-site comparative analysis. |
| Admin | PCDM / Division Chief I | Final noting/approval of reports and strategic oversight. |
| Superadmin | System Administrator | User governance, system configuration, and audit log oversight. |
| PCA Management | — | Program evaluation and regional oversight. |
| Thesis Panel | — | Academic assessment and system evaluation. |

---

## 5. User Roles and Permissions (Role-Based Algorithm System)

### 5.1 Supervisor (COS / Agriculturist)
- **Data Isolation**: Automatically restricted to their assigned field site.
- Create and manage field data records (Draft/Prepared status).
- View field-specific dashboard with production charts.
- Export Excel reports for their assigned site only.

### 5.2 Manager (Senior Agriculturist)
- Access and filter data across all field sites.
- Review and validate submitted records.
- Access the "Command Center" dashboard with site comparisons.
- Generate regional reports and comparative analytics.

### 5.3 Admin (PCDM / Division Chief I)
- Final authority for "Noting" reports.
- Full visibility of the system's production funnel.
- Strategic oversight via the Admin Summary Stats.

### 5.4 Superadmin (System Administrator)
- Full system governance.
- Manage users, roles, and field site assignments.
- Monitor system health via Audit Logs.

---

## 6. Field Sites

| Field Site | Description |
|-----------|-------------|
| Loay Farm | PCA hybridization field site in Loay, Bohol. |
| Balilihan Farm | PCA hybridization field site in Balilihan, Bohol. |
| Loon Farm | PCA hybridization field site in Loon, Bohol. |

---

## 7. Functional Requirements

### 7.1 Authentication and Authorization
- **Role-Based Access Control (RBAC)**: Managed via Laravel Polices and Filament Shield/Custom Logic.
- **Data Scoping**: Supervisors are globally scoped to their `field_site_id`.
- **Session Management**: Secure sessions with auto-logout and CSRF protection.

### 7.2 Dashboards
- **Production Ranking**: Visual comparison of seednut yields across sites.
- **Operations Funnel**: Real-time tracking of seedlings through the nursery and distribution lifecycle.
- **Efficiency Stats**: Dynamic cards showing record counts and growth metrics.

### 7.3 Field Data Modules
- **Monthly Harvest**: Features parent-child relationships and monthly production columns (Jan-Dec).
- **Pollen Production**: Tracks utilization across 5-week cycles with automatic balance calculation.
- **Nursery Operations**: Manages batch-level details including germination and culling.
- **Hybrid Distribution**: Tracks final seedling delivery to individual farmers with geographic data.

### 7.4 User Profile / Settings
- **Tabbed Interface**: Organized into "Profile Info", "Digital Signature", and "Security Settings" tabs for a streamlined user experience.
- **Personal Information**: View and update profile details such as name and email address.
- **Digital Signature Management**: Integration with camera/file upload to capture and store signatures used for automated report signing.
- **Security Governance**: Signature updates are locked for 3 months after each change to maintain audit integrity.
- **Account Security**: Password management with built-in validation and support for Two-Factor Authentication (2FA).

---

## 8. Non-Functional Requirements

### 8.1 Security
- Password hashing using Argon2/Bcrypt.
- Digital signature immutability logic (3-month update restriction).
- Audit trail for every create, update, and delete action.

### 8.2 Performance
- Optimized for large data volumes using Eloquent eager loading.
- Fast report generation using optimized PDF and Excel engines.

---

## 9. System Architecture

### 9.1 Technology Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Tailwind CSS, Alpine.js, Blade |
| Backend | PHP 8.x, Laravel 11.x |
| Admin Panel | Filament 3.x (TALL Stack) |
| Database | MySQL / PostgreSQL |
| Excel Engine | Maatwebsite Excel (PhpSpreadsheet) |
| PDF Engine | DomPDF / ReportLab Integration |

### 9.2 Application Structure (Laravel)
```
app/
├── Filament/      ── Resources, Pages, and Widgets
├── Models/        ── Eloquent models (FieldSite, User, etc.)
├── Policies/      ── Role-based access logic
├── Exports/       ── Excel export definitions
├── Mail/          ── Email report templates
└── Helpers/       ── Signature and data processing logic
resources/
└── views/         ── Blade templates and partials
```

---

## 10. Audit Logging
The `AuditLog` model tracks:
- User ID and Name.
- Action (Login, Create, Update, Delete, Export).
- Affected Model and ID.
- IP Address and Timestamp.
- Changed attributes (Old vs New values).

---

## 11. Success Metrics
- Successful data isolation for all Supervisors.
- Generation of error-free, PCA-branded reports.
- Completion of the 4-stage approval workflow for all modules.
- Positive evaluation from the PCA management and thesis panel.

---

## 12. Approval
This PRD is approved for development and thesis defense implementation.

---

**End of Document**
