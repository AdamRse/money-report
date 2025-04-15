<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeType extends Model {
    protected $table = 'income_types';

    protected $fillable = [
        'name',
        'description',
        'taxable',
        'must_declare'
    ];

    // Relation avec la table revenus
    public function income(): HasMany {
        return $this->hasMany(Income::class);
    }
}
