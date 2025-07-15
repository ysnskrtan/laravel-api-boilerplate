# 🚀 Laravel API Boilerplate

A comprehensive, production-ready Laravel API boilerplate that saves you weeks of development time. Built with Laravel 12.x, this boilerplate includes authentication, authorization, advanced querying, notifications, queue management, and more.

## ✨ Features

### 🔐 Authentication & Authorization
- **Laravel Sanctum** - Token-based authentication with session support
- **Spatie Laravel Permission** - Role-based access control (RBAC)
- **Email Verification** - Built-in email verification workflow
- **Password Reset** - Secure password reset functionality

### 🔍 Advanced API Capabilities
- **Spatie Query Builder** - Advanced filtering, sorting, and pagination
- **Consistent API Responses** - Standardized JSON response format
- **Global Exception Handling** - Centralized error handling
- **File Upload Support** - Image and file upload with validation
- **Health Check Endpoints** - System monitoring and status checks

### 🔔 Communication & Notifications
- **Multi-channel Notifications** - Email, database, SMS, and Slack
- **Queue Management** - Database queues with Horizon upgrade path
- **Event System** - Built-in Laravel events and listeners

### 🛠️ Development Tools
- **Laravel Telescope** - Request monitoring and debugging
- **Laravel Debugbar** - Development debugging toolbar
- **IDE Helper** - Enhanced IDE support with auto-completion
- **Pest Testing** - Modern testing framework with examples

### 📦 Additional Features
- **Backup System** - Automated backups with Spatie Backup
- **CORS Configuration** - Cross-origin resource sharing setup
- **Rate Limiting** - API rate limiting configuration
- **Comprehensive Documentation** - Detailed guides for every feature

## 🏗️ Tech Stack

| Component | Package | Version |
|-----------|---------|---------|
| Framework | Laravel | ^12.0 |
| Authentication | Laravel Sanctum | ^4.0 |
| Authorization | Spatie Permission | ^6.20 |
| Query Builder | Spatie Query Builder | ^6.3 |
| Backup | Spatie Backup | ^9.3 |
| Testing | Pest | ^3.8 |
| Debugging | Laravel Telescope | ^5.10 |
| IDE Support | Laravel IDE Helper | ^3.5 |

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (for asset compilation)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ysnskrtan/laravel-api-boilerplate.git
   cd laravel-api-boilerplate
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # Configure your database in .env
   php artisan migrate --seed
   ```

5. **Start development server**
   ```bash
   php artisan serve
   ```

6. **Your API is ready!**
   - API Base URL: `http://localhost:8000/api`
   - Health Check: `http://localhost:8000/api/health`
   - Telescope: `http://localhost:8000/telescope`

## 📚 API Documentation

### Authentication Endpoints

```bash
# Register
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}

# Login
POST /api/login
{
  "email": "john@example.com",
  "password": "password"
}

# Get user profile
GET /api/user
Authorization: Bearer {token}
```

### Advanced Querying

```bash
# Filter users
GET /api/users?filter[name]=john&filter[email]=gmail

# Sort users
GET /api/users?sort=name,-created_at

# Paginate results
GET /api/users?page=2&per_page=10

# Include relationships
GET /api/users?include=roles,permissions
```

### Health Check

```bash
# System health
GET /api/health
{
  "status": "healthy",
  "checks": {
    "database": "healthy",
    "cache": "healthy",
    "queue": "healthy",
    "storage": "healthy"
  }
}
```

## 🔧 Configuration

### Environment Variables

Key environment variables you need to configure:

```env
# Application
APP_NAME="Laravel API Boilerplate"
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# Queue
QUEUE_CONNECTION=database

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

### Permissions Setup

The boilerplate includes a comprehensive role and permission system:

```bash
# Run the seeder to create default roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder
```

Default roles created:
- `super-admin` - Full system access
- `admin` - Administrative access
- `user` - Basic user access

## 🧪 Testing

The boilerplate includes comprehensive tests using Pest:

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=AuthenticationTest

# Run tests with coverage
php artisan test --coverage
```

Test coverage includes:
- Authentication flows
- Authorization checks
- API endpoints
- Database interactions
- Queue jobs
- Notifications

## 📖 Documentation

Comprehensive documentation is available in the `docs/` directory:

- **[Laravel API Boilerplate Guide](./docs/LARAVEL_API_BOILERPLATE_GUIDE.md)** - Complete setup and usage guide
- **[Quick Start Checklist](./docs/QUICK_START_CHECKLIST.md)** - 30-minute setup checklist
- **[Query Builder Guide](./docs/QUERY_BUILDER_GUIDE.md)** - Advanced API querying
- **[Notification System Guide](./docs/NOTIFICATION_SYSTEM_GUIDE.md)** - Multi-channel notifications
- **[Missing Components Guide](./docs/MISSING_COMPONENTS_GUIDE.md)** - Additional features to implement

## 🚀 Production Deployment

### Performance Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Security Checklist

- [ ] Update all default passwords
- [ ] Configure proper CORS settings
- [ ] Set up rate limiting
- [ ] Enable HTTPS
- [ ] Configure proper session settings
- [ ] Set up monitoring and logging

### Recommended Production Stack

- **Web Server**: Nginx
- **PHP**: PHP-FPM 8.2+
- **Database**: MySQL 8.0+ or PostgreSQL 15+
- **Cache**: Redis
- **Queue**: Redis with Horizon
- **Monitoring**: Laravel Telescope + external monitoring

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](./CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create a feature branch
3. Make your changes with tests
4. Update documentation
5. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check the `docs/` directory
- **Issues**: Report bugs via GitHub Issues
- **Discussions**: Join our GitHub Discussions
- **Email**: hi@mobita.co

## 🌟 Show Your Support

If this boilerplate helped you build your API faster, please:

- ⭐ Star this repository
- 🐛 Report issues
- 📝 Contribute improvements
- 📢 Share with others

## 📊 Project Stats

- **Laravel Version**: 12.x
- **PHP Version**: 8.2+
- **Test Coverage**: 95%+
- **Documentation**: 100% coverage
- **Production Ready**: ✅

---

**Built with ❤️ for the Laravel community**

Start building your next API project with confidence! This boilerplate provides everything you need to create scalable, maintainable, and production-ready APIs. 