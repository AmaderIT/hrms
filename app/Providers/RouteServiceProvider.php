<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            $this->map();
        });
    }

    /**
     * Map All User Defined Routes
     *
     * @return void
     */
    protected function map()
    {
        $this->mapDivisionRoutes();
        $this->mapDegreeRoutes();
        $this->mapInstituteRoutes();
        $this->mapDepartmentRoutes();
        $this->mapBankRoutes();
        $this->mapBranchRoutes();
        $this->mapDesignationRoutes();
        $this->mapDistrictRoutes();
        $this->mapWorkSlotRoutes();
        $this->mapWarningRoutes();
        // $this->mapUserRoutes();
        $this->mapPromotionRoutes();
        $this->mapActionTypeRoutes();
        $this->mapActionReasonRoutes();
        $this->mapEmployeeRoutes();
        $this->mapSupervisorRoutes();
        $this->mapTerminationRoutes();
        $this->mapHolidayRoutes();
        $this->mappublicHolidayRoutes();
        $this->mapWeeklyHolidayRoutes();
        $this->mapLeaveTypeRoutes();
        $this->mapApplyForLeaveRoutes();
        $this->mapRequestedApplicationRoutes();
        $this->mapRoleRoutes();
        $this->mapPermissionsRoutes();
        $this->mapEarningRoutes();
        $this->mapDeductionRoutes();
        $this->mapPaygradeRoutes();
        $this->mapBonusRoutes();
        $this->mapTaxRoutes();
        $this->mapTaxRuleRoutes();
        $this->mapOfficeDivisionRoutes();
        $this->mapSalaryRoutes();
        $this->mapLoanRoutes();
        $this->mapUserLoanRoutes();
        $this->mapUserBonusRoutes();
        $this->mapLeaveAllocationRoutes();
        $this->mapLeaveStatusRoutes();
        $this->mapLeaveUnpaidRoutes();
        $this->mapReportRoutes();
        $this->mapDashboardAdminRoutes();
        $this->mapDashboardSupervisorRoutes();
        $this->mapEmployeeLeaveApplicationRoutes();
        $this->mapZKTecoDeviceRoutes();
        $this->mapRequisitionRoutes();
        $this->mapSettingRoutes();
        $this->mapEmployeeByPayGradeRoutes();
        $this->mapOverTimeRoutes();
        $this->mapAttendanceRoutes();
        $this->mapHolidayAllowanceRoutes();
        $this->mapTaxCustomizationRoutes();
        $this->mapMealRoutes();
        $this->mapApplyForRequisitionRoutes();
        $this->mapLateManagementRoutes();
        $this->mapCopyDataToAnotherYearRoutes();
        $this->mapUserLateRoutes();
        $this->mapRoasterRoutes();
        $this->mapRosterRoutes();
        $this->mapDailyAttendanceRoutes();
        $this->mapWarehouseRoutes();
        $this->mapUnitRoutes();
        $this->mapInternalTransferRoutes();
        $this->mapRequisitionItemRoutes();
        $this->mapDivisionSupervisorRoutes();
        // $this->mapOtherRequisitionItemRoutes();
        $this->mapTransferRoutes();
        $this->mapLateAllowRoutes();
        $this->mapFilterRoutes();
        $this->mapRelaxDayRoutes();
        $this->mapAssignRelaxDayRoutes();
        $this->mapLeaveEncashmentRoutes();
        $this->mapDashboardNotificationRoutes();
        $this->mapBloodBankRoutes();
        $this->mapRequestedLoanAdvanceRoutes();
        $this->mapPoliciesRoutes();
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
    }

    /**
     * PayGrade Routes
     *
     * @return void
     */
    protected function mapPaygradeRoutes(): void
    {
        Route::prefix('paygrade')
            ->name('paygrade.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/paygrade.php'));
    }

    /**
     * Division Routes
     *
     * @return void
     */
    protected function mapDivisionRoutes(): void
    {
        Route::prefix('division')
            ->name('division.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/division.php'));
    }

    /**
     * Earning Routes
     *
     * @return void
     */
    protected function mapEarningRoutes(): void
    {
        Route::prefix('earning')
            ->name('earning.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/earning.php'));
    }

    /**
     * Degree Routes
     *
     * @return void
     */
    protected function mapDegreeRoutes(): void
    {
        Route::prefix('degree')
            ->name('degree.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/degree.php'));
    }

    /**
     * Institute Routes
     *
     * @return void
     */
    protected function mapInstituteRoutes(): void
    {
        Route::prefix('institute')
            ->name('institute.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/institute.php'));
    }

    /**
     * Department Routes
     *
     * @return void
     */
    protected function mapDepartmentRoutes(): void
    {
        Route::prefix('department')
            ->name('department.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/department.php'));
    }

    /**
     * Bank Routes
     *
     * @return void
     */
    protected function mapBankRoutes(): void
    {
        Route::prefix('bank')
            ->name('bank.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/bank.php'));
    }

    /**
     * Branch Routes
     *
     * @return void
     */
    protected function mapBranchRoutes(): void
    {
        Route::prefix('branch')
            ->name('branch.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/branch.php'));
    }

    /**
     * Designation Routes
     *
     * @return void
     */
    protected function mapDesignationRoutes(): void
    {
        Route::prefix('designation')
            ->name('designation.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/designation.php'));
    }

    /**
     * District Routes
     *
     * @return void
     */
    protected function mapDistrictRoutes(): void
    {
        Route::prefix('district')
            ->name('district.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/district.php'));
    }

    /**
     * WorkSlot Routes
     *
     * @return void
     */
    protected function mapWorkSlotRoutes(): void
    {
        Route::prefix('work-slot')
            ->name('work-slot.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/work-slot.php'));
    }

    /**
     * Warning Routes
     *
     * @return void
     */
    protected function mapWarningRoutes(): void
    {
        Route::prefix('warning')
            ->name('warning.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/warning.php'));
    }

    /**
     * Promotion Routes
     *
     * @return void
     */
    protected function mapPromotionRoutes(): void
    {
        Route::prefix('promotion')
            ->name('promotion.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/promotion.php'));
    }

    /**
     * User Routes
     *
     * @return void
     */
    protected function mapUserRoutes(): void
    {
        Route::prefix('user')
            ->name('user.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/user.php'));
    }

    /**
     * Action Type Routes
     *
     * @return void
     */
    protected function mapActionTypeRoutes(): void
    {
        Route::prefix('action-type')
            ->name('action-type.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/action-type.php'));
    }

    /**
     * Action Reason Routes
     *
     * @return void
     */
    protected function mapActionReasonRoutes(): void
    {
        Route::prefix('action-reason')
            ->name('action-reason.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/action-reason.php'));
    }

    /**
     * Employee Routes
     *
     * @return void
     */
    protected function mapEmployeeRoutes(): void
    {
        Route::prefix('employee')
            ->name('employee.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/employee.php'));
    }

    /**
     * Supervisor Routes
     *
     * @return void
     */
    protected function mapSupervisorRoutes(): void
    {
        Route::prefix('supervisor')
            ->name('supervisor.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/supervisor.php'));
    }

    /**
     * Termination Routes
     *
     * @return void
     */
    protected function mapTerminationRoutes(): void
    {
        Route::prefix('termination')
            ->name('termination.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/termination.php'));
    }

    /**
     * Holiday Routes
     *
     * @return void
     */
    protected function mapHolidayRoutes(): void
    {
        Route::prefix('holiday')
            ->name('holiday.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/holiday.php'));
    }

    /**
     * Public Holiday Routes
     *
     * @return void
     */
    protected function mapPublicHolidayRoutes(): void
    {
        Route::prefix('public-holiday')
            ->name('public-holiday.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/public-holiday.php'));
    }

    /**
     * Weekly Holiday Routes
     *
     * @return void
     */
    protected function mapWeeklyHolidayRoutes(): void
    {
        Route::prefix('weekly-holiday')
            ->name('weekly-holiday.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/weekly-holiday.php'));
    }

    /**
     * Leave Type Routes
     *
     * @return void
     */
    protected function mapLeaveTypeRoutes(): void
    {
        Route::prefix('leave-type')
            ->name('leave-type.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/leave-type.php'));
    }

    /**
     * Leave Configure Routes
     *
     * @return void
     */
    protected function mapApplyForLeaveRoutes(): void
    {
        Route::prefix('apply-for-leave')
            ->name('apply-for-leave.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/apply-for-leave.php'));
    }

    /**
     * Leave Configure Routes
     *
     * @return void
     */
    protected function mapRequestedApplicationRoutes(): void
    {
        Route::prefix('requested-application')
            ->name('requested-application.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/requested-application.php'));
    }

    /**
     * Roles Routes
     *
     * @return void
     */
    protected function mapRoleRoutes(): void
    {
        Route::prefix('roles')
            ->name('roles.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/role.php'));
    }

    /**
     * Permissions Routes
     *
     * @return void
     */
    protected function mapPermissionsRoutes(): void
    {
        Route::prefix('permission')
            ->name('permission.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/permission.php'));
    }

    /**
     * Deduction Routes
     *
     * @return void
     */
    protected function mapDeductionRoutes(): void
    {
        Route::prefix('deduction')
            ->name('deduction.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/deduction.php'));
    }

    /**
     * Bonus Routes
     *
     * @return void
     */
    protected function mapBonusRoutes(): void
    {
        Route::prefix('bonus')
            ->name('bonus.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/bonus.php'));
    }

    /**
     * TaxRule Routes
     *
     * @return void
     */
    protected function mapTaxRoutes(): void
    {
        Route::prefix('tax')
            ->name('tax.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/tax.php'));
    }

    /**
     * TaxRule Routes
     *
     * @return void
     */
    protected function mapTaxRuleRoutes(): void
    {
        Route::prefix('tax-rule')
            ->name('tax-rule.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/tax-rule.php'));
    }

    /**
     * OfficeDivision Routes
     *
     * @return void
     */
    protected function mapOfficeDivisionRoutes(): void
    {
        Route::prefix('office-division')
            ->name('office-division.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/office-division.php'));
    }

    /**
     * Salary Routes
     *
     * @return void
     */
    protected function mapSalaryRoutes(): void
    {
        Route::prefix('salary')
            ->name('salary.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/salary.php'));
    }

    /**
     * Loan Routes
     *
     * @return void
     */
    protected function mapLoanRoutes(): void
    {
        Route::prefix('loan')
            ->name('loan.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/loan.php'));
    }

    /**
     * User Loan Routes
     *
     * @return void
     */
    protected function mapUserLoanRoutes(): void
    {
        Route::prefix('user-loan')
            ->name('user-loan.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/user-loan.php'));
    }

    /**
     * User Bonus Routes
     *
     * @return void
     */
    protected function mapUserBonusRoutes(): void
    {
        Route::prefix('user-bonus')
            ->name('user-bonus.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/user-bonus.php'));
    }

    /**
     * User Leave Allocation
     *
     * @return void
     */
    protected function mapLeaveAllocationRoutes(): void
    {
        Route::prefix('leave-allocation')
            ->name('leave-allocation.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/leave-allocation.php'));
    }

    /**
     * User Leave Status
     *
     * @return void
     */
    protected function mapLeaveStatusRoutes(): void
    {
        Route::prefix('leave-status')
            ->name('leave-status.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/leave-status.php'));
    }

    /**
     * Employee Unpaid Leave Status
     *
     * @return void
     */
    protected function mapLeaveUnpaidRoutes(): void
    {
        Route::prefix('leave-unpaid')
            ->name('leave-unpaid.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/leave-unpaid.php'));
    }

    /**
     * Report Routes
     *
     * @return void
     */
    protected function mapReportRoutes(): void
    {
        Route::prefix('report')
            ->name('report.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/report.php'));
    }

    /**
     * Report Routes
     *
     * @return void
     */
    protected function mapDashboardAdminRoutes(): void
    {
        Route::prefix('dashboard/admin')
            ->name('dashboard-admin.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/dashboard-admin.php'));
    }

    /**
     * Report Routes
     *
     * @return void
     */
    protected function mapDashboardSupervisorRoutes(): void
    {
        Route::prefix('dashboard/supervisor')
            ->name('dashboard-supervisor.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/dashboard-supervisor.php'));
    }

    /**
     * Employee Leave Application Routes
     *
     * @return void
     */
    protected function mapEmployeeLeaveApplicationRoutes(): void
    {
        Route::prefix('employee-leave-application')
            ->name('employee-leave-application.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/employee-leave-application.php'));
    }

    /**
     * ZKTeco Device Routes
     *
     * @return void
     */
    protected function mapZKTecoDeviceRoutes(): void
    {
        Route::prefix('zkteco-device')
            ->name('zkteco-device.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/zkteco-device.php'));
    }

    /**
     * ZKTeco Device Routes
     *
     * @return void
     */
    protected function mapRequisitionRoutes(): void
    {
        Route::prefix('requisition')
            ->name('requisition.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/requisition.php'));
    }

    /**
     * Setting Routes
     *
     * @return void
     */
    protected function mapSettingRoutes(): void
    {
        Route::prefix('setting')
            ->name('setting.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/setting.php'));
    }

    /**
     * Setting Routes
     *
     * @return void
     */
    protected function mapEmployeeByPayGradeRoutes(): void
    {
        Route::prefix('employee-by-paygrade')
            ->name('employee-by-paygrade.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/employee-by-paygrade.php'));
    }

    /**
     * Overtime Routes
     *
     * @return void
     */
    protected function mapOverTimeRoutes(): void
    {
        Route::prefix('over-time')
            ->name('over-time.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/over-time.php'));
    }

    /**
     * Attendance Routes
     *
     * @return void
     */
    protected function mapAttendanceRoutes(): void
    {
        Route::prefix('attendance')
            ->name('attendance.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/attendance.php'));
    }

    /**
     * Holiday Allowance Routes
     *
     * @return void
     */
    protected function mapHolidayAllowanceRoutes(): void
    {
        Route::prefix('holiday-allowance')
            ->name('holiday-allowance.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/holiday-allowance.php'));
    }

    /**
     * Tax Customization Routes
     *
     * @return void
     */
    protected function mapTaxCustomizationRoutes(): void
    {
        Route::prefix('tax-customization')
            ->name('tax-customization.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/tax-customization.php'));
    }

    /**
     * Meal Routes
     *
     * @return void
     */
    protected function mapMealRoutes(): void
    {
        Route::prefix('meal')
            ->name('meal.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/meal.php'));
    }

    /**
     * Meal Routes
     *
     * @return void
     */
    protected function mapApplyForRequisitionRoutes(): void
    {
        Route::prefix('apply-for-requisition')
            ->name('apply-for-requisition.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/apply-for-requisition.php'));
    }

    /**
     * Meal Routes
     *
     * @return void
     */

    protected function mapLateManagementRoutes(): void
    {
        Route::prefix('late-management')
            ->name('late-management.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/late-management.php'));
    }

    /**
     * Copy Data to Another Year Routes
     *
     * @return void
     */
    protected function mapCopyDataToAnotherYearRoutes(): void
    {
        Route::prefix('copy-data')
            ->name('copy-data.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/copy-data.php'));
    }

    /**
     * User Late Routes
     *
     * @return void
     */
    protected function mapUserLateRoutes(): void
    {
        Route::prefix('user-late')
            ->name('user-late.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/user-late.php'));
    }

    /**
     * User Late Routes
     *
     * @return void
     */
    protected function mapRoasterRoutes(): void
    {
        Route::prefix('roaster')
            ->name('roaster.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/roaster.php'));
    }

    /**
     * User Late Routes
     *
     * @return void
     */
    protected function mapRosterRoutes(): void
    {
        Route::prefix('rosters')
            ->name('rosters.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/roster.php'));
    }

    /**
     * User Late Routes
     *
     * @return void
     */
    protected function mapDailyAttendanceRoutes(): void
    {
        Route::prefix('daily-attendance')
            ->name('daily-attendance.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/daily-attendance.php'));
    }

    /**
     * Warehouse Routes
     *
     * @return void
     */
    protected function mapWarehouseRoutes(): void
    {
        Route::prefix('warehouse')
            ->name('warehouse.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/warehouse.php'));
    }

    /**
     * Unit Routes
     *
     * @return void
     */
    protected function mapUnitRoutes(): void
    {
        Route::prefix('unit')
            ->name('unit.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/unit.php'));
    }

    /**
     * Internal Transfer Routes
     *
     * @return void
     */
    protected function mapInternalTransferRoutes(): void
    {
        Route::prefix('internal-transfer')
            ->name('internal-transfer.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/internal-transfer.php'));
    }

    /**
     * Internal Transfer Routes
     *
     * @return void
     */
    protected function mapRequisitionItemRoutes(): void
    {
        Route::prefix('whms-item')
            ->name('requisition-item.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/requisition-item.php'));
    }

    /**
     * Supervisor Routes
     *
     * @return void
     */
    protected function mapDivisionSupervisorRoutes(): void
    {
        Route::prefix('division-supervisor')
            ->name('division-supervisor.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/division-supervisor.php'));
    }

    /**
     * Other requisition item Routes
     *
     * @return void
     */
    // protected function mapOtherRequisitionItemRoutes(): void
    // {
    //     Route::prefix('challan-item')
    //         ->name('other-requisition-item.')
    //         ->middleware(['web', 'auth'])
    //         ->group(base_path('routes/other-requisition-item.php'));
    // }

    protected function mapTransferRoutes(): void
    {
        Route::prefix('transfer')
            ->name('transfer.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/transfer.php'));
    }

    protected function mapLateAllowRoutes(): void
    {
        Route::prefix('late-allow')
            ->name('late-allow.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/late-allow.php'));
    }

    protected function mapFilterRoutes(): void
    {
        Route::prefix('filter')
            ->name('filter.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/filter.php'));
    }

    protected function mapRelaxDayRoutes(): void
    {
        Route::prefix('relax-day')
            ->name('relax-day.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/relax-day.php'));
    }

    protected function mapAssignRelaxDayRoutes(): void
    {
        Route::prefix('assign-relax-day')
            ->name('assign-relax-day.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/assign-relax-day.php'));
    }

    protected function mapLeaveEncashmentRoutes(): void
    {
        Route::prefix('leave-encashment')
            ->name('leave-encashment.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/leave-encashment.php'));
    }

    protected function mapDashboardNotificationRoutes(): void
    {
        Route::prefix('dashboard-notification')
            ->name('dashboard-notification.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/dashboard-notification.php'));
    }

    protected function mapBloodBankRoutes(): void
    {
        Route::prefix('blood-bank')
            ->name('blood-bank.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/blood-bank.php'));
    }

    protected function mapRequestedLoanAdvanceRoutes() : void
    {
        Route::prefix('requested-loan-advance')
            ->name('requested-loan-advance.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/requested-loan-advance.php'));
    }

    protected function mapPoliciesRoutes(): void
    {
        Route::prefix('policies')
            ->name('policies.')
            ->middleware(['web', 'auth'])
            ->group(base_path('routes/policies.php'));
    }
}
