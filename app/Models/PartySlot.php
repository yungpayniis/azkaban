<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartySlot extends Model
{
    protected $fillable = [
        'party_id',
        'position',
        'member_id',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(GuildMember::class, 'member_id');
    }
}
