<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;
    protected $table = 'service_categories';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'is_online',
        'position',
        'agenda_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
