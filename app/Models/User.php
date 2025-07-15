<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Query scope for filtering users created after a specific date.
     */
    public function scopeCreatedAfter($query, $date)
    {
        return $query->where('created_at', '>=', $date);
    }

    /**
     * Query scope for filtering users created before a specific date.
     */
    public function scopeCreatedBefore($query, $date)
    {
        return $query->where('created_at', '<=', $date);
    }

    /**
     * Query scope for filtering users with a specific role.
     */
    public function scopeHasRole($query, $role)
    {
        return $query->role($role);
    }

    /**
     * Query scope for filtering users with a specific permission.
     */
    public function scopeHasPermission($query, $permission)
    {
        return $query->permission($permission);
    }

    /**
     * Query scope for filtering users with any of the specified roles.
     */
    public function scopeHasAnyRole($query, $roles)
    {
        $rolesArray = is_array($roles) ? $roles : explode(',', $roles);
        return $query->role($rolesArray);
    }

    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the published posts for the user.
     */
    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->published();
    }

    /**
     * Get the draft posts for the user.
     */
    public function draftPosts()
    {
        return $this->hasMany(Post::class)->draft();
    }
}
