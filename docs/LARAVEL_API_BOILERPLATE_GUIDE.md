# Laravel API-Only Boilerplate Guide

Complete guide for building scalable Laravel API-only applications with authentication, authorization, advanced querying, and more.

## Table of Contents
- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Project Setup](#project-setup)
- [Authentication System](#authentication-system)
- [Authorization & Permissions](#authorization--permissions)
- [Advanced API Querying](#advanced-api-querying)
- [Notification System](#notification-system)
- [Queue Management](#queue-management)
- [Backup System](#backup-system)
- [Development Tools](#development-tools)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Deployment](#deployment)
- [Best Practices](#best-practices)

## Overview

This boilerplate provides a production-ready Laravel API with:
- **Laravel Sanctum** for API authentication
- **Spatie Laravel Permission** for roles and permissions
- **Spatie Query Builder** for advanced filtering and sorting
- **Laravel Notification System** for multi-channel notifications
- **Database Queues** with Horizon migration path
- **Comprehensive Testing** with Pest/PHPUnit
- **Development Tools** for debugging and IDE support

## Tech Stack

### Core Framework
- **Laravel 12.x** - PHP framework
- **PHP 8.2+** - Programming language
- **MySQL/PostgreSQL** - Database

### Key Packages
```json
{
  "require": {
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.0",
    "spatie/laravel-backup": "^9.3",
    "spatie/laravel-permission": "^6.20",
    "spatie/laravel-query-builder": "^6.3"
  },
  "require-dev": {
    "barryvdh/laravel-debugbar": "^3.15",
    "barryvdh/laravel-ide-helper": "^3.5",
    "laravel/breeze": "^2.3",
    "laravel/telescope": "^5.10",
    "pestphp/pest": "^3.8",
    "pestphp/pest-plugin-laravel": "^3.2"
  }
}
```

## Project Setup

### 1. Initial Laravel Installation
```bash
# Create new Laravel project
composer create-project laravel/laravel your-project-name

# Navigate to project
cd your-project-name

# Install required packages
composer require laravel/sanctum spatie/laravel-permission spatie/laravel-query-builder spatie/laravel-backup

# Install development packages
composer require --dev barryvdh/laravel-debugbar barryvdh/laravel-ide-helper laravel/telescope pestphp/pest pestphp/pest-plugin-laravel
```

### 2. Environment Configuration
```env
# .env file
APP_NAME="Your API Name"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue (start with database, migrate to Redis later)
QUEUE_CONNECTION=database

# Frontend URL (for notifications)
FRONTEND_URL=http://localhost:3000

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourapp.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE your_database;"

# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 4. Authentication Setup
```bash
# Install Laravel Breeze API
composer require laravel/breeze --dev
php artisan breeze:install api

# Install Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 5. Permission System Setup
```bash
# Publish permission migration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run permission migrations
php artisan migrate

# Create roles and permissions seeder
php artisan make:seeder RolesAndPermissionsSeeder
```

### 6. Development Tools Setup
```bash
# Install Telescope
php artisan telescope:install
php artisan migrate

# Install Debugbar (auto-discovery)
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"

# Install IDE Helper
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

## Authentication System

### Laravel Sanctum Configuration

**User Model Setup:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Authentication Routes
```php
// routes/auth.php
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1']);

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1']);
```

### API Authentication Usage
```php
// Protect routes with Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::apiResource('users', UserController::class);
});
```

### Frontend Integration
```javascript
// Login example
const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password'
    })
});

// For subsequent requests (if using session-based auth)
const userResponse = await fetch('/api/user', {
    headers: {
        'Accept': 'application/json'
    },
    credentials: 'include' // Include cookies
});
```

## Authorization & Permissions

### Spatie Laravel Permission Setup

**Middleware Registration:**
```php
// bootstrap/app.php
$middleware->alias([
    'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

### Roles and Permissions Seeder
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'access admin panel']);

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->givePermissionTo(['manage users']);

        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create default admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $adminUser->assignRole('admin');
    }
}
```

### Using Permissions in Controllers
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        $this->authorize('viewAny', User::class);
        // or
        // $request->user()->can('manage users');
        
        return UserResource::collection(User::paginate());
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $user = User::create($request->validated());
        
        return new UserResource($user);
    }
}
```

### Route Protection
```php
// Protect routes with roles/permissions
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});

Route::middleware(['auth:sanctum', 'permission:manage users'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

## Advanced API Querying

### Spatie Query Builder Implementation

**Controller with Advanced Filtering:**
```php
<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                'name',
                'email',
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::scope('created_after'),
                AllowedFilter::scope('created_before'),
                AllowedFilter::scope('has_role'),
                AllowedFilter::scope('has_permission'),
            ])
            ->allowedSorts([
                'name',
                'email',
                'created_at',
                'updated_at',
                AllowedSort::field('latest', 'created_at'),
            ])
            ->allowedIncludes([
                'roles',
                'permissions',
                'roles.permissions',
            ])
            ->defaultSort('-created_at')
            ->paginate($request->input('page.size', 15))
            ->appends($request->query());

        return UserResource::collection($users);
    }
}
```

### User Model with Query Scopes
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Query scopes for advanced filtering
    public function scopeCreatedAfter($query, $date)
    {
        return $query->where('created_at', '>=', $date);
    }

    public function scopeCreatedBefore($query, $date)
    {
        return $query->where('created_at', '<=', $date);
    }

    public function scopeHasRole($query, $role)
    {
        return $query->role($role);
    }

    public function scopeHasPermission($query, $permission)
    {
        return $query->permission($permission);
    }
}
```

