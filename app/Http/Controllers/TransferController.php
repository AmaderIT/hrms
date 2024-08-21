<?php

namespace App\Http\Controllers;


use App\Helpers\Common;
use App\Http\Requests\transfer\RequestTransfer;
use App\Models\Department;
use App\Models\EmployeeStatus;
use App\Models\OfficeDivision;
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

class TransferController extends Controller
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

        return view('transfer.index');
    }

    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();
        $data = Promotion::where('type', Promotion::TYPE_TRANSFERRED)
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
            ->with("user", "officeDivision", "department", "designation", "payGrade", "workSlot")
            ->orderBy("user_id");

        return DataTables::eloquent($data)
            ->addColumn('promoted_date', function (Promotion $obj) use ($authUser) {
                return date("d M,Y", strtotime($obj->promoted_date));
            })
            ->addColumn('action', function (Promotion $obj) use ($authUser) {
                $str = "";

                if ($authUser->can('Edit Transfer')) {
                    $str .= '<a href="' . route('transfer.edit', ['transfer' => $obj->id]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }
                if ($authUser->can('Delete Transfer')) {

                    $delteUrl = "'" . route('transfer.delete', ['transfer' => $obj->id]) . "'";
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
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "departments" => Department::select("id", "name")->get(),
            "workSlots" => WorkSlot::select("id", "title")->get()
        );

        return view("transfer.create", compact("data"));
    }

    /**
     * @param transfer $transfer
     * @return Application|Factory|View
     */
    public function edit(Promotion $transfer)
    {
        $item = $transfer->load("user", "department", "designation");

        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "departments" => Department::select("id", "name")->get(),
            "workSlots" => WorkSlot::select("id", "title")->get()
        );

        return view("transfer.edit", compact("item", "data"));
    }

    /**
     * @param RequestTransfer $request
     * @return RedirectResponse
     */
    public function store(RequestTransfer $request)
    {
        try {
            $request->validated();
            $emp = User::find($request->user_id);
            if ($emp) {
                $currentPosition = $emp->lastPromotion;
                if ($currentPosition) {
                    if ($currentPosition->department_id == $request->department_id) {
                        return redirect()->back()->withInput()->withErrors("This employee can not be transferred from same department("
                            . $currentPosition->department->name .
                            ") to same department("
                            . $currentPosition->department->name .
                            ")!");
                    }
                    //checking transfer date
                    $empStatus = EmployeeStatus::where('user_id', $request->user_id)->first();
                    if ($empStatus && date('Y-m-d', strtotime($empStatus->action_date)) > date('Y-m-d', strtotime($request->promoted_date))) {
                        return redirect()->back()->withInput()->withErrors("The transfer date can not be less than joining or last action date");
                    }
                    if (date('Y-m-d', strtotime($currentPosition->promoted_date)) > date('Y-m-d', strtotime($request->promoted_date))) {
                        return redirect()->back()->withInput()->withErrors("The transfer date can not be less than  last action date");
                    }
                    DB::beginTransaction();
                    $transferData = $currentPosition->replicate()->fill(
                        [
                            'type' => Promotion::TYPE_TRANSFERRED,
                            'employment_type' => !empty($currentPosition->employment_type) ? $currentPosition->employment_type : NULL,
                            'promoted_date' => $request->promoted_date,
                            'workslot_id' => $request->workslot_id,
                            'office_division_id' => $request->office_division_id,
                            'department_id' => $request->department_id
                        ]
                    );
                    $transferData->save();

                    if (empty($currentPosition->employment_type)) {
                        $getResponse = Common::modifyPromotionEmploymentTypeEmployeeWise($request->user_id);
                        if (!empty($getResponse['errorMsg'])) {
                            return redirect()->back()->withInput()->withErrors($getResponse['errorMsg']);
                        }
                    }

                    // relax day
                    if($currentPosition->department_id != $transferData->department_id){
                        AssignRelaxDayController::removeEmployeeRelaxDay((object)[
                            'user_id' => $transferData->user_id,
                            'prev_department_id' => $currentPosition->department_id,
                            'current_department_id' => $transferData->department_id,
                            'date' => $transferData->promoted_date
                        ]);
                    }

                    DB::commit();
                    session()->flash("message", "Transfer Created Successfully");
                    $redirect = redirect()->route("transfer.index");
                }
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Requesttransfer $request
     * @param transfer $transfer
     * @return RedirectResponse
     */
    public function update(Requesttransfer $request, Promotion $transfer)
    {
        try {
            $emp = User::find($request->user_id);

            if ($emp) {

                $currentPosition = $emp->currentPromotion;

                if ($currentPosition) {

                    //checking transfer date
                    $empStatus = EmployeeStatus::where('user_id', $request->user_id)->first();

                    if ($empStatus && date('Y-m-d', strtotime($empStatus->action_date)) > date('Y-m-d', strtotime($request->promoted_date))) {

                        return redirect()->back()->withInput()->withErrors("The transfer date can not be less than joining or last action date");
                    }

                    if (date('Y-m-d', strtotime($currentPosition->promoted_date)) > date('Y-m-d', strtotime($request->promoted_date))) {

                        return redirect()->back()->withInput()->withErrors("The transfer date can not be less than  last action date");
                    }

                    DB::beginTransaction();
                    $transfer->update($request->validated());

                    // relax day
                    if($currentPosition->department_id != $request->department_id){
                        AssignRelaxDayController::removeEmployeeRelaxDay((object)[
                            'user_id' => $request->user_id,
                            'prev_department_id' => $currentPosition->department_id,
                            'current_department_id' => $request->department_id,
                            'date' => $request->promoted_date
                        ]);
                    }

                    DB::commit();
                    session()->flash("message", "Transfer Information Updated Successfully");
                    $redirect = redirect()->route("transfer.index");
                }
            }
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param transfer $transfer
     * @return mixed
     */
    public function delete(Promotion $transfer)
    {
        try {
            $feedback['status'] = $transfer->delete();
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
            $employees = User::orderby('name', 'asc')->select('id', 'name', 'fingerprint_no')->limit(5)->get();
        } else {
            $employees = User::orderby('name', 'asc')->select('id', 'name', 'fingerprint_no')
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('fingerprint_no', 'like', '%' . $search . '%')
                ->limit(5)->get();
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

    public function getEmployeeCurrentPromotion(User $employee)
    {

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

    public function getHistory(Request $request)
    {
        try {
            $result = Promotion::where('user_id', $request->emp_id)
                ->select([
                    'id',
                    'user_id',
                    'designation_id',
                    'pay_grade_id',
                    'office_division_id',
                    'department_id',
                    'workslot_id',
                    'promoted_date',
                    'type',
                    'employment_type'
                ])
                ->orderBy('promoted_date', 'ASC')
                ->get();

            foreach ($result as $data) {

                $data->designation = $data->designation;
                $data->pay_grade = $data->payGrade;
                $data->work_slot = $data->workSlot;
                $data->office_division = $data->officeDivision;
                $data->department = $data->department;
                $data->transfer_date = date('d M, Y', strtotime($data->promoted_date));
                $data->employment_type = !empty($data['employment_type']) ? $data['employment_type'] : "";
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
