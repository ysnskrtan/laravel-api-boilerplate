# Laravel API Boilerplate - Quick Start Checklist

Use this checklist to quickly set up a new Laravel API project based on this boilerplate. Complete each step in order.

## 📋 Pre-Setup Requirements

- [ ] **PHP 8.2+** installed
- [ ] **Composer** installed
- [ ] **Node.js & npm** installed (for concurrent development)
- [ ] **MySQL/PostgreSQL** database server
- [ ] **Redis** (optional, for Horizon later)
- [ ] **Mail server** (Mailhog for development)

## 🚀 Step 1: Create New Project

```bash
# Create fresh Laravel project
composer create-project laravel/laravel your-project-name
cd your-project-name

# Install core packages
composer require laravel/sanctum spatie/laravel-permission spatie/laravel-query-builder spatie/laravel-backup

# Install development packages
composer require --dev laravel/breeze barryvdh/laravel-debugbar barryvdh/laravel-ide-helper laravel/telescope pestphp/pest pestphp/pest-plugin-laravel
```

**✅ Status**: [ ] Complete

## 🔧 Step 2: Environment Configuration

```bash
# Copy and configure .env file
cp .env.example .env
```

**Update .env with your settings:**
```env
APP_NAME="Your API Name"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue
QUEUE_CONNECTION=database

# Frontend URL
FRONTEND_URL=http://localhost:3000

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000

# Mail (Development)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourapp.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**✅ Status**: [ ] Complete

## 🗄️ Step 3: Database Setup

```bash
# Generate application key
php artisan key:generate

# Create database (if not exists)
mysql -u root -p -e "CREATE DATABASE your_database_name;"

# Run migrations
php artisan migrate
```

**✅ Status**: [ ] Complete

## 🔐 Step 4: Authentication Setup

```bash
# Install Breeze API
php artisan breeze:install api

# Install Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**✅ Status**: [ ] Complete

## 👥 Step 5: Permission System

```bash
# Install permission system
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**Create and run seeder:**
```bash
# Create seeder
php artisan make:seeder RolesAndPermissionsSeeder

# Add seeder content (copy from docs/LARAVEL_API_BOILERPLATE_GUIDE.md)
# Then run:
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**✅ Status**: [ ] Complete

## 🛠️ Step 6: Development Tools

```bash
# Install Telescope
php artisan telescope:install
php artisan migrate

# Install Debugbar
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"

# Install IDE Helper
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta

# Install Pest
php artisan pest:install
```

**✅ Status**: [ ] Complete

## 📁 Step 7: Copy Boilerplate Files

**Copy these files from the boilerplate:**

### Models
- [ ] **app/Models/User.php** - Updated with HasRoles and HasApiTokens traits

