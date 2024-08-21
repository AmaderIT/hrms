<?php

namespace App\Models\ZKTeco;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $connection = "zkteco";

    /**
     * @var string
     */
    protected $table = "iclock_transaction";

    /**
     * @return BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, "emp_code", "emp_code")
            ->select("id", "emp_code", "first_name", "last_name");
    }

    /**
     * @return HasOne
     */
    public function todayTimeIn()
    {
        $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
        return $this->hasOne(Attendance::class, "emp_code", "emp_code")
            ->where("punch_time", ">=", Carbon::parse("today {$attendanceCountStartTime}"))
            ->orderBy("punch_time")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    /**
     * @return HasOne
     */
    public function todayTimeOut()
    {
        $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
        return $this->hasOne(Attendance::class, "emp_code", "emp_code")
            ->where("punch_time", ">=", Carbon::parse("today 12am"))
            ->where("punch_time", "<", Carbon::parse("tomorrow {$attendanceCountStartTime}"))
            ->orderByDesc("punch_time")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    /**
     * @return HasMany
     */
    public function timeInThisMonth()
    {
        return $this->hasMany(Attendance::class, "emp_code", "emp_code")
            ->whereMonth("punch_time", date("m"))
            ->whereYear("punch_time", date("Y"))
            ->orderByDesc("id")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias")
            ->groupBy(DB::raw('DATE(punch_time)'));
    }

    /**
     * @return HasMany
     */
    public function timeOutThisMonth()
    {
        $attendanceDatabase = env("ZKTECO_DB_DATABASE");
        $emp_code = auth()->user()->fingerprint_no;

        $ids = Attendance::hydrate(
            DB::connection("zkteco")->select(
                "SELECT MAX(id) as id from {$attendanceDatabase}.iclock_transaction WHERE emp_code = {$emp_code} GROUP BY date(punch_time)"
            )
        )->pluck("id");

        return $this->hasMany(Attendance::class, "emp_code", "emp_code")
            ->whereMonth("punch_time", date("m"))
            ->whereYear("punch_time", date("Y"))
            ->orderByDesc("id")
            ->whereIn("id", $ids)
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    /**
     * @return HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, "emp_code", "emp_code")->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    public function lates()
    {
        return $this->hasMany(Attendance::class, "emp_code", "emp_code")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }
}
