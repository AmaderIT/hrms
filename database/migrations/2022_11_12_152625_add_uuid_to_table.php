<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class AddUuidToTable extends Migration
{
    public const MODEL_CLASSES = [
        \App\Models\User::class,

        /*
        \App\Models\Bank::class,
        \App\Models\Branch::class,
        \App\Models\BankUser::class,
        \App\Models\Degree::class,
        \App\Models\Institute::class,
        \App\Models\DegreeUser::class,
        \App\Models\OfficeDivision::class,
        \App\Models\Department::class,
        \App\Models\Designation::class,
        \App\Models\Warning::class,
        \App\Models\Tax::class,
        \App\Models\Division::class,
        \App\Models\District::class,
        \App\Models\PayGrade::class,
        \App\Models\WorkSlot::class,
        \App\Models\Promotion::class,
        \App\Models\Supervisor::class,
        \App\Models\Address::class,
        \App\Models\Profile::class,
        \App\Models\JobHistory::class,
        \App\Models\ActionReason::class,
        \App\Models\EmployeeStatus::class,
        \App\Models\Holiday::class,
        \App\Models\PublicHoliday::class,
        \App\Models\LeaveType::class,
        \App\Models\Leave::class,
        \App\Models\WeeklyHoliday::class,
        \App\Models\Attendance::class,
        \App\Models\Earning::class,
        \App\Models\Deduction::class,
        \App\Models\Bonus::class,
        \App\Models\PayGradeEarning::class,
        \App\Models\PayGradeDeduction::class,
        \App\Models\TaxRule::class,
        // \App\Models\Salary::class,
        \App\Models\Loan::class,
        \App\Models\UserLoan::class,
        \App\Models\UserBonus::class,
        \App\Models\LeaveAllocation::class,
        \App\Models\LeaveAllocationDetails::class,
        */

        \App\Models\LeaveRequest::class,

        /*
        \App\Models\LeaveUnpaid::class,
        \App\Models\DepartmentSupervisor::class,
        \App\Models\Device::class,
        \App\Models\RequisitionItem::class,
        \App\Models\Requisition::class,
        \App\Models\RequisitionDetails::class,
        \App\Models\Setting::class,
//        \App\Models\SalaryDepartment::class,
        \App\Models\Overtime::class,
        \App\Models\HolidayAllowance::class,
        \App\Models\TaxCustomization::class,
        \App\Models\Meal::class,
        \App\Models\UserMeal::class,
        \App\Models\UserLeave::class,
        \App\Models\LateDeduction::class,
        \App\Models\UserLate::class,
        \App\Models\Roaster::class,
        \App\Models\Warehouse::class,
        \App\Models\Unit::class,
        \App\Models\InternalTransfer::class,
        \App\Models\InternalTransferItems::class,
        \App\Models\DivisionSupervisor::class,
        \App\Models\MeasurementDetails::class,
        \App\Models\InternalTransferDeptToWareReturn::class,
        \App\Models\InternalTransferSourceWarehouseReject::class,
        // \App\Models\ZKTeco\DailyAttendance::class, // Large volume of data. No need to add uuid here
        \App\Models\AssignRelaxDay::class,
//        \App\Models\RelaxDay::class,
//        \App\Models\DepartmentRoaster::class,
        */
    ];
    public const UUID_FIELD = 'uuid';
    public const UUID_FIELD_AFTER = 'id';
    public const UUID_UPSERT = true;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (static::MODEL_CLASSES as $classname) {
            /** @var Model $model */
            $model = new $classname;

            Schema::table($model->getTable(), function (Blueprint $table) {
                 $col = $table->uuid(static::UUID_FIELD)->after(static::UUID_FIELD_AFTER)->index();

                if (static::UUID_UPSERT) {
                    $col->nullable();
                }
            });

            if (static::UUID_UPSERT) {
                $query = $model->newQuery();

                if (method_exists($query, 'withTrashed')) {
                    $query->withTrashed();
                }

                $query->chunk(1000, function (\Illuminate\Database\Eloquent\Collection $chunk) {
                    $chunk->each(function (\Illuminate\Database\Eloquent\Model $model) {
                        // $model->uuid = \Illuminate\Support\Str::uuid();
                        $model->uuid = Uuid::uuid4()->toString();
                        $model->save();
                    });
                });

                Schema::table($model->getTable(), function (Blueprint $table) {
                    $table->uuid(static::UUID_FIELD)->after(static::UUID_FIELD_AFTER)->nullable(false)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::MODEL_CLASSES as $classname) {
            /** @var Model $model */
            $model = new $classname;

            Schema::table($model->getTable(), function (Blueprint $table) {
                $table->dropColumn(static::UUID_FIELD);
            });
        }
    }
}
