<?php
// app/Models/Income.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model {
    protected $table = 'income';

    protected $fillable = [
        'amount',
        'income_date',
        'incomeType_id',
        'notes'
    ];

    // Définition des cast pour convertir automatiquement les types
    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'incomeType_id' => 'integer'
    ];

    // Relation avec la table type_revenus
    public function typeRevenu(): BelongsTo {
        return $this->belongsTo(IncomeType::class, 'incomeType_id');
    }
}
