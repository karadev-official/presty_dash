<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table = 'images';

    protected $fillable = [
        'user_id',
        'path',
        'name',
        'mime_type',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'image_service');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
