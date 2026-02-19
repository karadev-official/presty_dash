<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'professional_profile_id',
        'user_id',
        'avatar_image_id',
        'display_name',
        'notes',
        'custom_phone',
        'custom_email',
        'tags',
        'preferences',
        'is_favorite',
        'is_blocked',
        'first_visit_at',
        'last_visit_at',
        'total_appointments',
        'total_spent',
    ];

    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'preferences' => 'array',
            'is_favorite' => 'boolean',
            'is_blocked' => 'boolean',
//            'first_visit_at' => 'timestamp',
//            'last_visit_at' => 'timestamp',
            'total_appointments' => 'integer',
            'total_spent' => 'integer',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return $this->display_name ?? $this->user->name;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->custom_phone ?? $this->user->phone ?? null;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->custom_email ?? $this->user->email;
    }

    public function avatarImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'avatar_image_id');
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->avatarImage?->url ?? $this->user->avatar_url;
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->display_name)
        ->explode(' ')
        ->take(2)
        ->map(fn($word) => Str::substr($word, 0, 1))
        ->implode('') ?? $this->user->initials();
    }

    public function getTotalSpentEurosAttribute(): float
    {
        return $this->total_spent / 100;
    }

    public function getTotalSpentFormattedAttribute(): string
    {
        return number_format($this->total_spent / 100, 2, ',', ' ') . ' €';
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Clients favoris
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Clients non bloqués
     */
    public function scopeNotBlocked($query)
    {
        return $query->where('is_blocked', false);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('display_name', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('custom_phone', 'like', "%{$search}%")
                ->orWhere('custom_email', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Filtrer par tag
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Filtrer par plusieurs tags (OR)
     */
    public function scopeWithAnyTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Filtrer par plusieurs tags (AND)
     */
    public function scopeWithAllTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }
        return $query;
    }

    /**
     * Trier par dernière visite (plus récent d'abord)
     */
    public function scopeRecentVisits($query)
    {
        return $query->orderBy('last_visit_at', 'desc');
    }

    /**
     * Trier par montant dépensé (plus élevé d'abord)
     */
    public function scopeTopSpenders($query)
    {
        return $query->orderBy('total_spent', 'desc');
    }

    /**
     * Clients ayant visité au moins une fois
     */
    public function scopeHasVisited($query)
    {
        return $query->whereNotNull('first_visit_at');
    }

    /**
     * Clients n'ayant jamais visité
     */
    public function scopeNeverVisited($query)
    {
        return $query->whereNull('first_visit_at');
    }

    /**
     * Clients ayant dépensé plus de X centimes
     */
    public function scopeSpentMoreThan($query, int $amountInCents)
    {
        return $query->where('total_spent', '>', $amountInCents);
    }

    /**
     * Clients ayant dépensé moins de X centimes
     */
    public function scopeSpentLessThan($query, int $amountInCents)
    {
        return $query->where('total_spent', '<', $amountInCents);
    }

    // ========================================
    // Méthodes utilitaires
    // ========================================

    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatar($newAvatarImage): self
    {
        $this->update(['avatar_image_id' => $newAvatarImage]);
        return $this;
    }

    /**
     * Vérifier si le client a déjà visité
     */
    public function hasVisited(): bool
    {
        return !is_null($this->first_visit_at);
    }

    /**
     * Vérifier si le client a un tag spécifique
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Ajouter un tag
     */
    public function addTag(string $tag): self
    {
        $tags = $this->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }

        return $this;
    }

    /**
     * Retirer un tag
     */
    public function removeTag(string $tag): self
    {
        $tags = $this->tags ?? [];

        if (($key = array_search($tag, $tags)) !== false) {
            unset($tags[$key]);
            $this->tags = array_values($tags);
            $this->save();
        }

        return $this;
    }

    /**
     * Marquer comme favori
     */
    public function markAsFavorite(): self
    {
        $this->update(['is_favorite' => true]);
        return $this;
    }

    /**
     * Retirer des favoris
     */
    public function unmarkAsFavorite(): self
    {
        $this->update(['is_favorite' => false]);
        return $this;
    }

    /**
     * Bloquer le client
     */
    public function block(): self
    {
        $this->update(['is_blocked' => true]);
        return $this;
    }

    /**
     * Débloquer le client
     */
    public function unblock(): self
    {
        $this->update(['is_blocked' => false]);
        return $this;
    }

    /**
     * Enregistrer une visite
     */
    public function recordVisit(): self
    {
        $this->update([
            'first_visit_at' => $this->first_visit_at ?? now(),
            'last_visit_at' => now(),
            'total_appointments' => $this->total_appointments + 1,
        ]);

        return $this;
    }

    /**
     * Ajouter un montant dépensé (en centimes)
     */
    public function addSpent(int $amountInCents): self
    {
        $this->increment('total_spent', $amountInCents);

        return $this;
    }

    /**
     * Ajouter un montant dépensé (en euros)
     */
    public function addSpentEuros(float $amountInEuros): self
    {
        $amountInCents = (int) round($amountInEuros * 100);
        return $this->addSpent($amountInCents);
    }

    /**
     * Réinitialiser le montant dépensé
     */
    public function resetSpent(): self
    {
        $this->update(['total_spent' => 0]);
        return $this;
    }
}
