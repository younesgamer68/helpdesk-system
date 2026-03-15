<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property array $conditions
 * @property array $actions
 * @property bool $is_active
 * @property int $priority
 * @property int $executions_count
 * @property \Carbon\Carbon|null $last_executed_at
 */
class AutomationRule extends Model
{
    /** @use HasFactory<\Database\Factories\AutomationRuleFactory> */
    use HasFactory;

    public const TYPE_ASSIGNMENT = 'assignment';

    public const TYPE_PRIORITY = 'priority';

    public const TYPE_AUTO_REPLY = 'auto_reply';

    public const TYPE_ESCALATION = 'escalation';

    public const TYPE_SLA_BREACH = 'sla_breach';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'executions_count' => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to only active rules.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to order by priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Record a rule execution.
     */
    public function recordExecution(): void
    {
        $this->increment('executions_count');
        $this->update(['last_executed_at' => now()]);
    }

    /**
     * Get available rule types.
     *
     * @return array<string, string>
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_ASSIGNMENT => 'Auto Assignment',
            self::TYPE_PRIORITY => 'Priority Change',
            self::TYPE_AUTO_REPLY => 'Auto Reply',
            self::TYPE_ESCALATION => 'Escalation',
            self::TYPE_SLA_BREACH => 'SLA Breach',
        ];
    }
}
