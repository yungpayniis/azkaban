<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuildMemberStat extends Model
{
    protected $fillable = [
        'guild_member_id',
        'str',
        'vit',
        'luk',
        'agi',
        'dex',
        'int',
        'hp',
        'sp',
        'patk',
        'matk',
        'pdef',
        'mdef',
    ];

    public function guildMember(): BelongsTo
    {
        return $this->belongsTo(GuildMember::class);
    }
}
