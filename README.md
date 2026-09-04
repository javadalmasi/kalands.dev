# Kalands - Laravel E-commerce & Affiliate Platform

A modern, high-performance Laravel 13 application built for product comparison, affiliate marketing, and content management with a comprehensive admin dashboard.

## 🚀 Features

### Core Functionality
- **Product Comparison & Search** - Advanced product filtering by category, brand, and seller
- **Affiliate System** - Track clicks, manage affiliate links, and generate revenue through partner programs
- **User Dashboard** - Bookmarks, likes, comments, and support tickets
- **Admin Dashboard** - Comprehensive management interface with granular permissions

### Authentication & Security
- **Multi-guard Authentication** - Separate `web` and `admin` guards
- **Two-Factor Authentication (2FA)** - TOTP with QR codes (pragmarx/google2fa-qrcode)
- **Role-Based Access Control** - Permissions system with roles for admins
- **Laravel Sanctum** - API token authentication

### Content Management
- **Dynamic Sitemaps** - Yoast-style index + sharded product sitemaps
- **IndexNow Integration** - Instant search engine indexing
- **SEO Tools** - Robots.txt management, meta tags, structured data
- **FAQ System** - Categorized frequently asked questions
- **Contact Forms** - With admin management

### Admin Modules
| Module | Description |
|--------|-------------|
| **Products** | Management, bulk actions, API status checking, Digikala ID mapping |
| **Categories** | Tree structure, AI-powered embeddings, SnappShop import, mappings |
| **Mega Menu** | Visual builder with link testing |
| **File Manager** | Full CRUD with upload, folders, move, copy, rename |
| **Email/SMS** | Configuration, templates, testing |
| **Queues** | Monitoring, token regeneration |
| **Cache Management** | Object cache, htaccess, backups |
| **Visitor Intelligence** | GeoIP, ASN data, user agent testing |
| **Error Pages** | Custom 404, 500, maintenance pages |
| **Artisan Commands** | Secure command execution from UI |

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Laravel 13.x |
| **PHP** | 8.3+ |
| **Frontend** | Livewire 4.x, TailwindCSS |
| **Queue/Performance** | Laravel Octane 2.x |
| **Database** | MySQL/PostgreSQL (Eloquent ORM) |
| **Caching** | Redis (Object Cache), File/Database |
| **Search** | Custom autocomplete, brand/category filtering |
| **Background Jobs** | Laravel Queues with custom monitoring |

### Key Packages
- `laravel/octane` - High-performance application server
- `livewire/livewire` - Reactive frontend components
- `laravel/socialite` - OAuth authentication (Google, GitHub, etc.)
- `hekmatinasser/verta` - Persian/Jalali date handling
- `pishran/laravel-persian-slug` - Persian URL slugs
- `bacon/bacon-qr-code` - QR code generation
- `geoip2/geoip2` - Visitor geolocation
- `mobiledetect/mobiledetectlib` - Device detection

## 📁 Project Structure

```
app/
├── Actions/           # Single-action classes
├── Console/           # Artisan commands
├── Contracts/         # Interfaces
├── Exceptions/        # Custom exceptions
├── Http/
│   ├── Controllers/   # Organized by feature (Admin, Auth, Dashboard, etc.)
│   ├── Middleware/    # Custom middleware (2FA, authkey, permissions)
│   └── Requests/      # Form requests
├── Jobs/              # Queue jobs
├── Livewire/          # Livewire components
├── Mail/              # Mailable classes
├── Models/            # Eloquent models (33+ models)
├── Providers/         # Service providers
├── Repositories/      # Data access layer
├── Rules/             # Custom validation rules
├── Services/          # Business logic services
├── Traits/            # Reusable traits
└── helpers.php        # Global helper functions
```

## 🚦 Getting Started

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & NPM/PNPM
- MySQL 8.0+ or PostgreSQL 14+
- Redis (recommended for Octane & caching)

### Installation

```bash
# Clone repository
git clone <repository-url>
cd kalands

# Install PHP dependencies
composer install

# Install frontend dependencies
pnpm install  # or npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure .env with database, mail, Redis, etc.

# Run migrations & seeders
php artisan migrate --seed

# Build frontend assets
pnpm run build  # or npm run build

# Start development server
php artisan serve

# Or with Octane (production-like)
php artisan octane:start
```

### Development Commands

```bash
# Run tests
php artisan test

# Code style (Laravel Pint)
./vendor/bin/pint

# Queue worker
php artisan queue:work

# Schedule worker
php artisan schedule:work

# Rebuild sitemap
php artisan sitemap:rebuild

# IndexNow ping
php artisan indexnow:ping
```

## ⚙️ Configuration

### Environment Variables
Key `.env` settings:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kalands
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Octane
OCTANE_SERVER=swoole  # or roadrunner

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

# Socialite (OAuth)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

### Permissions System
The admin panel uses a granular permission system. Key permissions:
- `dashboard.view`, `users.*`, `admins.*`, `roles.*`
- `products.*`, `categories.*`, `megamenu.*`
- `communication.*`, `email_templates.*`
- `tickets.*`, `comments.*`, `faq.*`
- `affiliate.*`, `sitemap.*`, `indexnow.*`
- `cache_management.*`, `object_cache.*`
- `file_manager.*`, `geoip.*`, `search.*`

## 📚 Documentation

See the [`docs/`](docs/) folder for detailed documentation:
- `ADMIN_DASHBOARD_REDESIGN.md` - Admin UI/UX specifications
- `ADMIN_QUICK_REFERENCE.md` - Quick command reference
- `CURRENT_SYSTEM_MECHANISMS.md` - System architecture overview
- `AGENTS.md` - AI agent configurations
- `CLAUDE.md` - Claude-specific instructions
- `skills/` - Development skills reference

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Specific test suite
php artisan test --testsuite=Feature
```

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Configure `APP_URL` with HTTPS
- [ ] Set up Redis for sessions, cache, queues
- [ ] Configure Octane with Swoole/RoadRunner
- [ ] Run `php artisan config:cache`, `route:cache`, `view:cache`
- [ ] Set up queue workers with Supervisor
- [ ] Configure scheduler cron: `* * * * * php artisan schedule:run`
- [ ] Set up SSL certificates
- [ ] Configure backup strategy

### Octane Deployment
```bash
# Install Swoole
pecl install swoole

# Start Octane server
php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --workers=auto --task-workers=auto
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow Laravel Pint code style (`./vendor/bin/pint`)
4. Write tests for new functionality
5. Submit a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [Livewire](https://livewire.laravel.com) - Full-stack reactive framework
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Laravel Octane](https://octane.laravel.com) - High-performance server