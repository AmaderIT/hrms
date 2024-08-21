<?php

namespace App\Http\Controllers;

use App\Http\Requests\leave\holidays\RequestWeeklyHoliday;
use App\Models\Department;
use App\Models\WeeklyHoliday;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class WeeklyHolidayController extends Controller
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

    /**+
     * @return Application|Factory|View
     */
    public function index()
    {
        $sql = "SELECT weekly_holidays.id, weekly_holidays.department_id, weekly_holidays.days, weekly_holidays.effective_date, departments.`name` as department_name FROM `weekly_holidays` LEFT JOIN departments ON departments.id = weekly_holidays.department_id WHERE weekly_holidays.id IN( SELECT MAX( wh.id) FROM `weekly_holidays` AS wh GROUP BY wh.department_id )";
        $items = DB::select($sql);
        return view("weekly-holiday.index", compact("items"));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $departments = Department::select("id", "name")->get();

        return view("weekly-holiday.create", compact("departments"));
    }

    /**
     * @param WeeklyHoliday $weeklyHoliday
     * @return Application|Factory|View
     */
    public function edit(WeeklyHoliday $weeklyHoliday)
    {
        $departments = Department::select("id", "name")->get();

        return view("weekly-holiday.edit", compact("weeklyHoliday", "departments"));
    }

    /**
     * @param RequestWeeklyHoliday $request
     * @return RedirectResponse
     */
    public function store(RequestWeeklyHoliday $request)
    {
        try {
            $weeklyHolidays = array();
            $effective_date = $request->input("effective_date");
            if(in_array(0, $request->input("department_id")))
            {
                $departments = Department::select("id")->get();
                $sql = "SELECT id,department_id,days,effective_date,end_date FROM `weekly_holidays` where id IN (select MAX(wh.id) from `weekly_holidays` as wh GROUP BY wh.department_id)";
                $weekly_holiday_records = DB::select($sql);
                $update_arr = [];
                foreach ($weekly_holiday_records as $weekly_holiday){
                    if($effective_date>$weekly_holiday->effective_date){
                        $new_arr = [];
                        $new_arr['id'] = $weekly_holiday->id;
                        $new_arr['end_date'] = date('Y-m-d', strtotime($effective_date .' -1 day'));
                        $update_arr[]=$new_arr;
                    }else{
                        session()->flash("type", "error");
                        session()->flash("message", "Effected date must be greater than $weekly_holiday->effective_date");
                        return redirect()->back();
                    }
                }
                foreach ($departments as $department)
                {
                    $weeklyHoliday = array(
                        "department_id" => $department->id,
                        "days"          => $request->input("days"),
                        "effective_date" => $effective_date,
                        "created_at"    => now()
                    );

                    array_push($weeklyHolidays, $weeklyHoliday);
                }
            }
            else
            {
                $update_arr = [];
                $departments = $request->input("department_id");
                $department_ids = implode(',',$departments);
                $sql = "SELECT id, department_id, days, effective_date, end_date FROM `weekly_holidays` WHERE id IN( SELECT MAX( wh.id) FROM `weekly_holidays` AS wh WHERE department_id IN ($department_ids) GROUP BY wh.department_id)";
                $weekly_holiday_records = DB::select($sql);
                foreach ($weekly_holiday_records as $weekly_holiday){
                    if($effective_date>$weekly_holiday->effective_date){
                        $new_arr = [];
                        $new_arr['id'] = $weekly_holiday->id;
                        $new_arr['end_date'] = date('Y-m-d', strtotime($effective_date .' -1 day'));
                        $update_arr[]=$new_arr;
                    }else{
                        session()->flash("type", "error");
                        session()->flash("message", "Effected date must be greater than $weekly_holiday->effective_date");
                        return redirect()->back();
                    }
                }
                foreach ($departments as $department)
                {
                    $weeklyHoliday = array(
                        "department_id" => $department,
                        "days"          => $request->input("days"),
                        "effective_date" => $effective_date,
                        "created_at"    => now()
                    );

                    array_push($weeklyHolidays, $weeklyHoliday);
                }
            }
            if($update_arr){
                $weekly_holiday_model = new WeeklyHoliday();
                $index = 'id';
                batch()->update($weekly_holiday_model, $update_arr, $index);
            }
            WeeklyHoliday::insert($weeklyHolidays);
            session()->flash("message", "Weekly Holiday Created Successfully");
            $redirect = redirect()->route("weekly-holiday.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestWeeklyHoliday $request
     * @param WeeklyHoliday $weeklyHoliday
     * @return RedirectResponse
     */
    public function update(RequestWeeklyHoliday $request, WeeklyHoliday $weeklyHoliday)
    {
        try {
            $effective_date = $request->input("effective_date");
            $departments = $request->input("department_id");
            $department_ids = implode(',',$departments);
            $sql = "SELECT * FROM weekly_holidays WHERE id < (SELECT MAX(id) FROM weekly_holidays where department_id IN ($department_ids)) AND department_id IN ($department_ids) ORDER BY id DESC LIMIT 1";
            $weekly_holiday_record = DB::select($sql);
            if($weekly_holiday_record){
                if($effective_date>$weekly_holiday_record[0]->effective_date){
                    $new_arr['end_date'] = date('Y-m-d', strtotime($effective_date .' -1 day'));
                    WeeklyHoliday::where('id','=',$weekly_holiday_record[0]->id)->update($new_arr);
                }else{
                    session()->flash("type", "error");
                    session()->flash("message", "Effected date must be greater than ".$weekly_holiday_record[0]->effective_date);
                    return redirect()->back();
                }
            }
            foreach ($departments as $department)
            {
                $holiday = array(
                    "department_id" => $department,
                    "days"          => $request->input("days"),
                    "effective_date" => $effective_date,
                    "updated_at"    => now()
                );

                $weeklyHoliday->update($holiday);
            }

            session()->flash("message", "Weekly Holiday Updated Successfully");
            $redirect = redirect()->route("weekly-holiday.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param WeeklyHoliday $weeklyHoliday
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(WeeklyHoliday $weeklyHoliday)
    {
        try {
            $sql = "SELECT * FROM weekly_holidays WHERE id < $weeklyHoliday->id AND department_id IN ($weeklyHoliday->department_id) ORDER BY id DESC LIMIT 1";
            $weekly_holiday_record = DB::select($sql);
            if($weekly_holiday_record){
                $new_arr['end_date'] = null;
                WeeklyHoliday::where('id','=',$weekly_holiday_record[0]->id)->update($new_arr);
            }
            $feedback['status'] = $weeklyHoliday->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
