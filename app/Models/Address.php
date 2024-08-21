<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    const TYPE_PRESENT = "Present";
    const TYPE_PERMANENT = "Permanent";

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class)->select('id','name');
    }

    /**
     * @return BelongsTo
     */
    public function district()
    {
        return $this->BelongsTo(District::class)->select('id','name');
    }
}
