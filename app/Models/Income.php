<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model {
    protected $table = 'incomes';

    protected $fillable = [
        'amount',
        'income_date',
        'user_id',
        'income_type_id',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'user_id' => 'integer',
        'income_date' => 'date',
        'income_type_id' => 'integer'
    ];

    // Relations
    public function incomeType(): BelongsTo {
        return $this->belongsTo(IncomeType::class);
    }
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
