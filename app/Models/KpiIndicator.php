<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiIndicator extends Model
{
    protected $fillable = [
        'form_template_id',
        'name',
        'code',
        'type',
        'period',
        'target',
        'weight',
        'reward_amount',
        'reward_unit',
        'allow_overachievement',
        'is_active',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'decimal:2',
            'weight' => 'decimal:2',
            'reward_amount' => 'decimal:2',
            'allow_overachievement' => 'boolean',
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(KpiAssignment::class);
    }
}
