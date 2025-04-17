<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'label',
        'admin'
    ];

    protected $casts = [
        'id' => 'integer',
        'admin' => 'boolean'
    ];

    public $timestamps = false;
}
