<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuildMemberNameHistory extends Model
{
    protected $fillable = [
        'guild_member_id',
        'name',
        'recorded_at',
    ];

    public $timestamps = true;

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function guildMember(): BelongsTo
    {
        return $this->belongsTo(GuildMember::class);
    }
}
