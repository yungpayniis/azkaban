<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobClass extends Model
{
    protected $fillable = [
        'name',
        'tier',
        'parent_id',
        'color',
        'force_dark_text',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(JobClass::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(JobClass::class, 'parent_id');
    }
}