### API Resource with Conditional Loading
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include relationships only when requested
            'roles' => $this->when($this->relationLoaded('roles'), function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                    ];
                });
            }),
            
            'permissions' => $this->when($this->relationLoaded('permissions'), function () {
                return $this->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                    ];
                });
            }),
        ];
    }
}
```

### Usage Examples
```bash
# Filter by name
GET /api/users?filter[name]=john

# Filter and sort
GET /api/users?filter[email]=gmail.com&sort=-created_at

# Include relationships
GET /api/users?include=roles,permissions

# Complex query
GET /api/users?filter[has_role]=admin&filter[created_after]=2024-01-01&sort=name&include=roles&page[size]=10
```

## Notification System

### Creating Notifications
```bash
# Create notification class
php artisan make:notification UserWelcome
```

### Notification Implementation
```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserWelcome extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->line('Welcome, ' . $this->user->name . '!')
            ->action('Get Started', url('/dashboard'))
            ->line('Thank you for joining us!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Welcome!',
            'message' => 'Welcome to our platform!',
            'action_url' => url('/dashboard'),
            'action_text' => 'Get Started',
        ];
    }
}
```

### Sending Notifications
```php
// In controller
use App\Notifications\UserWelcome;

$user = User::create($request->validated());
$user->notify(new UserWelcome($user));
```

## Queue Management

### Database Queue Setup (Default)
```php
// .env
QUEUE_CONNECTION=database

// Development command
php artisan queue:listen --tries=1
```

### Development Script
```json
{
  "scripts": {
    "dev": [
      "Composer\\Config::disableProcessTimeout",
      "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'"
    ]
  }
}
```

### Migration to Laravel Horizon
When you need advanced queue management, follow the [Laravel Horizon Migration Guide](./LARAVEL_HORIZON_MIGRATION_GUIDE.md).

## Backup System

### Spatie Laravel Backup Configuration
```php
// config/backup.php
return [
    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],
            ],
            'databases' => [
                'mysql',
            ],
        ],
        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                'local',
            ],
        ],
    ],
];
```

### Backup Commands
```bash
# Create backup
php artisan backup:run

# List backups
php artisan backup:list

# Clean old backups
php artisan backup:clean

