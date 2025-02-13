<?php
// app/Models/income_types.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class income_types extends Model {
    protected $table = 'income_types';

    protected $fillable = [
        'name',
        'description',
        'taxable',
        'must_declare'
    ];

    // Relation avec la table revenus
    public function incomes(): HasMany {
        return $this->hasMany(Income::class, 'income_type_id');
    }
}
