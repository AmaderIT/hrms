<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LateAllow extends Model
{
    use HasFactory;

    protected $guarded = [];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where("status", self::STATUS_ACTIVE)->orderBy("id", "desc");
    }

    /**
     * @return BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function allowed_by()
    {
        return $this->belongsTo(User::class, 'allowed_by');
    }

    public function removed_by()
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }
    public function history()
    {
        return $this->hasMany(LateAllow::class, 'user_id');
    }
}
