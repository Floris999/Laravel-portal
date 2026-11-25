# Laravel Portal Template

A flexible, feature-rich Laravel portal/intranet starter kit built on modern technologies. This template provides a solid foundation for building business portals, internal dashboards, and management systems.

## Features

This template is built on a proven foundation and includes:

- **Modular Architecture:** Easily customizable for different business domains (customers, finances, registrations, etc.)
- **Role-Based Access Control (RBAC):** Built-in user roles (Admin, Manager, User) with permission gates
- **Authentication & Security:** 
  - Two-factor authentication (2FA)
  - Passkey support
  - Secure session management
- **Responsive Dashboard:** Interactive maps, charts, and data visualizations
- **Data Management:** 
  - CRUD operations for entities
  - Bulk actions and batch processing
  - Search and sorting
- **Queue-Based Operations:** Background job processing for integrations
- **API Integration Ready:** Structured for external service integrations
- **Categories System:** Sync accounting categories from external services and assign them to products/expenses

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13
- **Frontend:** Livewire 4, Flux UI (component library)
- **Authentication:** Laravel Fortify
- **Database:** MySQL (see `.env` configuration)
- **Queue System:** Database queue driver
- **Testing:** Pest (PHP testing framework)
- **Code Quality:** Pint (code style), Larastan (PHPStan for static analysis)

## Quick Start

### Prerequisites

- PHP 8.3 or higher
- Composer
- Node.js and npm
- MySQL or compatible database

### Installation

```bash
# Clone or use this as a template
git clone https://github.com/Floris999/Laravel-portal.git
cd Laravel_portal

# Install dependencies
composer setup            # Install PHP dependencies, create .env, run migrations, build assets

# (Optional) Seed demo data
php artisan db:seed

# Start development
composer dev              # Start Laravel development server
php artisan queue:work   # (In separate terminal) Process queued jobs
```

#### Manual Setup (if `composer setup` doesn't work)

```bash
composer install
npm install

# Create environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Setup database
php artisan migrate
npm run build

# (Optional) Seed with demo data
php artisan db:seed
```

### Useful Commands

```bash
composer test             # Run linting, static analysis, and tests
composer lint             # Fix code style with Pint
php artisan test          # Run test suite only
php artisan queue:work    # Start queue worker (run in separate terminal)
php artisan queue:failed  # View failed queue jobs
php artisan queue:retry all  # Retry failed jobs
```

## Project Structure

```
app/
├── Http/Controllers/      # Page controllers
├── Livewire/              # Interactive Livewire components
├── Models/                # Eloquent models
├── Concerns/              # Shared traits
├── Enums/                 # PHP enums
├── Jobs/                  # Queued jobs
├── Notifications/         # Email & notifications
└── Policies/              # Authorization policies

resources/
├── views/                 # Blade templates
│   ├── layouts/           # Layout components
│   └── pages/             # Livewire full-page components
├── css/                   # Stylesheets
└── js/                    # JavaScript

database/
├── migrations/            # Schema migrations
├── factories/             # Model factories for testing
└── seeders/               # Database seeders

routes/
├── web.php                # Web routes
└── console.php            # Artisan commands
```

## Configuration

Copy `.env.example` to `.env` and configure:

```dotenv
# App
APP_NAME=YourPortalName
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

## Customization Guide

### 1. Update Branding

- Change `APP_NAME` in `.env`
- Replace logo/favicon in `/public`
- Update application title in layout files

### 2. Configure Database Schema

Modify or create migrations in `database/migrations/`:

```bash
php artisan make:migration create_your_table --create=your_table
php artisan make:model YourModel --migration --factory
```

### 3. Create New Pages

**Single-action pages** (no form):

```bash
php artisan make:controller YourController
# Create view in resources/views/your-page.blade.php
# Add route in routes/web.php
```

**CRUD resources** (create/read/update/delete):

```bash
php artisan make:controller YourController --resource
# Views go in resources/views/your-things/ folder
# Add route: Route::resource('your-things', YourController::class)
```

### 4. Add External Integrations

Create jobs in `app/Jobs/` for external service integration (similar to the included example):

```bash
php artisan make:job YourIntegration
```

Then queue it from your controller:

```php
YourIntegration::dispatch($data);
```

## Authentication & Authorization

### User Roles

Roles can be defined in `app/Enums/UserRole.php`. Examples:

```php
Admin      // Full access
Manager    // Department/team management
User       // Limited access
```

### Authorization

Use policies in `app/Policies/` for model-level authorization:

```php
Route::get('/items/{item}', [ItemController::class, 'show'])
    ->middleware('can:view,item');
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/YourTest.php

# Run with coverage
php artisan test --coverage
```

## Deployment

### Pre-deployment checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure database credentials
- [ ] Set up mail service credentials
- [ ] Generate app key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Run queue worker as a background process

### Production Requirements

- PHP 8.3+
- MySQL 8.0+ or PostgreSQL 12+
- Background job processor (Supervisor, etc.)
- SSL certificate
- File storage (local or cloud)

## Extending the Template

This template is designed to be extended. Common customizations:

- **Add new data models** via migrations and Eloquent models
- **Create new pages** using the standard controller + view pattern
- **Build interactive features** with Livewire components
- **Integrate external APIs** via queued jobs
- **Customize the UI** by modifying Blade templates and CSS

## License

This template is open source and available under the MIT License.

## Support & Contributing

For issues, feature requests, or contributions to the template, please open an issue on GitHub.

---

**Ready to build?** Start by customizing the `.env` file and exploring the existing structure. Happy coding! 🚀
