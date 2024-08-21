<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Http\Requests\termination\RequestTermination;
use App\Http\Requests\termination\RequestTerminationUpdate;
use App\Models\ActionReason;
use App\Models\Promotion;
use App\Models\Termination;
use App\Models\User;
use Barryvdh\DomPDF\PDF;
use Hamcrest\Type\IsNumeric;
use Hamcrest\Type\IsString;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;
use Yajra\DataTables\DataTables;

class TerminationController extends Controller
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
        $authUser = auth()->user();
        if (request()->ajax()) {
            $data = Termination::with("user", "reason", "actionTakenBy")
                ->select("id", "user_id", "action_reason_id", "action_taken_by", "action_date")
                ->orderBy("user_id");
            return DataTables::eloquent($data)
                ->addColumn('action_date', function (Termination $obj) use ($authUser) {
                    return date("M d, Y", strtotime($obj->action_date));
                })
                ->addColumn('action', function (Termination $obj) use ($authUser) {
                    $str = "";
                    if ($authUser->can('Termination Edit')) {
                        $str .= '<a href="' . route('termination.edit', ['termination' => $obj->id]) . '"><i class="fa fa-edit" style="color: green"></i></a>&nbsp;';
                    }
                    return $str;
                })
                ->rawColumns(['action', 'action_date'])
                ->toJson();
        }
        return view("termination.index");
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $actionReasons = ActionReason::reasonForTermination()->get();
        return view("termination.create", compact("actionReasons"));
    }

    /**
     * @param Termination $termination
     * @return Application|Factory|View
     */
    public function edit(Termination $termination)
    {
        $supervisors = [];
        $actionReasons = ActionReason::reasonForTermination()->get();
        $terminationEmployeeCheck = User::where(['id' => $termination->user_id, 'status' => User::STATUS_DISABLE])->select("id", "name", "email", "fingerprint_no", "is_supervisor")->first();
        $users = User::orderby('name', 'asc')
            ->select("id", "name", "email", "fingerprint_no", "is_supervisor")
            ->whereNotIn("id", Termination::with("user", "reason", "actionTakenBy")->pluck("user_id"))
            ->where(['status' => User::STATUS_ACTIVE])
            ->whereIn("is_supervisor", [User::SUPERVISOR_DEPARTMENT, User::SUPERVISOR_OFFICE_DIVISION])
            ->get();
        foreach ($users as $user) {
            array_push($supervisors, $user);
        }
        return view("termination.edit", compact("termination", "supervisors", "actionReasons", 'terminationEmployeeCheck'));
    }

    /**
     * @param RequestTermination $request
     * @return RedirectResponse
     */
    public function store(RequestTermination $request)
    {
        try {
            if (empty($request->input('user_id'))) {
                throw new \Exception("Employee not found!!!");
            }
            $terminationEmployeeCheck = User::join("promotions", function ($join) {
                $join->on('promotions.user_id', 'users.id');
                $join->on('promotions.id', DB::raw("(select max(p.id) from promotions p where p.user_id = users.id limit 1)"));
            })->select([
                "users.id",
                "user_id",
                "office_division_id",
                "department_id",
                "designation_id",
                "pay_grade_id",
                "salary",
                "workslot_id",
                "promotions.id as promotion_id",
                "promoted_date"
            ])->where(['users.id' => $request->input('user_id'), 'users.status' => User::STATUS_DISABLE, 'promotions.type' => Promotion::TYPE_TERMINATED])->first();
            if (!empty($terminationEmployeeCheck->id)) {
                throw new \Exception("Employment already closed!!!");
            }
            DB::beginTransaction();
            //$todayDate = date("Y-m-d");
            //$promotionInfos = Promotion::where('user_id', $request->input('user_id'))->where('promoted_date', '<=', $todayDate)->orderBy('promoted_date', 'DESC')->first();
            $promotionInfos = Promotion::where('user_id', $request->input('user_id'))->orderBy('promoted_date', 'DESC')->orderBy('id', 'DESC')->first();
            if (!empty($promotionInfos->id)) {
                if ($request->input('action_date') <= date('Y-m-d', strtotime($promotionInfos->promoted_date))) {
                    throw new \Exception("Action Date not less than or equal Last Action Date " . date('Y-m-d', strtotime($promotionInfos->promoted_date)));
                }

                if ($request->action_reason_id < 1) {
                    $request->merge(['action_reason_id' => Termination::addNewReason($request->action_reason_id)->id ?? 0]);
                }
                Promotion::create([
                    "office_division_id" => $promotionInfos->office_division_id,
                    "user_id" => $request->input('user_id'),
                    "department_id" => $promotionInfos->department_id,
                    "designation_id" => $promotionInfos->designation_id,
                    "pay_grade_id" => $promotionInfos->pay_grade_id,
                    "salary" => $promotionInfos->salary,
                    "promoted_date" => $request->input('action_date'),
                    "type" => Promotion::TYPE_TERMINATED,
                    "employment_type" => $promotionInfos->employment_type,
                    "workslot_id" => $promotionInfos->workslot_id
                ]);

                $getResponse = Common::modifyPromotionEmploymentTypeEmployeeWise($request->user_id);
                if (!empty($getResponse['errorMsg'])) {
                    throw new \Exception($getResponse['errorMsg']);
                }

                User::findOrFail($request->input('user_id'))->update([
                    "status" => User::STATUS_DISABLE,
                ]);

                if ($request->action_reason_id < 1) {
                    $request->merge(['action_reason_id' => Termination::addNewReason($request->action_reason_id)->id ?? 0]);
                }
                $payLoads = $request->except(['_token']);
                $payLoads['action_taken_by'] = auth()->user()->id;
                Termination::create($payLoads);

                // relax day
                AssignRelaxDayController::removeEmployeeRelaxDay((object)[
                    'user_id' => $request->user_id,
                    'prev_department_id' => null,
                    'current_department_id' => null,
                    'date' => $request->action_date
                ]);
            }

            DB::commit();
            session()->flash("message", "Employment Close Successfully");
            $redirect = redirect()->route("termination.index");
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestTermination $request
     * @param Termination $termination
     * @return RedirectResponse
     */
    public function update(RequestTerminationUpdate $request, Termination $termination)
    {
        try {
            $existActionDate = base64_decode($request->input('hidden_action_date'));
            if (empty($request->input('user_id'))) {
                throw new \Exception("Employee ID not found!!!");
            }
            if (empty($existActionDate)) {
                throw new \Exception("Employee exist action date not found!!!");
            }
            $terminationEmployeeCheck = Promotion::whereDate('promoted_date', $existActionDate)->where(['user_id' => $request->input('user_id'), 'type' => Promotion::TYPE_TERMINATED])->orderBy('promoted_date', 'DESC')->first();
            if (empty($terminationEmployeeCheck->id)) {
                throw new \Exception("Employee Information Mismatch!!!");
            }
            DB::beginTransaction();
            if ($request->input('action_date') < $existActionDate) {
                throw new \Exception('Action date not less than or equal promoted date ' . $existActionDate);
            }
            //$todayDate = date("Y-m-d");
            //$promotionInfos = Promotion::where('user_id', $request->input('user_id'))->where('promoted_date', '<=', $todayDate)->where('type', Promotion::TYPE_TERMINATED)->orderBy('promoted_date', 'DESC')->first();
            $promotionInfos = Promotion::where('user_id', $request->input('user_id'))->where('promoted_date', '>', $existActionDate)->orderBy('promoted_date', 'ASC')->first();
            if (!empty($promotionInfos->id) && $request->input('action_date') >= date('Y-m-d', strtotime($promotionInfos->promoted_date))) {
                throw new \Exception("Action Date not greater than or equal Promoted Date " . date('Y-m-d', strtotime($promotionInfos->promoted_date)));
            }
            Promotion::where("id", $terminationEmployeeCheck->id)->update([
                'promoted_date' => $request->input('action_date')
            ]);
            if ($request->action_reason_id < 1) {
                $request->merge(['action_reason_id' => Termination::addNewReason($request->action_reason_id)->id ?? 0]);
            }

            $payLoads = $request->except(['_token']);
            $payLoads['action_taken_by'] = auth()->user()->id;

            $termination->update($payLoads);

            // relax day
            AssignRelaxDayController::removeEmployeeRelaxDay((object)[
                'user_id' => $request->user_id,
                'prev_department_id' => null,
                'current_department_id' => null,
                'date' => $request->action_date
            ]);
            DB::commit();
            session()->flash("message", "Employment Close Updated Successfully");
            $redirect = redirect()->route("termination.index");
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Request $request
     */
    public function getActiveUsers(Request $request)
    {
        $search = $request->search;
        getActiveUsers($search);
    }

    /**
     * @param Request $request
     */
    public function getActionTakenByUsers(Request $request)
    {
        $search = $request->search;
        getActionTakenByUsers($search);
    }

}
