<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuildMemberRedCard extends Model
{
    protected $fillable = [
        'guild_member_id',
        'reason',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function guildMember(): BelongsTo
    {
        return $this->belongsTo(GuildMember::class);
    }
}
