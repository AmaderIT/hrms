<?php

namespace App\Models;

use App\Library\UsesUuid;
use App\Models\ZKTeco\Attendance;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, HasRoles, SoftDeletes, UsesUuid;

    const PHOTO_EXTENSION = "jpg";
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    const ROLE_ADMIN = "Admin";
    const ROLE_SUPERVISOR = "Supervisor";
    const ROLE_DIVISION_SUPERVISOR = "Division Supervisor";
    const ROLE_GENERAL_USER = "General User";
    const ROLE_HR_ADMIN = "HR-Admin";
    const ROLE_HR_ADMIN_SUPERVISOR = "HR-Admin + Supervisor";
    const ROLE_GENERAL_SECURITY = "General user + security";
    const SUPERVISOR_DEPARTMENT = 1;
    const SUPERVISOR_OFFICE_DIVISION = 2;
    const IS_SUPERVISOR = 'supervisor';
    const IS_DIVISIONAL_SUPERVISOR = 'divisional_supervisor';
    const BANK_MODE = 1;
    const CASH_MODE = 2;
    const CHEQUE_MODE = 3;

    /**
     * @var string[]
     */
    protected $guarded = ["id"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["created_at", "updated_at", 'password', 'remember_token'];

    /**
     * @var array
     */
    protected static $ignoreChangedAttributes = ['remember_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime',];

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereStatus(User::STATUS_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array(User::ROLE_ADMIN, auth()->user()->roles->pluck('name')->toArray());
    }

    public static function getAdminId()
    {
        return 1;
    }

    public function isAdminUser(){
        return (auth()->user()->email == 'admin@byslglobal.com') ? true : false;
    }

    /**
     * @return bool
     */
    public function isSecurity(): bool
    {
        return in_array(User::ROLE_GENERAL_SECURITY, auth()->user()->roles->pluck('name')->toArray());
    }

    /**
     * @return bool
     */
    public function isSupervisor(): bool
    {
        return $this->is_supervisor === 1;
    }

    /**
     * @return bool
     */
    public function isOrdinaryEmployee(): bool
    {
        return $this->isAdmin() === false and $this->isSupervisor() === false;
    }

    /**
     * @return HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class)
            ->select("id", "user_id", "personal_phone", "personal_email", "gender", "religion", "dob", "marital_status", "emergency_contact", "relation", "blood_group", "nid", "tin");
    }

    /**
     * TODO: Check it again
     *
     * @return BelongsTo
     */
    public function workSlot()
    {
        return $this->belongsTo(WorkSlot::class);
    }

    /**
     * @return HasMany
     */
    public function warnings()
    {
        return $this->hasMany(Warning::class)->with("warnBy");
    }

    /**
     * @return BelongsToMany
     */
    public function workSlots()
    {
        return $this->belongsToMany(WorkSlot::class);
    }

    /**
     * TODO: Not necessary may be
     *
     * @return HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class)->with("division", "district")
            ->select("id", "user_id", "type", "address", "zip", "division_id", "district_id");
    }

    /**
     * @return HasOne
     */
    public function presentAddress()
    {
        return $this->hasOne(Address::class)
            ->where("type", Address::TYPE_PRESENT)
            ->orderByDesc("id")
            ->select("id", "user_id", "type", "address", "zip", "division_id", "district_id");
    }

    /**
     * @return HasOne
     */
    public function permanentAddress()
    {
        return $this->hasOne(Address::class)
            ->where("type", Address::TYPE_PERMANENT)
            ->orderByDesc("id")
            ->select("id", "user_id", "type", "address", "zip", "division_id", "district_id");
    }

    /**
     * @return BelongsToMany
     */
    public function banks()
    {
        return $this->belongsToMany(Bank::class, "bank_user")
            ->withPivot(
                "id", "branch_id", "account_name", "account_type", "account_name", "account_no",
                "nominee_name", "relation_with_nominee", "nominee_contact", "tax_opening_balance"
            )
            ->withTimestamps();
    }

    /**
     * @return HasOne
     */
    public function currentBank()
    {
        return $this->hasOne(BankUser::class)->orderByDesc("id");
    }

    /**
     * @return BelongsToMany
     */
    public function degrees()
    {
        return $this->belongsToMany(Degree::class)
            ->withPivot(["id", "institute_id", "passing_year", "result"])
            ->orderByDesc("pivot_passing_year")
            ->withTimestamps();
    }

    /**
     * @return HasOne
     */
    public function currentPromotion()
    {
        $latestJoinDate =  Promotion::where('user_id', $this->id)
            ->whereIn('type', array_keys(Promotion::joiningTypes()))
            ->orderByDesc("promoted_date")
            ->orderByDesc("id")
            ->select('id', 'promoted_date')
            ->first();
        $relations = $this->hasOne(Promotion::class)
            ->orderByDesc("id")
            //->where('promoted_date','<=',date('Y-m-d'))
            ->select("id", "user_id", "office_division_id", "department_id", "designation_id", "promoted_date", "type", "salary", "workslot_id", "pay_grade_id", "employment_type");
        if (!empty($latestJoinDate->promoted_date) && date('Y-m-d',strtotime($latestJoinDate->promoted_date)) <= date('Y-m-d')) {
            $relations->where('promoted_date', '<=', date('Y-m-d'));
        }
        return $relations;
    }

    /**
     * @return HasMany
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class)
            ->with("department", "designation", "payGrade")
            ->select("id", "user_id", "office_division_id", "department_id", "designation_id", "promoted_date", "type", "salary", "workslot_id", "pay_grade_id", "employment_type");
    }

    /**
     * TODO: Remove it asap
     *
     * @return Model|HasMany|object|null
     */
    public function latestPromotion()
    {
        if ($this->promotions) return $this->promotions()->orderBy("id", "desc")->first();
    }

    /**
     * @return BelongsTo
     */
    public function supervisedBy()
    {
        return $this->belongsTo(User::class, "supervised_by");
    }

    /**
     * @return HasMany
     */
    public function jobHistories()
    {
        return $this->hasMany(JobHistory::class)
            ->select("id", "user_id", "organization_name", "designation", "start_date", "end_date")
            ->with("designationEmployee")
            ->orderByDesc("start_date");
    }

    /**
     * @return HasMany
     */
    public function employeeStatus()
    {
        return $this->hasMany(EmployeeStatus::class);
    }

    /**
     * @return HasOne
     */
    public function employeeStatusJoining()
    {
        return $this->hasOne(EmployeeStatus::class)->where("action_reason_id", 2)->orderByDesc("id");
    }

    /**
     * @return HasOne
     */
    public function currentStatus()
    {
        return $this->hasOne(EmployeeStatus::class)->orderBy("id", "desc");
    }

    /*
     * TODO: Remove it asap
    public function currentStatus()
    {
        if(isset($this->employeeStatus)) return $this->employeeStatus()->orderBy("id", "desc")->first();
    }
    */

    /**
     * @return HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * TODO: Replace it with a hasOne relationship, nesting is not necessary here
     *
     * @return Model|HasMany|object|null
     */
    public function todayTimeIn()
    {
        if (isset($this->attendances)) {
            return $this->attendances()->whereDay('created_at', now()->day)->select("log_time")->first();
        }
    }

    /**
     * TODO: Replace it with a hasOne relationship, nesting is not necessary here
     *
     * @return Model|HasMany|object|null
     */
    public function todayTimeOut()
    {
        if (isset($this->attendances)) {
            return $this->attendances()
                ->whereDay('created_at', now()->day)
                ->orderBy('id', 'desc')
                ->select("log_time")
                ->first();
        }
    }

    /**
     * @return HasOne
     */
    public function timeInToday()
    {
        $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
        return $this->hasOne(Attendance::class, "emp_code", "fingerprint_no")
            ->where("punch_time", ">=", Carbon::parse("today {$attendanceCountStartTime}"))
            ->orderBy("punch_time")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    /**
     * @return HasOne
     */
    public function timeOutToday()
    {
        $attendanceCountStartTime = Setting::where("name", "attendance_count_start_time")->select("id", "value")->first()->value;
        return $this->hasOne(Attendance::class, "emp_code", "fingerprint_no")
            ->where("punch_time", "<", Carbon::parse("tomorrow {$attendanceCountStartTime}"))
            ->orderByDesc("punch_time")
            ->select("id", "emp_code", "punch_time", "terminal_alias", "area_alias");
    }

    /**
     * @return mixed
     */
    public function employeeEarnings()
    {
        return $this->currentPromotion->paygrade->payGradeEarnings();
    }

    /**
     * @return mixed
     */
    public function employeeDeductions()
    {
        return $this->currentPromotion->paygrade->payGradeDeductions();
    }

    /**
     * @return HasMany
     */
    public function loans()
    {
        return $this->hasMany(Loan::class)->select(
            "id", "office_division_id", "department_id", "user_id", "loan_amount", "loan_tenure",
            "installment_amount", "loan_paid_date", "remarks", "loan_paid_by", "status"
        );
    }

    /**
     * @return HasOne
     */
    public function activeLoan()
    {
        return $this->hasOne(Loan::class)
            ->where("status", Loan::STATUS_ACTIVE)
            ->select(
                "id", "office_division_id", "department_id", "user_id", "loan_amount", "loan_tenure",
                "installment_amount", "loan_paid_date", "remarks", "loan_paid_by", "status", "type"
            );
    }

    /**
     * @return HasMany
     */
    public function userLoans()
    {
        return $this->hasMany(UserLoan::class)->select("id", "user_id", "loan_id", "amount_paid", "month");
    }

    /**
     * @return HasMany
     */
    public function activeLoans()
    {
        return $this->hasMany(Loan::class)
            ->where("status", Loan::STATUS_ACTIVE)
            ->select(
                "id", "office_division_id", "department_id", "user_id", "loan_amount", "loan_tenure",
                "installment_amount", "loan_paid_date", "remarks", "loan_paid_by", "status", "type"
            );
    }

    public function lastPaidLoan()
    {
        return $this->hasOne(Loan::class)
            ->where("status", Loan::STATUS_PAID)
            ->select(
                "id", "office_division_id", "department_id", "user_id", "loan_amount", "loan_tenure",
                "installment_amount", "loan_paid_date", "remarks", "loan_paid_by", "status", "type"
            )
            ->orderBy('id', 'desc');
    }

    /**
     * public function getPhotoAttribute($value): string
     * {
     * $defaultHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
     * $defaultPhoto = "/assets/media/svg/avatars/001-boy.svg";
     *
     * if(!is_null($value)) {
     * $host   = $defaultHost;
     * $photo  = "/storage/${value}";
     * $link   = $host . $photo;
     *
     * if(!Storage::exists('public/'.$value)) {
     * $fingerprintNo = $this->fingerprint_no;
     * $link = env("ZKTECO_SERVER_PORT") . "/files/photo/${fingerprintNo}.jpg";
     *
     * $cURL = curl_init($link);
     *
     * curl_setopt($cURL, CURLOPT_NOBODY, true);
     * curl_exec($cURL);
     * $statusCode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
     * curl_close($cURL);
     * $exists = $statusCode === 200 ? true : false;
     *
     * if($exists === false) $link = $defaultHost . $defaultPhoto;
     * }
     * } elseif(is_null($value)) {
     * # Check if photo exists on ZKTeco server
     * $fingerprintNo = $this->fingerprint_no;
     * $linkToAttendanceServer = env("ZKTECO_SERVER_PORT") . "/files/photo/${fingerprintNo}.jpg";
     * $cURL = curl_init($linkToAttendanceServer);
     * curl_setopt($cURL, CURLOPT_NOBODY, true);
     * curl_exec($cURL);
     * $statusCode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
     * $exists = $statusCode === 200 ? true : false;
     *
     * if($exists === true) $link = $linkToAttendanceServer;
     * else $link = $defaultHost . $defaultPhoto;
     *
     *
     * $fingerprintNo = $this->fingerprint_no;
     * $link = env("ZKTECO_SERVER_PORT") . "/files/photo/${fingerprintNo}.jpg";
     *
     * $cURL = curl_init($link);
     *
     * curl_setopt($cURL, CURLOPT_NOBODY, true);
     * curl_exec($cURL);
     * $statusCode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
     * curl_close($cURL);
     * $exists = $statusCode === 200 ? true : false;
     *
     * if($exists === false) $link = $defaultHost . $defaultPhoto;
     * } else {
     * $link = $defaultHost . $defaultPhoto;
     * }
     *
     * return $link;
     * }
     */

    /**
     * @param $value
     */
    /*public function setPhotoAttribute($value)
    {
        $defaultHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $defaultPhoto = "/assets/media/svg/avatars/001-boy.svg";

        if(str_contains($defaultPhoto, $value) OR str_starts_with($value, env("ZKTECO_SERVER_PORT"))) {
            $this->attributes["photo"] = null;
        }
        elseif(str_starts_with($value, $defaultHost)) {
            $photoPath = explode('/', $value);
            $photo = end($photoPath);

            $this->attributes["photo"] = $photo;
        }
        else {
            $this->attributes["photo"] = $value;
        }
    }*/

    /**
     * @return HasMany
     */
    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * @return HasMany
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class)->where("status", LeaveRequest::STATUS_APPROVED);
    }

    /**
     * @return HasOne
     */
    public function lateAllow()
    {
        return $this->hasOne(LateAllow::class)->where("is_active", LateAllow::STATUS_ACTIVE);
    }

    /**
     * @return HasOne
     */
    public function meal()
    {
        return $this->hasOne(Meal::class);
    }

    public function meals()
    {
        return $this->hasMany(UserMeal::class);
    }

    public function dailyMeal()
    {
        return $this->hasOne(UserMeal::class)->whereDate('date', today());
    }

    public function tomorrowMeal()
    {
        return $this->hasOne(UserMeal::class)->whereDate('date', Carbon::tomorrow());
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Employee has been {$eventName}";
    }


    public function getEmploymentStatus()
    {
        $latestJoinDate =  Promotion::where('user_id', $this->id)
            ->whereIn('type', array_keys(Promotion::joiningTypes()))
            ->orderByDesc("promoted_date")
            ->orderByDesc("id")
            ->select('id', 'promoted_date')
            ->first();
        $getEmploymentStatus = Promotion::where('user_id', $this->id)
            //->whereDate('promoted_date', '<=', date('Y-m-d'))
            ->whereIn('employment_type', array_keys(Promotion::employmentType()))
            ->orderByDesc("promoted_date")
            ->select('id', 'employment_type')
            ->first();
        if (!empty($latestJoinDate->promoted_date) && date('Y-m-d',strtotime($latestJoinDate->promoted_date)) <= date('Y-m-d')) {
            $getEmploymentStatus->whereDate('promoted_date', '<=', date('Y-m-d'));
        }
        return $getEmploymentStatus;
    }

    public function joiningDate()
    {
        $joining_date = Promotion::where('user_id', $this->id)->orderBy('promoted_date', 'ASC')->first();
        return isset($joining_date) ? date("d M Y", strtotime($joining_date->promoted_date)) : 'N/A';
    }

    public function provisionEndDate()
    {
        return date("d M Y", strtotime($this->provision_end_date));
    }

    public function servicePeriod()
    {
        $service_period = 0;
        $today = date("Y-m-d");
        $joining_date = Promotion::where('user_id', $this->id)->orderBy('promoted_date', 'ASC')->first();
        if ($joining_date) {
            $to = Carbon::createFromFormat('Y-m-d', $today);
            $from = Carbon::createFromFormat('Y-m-d', date("Y-m-d", strtotime($joining_date->promoted_date)));
            $service_period = $to->diffInDays($from);
            if ($service_period > 0) {
                $service_period = $service_period / 365;
            }
        }
        return number_format($service_period, 1);
    }

    public function provisionRemainingDay()
    {
        $today = date("Y-m-d");
        $to = Carbon::createFromFormat('Y-m-d', $today);
        $from = Carbon::createFromFormat('Y-m-d', date("Y-m-d", strtotime($this->provision_end_date)));
        $period = $to->diffInDays($from);
        return $period;
    }

    public function lastPromotion()
    {
        return $this->hasOne(Promotion::class)
            ->orderByDesc("id")
            ->orderByDesc("promoted_date")
            ->select("id", "user_id", "office_division_id", "department_id", "designation_id", "promoted_date", "type", "salary", "workslot_id", "pay_grade_id", "employment_type");
    }

    public static function supervisorTypes (){
        return [

            self::SUPERVISOR_DEPARTMENT => 'Is Supervisor?',
            self::SUPERVISOR_OFFICE_DIVISION => 'Is Divisional Supervisor?'

        ];
    }
    public static function paymentModes(){
        return[
            self::BANK_MODE => 'Bank',
            self::CASH_MODE => 'Cash',
            self::CHEQUE_MODE => 'Cheque'
        ];
    }
    public function getLatestJoiningRelatedDateFromPromotion()
    {
        $joiningRowID = "";
        $joiningDate = "";
        $minimumRowAfterJoiningDate = "";
        $terminatedDate = "";

        $getLatestJoinDate =  Promotion::where('user_id', $this->id)
            //->whereDate('promoted_date', '<=', date('Y-m-d'))
            ->whereIn('type', array_keys(Promotion::joiningTypes()))
            ->orderByDesc("promoted_date")
            ->orderByDesc("id")
            ->select('id', 'promoted_date')
            ->first();
        if(!empty($getLatestJoinDate->promoted_date)){
            $joiningDate = date('Y-m-d',strtotime($getLatestJoinDate->promoted_date));
            $joiningRowID = $getLatestJoinDate->id;
            $getLastMinimumRowAfterJoiningDate =  Promotion::where('user_id', $this->id)
                //->whereDate('promoted_date', '<=', date('Y-m-d'))
                ->whereDate('promoted_date', '>',$joiningDate)
                ->orderBy("promoted_date","ASC")
                ->orderBy("id","ASC")
                ->select('id', 'promoted_date')
                ->first();
            if(!empty($getLastMinimumRowAfterJoiningDate->promoted_date)){
                $minimumRowAfterJoiningDate = date('Y-m-d',strtotime($getLastMinimumRowAfterJoiningDate->promoted_date));
            }
        }
        $getLatestTerminatedRow =  Promotion::where('user_id', $this->id)
            //->whereDate('promoted_date', '<=', date('Y-m-d'))
            ->where('type', Promotion::TYPE_TERMINATED)
            ->orderByDesc("promoted_date")
            ->orderByDesc("id")
            ->select('id', 'promoted_date')
            ->first();
        if(!empty($getLatestTerminatedRow->promoted_date)){
            $terminatedDate = date('Y-m-d',strtotime($getLatestTerminatedRow->promoted_date));
        }
        return [
            'joiningDateRowID' => $joiningRowID,
            'joiningDate' => $joiningDate,
            'minimumRowAfterJoiningDate' => $minimumRowAfterJoiningDate,
            'terminatedDate' => $terminatedDate
        ];
    }
}