# Schedule backups (in app/Console/Kernel.php)
$schedule->command('backup:run')->daily()->at('01:00');
```

## Development Tools

### Laravel Telescope
```bash
# Access dashboard
http://your-app.test/telescope

# Useful for debugging:
# - Database queries
# - HTTP requests
# - Jobs and queues
# - Notifications
# - Performance monitoring
```

### Laravel Debugbar
```bash
# Shows debug information in development
# - Queries
# - Routes
# - Views
# - Mail
# - Performance metrics
```

### IDE Helper
```bash
# Generate helper files
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

## Testing

### Pest Configuration
```php
// tests/Pest.php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
```

### Authentication Tests
```php
// tests/Feature/Auth/AuthenticationTest.php
use App\Models\User;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertNoContent();
});
```

### API Resource Tests
```php
// tests/Feature/UserControllerTest.php
use App\Models\User;

test('can list users with filtering', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    
    $response = $this->actingAs($user)
        ->get('/api/users?filter[name]=John');
    
    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'John Doe');
});
```

## API Documentation

### OpenAPI/Swagger Setup
```bash
# Install L5 Swagger
composer require darkaonline/l5-swagger

# Publish config
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"

# Generate documentation
php artisan l5-swagger:generate
```

### API Documentation Example
```php
/**
 * @OA\Get(
 *     path="/api/users",
 *     summary="Get list of users",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="filter[name]",
 *         in="query",
 *         description="Filter by name",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
 *         )
 *     )
 * )
 */
public function index(Request $request)
{
    // Implementation
}
```

## Deployment

### Environment Setup
```bash
# Production environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your_production_db

# Queue (consider Redis for production)
QUEUE_CONNECTION=redis
REDIS_HOST=your-redis-host

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
```

### Production Commands
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed production data
php artisan db:seed --class=RolesAndPermissionsSeeder

# Generate application key
php artisan key:generate

# Create storage link
php artisan storage:link

# Queue worker (use supervisor in production)
php artisan queue:work --daemon
```

## Best Practices

### 1. Code Organization
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/          # Authentication controllers
│   │   ├── Admin/         # Admin controllers
│   │   └── Api/           # API controllers
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Notifications/
├── Jobs/
└── Services/              # Business logic
```

### 2. Security
- Always use middleware for route protection
- Validate all inputs with Form Requests
- Use API resources to control data exposure
- Implement rate limiting
- Use HTTPS in production

### 3. Performance
- Use eager loading for relationships
- Implement caching for expensive operations
- Use database indexing
- Monitor with Telescope
- Optimize queries with Query Builder

### 4. Testing
- Write tests for all API endpoints
- Use factories for test data
- Test authentication and authorization
- Test validation rules
- Use feature tests for integration

### 5. Documentation
- Document all API endpoints
- Keep README updated
- Document configuration changes
- Use clear commit messages
- Update this guide as features evolve

## Conclusion

This Laravel API-only boilerplate provides a solid foundation for building scalable APIs with:

- ✅ **Complete Authentication System** with Sanctum
- ✅ **Flexible Authorization** with roles and permissions
- ✅ **Advanced API Querying** with filtering, sorting, and pagination
- ✅ **Multi-channel Notifications** with queue support
- ✅ **Robust Queue Management** with Horizon migration path
- ✅ **Comprehensive Testing** with Pest
- ✅ **Development Tools** for debugging and productivity
- ✅ **Production-ready** configuration and deployment guides

Start with this boilerplate and customize it based on your specific project requirements. The modular structure makes it easy to add new features while maintaining clean, maintainable code.

For specific feature guides, refer to:
- [Laravel Horizon Migration Guide](./LARAVEL_HORIZON_MIGRATION_GUIDE.md)
- [Notification System Guide](./NOTIFICATION_SYSTEM_GUIDE.md)
- [Query Builder Guide](./QUERY_BUILDER_GUIDE.md) 