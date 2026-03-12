<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoyaltyCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'loyalty_program_id',
        'total_visits',
        'last_activity_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function loyaltyProgram(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }

    /**
     * Prochaine récompense à débloquer
     */
    public function getNextRewardAttribute(): ?object
    {
        return $this->loyaltyProgram
            ->rewards()
            ->where('is_active', true)
            ->where('required_visits', '>', $this->total_visits)
            ->orderBy('required_visits')
            ->first();
    }

    /**
     * Progrès vers la prochaine récompense (en %)
     */
    public function getProgressPercentageAttribute(): int
    {
        $nextReward = $this->next_reward;
        if (!$nextReward) {
            return 100;
        }
        return min(100, (int)(($this->total_visits / $nextReward->required_visits) * 100));
    }

    /**
     * Nombre de visites restantes
     */
    public function getVisitsRemainingAttribute(): int
    {
        $nextReward = $this->next_reward;
        if (!$nextReward) {
            return 0;
        }
        return max(0, $nextReward->required_visits - $this->total_visits);
    }
}
