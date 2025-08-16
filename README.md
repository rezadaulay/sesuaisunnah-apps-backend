# Backend - Sesuai Sunnah Apps

Backend API untuk platform komunitas Islam Sesuai Sunnah yang dibangun dengan Laravel 12, Filament Admin Panel, dan Spatie Permission.

## 🚀 Fitur

- **Laravel 12**: Framework PHP terbaru dengan performa tinggi
- **Filament Admin Panel**: Admin panel yang powerful dan customizable
- **Spatie Permission**: Role-based access control (RBAC)
- **Laravel Sanctum**: API authentication
- **SQLite Database**: Database ringan untuk development

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament 3.x
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Database**: SQLite (development), MySQL (production)
- **Package Manager**: Composer

## 📁 Struktur Proyek

```
app/
├── Models/              # Eloquent models
├── Http/                # Controllers, Middleware, Requests
│   ├── Controllers/     # API Controllers
│   ├── Middleware/      # Custom middleware
│   └── Requests/        # Form requests
├── Providers/           # Service providers
│   └── Filament/        # Filament admin panel providers
└── Console/             # Artisan commands

database/
├── migrations/          # Database migrations
├── seeders/            # Database seeders
└── factories/          # Model factories

config/                  # Configuration files
routes/                  # API routes
```

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM (untuk assets)

### Installation

1. Clone repository
```bash
git clone <repository-url>
cd sesuaisunnah-apps/backend
```

2. Install dependencies
```bash
composer install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Setup database
```bash
php artisan migrate
php artisan db:seed
```

5. Create admin user
```bash
php artisan make:filament-user
```

6. Run development server
```bash
php artisan serve
```

## 🔐 Authentication & Authorization

### Roles & Permissions

- **Admin**: Full access ke semua fitur
- **Event Manager**: Manage events dan registrations
- **Member**: Akses terbatas ke events dan ebooks

### API Authentication

- Menggunakan Laravel Sanctum untuk API tokens
- OTP authentication via WhatsApp (akan diimplementasikan)

## 📊 Database Schema

### Tabel Utama

- **users**: User management (Laravel default + custom fields)
- **roles, permissions**: Role-based access control (Spatie)
- **events**: Event management
- **event_registrations**: Event participant registrations
- **event_galleries**: Event photo galleries
- **ebooks**: E-book management
- **audiobook_files**: Audio book files
- **ebook_interactions**: User interactions with ebooks
- **donations**: Donation tracking
- **audit_logs**: System activity logs

## 🎯 API Endpoints

### Authentication
- `POST /api/auth/login` - Login dengan OTP
- `POST /api/auth/register` - Registrasi user
- `POST /api/auth/logout` - Logout

### Events
- `GET /api/events` - List events
- `POST /api/events` - Create event (admin/event manager)
- `GET /api/events/{id}` - Event detail
- `POST /api/events/{id}/register` - Register for event

### E-books
- `GET /api/ebooks` - List ebooks
- `GET /api/ebooks/{id}` - Ebook detail
- `POST /api/ebooks/{id}/interact` - Record interaction

### Donations
- `GET /api/donations` - List donations (admin)
- `POST /api/donations` - Create donation

## 🔧 Admin Panel

### Access URL
- **Admin Panel**: `http://localhost:8000/admin`
- **Default Admin**: `daulayreza@gmail.com` / `password`

### Features
- User management
- Event management
- E-book management
- Donation tracking
- Role & permission management
- Audit logs

## 📝 TODO

- [ ] Implementasi API Controllers
- [ ] Implementasi OTP authentication via WhatsApp
- [ ] Implementasi file upload untuk images dan documents
- [ ] Implementasi notification system
- [ ] Implementasi export data (Excel/CSV)
- [ ] Implementasi API documentation
- [ ] Testing dengan PHPUnit
- [ ] Setup MySQL untuk production

## 🔧 Scripts

- `php artisan serve`: Development server
- `php artisan migrate`: Run migrations
- `php artisan db:seed`: Run seeders
- `php artisan make:filament-user`: Create admin user
- `php artisan route:list`: List all routes

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Proyek ini adalah bagian dari platform Sesuai Sunnah.
