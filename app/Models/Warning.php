<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warning extends Model
{
    use HasFactory;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'warning_date'
    ];

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name');
    }

    /**
     * @return BelongsTo
     */
    public function warnBy()
    {
        return $this->belongsTo(User::class, "warned_by")->select('id', 'name');
    }

    /**
     * @return BelongsTo
     */
    public function updateBy()
    {
        return $this->belongsTo(User::class, "updated_by")->select('id', 'name');
    }
}
