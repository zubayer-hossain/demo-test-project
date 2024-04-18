<?php

namespace App\Models;

use App\Enums\DemoTestInquiryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoTestInquiry extends Model
{
    use HasFactory;

    protected $table = 'demo_test_inquiry';
    protected $fillable = [
        'payload',
        'status',
        'items_total_count',
        'items_processed_count',
        'items_failed_count'
    ];

    protected $casts = [
        'payload' => 'array',
        'status' => DemoTestInquiryStatus::class,
    ];
}
