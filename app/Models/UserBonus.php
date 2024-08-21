<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBonus extends Model
{
    use HasFactory;

    const STATUS_PAID = "1";
    const STATUS_UNPAID = "0";

    /**
     * @var string
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select("id", "name", "fingerprint_no");
    }

    /**
     * @return BelongsTo
     */
    public function officeDivision(): BelongsTo
    {
        return $this->belongsTo(OfficeDivision::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->select("id", "name");
    }

    /**
     * @return BelongsTo
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class)->select("id", "title");
    }

    /**
     * @return BelongsTo
     */
    public function bonus()
    {
        return $this->belongsTo(Bonus::class)->select("id", "festival_name", "type", "percentage_of_bonus");
    }
}
