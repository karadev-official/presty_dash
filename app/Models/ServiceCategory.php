<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    use HasFactory;
    protected $table = 'service_categories';

    protected $fillable = [
        'name',
        'slug',
        'user_id',
        'is_active',
        'is_online',
        'position',
        'agenda_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
    ];

    // setter de la position automatique
    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (is_null($category->position)) {
                $maxPosition = self::where('user_id', $category->user_id)->max('position');
                $category->position = is_null($maxPosition) ? 0 : $maxPosition + 1;
            }
        });
    }

    public function setSlugAttribute($value): void
    {
        $originalSlug = Str::slug($value);
        $slug = $originalSlug;
        $count = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        $this->attributes['slug'] = $slug;
    }



    public function services() : HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
