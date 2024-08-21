<?php

namespace App\Http\Controllers;

use App\Http\Requests\leave\holidays\RequestLeaveType;
use App\Models\LeaveType;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveTypeController extends Controller
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
        $items = LeaveType::select("id", "name", "is_paid","priority")->orderBy("name")->paginate(\Functions::getPaginate());
        return view("leave-type.index",compact('items'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        return view("leave-type.create");
    }

    /**
     * @param RequestLeaveType $request
     * @return RedirectResponse
     */
    public function store(RequestLeaveType $request)
    {
        try {
            LeaveType::create($request->validated());

            session()->flash("message", "Leave type Created Successfully");
            $redirect = redirect()->route("leave-type.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param LeaveType $leaveType
     * @return Application|Factory|View
     */
    public function edit(LeaveType $leaveType)
    {
        return view("leave-type.edit",compact('leaveType'));
    }

    /**
     * @param RequestLeaveType $request
     * @param LeaveType $leaveType
     * @return RedirectResponse
     */
    public function update(RequestLeaveType $request, LeaveType $leaveType)
    {
        try {
            $leaveType->update($request->validated());

            session()->flash("message", "Leave type Updated Successfully");
            $redirect = redirect()->route("leave-type.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param LeaveType $leaveType
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(LeaveType $leaveType)
    {
        try {
            $feedback['status'] = $leaveType->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
