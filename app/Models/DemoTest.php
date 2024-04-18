<?php

namespace App\Models;

use App\Enums\DemoTestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoTest extends Model
{
    use HasFactory;

    protected $table = 'demo_test';
    protected $fillable = [
        'ref',
        'name',
        'description',
        'status',
        'is_active'
    ];

    protected $casts = [
        'status' => DemoTestStatus::class,
    ];
}
