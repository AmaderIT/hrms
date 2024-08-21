<?php

namespace App\Http\Controllers;

use App\Http\Requests\LateDeduction\LateDeductionRequest;
use App\Models\LateDeduction;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LateManagementController extends Controller
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
        /*$late_deductions = LateDeduction::with("department")
            ->orderBy("id")
            ->paginate(\Functions::getPaginate());*/

        return view('late-management.index');
    }

    /**
     * @param LateDeduction $lateDeduction
     * @return Application|Factory|View
     */
    public function edit(LateDeduction $lateDeduction)
    {
        $late_deduction = LateDeduction::with("department")->where('id', 'like', $lateDeduction->id)->first();
        $items = Department::select("id", "name")->get();
        return view("late-management.edit", compact("late_deduction", "items"));
    }

    /**
     * @param LateDeductionRequest $request
     * @param LateDeduction $lateDeduction
     * @return RedirectResponse
     */
    public function update(LateDeductionRequest $request, LateDeduction $lateDeduction)
    {
        try {
            $lateDeduction->update($request->validated());
            session()->flash("message", "Late Management Updated Successfully");
            $redirect = redirect()->route("late-management.index");
        } catch (Exception $exception) {
            session()->flash("type", "danger");
            session()->flash("message", "Late Management Updated fields!");
            $redirect = redirect()->back()->withInput($request->all())->withErrors($request->messages());
        }
        return $redirect;
    }

    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();
        $items = LateDeduction::with("department")
            ->select(
                [
                    'id',
                    'total_days',
                    'deduction_day',
                    'department_id',
                    'type'
                ]);
        return DataTables::eloquent($items)
            ->addColumn('action', function (LateDeduction $obj) use ($authUser) {
                $str = "";
                if ($authUser->can('Edit Late Management')) {
                    $str .= '<a href="' . route('late-management.edit', ['lateDeduction' => $obj->id]) . '"><i class="fa fa-edit" style = "color: green" ></i ></a>&nbsp;';
                }
                return $str;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

}
