<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'due_time' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'warning_sent_at' => 'datetime',
        ];
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user()
    {
        return $this->assignedTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, foreignKey: 'company_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, foreignKey: 'category_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getRouteKeyName()
    {
        return 'ticket_number';
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'pending']);
    }

    // Accessor: allows $ticket->customer_name to still work during migration
    // Returns from customer relationship, falls back to empty string
    public function getCustomerNameAttribute(): string
    {
        return $this->customer?->name ?? '';
    }

    public function getCustomerEmailAttribute(): string
    {
        return $this->customer?->email ?? '';
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->customer?->phone;
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function parentTicket(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_ticket_id');
    }

    public function childTickets(): HasMany
    {
        return $this->hasMany(self::class, 'parent_ticket_id');
    }
}
