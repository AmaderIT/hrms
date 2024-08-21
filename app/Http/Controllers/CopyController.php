<?php

namespace App\Http\Controllers;

use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationDetails;
use App\Models\PublicHoliday;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class CopyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('copy.index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function copy(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->copyPublicHoliday($request);
            $this->copyLeaveAllocationWithDetails($request);

            DB::commit();

            session()->flash('message', 'Data Copied Successfully');

            $redirect = redirect()->route("requested-application.syncBalance");
        } catch(Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');

            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param $request
     */
    protected function copyPublicHoliday($request) {
        $publicHolidays = PublicHoliday::whereYear("from_date", $request->input("from_year"))
            ->whereYear("to_date", $request->input("from_year"))
            ->get();

        foreach ($publicHolidays as $publicHoliday) {
            $fromDate = \Carbon\Carbon::parse($publicHoliday->from_date)->year(now()->format('Y'))->format('Y-m-d');
            $toDate = \Carbon\Carbon::parse($publicHoliday->to_date)->year(now()->format('Y'))->format('Y-m-d');

            PublicHoliday::firstOrCreate([
                "holiday_id"=> $publicHoliday->holiday_id,
                "from_date" => $fromDate,
                "to_date"   => $toDate,
                "remarks"   => $publicHoliday->remarks
            ]);
        }
    }

    /**
     * @param $request
     */
    protected function copyLeaveAllocationWithDetails($request) {
        $leaveAllocations = LeaveAllocation::with("leaveAllocationDetails")->where("year", $request->input("from_year"))->get();

        foreach ($leaveAllocations as $leaveAllocation) {
            $lA = LeaveAllocation::firstOrCreate([
                "office_division_id" => $leaveAllocation->office_division_id,
                "department_id" => $leaveAllocation->department_id,
                "year" => $request->input("to_year"),
            ]);

            if($leaveAllocation->leaveAllocationDetails->count() > 0) {
                foreach ($leaveAllocation->leaveAllocationDetails as $leaveAllocationDetails) {
                    LeaveAllocationDetails::firstOrCreate([
                        "leave_allocation_id"   => $lA->id,
                        "leave_type_id"         => $leaveAllocationDetails->leave_type_id,
                        "total_days"            => $leaveAllocationDetails->total_days
                    ]);
                }
            }
        }
    }
}
