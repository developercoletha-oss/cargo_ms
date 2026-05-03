# CFTMS - Cargo and Freight Tracking Management System

CFTMS is a comprehensive Laravel-based management system designed specifically for local transport companies to manage cargo operations efficiently. Track shipments in real-time, manage vehicles and drivers, handle client invoices, and monitor delivery performance - all from a single integrated platform.

The goal is simple: provide transport businesses with a reliable, scalable solution for everyday logistics operations.

## What Is Included

- Public home page and about page showcasing cargo management capabilities
- Custom login, logout, and password reset flow with role-based authentication
- Protected admin dashboard for managing operations
- Real-time shipment tracking interface with GPS status updates
- Fleet management module for vehicles and drivers
- Client portal for tracking and request management
- Invoicing and billing system with financial reporting
- Analytics dashboard for operational insights
- Responsive Blade layouts using Bootstrap 5 and custom CSS
- MySQL-ready Laravel migrations and audit trail structure

## Core Business Features

- **Shipment Tracking** - Real-time GPS tracking with status updates and client notifications
- **Fleet Management** - Vehicle profiles, maintenance scheduling, and resource allocation
- **Driver Management** - Driver profiles, assignments, performance monitoring, and payroll
- **Client Portal** - Self-service tracking, document access, and pickup requests
- **Invoicing System** - Automated billing, payment tracking, and financial reporting
- **Analytics Dashboard** - Key metrics visualization and operational insights
- **Role-Based Access** - Secure authentication with permissions for admins, staff, and clients

## Tech Stack

- PHP 8.2+
- Laravel 12
- Blade templates
- Bootstrap 5
- Bootstrap Icons
- JavaScript
- MySQL
- Chart.js for analytics

## Main Routes

```text
/              Home page
/about         About page
/login         Login page
/forgot-password
/dashboard     Protected admin dashboard
/dashboard/profile
/dashboard/notifications
```

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or another database supported by Laravel

## Installation

```bash
composer install
```

Create your local environment file:

```bash
cp .env.example .env
```

Then update the database values in `.env`:

```env
APP_NAME="CFTMS - Cargo and Freight Tracking Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Then run:

```bash
php artisan key:generate
php artisan migrate
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Quick Start Guide

1. Set up your database and update `.env` file
2. Run migrations to create tables
3. **Create an admin user via seeder:**
   ```bash
   php artisan db:seed --class=AdminSeeder
   ```
   Or update `.env` with your preferred credentials:
   ```env
   ADMIN_EMAIL=your-email@test.com
   ADMIN_PASSWORD=your-secure-password
   ```
4. Log into `/dashboard` to configure settings
5. Set up your fleet (vehicles and drivers)
6. Configure tax rates and invoice templates
7. Set up client accounts and begin tracking shipments

## Dashboard Notes

The dashboard contains placeholder data for demonstration. You should replace:

- Statistics with real queries from your cargo database
- Chart data with actual shipment and delivery metrics
- Activity feed with real operational events
- Summary tiles with key performance indicators

## Authentication Notes

Authentication is custom-built with local controllers:

- `LoginController` - Handles user login/logout
- `PasswordResetController` - Manages password recovery

This project does not use Laravel Breeze or Jetstream.

## Development Roadmap

Key areas to build out:

- CRUD modules for shipments, vehicles, drivers, and clients
- GPS integration API for real-time tracking updates
- SMS/email notification system for status changes
- Report generation (delivery certificates, invoices, performance reports)
- Mobile-responsive interfaces for drivers and clients
- API endpoints for third-party integrations

## Next Steps

- Add role and permission logic (admin, staff, driver, client)
- Implement CRUD for cargo shipments with status workflow
- Set up vehicle and driver management modules
- Configure invoice generation and payment tracking
- Add real-time tracking with map integration
- Implement notification system (email/SMS)
- Build client self-service portal
- Add reporting and analytics features

## License

This project uses the MIT license. See [LICENSE](LICENSE) for details.

## Developer

Built and maintained by Hagai Harold Ngobey.

- GitHub: <https://github.com/harryhagai>
- Email: hngobey@gmail.com

