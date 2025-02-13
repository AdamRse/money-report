<?php
// app/Models/Income.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model {
    protected $table = 'incomes';

    protected $fillable = [
        'amount',
        'income_date',
        'income_type_id',
        'notes'
    ];

    // Définition des cast pour convertir automatiquement les types
    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'income_type_id' => 'integer'
    ];

    // Relation avec la table income_types
    public function income_types(): BelongsTo {
        return $this->belongsTo(income_types::class, 'income_type_id');
    }
}
