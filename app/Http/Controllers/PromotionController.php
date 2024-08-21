<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Http\Requests\promotion\RequestPromotion;
use App\Models\Department;
use App\Models\Designation;
use App\Models\OfficeDivision;
use App\Models\PayGrade;
use App\Models\Promotion;
use App\Models\User;
use App\Models\WorkSlot;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class PromotionController extends Controller
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

        return view('promotion.index');
    }


    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();

        $data = Promotion::whereNotNull("promoted_date")
            ->whereIn('type',array_keys(Promotion::promoteTypes()))
            ->select([
                "promotions.id",
                "user_id",
                "promotions.office_division_id",
                "department_id",
                "designation_id",
                "pay_grade_id",
                "promoted_date",
                "type",
                "workslot_id"
            ])
            ->with([
                "user",
                "officeDivision",
                "department",
                "designation",
                "payGrade"
            ])
            ->orderBy("user_id");


        return DataTables::eloquent($data)
            ->addColumn('promoted_date', function (Promotion $obj) use ($authUser) {
                return date("d M,Y", strtotime($obj->promoted_date));
            })
            ->addColumn('action', function (Promotion $obj) use ($authUser) {
                $str = "";

                if ($authUser->can('Edit Promotion')) {
                    $str .= '<a href="' . route('promotion.edit', ['promotion' => $obj->id]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }
                if ($authUser->can('Delete Promotion')) {

                    $delteUrl = "'" . route('promotion.delete', ['promotion' => $obj->id]) . "'";
                    $str .= '<a href="#" onclick="deleteAlert(' . $delteUrl . ')" ><i class="fa fa-trash" style="color: red"></i></a>';

                }

                return $str;
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $data = array(
            "users" => User::select("id", "name", "email")->get(),
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "departments" => Department::select("id", "name")->get(),
            "designations" => Designation::select("id", "title")->get(),
            "payGrades" => PayGrade::select("id", "name")->get(),
            "workSlots" => WorkSlot::select("id", "title")->get()
        );

        return view("promotion.create", compact("data"));
    }

    /**
     * @param Promotion $promotion
     * @return Application|Factory|View
     */
    public function edit(Promotion $promotion)
    {
        $item = $promotion->load("user", "department", "designation");

        $data = array(
            "users" => User::select("id", "name", "email")->get(),
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "departments" => Department::select("id", "name")->get(),
            "designations" => Designation::select("id", "title")->get(),
            "payGrades" => PayGrade::select("id", "name")->get(),
            "workSlots" => WorkSlot::select("id", "title")->get()
        );

        return view("promotion.edit", compact("item", "data"));
    }

    /**
     * @param RequestPromotion $request
     * @return RedirectResponse
     */
    public function store(RequestPromotion $request)
    {
        try {
            $latestPrmData = Promotion::where('user_id', $request->user_id)->where('promoted_date','<=',date('Y-m-d'))->whereIn('employment_type',array_keys(Promotion::employmentType()))->select('id', 'department_id', 'employment_type')->orderByDesc("promoted_date")->first();
            if(!empty($latestPrmData->id)){
                $request['employment_type'] = $latestPrmData->employment_type;
            }
            DB::beginTransaction();
            $newPrmData = Promotion::create($request->validated());

            $getResponse = Common::modifyPromotionEmploymentTypeEmployeeWise($request->user_id);
            if(!empty($getResponse['errorMsg'])){
                return redirect()->back()->withInput()->withErrors($getResponse['errorMsg']);
            }

            // relax Day
            if($latestPrmData->department_id != $newPrmData->department_id){
                AssignRelaxDayController::removeEmployeeRelaxDay((object)[
                    'user_id' => $newPrmData->user_id,
                    'prev_department_id' => $latestPrmData->department_id,
                    'current_department_id' => $newPrmData->department_id,
                    'date' => $newPrmData->promoted_date
                ]);
            };

            DB::commit();

            session()->flash("message", "Promotion Created Successfully");
            $redirect = redirect()->route("promotion.index");
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex->getMessage());
            $redirect = redirect()->back()->withErrors($ex->getMessage());
        }

        return $redirect;
    }

    /**
     * @param RequestPromotion $request
     * @param Promotion $promotion
     * @return RedirectResponse
     */
    public function update(RequestPromotion $request, Promotion $promotion)
    {
        $emp = User::find($request->user_id);
        $currentPosition = $emp->currentPromotion;
        try {
            $promotion->update($request->validated());

            // relax Day
            if($currentPosition->department_id != $request->department_id){
                AssignRelaxDayController::removeEmployeeRelaxDay((object)[
                    'user_id' => $request->user_id,
                    'prev_department_id' => $currentPosition->department_id,
                    'current_department_id' => $request->department_id,
                    'date' => $request->promoted_date
                ]);
            };

            session()->flash("message", "Promotion Updated Successfully");
            $redirect = redirect()->route("promotion.index");
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            $redirect = redirect()->back()->withErrors($ex->getMessage());
        }

        return $redirect;
    }

    /**
     * @param Promotion $promotion
     * @return mixed
     */
    public function delete(Promotion $promotion)
    {
        try {
            $feedback['status'] = $promotion->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param Request $request
     */
    public function getUsers(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $employees = User::whereIn('id', FilterController::getEmployeeIds())->orderby('name', 'asc')->select('id', 'name', 'fingerprint_no')->limit(5)->get();
        } else {
            $employees = User::orderby('name', 'asc')->select('id', 'name', 'fingerprint_no')
                ->whereIn('id', FilterController::getEmployeeIds())->where(
                    function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%')->orWhere('fingerprint_no', 'like', '%' . $search . '%');
                    })
                ->limit(25)->get();;
        }

        $response = array();
        foreach ($employees as $employee) {
            $response[] = array(
                "id" => $employee->id,
                "text" => $employee->fingerprint_no . ' - ' . $employee->name,
            );
        }

        echo json_encode($response);
        exit;
    }

    /**
     * @param User $employee
     * @return JsonResponse
     */
    public function getEmployeeCurrentPromotion($userId)
    {
        $employee = User::where('id', $userId)->first();
        try {
            $result = $employee->load("currentPromotion");

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
