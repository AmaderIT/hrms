<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayGradeDeduction extends Model
{
    use HasFactory;

    const TYPE_PERCENTAGE = "Percentage";
    const TYPE_FIXED = "Fixed";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var string
     */
    protected $table = "paygrade_deductions";

    /**
     * @return BelongsTo
     */
    public function deduction()
    {
        return $this->belongsTo(Deduction::class)->select("id", "name");
    }
}
