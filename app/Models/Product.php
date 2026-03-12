<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_category_id',
        'name',
        'slug',
        'description',
        'position',
        'price',
        'quantity',
        'is_active',
        'is_online',
    ];

    public function category() : BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images() : BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'product_images');
    }

    protected function casts() : array
    {
        return [
            'is_active' => 'boolean',
            'is_online' => 'boolean',
        ];
    }

    /**
     * Rendez-vous utilisant ce produit
     */
    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_products')
            ->using(AppointmentProduct::class)
            ->withPivot(['price', 'quantity', 'total', 'notes'])
            ->withTimestamps();
    }
}
