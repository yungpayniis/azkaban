<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KvmPartySlot extends Model
{
    protected $fillable = [
        'kvm_party_id',
        'position',
        'member_id',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(KvmParty::class, 'kvm_party_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(GuildMember::class, 'member_id');
    }
}
