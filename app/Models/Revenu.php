<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenu extends Model
{
    protected $table = 'revenus';

    protected $fillable = [
        'montant',
        'date_revenu',
        'type_revenu_id',
        'notes'
    ];

    // Définition des cast pour convertir automatiquement les types
    protected $casts = [
        'montant' => 'decimal:2',
        'date_revenu' => 'date',
        'type_revenu_id' => 'integer'
    ];

    // Relation avec la table type_revenus
    public function typeRevenu(): BelongsTo
    {
        return $this->belongsTo(TypeRevenu::class, 'type_revenu_id');
    }
}
