<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuildMember extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_LEFT = 'left';

    protected $fillable = [
        'name',
        'job_class_id',
        'tier',
        'role',
        'nationality',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function slot(): HasOne
    {
        return $this->hasOne(PartySlot::class, 'member_id');
    }

    public function jobClass(): BelongsTo
    {
        return $this->belongsTo(JobClass::class, 'job_class_id');
    }

    public function gvgWeeklyStats(): HasMany
    {
        return $this->hasMany(GvgWeeklyStat::class);
    }

    public function stat(): HasOne
    {
        return $this->hasOne(GuildMemberStat::class);
    }

    public function statHistories(): HasMany
    {
        return $this->hasMany(GuildMemberStatHistory::class);
    }

    public function nameHistories(): HasMany
    {
        return $this->hasMany(GuildMemberNameHistory::class);
    }

    public function redCards(): HasMany
    {
        return $this->hasMany(GuildMemberRedCard::class);
    }
}
