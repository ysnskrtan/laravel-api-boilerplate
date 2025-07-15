# Laravel Notification System Guide

This guide covers Laravel's built-in notification system, how it works with your current setup, and when to consider upgrading to Horizon.

## Table of Contents
- [Current System Status](#current-system-status)
- [Available Notification Channels](#available-notification-channels)
- [Implementation Examples](#implementation-examples)
- [Database Notifications](#database-notifications)
- [Queue Integration](#queue-integration)
- [Performance Considerations](#performance-considerations)
- [When to Consider Horizon](#when-to-consider-horizon)
- [Migration to Horizon](#migration-to-horizon)

## Current System Status

### ✅ Fully Functional Right Now
- **User Model**: Has `Notifiable` trait
- **Queue Support**: Database queues handle notification jobs
- **Multiple Channels**: Mail, database, broadcast, SMS, Slack
- **Real-time Capabilities**: WebSocket broadcasting available
- **Auth Notifications**: Email verification and password reset working

### 🗂️ Database Setup
- **Notifications Table**: ✅ Created and migrated
- **Jobs Table**: ✅ Available for queuing
- **Queue Connection**: Database (perfect for current scale)

## Available Notification Channels

### 1. Mail Notifications
```php
// Already working with your current setup
public function via($notifiable): array
{
    return ['mail'];
}

public function toMail($notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('Welcome!')
        ->line('Welcome to our platform!')
        ->action('Get Started', url('/dashboard'))
        ->line('Thank you for joining us!');
}
```

### 2. Database Notifications
```php
public function via($notifiable): array
{
    return ['database'];
}

public function toDatabase($notifiable): array
{
    return [
        'title' => 'New Message',
        'message' => 'You have a new message from support.',
        'action_url' => url('/messages'),
        'action_text' => 'View Message',
    ];
}
```

### 3. Broadcast Notifications (Real-time)
```php
public function via($notifiable): array
{
    return ['broadcast'];
}

public function toBroadcast($notifiable): BroadcastMessage
{
    return new BroadcastMessage([
        'title' => 'Live Update',
        'message' => 'Your order status has changed.',
    ]);
}
```

### 4. SMS Notifications
```php
// Install: composer require laravel/vonage-notification-channel
public function via($notifiable): array
{
    return ['vonage'];
}

public function toVonage($notifiable)
{
    return (new VonageMessage)
        ->content('Your verification code is: 123456');
}
```

### 5. Slack Notifications
```php
// Install: composer require laravel/slack-notification-channel
public function via($notifiable): array
{
    return ['slack'];
}

public function toSlack($notifiable)
{
    return (new SlackMessage)
        ->content('New user registered: ' . $notifiable->name);
}
```

## Implementation Examples

### Basic Notification Class
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
            ->line('Welcome to our platform, ' . $this->user->name . '!')
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
use App\Notifications\UserWelcome;

// Send to single user
$user = User::find(1);
$user->notify(new UserWelcome($user));

// Send to multiple users
$users = User::where('active', true)->get();
Notification::send($users, new UserWelcome($user));

// Send on-demand to email
Notification::route('mail', 'user@example.com')
    ->notify(new UserWelcome($user));
```

### Controller Implementation
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserWelcome;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create($request->validated());
        
        // Send welcome notification (queued automatically)
        $user->notify(new UserWelcome($user));
        
        return response()->json($user, 201);
    }

    public function notifications(User $user)
    {
        return $user->notifications;
    }

    public function unreadNotifications(User $user)
    {
        return $user->unreadNotifications;
    }

    public function markNotificationAsRead(User $user, $notificationId)
    {
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        return response()->json(['status' => 'success']);
    }
}
```

## Database Notifications

### Reading Notifications
```php
// Get all notifications
$notifications = $user->notifications;

// Get unread notifications
$unreadNotifications = $user->unreadNotifications;

// Check if user has unread notifications
$hasUnread = $user->unreadNotifications->count() > 0;
```

### Managing Notifications
```php
// Mark as read
$user->unreadNotifications->markAsRead();

// Mark specific notification as read
$notification = $user->notifications()->find($notificationId);
$notification->markAsRead();

// Delete notification
$notification->delete();
```

### Frontend Integration
```javascript
// Fetch notifications
fetch('/api/users/1/notifications')
    .then(response => response.json())
    .then(notifications => {
        // Display notifications
    });

// Mark as read
fetch('/api/users/1/notifications/123/read', {
    method: 'POST'
});
```

## Queue Integration

### Current Setup (Database Queues)
```php
// This notification will be queued automatically
class UserWelcome extends Notification implements ShouldQueue
{
    use Queueable;
    
    // Notification content...
}

// Queue processing
php artisan queue:listen --tries=1
```

### Queue Configuration
```php
// In notification class
public function __construct($user)
{
    $this->user = $user;
    
    // Queue on specific connection
    $this->onConnection('database');
    
    // Queue on specific queue
    $this->onQueue('notifications');
    
    // Delay notification
    $this->delay(now()->addMinutes(5));
}
```

## Performance Considerations

### Current System Performance
- **Small Scale** (< 100 notifications/hour): Perfect
- **Medium Scale** (100-1000 notifications/hour): Good
- **Large Scale** (1000+ notifications/hour): Consider optimization

### Optimization Strategies
```php
// Batch notifications
$users = User::where('active', true)->get();
$users->each(function ($user) {
    $user->notify(new UserWelcome($user));
});

// Use notification facades for bulk
Notification::send($users, new UserWelcome());

// Conditional channels
public function via($notifiable): array
{
    $channels = ['database'];
    
    if ($notifiable->email_notifications) {
        $channels[] = 'mail';
    }
    
    if ($notifiable->sms_notifications) {
        $channels[] = 'vonage';
    }
    
    return $channels;
}
```

## When to Consider Horizon

### Current System is Perfect For:
- **Development** and testing
- **Small to medium applications** (< 500 users)
- **Low notification volume** (< 100 notifications/hour)
- **Simple notification workflows**

### Consider Horizon When:
- **High notification volume** (1000+ notifications/hour)
- **Real-time monitoring** needed
- **Complex retry logic** required
- **Production environment** with scaling needs
- **Performance bottlenecks** in notification processing

### Performance Comparison

| Metric | Database Queues | Horizon + Redis |
|--------|----------------|-----------------|
| Setup Complexity | ⭐ Simple | ⭐⭐⭐ Complex |
| Performance | ⭐⭐⭐ Good | ⭐⭐⭐⭐⭐ Excellent |
| Monitoring | ⭐⭐ Basic | ⭐⭐⭐⭐⭐ Advanced |
| Scalability | ⭐⭐⭐ Good | ⭐⭐⭐⭐⭐ Excellent |
| Debugging | ⭐⭐⭐⭐ Easy | ⭐⭐⭐ Medium |

## Migration to Horizon

### When You're Ready
Follow the [Laravel Horizon Migration Guide](./LARAVEL_HORIZON_MIGRATION_GUIDE.md) when you need:

1. **Real-time Dashboard**: Monitor notification processing
2. **Auto-scaling**: Handle varying notification loads
3. **Advanced Metrics**: Detailed performance insights
4. **Production Features**: Supervisor integration, health checks

### Migration Benefits for Notifications
- **Real-time monitoring** of notification jobs
- **Failed notification management** with retry controls
- **Performance metrics** for notification throughput
- **Auto-scaling** workers based on notification volume

## Best Practices

### Notification Design
```php
// Keep notifications focused
class OrderShipped extends Notification implements ShouldQueue
{
    use Queueable;
    
    public $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
    
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
}
```

### Error Handling
```php
// Implement failure handling
public function failed($exception)
{
    // Log the failure
    Log::error('Notification failed: ' . $exception->getMessage());
    
    // Optionally notify administrators
    // Admin::notify(new NotificationFailure($this, $exception));
}
```

### Testing
```php
// In tests
use Illuminate\Support\Facades\Notification;

public function test_user_receives_welcome_notification()
{
    Notification::fake();
    
    $user = User::factory()->create();
    $user->notify(new UserWelcome($user));
    
    Notification::assertSentTo($user, UserWelcome::class);
}
```

## Conclusion

**Your current notification system is production-ready and supports all major notification types.** You can build a complete notification system right now with:

- ✅ Email notifications (queued)
- ✅ Database notifications (for in-app alerts)
- ✅ Real-time broadcasting
- ✅ SMS and Slack integration
- ✅ Proper queue handling

**Start building your notification features now.** Consider Horizon later when you need advanced monitoring and scaling capabilities.

The notification system works seamlessly with your current database queue setup and will continue to work when you eventually migrate to Horizon. 