<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayGradeEarning extends Model
{
    use HasFactory;

    const TYPE_PERCENTAGE = "Percentage";
    const TYPE_FIXED = "Fixed";
    const TYPE_REMAINING = "Remaining";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var string
     */
    protected $table = "paygrade_earnings";

    /**
     * @return BelongsTo
     */
    public function earning()
    {
        return $this->belongsTo(Earning::class)->select("id", "name");
    }
}
