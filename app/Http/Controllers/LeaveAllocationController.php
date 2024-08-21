<?php

namespace App\Http\Controllers;

use App\Http\Requests\leave\RequestLeaveAllocation;
use App\Models\Department;
use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use App\Models\OfficeDivision;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class LeaveAllocationController extends Controller
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
     * @return Factory|View
     */
    public function index()
    {
        $items = LeaveAllocation::with("leaveAllocationDetails.leaveType", "officeDivision", "department")
            ->select('id','office_division_id','department_id','year','created_at','updated_at')
            ->orderByDesc("id")
            ->paginate(\Functions::getPaginate());
        return view('leave-allocation.index', compact('items'));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        $data = array(
            "officeDivisions"   => OfficeDivision::select("id", "name")->get(),
            "leaveTypes"        => LeaveType::select("id", "name")->orderByDesc("id")->get()
        );

        return view("leave-allocation.create", compact("data"));
    }

    /**
     * @param LeaveAllocation $leaveAllocation
     * @return Factory|View
     */
    public function edit(LeaveAllocation $leaveAllocation)
    {
        $data = array(
            "officeDivisions"       => OfficeDivision::select("id", "name")->get(),
            "departments"           => Department::where("office_division_id", $leaveAllocation->office_division_id)->select("id", "name")->get(),
            "leaveTypes"            => LeaveType::select("id", "name")->orderByDesc("id")->get(),
            "leaveAllocationDetails"=> $leaveAllocation->load("leaveAllocationDetails.leaveType")
        );
        return view("leave-allocation.edit", compact("leaveAllocation", "data"));
    }

    /**
     * @param RequestLeaveAllocation $request
     * @return RedirectResponse
     */
    public function store(RequestLeaveAllocation $request)
    {
        try {
            # Leave Allocation
            $leaveAllocation = array();
            foreach ($request->input("department_id") as $key => $value) {
                $leaveAllocation = LeaveAllocation::create(array(
                    "office_division_id"=> Department::where("id", $value)->first()->office_division_id,
                    "department_id"     => $value,
                    "year"              => $request->input("year"),
                    "short_day_count"   => $request->input("short_day_count") ?? null,
                    "half_day_count"    => $request->input("half_day_count") ?? null,
                ));

                $data = array();
                foreach ($request->input("days") as $key => $day) {
                    array_push($data, array(
                        "leave_allocation_id" => $leaveAllocation->id,
                        "leave_type_id" => $request->input("leave_type_id")[$key],
                        "total_days" => $request->input("days")[$key],
                        "created_at" => now(),
                        "updated_at" => now(),
                    ));
                }

                # Leave Allocation Details
                $leaveAllocation->leaveAllocationDetails()->createMany($data);
            }

            session()->flash('message', 'Leave Allocation Created Successfully');
            $redirect = redirect()->route("leave-allocation.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestLeaveAllocation $request
     * @param LeaveAllocation $leaveAllocation
     * @return RedirectResponse
     */
    public function update(RequestLeaveAllocation $request, LeaveAllocation $leaveAllocation)
    {
        try {

            $update_allocation = array(
                "office_division_id"=> $request->input("office_division_id")[0],
                "department_id"     => $request->input("department_id")[0],
                "year"              => $request->input("year"),
            );
            $update_allocation['short_day_count'] = $request->input("short_day_count") ?? null;
            $update_allocation['half_day_count'] = $request->input("half_day_count") ?? null;
            $leaveAllocation->update($update_allocation);

            foreach ($request->input("leave_type_id") as $key => $value) {
                $leaveAllocation->leaveAllocationDetails()->where("leave_type_id", $request->input("leave_type_id")[$key])->update(array(
                    "total_days"    => $request->input("days")[$key]
                ));
            }

            session()->flash('message', 'Leave Allocation Updated Successfully');
            $redirect = redirect()->route("leave-allocation.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param LeaveAllocation $leaveAllocation
     * @return mixed
     */
    public function delete(LeaveAllocation $leaveAllocation)
    {
        try {
            $feedback['status'] = $leaveAllocation->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
