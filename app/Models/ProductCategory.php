<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'position',
        'is_active',
        'is_online',
    ];


    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
    ];

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

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products() : HasMany
    {
        return $this->hasMany(Product::class);
    }
}
