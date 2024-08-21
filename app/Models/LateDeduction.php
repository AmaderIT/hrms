<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LateDeduction extends Model
{
    use HasFactory;

    const TYPE_LEAVE = "leave";
    const TYPE_SALARY = "salary";


    protected $table = "late_deductions";

    protected $guarded = ["id"];

    protected $hidden = ["created_at", "updated_at"];

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
