<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'is_active',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    // scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // MÉTHODES
    // =================================
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paymentMethod) {
            if (empty($paymentMethod->slug)) {
                $paymentMethod->slug = Str::slug($paymentMethod->name);
            }
        });
    }

    /**
     * Activer/Désactiver
     */
    public function activate(): self
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    /**
     * Déplacer vers le haut
     */
    public function moveUp(): self
    {
        $previous = static::where('position', '<', $this->position)
            ->orderBy('position', 'desc')
            ->first();

        if ($previous) {
            $tempPosition = $this->position;
            $this->update(['position' => $previous->position]);
            $previous->update(['position' => $tempPosition]);
        }

        return $this;
    }

    /**
     * Déplacer vers le bas
     */
    public function moveDown(): self
    {
        $next = static::where('position', '>', $this->position)
            ->orderBy('position', 'asc')
            ->first();

        if ($next) {
            $tempPosition = $this->position;
            $this->update(['position' => $next->position]);
            $next->update(['position' => $tempPosition]);
        }

        return $this;
    }
}
