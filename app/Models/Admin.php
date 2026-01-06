<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
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
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Get the permissions for the admin.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'admin_permission')
            ->withTimestamps();
    }

    /**
     * Check if admin has a specific permission.
     */
    public function hasPermission(string $slug): bool
    {
        // Super admins have all permissions
        // Access the attribute directly - Laravel's casting will handle the conversion
        if ($this->is_super_admin) {
            return true;
        }

        return $this->permissions()->where('slug', $slug)->exists();
    }

    /**
     * Check if admin has any of the given permissions.
     */
    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->permissions()->whereIn('slug', $slugs)->exists();
    }

    /**
     * Grant permission to admin.
     */
    public function grantPermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching($permission);
    }

    /**
     * Revoke permission from admin.
     */
    public function revokePermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission);
    }

    /**
     * Sync admin permissions.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get the tickets assigned to this admin.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Get the ticket responses created by this admin.
     */
    public function ticketResponses(): HasMany
    {
        return $this->hasMany(TicketResponse::class, 'admin_id');
    }
}








