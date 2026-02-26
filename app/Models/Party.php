<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    protected $fillable = [
        'name',
        'position',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(PartySlot::class);
    }
}
