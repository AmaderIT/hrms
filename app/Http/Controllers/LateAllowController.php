<?php

namespace App\Http\Controllers;


use App\Http\Requests\late_allow\RequestLateAllow;

use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\Designation;
use App\Models\DivisionSupervisor;
use App\Models\EmployeeStatus;
use App\Models\LateAllow;
use App\Models\OfficeDivision;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Exception;

class LateAllowController extends Controller
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

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $officeDivision = OfficeDivision::select(['id', 'name']);

        if (!auth()->user()->can('Show All Office Division')) {
            $departmentalSupervisorDivisions = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->pluck('office_division_id');

            $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                ->where('supervised_by', auth()->user()->id)
                ->pluck('office_division_id');

            $officeDivision->whereIn('id', $departmentalSupervisorDivisions);
            $officeDivision->OrwhereIn('id', $divisionalSupervisorDivisions);
        }

        $officeDivision = $officeDivision->get();

        $data = array(
            "officeDivisions" => $officeDivision,
        );

        return view('late-allow.index', compact('data'));
    }

    /**
     * @param RequestLateAllow $request
     * @return RedirectResponse
     */
    public function store(RequestLateAllow $request)
    {
        try {
            $request->validated();

            $emp = User::find($request->user_id);

            if ($emp) {

                $currentPosition = $emp->currentPromotion;

                if ($currentPosition) {

                    if ($currentPosition->department_id != $request->department_id) {
                        return redirect()->back()->withInput()->withErrors("This employee does not belong on this department("
                            . $currentPosition->department->name . ")");
                    }

                    $checkSameData = LateAllow::where('user_id', $emp->id)->orderBy('id', 'DESC')->first();

                    if ($checkSameData && $checkSameData->is_active == LateAllow::STATUS_ACTIVE && $checkSameData->allow == $request->allow) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(["allow" => "Since this value is active and same that is why this can not be updated!"]);
                    }

                    $data = [
                        'user_id' => $emp->id,
                        'allow' => $request->allow,
                        'allowed_by' => auth()->user()->id,
                        'allowed_date' => date('Y-m-d h:i:s'),
                        'is_active' => LateAllow::STATUS_ACTIVE,
                    ];

                    LateAllow::where('user_id', $emp->id)->where('is_active', LateAllow::STATUS_ACTIVE)->orderBy('id', 'DESC')->take(1)
                        ->update([
                            'is_active' => LateAllow::STATUS_INACTIVE,
                            'replaced_by' => auth()->user()->id,
                            'replaced_date' => date('Y-m-d h:i:s'),

                        ]);

                    LateAllow::create($data);

                    session()->flash("message", "Allowed late given Successfully");
                    $redirect = redirect()->route("late-allow.index")->withInput();
                }
            }
        } catch (Exception $exception) {
            $redirect = redirect()->back()->withErrors($exception->getMessage());
        }

        return $redirect;
    }


    /**
     * @param LateAllow $lateAllow
     * @return mixed
     */
    public function delete(LateAllow $lateAllow)
    {
        try {

            $feedback['status'] = $lateAllow->update([
                'is_active' => LateAllow::STATUS_INACTIVE,
                'replaced_by' => auth()->user()->id,
                'replaced_date' => date('Y-m-d h:i:s')
            ]);

        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    public function getHistory(Request $request)
    {
        try {
            $result = LateAllow::
            select([
                'late_allows.id',
                'user_id',
                'allow',
                'allowed_by',
                'allowed_date',
                'replaced_by',
                'replaced_date',
                'is_active'
            ])
                ->with([
                    'employee',
                    'employee.currentPromotion.designation',
                    'allowed_by',
                    'removed_by'
                ])
                ->whereRaw(DB::raw('id in( select max(id) from late_allows group by user_id)'))
                ->groupBy('late_allows.user_id')
                ->orderBy('late_allows.id', 'DESC');


            if ($request->department_id) {

                $userIds = User::join('promotions', function ($join) {
                    $join->on('promotions.user_id', 'users.id');
                    $join->on('promotions.id', DB::raw('( select max(p.id) from promotions p where p.user_id= users.id limit 1)'));
                })
                    ->where('status', 1)
                    ->where('promotions.department_id', $request->department_id)
                    ->pluck('users.id');

                $result->whereIn('user_id', $userIds);
            }

            if ($request->user_id) {
                $result->where('user_id', $request->user_id);
            }


            $result = $result->get();

            foreach ($result as $data) {
                $data->allowed_date = date('d M, Y', strtotime($data->allowed_date));
                $data->replaced_date = date('d M, Y', strtotime($data->replaced_date));
                $data->actions = "";

                $data->actions .= ' <a title="Show History" href="#" data-toggle="modal" data-target="#modalDiv" data-user-id="' . $data->user_id . '" onclick="showDetails(this)"><i class="fa fa-book" style="color: #50c7a0"></i></a> ';

                if ($data->is_active == LateAllow::STATUS_ACTIVE) {

                    $data->actions .= ' <a title="Edit" href="#" data-user-id="' . $data->user_id . '" data-allow-value="' . $data->allow . '" onclick="edit(this)"><i class="fa fa-edit" style="color: green"></i></a>';

                    if (auth()->user()->can('Delete Late Allow')) {

                        $deleteUrl = "'" . route('late-allow.delete', ['lateAllow' => $data->id]) . "'";

                        $data->actions .= ' <a title="Delete" href="#" onclick="deleteAlert(' . $deleteUrl . ')"><i class="fa fa-trash" style="color: red"></i></a>';
                    }

                } else {
                    $data->actions .= ' Removed By ' . optional($data->removed_by)->name . ' at ' . $data->replaced_date;
                }

            }

            $response = response()->json([
                "status" => 200,
                "result" => $result
            ]);
        } catch (Exception $exception) {
            $response = response()->json([
                "status" => 404,
                "result" => $exception->getMessage()
            ]);
        }

        return $response;
    }

    public function getDetails(Request $request)
    {
        try {
            $result = LateAllow::
            select([
                'late_allows.id',
                'user_id',
                'allow',
                'allowed_by',
                'allowed_date',
                'replaced_by',
                'replaced_date',
                'is_active'
            ])
                ->with([
                    'employee',
                    'employee.currentPromotion.designation',
                    'allowed_by',
                    'removed_by'
                ])
                ->where('user_id', $request->user_id)
                ->get();

            foreach ($result as $data) {

                if ($data->allowed_date != null) {
                    $data->allowed_date = date('d M, Y', strtotime($data->allowed_date));
                }
                if ($data->replaced_date != null) {
                    $data->replaced_date = date('d M, Y', strtotime($data->replaced_date));
                }
            }

            $response = response()->json([
                "status" => 200,
                "result" => $result
            ]);
        } catch (Exception $exception) {
            $response = response()->json([
                "status" => 404,
                "result" => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