### Controllers
- [ ] **app/Http/Controllers/UserController.php** - With query builder implementation
- [ ] **app/Http/Controllers/Auth/*.php** - Authentication controllers

### Resources
- [ ] **app/Http/Resources/UserResource.php** - API resource with conditional loading

### Middleware
- [ ] **app/Http/Middleware/EnsureEmailIsVerified.php** - Email verification middleware

### Requests
- [ ] **app/Http/Requests/Auth/LoginRequest.php** - Login validation

### Configuration
- [ ] **bootstrap/app.php** - Middleware aliases configuration
- [ ] **routes/api.php** - API routes
- [ ] **routes/auth.php** - Authentication routes

**✅ Status**: [ ] Complete

## 🧪 Step 8: Testing Setup

```bash
# Update composer.json with dev script
```

**Add to composer.json scripts:**
```json
{
  "scripts": {
    "dev": [
      "Composer\\Config::disableProcessTimeout",
      "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'"
    ],
    "test": [
      "@php artisan config:clear --ansi",
      "@php artisan test"
    ]
  }
}
```

**Copy test files:**
- [ ] **tests/Feature/Auth/AuthenticationTest.php**
- [ ] **tests/Pest.php**

**✅ Status**: [ ] Complete

## 📚 Step 9: Copy Documentation

**Copy documentation files:**
- [ ] **docs/LARAVEL_API_BOILERPLATE_GUIDE.md**
- [ ] **docs/QUERY_BUILDER_GUIDE.md**
- [ ] **docs/NOTIFICATION_SYSTEM_GUIDE.md**
- [ ] **docs/LARAVEL_HORIZON_MIGRATION_GUIDE.md**
- [ ] **docs/README.md**
- [ ] **docs/QUICK_START_CHECKLIST.md**

**✅ Status**: [ ] Complete

## 🔄 Step 10: Install Concurrently (for dev script)

```bash
# Install concurrently for development
npm install --save-dev concurrently
```

**✅ Status**: [ ] Complete

## ✅ Step 11: Final Verification

**Test that everything works:**

```bash
# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run tests
php artisan test
# or
composer test

# Start development server
composer run dev
```

**Verify these endpoints work:**
- [ ] **GET /api/user** (with authentication)
- [ ] **POST /api/register** (user registration)
- [ ] **POST /api/login** (user login)
- [ ] **GET /api/users** (user listing with filters)
- [ ] **GET /telescope** (development monitoring)

**✅ Status**: [ ] Complete

## 🚀 Step 12: Project Customization

**Now customize for your project:**

- [ ] **Update app name** in .env and config
- [ ] **Add your specific models** and controllers
- [ ] **Define your roles and permissions** in seeder
- [ ] **Create your API endpoints** using the patterns shown
- [ ] **Add your notification types** as needed
- [ ] **Configure backup destinations** if needed
- [ ] **Update tests** for your specific features

**✅ Status**: [ ] Complete

## 📊 What You Get

After completing this checklist, you'll have:

### ✅ Authentication & Authorization
- User registration and login
- API token authentication with Sanctum
- Role-based permissions with Spatie Permission
- Email verification system
- Password reset functionality

### ✅ Advanced API Features
- Filtering, sorting, and pagination with Query Builder
- JSON API resources with conditional loading
- Proper error handling and validation
- Rate limiting and security middleware

### ✅ Development Tools
- Laravel Telescope for request monitoring
- Laravel Debugbar for debugging
- IDE Helper for better development experience
- Pest testing framework setup

### ✅ Production Ready
- Database queue system (upgradeable to Horizon)
- Notification system (email, database, SMS, Slack)
- Backup system with Spatie Backup
- Comprehensive test coverage
- Production deployment guides

## 🆘 Troubleshooting

**Common Issues:**

1. **Database connection error**
   - Check database credentials in .env
   - Ensure database server is running
   - Create database if it doesn't exist

2. **Queue not processing**
   - Run `php artisan queue:listen --tries=1`
   - Check database queue table exists
   - Verify QUEUE_CONNECTION=database in .env

3. **Sanctum authentication not working**
   - Check SANCTUM_STATEFUL_DOMAINS in .env
   - Verify Sanctum middleware is configured
   - Clear config cache: `php artisan config:clear`

4. **Tests failing**
   - Run `php artisan config:clear`
   - Ensure test database is configured
   - Check all required files are copied

5. **Development script not working**
   - Install concurrently: `npm install --save-dev concurrently`
   - Check composer.json scripts section
   - Verify all artisan commands work individually

## 📖 Next Steps

1. **Read the comprehensive guide**: [Laravel API Boilerplate Guide](./LARAVEL_API_BOILERPLATE_GUIDE.md)
2. **Learn advanced querying**: [Query Builder Guide](./QUERY_BUILDER_GUIDE.md)
3. **Set up notifications**: [Notification System Guide](./NOTIFICATION_SYSTEM_GUIDE.md)
4. **Plan for scale**: [Laravel Horizon Migration Guide](./LARAVEL_HORIZON_MIGRATION_GUIDE.md)

---

**🎉 Congratulations!** You now have a production-ready Laravel API boilerplate with all the essential features configured and documented. Start building your amazing API! 🚀 