<?php

namespace App\Models\ZKTeco;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $connection = "zkteco";

    /**
     * @var string
     */
    protected $table = "personnel_employee";

    /**
     * @return HasOne
     */
    public function timeIn()
    {
        return $this->hasOne(Attendance::class, "emp_code", "emp_code")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    /**
     * @return HasOne
     */
    public function timeOut()
    {
        return $this->hasOne(Attendance::class, "emp_code", "emp_code")->orderByDesc("id")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }
}
