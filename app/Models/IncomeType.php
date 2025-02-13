<?php
// app/Models/IncomeType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeType extends Model {
    protected $table = 'incomeType';

    protected $fillable = [
        'name',
        'description',
        'taxable',
        'declarable'
    ];

    // Relation avec la table revenus
    public function revenus(): HasMany {
        return $this->hasMany(Income::class, 'incomeType_id');
    }
}
