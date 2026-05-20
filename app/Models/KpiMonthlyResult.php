<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiMonthlyResult extends Model
{
    protected $fillable = [
        'user_id',
        'outlet_id',
        'period_month',
        'score',
        'reward_total',
        'breakdown',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'reward_total' => 'decimal:2',
            'breakdown' => 'array',
            'calculated_at' => 'datetime',
        ];
    }
}
