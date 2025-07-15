# Laravel Horizon Migration Guide

This guide covers when and how to migrate from database queues to Laravel Horizon for better queue management and monitoring.

## Table of Contents
- [Current Setup](#current-setup)
- [What is Laravel Horizon?](#what-is-laravel-horizon)
- [When to Migrate](#when-to-migrate)
- [Migration Process](#migration-process)
- [Configuration](#configuration)
- [Monitoring and Management](#monitoring-and-management)
- [Troubleshooting](#troubleshooting)

## Current Setup

### Database Queue Configuration
Currently, the application uses database queues with the following setup:

**Queue Connection:** `database` (default)
**Queue Table:** `jobs`
**Development Command:** `php artisan queue:listen --tries=1`

**Advantages:**
- Simple setup, no additional infrastructure
- Easy debugging and monitoring
- Perfect for development and low-traffic applications
- Jobs are stored in database (persistent)

**Limitations:**
- Performance bottlenecks with high job volume
- No real-time monitoring dashboard
- Manual worker management
- Limited scaling capabilities

## What is Laravel Horizon?

Laravel Horizon is a dashboard and configuration system for Laravel's Redis-powered queues that provides:

### Key Features
- **Real-time Dashboard**: Monitor jobs, failed jobs, and queue metrics
- **Auto-scaling**: Automatically scale workers based on workload
- **Job Management**: Retry, delete, and inspect jobs easily
- **Performance Metrics**: Detailed insights into job performance
- **Beautiful UI**: Web-based interface for queue management
- **Supervisor Integration**: Process management for production

### Requirements
- Redis server
- Laravel application
- Web server access for dashboard

## When to Migrate

### Performance Indicators
Consider migrating when you experience:

- **High Job Volume**: More than 100 jobs per hour
- **Database Performance Issues**: Queue operations slowing down database
- **Need for Real-time Monitoring**: Want to see job status instantly
- **Production Deployment**: Moving to production environment
- **Multiple Workers**: Need to run multiple queue workers
- **Complex Job Workflows**: Advanced job management requirements

### User Load Thresholds
- **Low Load** (< 50 users): Database queues sufficient
- **Medium Load** (50-500 users): Consider migration
- **High Load** (500+ users): Horizon recommended

## Migration Process

### Step 1: Install Redis

**Using Docker (Recommended for Development):**
```bash
# Add to docker-compose.yml
version: '3.8'
services:
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

volumes:
  redis_data:
```

**Using Herd (Laravel Herd):**
```bash
# Redis is included with Herd
# Check if running:
redis-cli ping
```

### Step 2: Install Required Packages

```bash
# Install Redis client
composer require predis/predis

# Install Laravel Horizon
composer require laravel/horizon
```

### Step 3: Publish Horizon Configuration

```bash
# Publish Horizon assets and configuration
php artisan horizon:install

# Publish Horizon configuration (optional)
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
```

### Step 4: Update Environment Configuration

Update your `.env` file:

```env
# Change queue connection
QUEUE_CONNECTION=redis

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Horizon settings (optional)
HORIZON_DARKMODE=false
HORIZON_DOMAIN=null
HORIZON_PATH=horizon
```

### Step 5: Update Development Script

Update the `dev` script in `composer.json`:

```json
"scripts": {
    "dev": [
        "Composer\\Config::disableProcessTimeout",
        "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan horizon\" \"npm run dev\" --names='server,horizon,vite'"
    ]
}
```

### Step 6: Configure Horizon

Edit `config/horizon.php` for your needs:

```php
<?php

return [
    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 1,
            'tries' => 1,
            'timeout' => 60,
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'processes' => 10,
                'tries' => 3,
                'timeout' => 60,
                'nice' => 0,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'processes' => 1,
                'tries' => 1,
                'timeout' => 60,
                'nice' => 0,
            ],
        ],
    ],
];
```

### Step 7: Test Migration

```bash
# Clear config cache
php artisan config:clear

# Start Horizon
php artisan horizon

# Test with a simple job
php artisan make:job TestJob
```

## Configuration

### Queue Configuration Updates

Your existing `config/queue.php` already has Redis configuration. Verify it matches:

```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
    'block_for' => null,
    'after_commit' => false,
],
```

### Horizon Supervisor Configuration

For production, create supervisor configuration:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /path/to/your/app/artisan horizon
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/horizon.log
stopwaitsecs=3600
```

## Monitoring and Management

### Horizon Dashboard

Access the dashboard at: `http://your-app.test/horizon`

**Dashboard Features:**
- Real-time job monitoring
- Failed job management
- Queue metrics and throughput
- Worker status and performance
- Job retry and deletion

### Useful Commands

```bash
# Start Horizon
php artisan horizon

# Pause Horizon
php artisan horizon:pause

# Continue Horizon
php artisan horizon:continue

# Terminate Horizon
php artisan horizon:terminate

# Clear all jobs
php artisan horizon:clear

# Check status
php artisan horizon:status
```

### Monitoring Jobs

```php
// Dispatch jobs as usual
use App\Jobs\YourJob;

YourJob::dispatch($data);

// Jobs will now appear in Horizon dashboard
```

## Troubleshooting

### Common Issues

**1. Redis Connection Failed**
```bash
# Check if Redis is running
redis-cli ping

# Should return: PONG
```

**2. Horizon Dashboard Not Accessible**
- Check `HORIZON_PATH` in `.env`
- Verify web server configuration
- Clear route cache: `php artisan route:clear`

**3. Jobs Not Processing**
```bash
# Check Horizon status
php artisan horizon:status

# Restart Horizon
php artisan horizon:terminate
php artisan horizon
```

**4. Performance Issues**
- Increase `processes` in horizon config
- Optimize Redis configuration
- Monitor memory usage

### Rollback to Database Queues

If you need to rollback:

```bash
# Update .env
QUEUE_CONNECTION=database

# Clear config
php artisan config:clear

# Use old dev script
php artisan queue:listen --tries=1
```

## Best Practices

### Development
- Start with 1 process for local development
- Use `balance: simple` for predictable behavior
- Monitor logs for debugging

### Production
- Use multiple processes based on load
- Implement proper supervisor configuration
- Set up monitoring and alerting
- Regular Redis maintenance

### Job Design
- Keep jobs small and focused
- Implement proper error handling
- Use job batching for related tasks
- Set appropriate timeouts

## Conclusion

Migrate to Laravel Horizon when you need:
- Better performance for high job volumes
- Real-time monitoring capabilities
- Advanced queue management features
- Production-ready queue infrastructure

The migration is straightforward and doesn't require changes to existing job classes. Start with the development setup and gradually move to production configuration as your application grows. 