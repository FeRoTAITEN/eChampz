<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'admin_id',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the ticket this response belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user that created the response (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that created the response (if any).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the attachments for this response.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'response_id');
    }

    /**
     * Check if this response is from an admin.
     */
    public function isFromAdmin(): bool
    {
        return $this->admin_id !== null;
    }

    /**
     * Check if this response is from the user.
     */
    public function isFromUser(): bool
    {
        return $this->user_id !== null;
    }
}

