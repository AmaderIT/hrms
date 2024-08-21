<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\Institute;
use App\Models\OfficeDivision;
use App\Models\PayGrade;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class EmployeeByPayGradeController extends Controller
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
        $data = array(
            "banks"             => Bank::orderByDesc("id")->select("id", "name")->get(),
            "branches"          => Branch::orderByDesc("id")->select("id", "name")->get(),
            "institutes"        => Institute::orderByDesc("id")->select("id", "name")->get(),
            "officeDivisions"   => OfficeDivision::select("id", "name")->get(),
            "payGrades"         => PayGrade::all(),
        );

        return view("employee-by-paygrade.index", compact("data"));
    }

    /**
     * @param PayGrade $payGrade
     * @return JsonResponse
     */
    public function getEmployeeByPayGrade(PayGrade $payGrade)
    {
        $items = User::with(["currentPromotion" => function($query) {
            $query->with("officeDivision","department", "designation", "payGrade");
        }])->whereHas("currentPromotion", function ($query) use ($payGrade) {
            return $query->where("pay_grade_id", $payGrade->id)->orderByDesc("id");
        })->whereStatus(User::STATUS_ACTIVE)->get();

        return response()->json(["data" => $items]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function modifyEmployeePayGrade(Request $request)
    {
        try {
            $items = User::with("currentPromotion")
                ->whereIn("id", $request->input("user_id"))
                ->active()
                ->get();

            foreach ($items as $item) {
                $item->currentPromotion->update([
                    "pay_grade_id"  => $request->input("pay_grade_id_to")
                ]);
            }

            $success = true;
        } catch (Exception $exception) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }
}
