<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class Product extends Model {
        use HasFactory, SoftDeletes;

        protected $fillable = [
        'name',
        'slug',
        'description',
        'user_id',
        'product_category_id',
        'position',
        'price',
        'quantity',
        'is_active',
        'is_online',
        ];

        public function user()
        {
        return $this->belongsTo(User::class);
        }

        public function productCategory()
        {
        return $this->belongsTo(ProductCategory::class);
        }

        protected function casts()
        {
        return [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        ];
        }
    }
