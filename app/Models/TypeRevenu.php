<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeRevenu extends Model {
    protected $table = 'type_revenus';

    protected $fillable = [
        'nom',
        'description',
        'imposable',
        'declarable'
    ];

    // Relation avec la table revenus
    public function revenus(): HasMany {
        return $this->hasMany(Revenu::class, 'type_revenu_id');
    }
}
