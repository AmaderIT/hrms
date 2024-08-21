<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class PayGrade extends Model
{
    use HasFactory, LogsActivity;

    const BASED_ON_BASIC = "basic";
    const BASED_ON_GROSS = "gross";

    /**
     * @var array
     */
    protected $guarded = ["id"];

    /**
     * @var array
     */
    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsTo
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * @return HasMany
     */
    public function earnings()
    {
        return $this->hasMany(PayGradeEarning::class)->with("earning")->select("id", "pay_grade_id", "earning_id", "type", "value", "tax_exempted", "tax_exempted_percentage", "non_taxable");
    }

    /**
     * @return mixed
     * TODO: Remove whether not necessary
     */
    public function payGradeEarnings()
    {
        if($this->earnings) return $this->earnings;
    }

    /**
     * @return HasMany
     */
    public function deductions()
    {
        return $this->hasMany(PayGradeDeduction::class)->with("deduction")->select("id", "pay_grade_id", "deduction_id", "type", "value");
    }

    /**
     * @return mixed
     * TODO: Remove whether not necessary
     */
    public function payGradeDeductions()
    {
        if($this->deductions) return $this->deductions;
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this) . " has been {$eventName}";
    }
}
