<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinancialReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'period',
        'type',
        'total_received',
        'total_spent',
        'medical_spending',
        'operations_spending',
        'document_url',
        'breakdown',
    ];

    protected $casts = [
        'breakdown' => 'array',
        'total_received' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'medical_spending' => 'decimal:2',
        'operations_spending' => 'decimal:2',
    ];
}
