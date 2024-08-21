<?php

namespace App\Http\Controllers;

use App\Http\Requests\relaxday\RequestRelaxDay;
use App\Models\AssignRelaxDay;
use App\Models\Department;
use App\Models\RelaxDay;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDF;
use View;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\PublicHoliday;
use Exception;

class RelaxDayController extends Controller
{
    public function create()
    {
        $data['monthly_holiday']=[];
        $data['current_date'] = date('Y-m-d');
        $data['current_year_month'] = date('Y-m');
        $data['current_year'] = date('Y');
        $data['current_month'] = date('m');
        $dateObj = DateTime::createFromFormat('!m', $data['current_month']);
        $data['current_month_name'] = $dateObj->format('F');
        $data['first_date'] = $data['current_year_month'] . "-" . "01";
        $data['first_date_time'] = new DateTime($data['first_date']);
        $data['first_day_number'] = (int) $data['first_date_time']->format('N') + 2;
        $data['first_day_number'] = ($data['first_day_number'] > 7) ? ($data['first_day_number'] - 7) : $data['first_day_number'];
        $data['days'] =cal_days_in_month(CAL_GREGORIAN,$data['current_month'],$data['current_year']);
        $data['last_date'] = $data['current_year_month'] . "-" . $data['days'];
        $data['public_holidays'] = [];
        $holiday_sql = "SELECT public_holidays.*, holidays.`name` FROM `public_holidays` INNER JOIN holidays ON holidays.id=public_holidays.holiday_id WHERE( `from_date` BETWEEN '$data[first_date]' AND '$data[last_date]' OR `to_date` BETWEEN '$data[first_date]' AND '$data[last_date]')";
        $holiday_sql_records = DB::select($holiday_sql);
        foreach ($holiday_sql_records as $record){
            $begin = new DateTime($record->from_date);
            $end = new DateTime($record->to_date);
            for($i = $begin; $i <= $end; $i->modify('+1 day')){
                if($data['current_month']==$i->format("m")){
                    if(!array_key_exists($i->format("d"),$data['public_holidays'])){
                        $data['public_holidays'][(int) $i->format("d")]['holiday_id']=$record->id;
                        $data['public_holidays'][(int) $i->format("d")]['holiday_name']=$record->name;
                        $data['monthly_holiday'][(int) $i->format("d")]['public']['name']=$record->name;
                        $data['monthly_holiday'][(int) $i->format("d")]['public']['date']=$i->format("jS F, Y");
                    }
                }
            }
        }
        $data['relax_days'] = [];
        $relax_day_sql = "SELECT * FROM `relax_day` WHERE `date` BETWEEN '$data[first_date]' AND '$data[last_date]' AND `deleted_at` IS NULL";
        $relax_result = DB::select($relax_day_sql);
        foreach ($relax_result as $result){
            $d = (int) explode('-',$result->date)[2];
            $data['relax_days'][$d] = $result->note;
            $data['monthly_holiday'][$d]['relax']['name']='Relax Day';
            $data['monthly_holiday'][$d]['relax']['note']=$result->note;
            $data['monthly_holiday'][$d]['relax']['date']=date("jS F, Y",strtotime($result->date));
        }
        ksort($data['monthly_holiday']);
        return view("relax-day.add-edit",$data);
    }

    public function getModalForm(Request $request){
        if(strlen($request->day_number)==1){
            $request->day_number = '0'.$request->day_number;
        }
        $data['date'] = $request->current_year.'-'.$request->current_month.'-'.$request->day_number;
        $data['date_value'] = $request->start_date;
        $data['min_date'] = $request->start_date;
        $data['date_range'] = $request->start_date.' - '.$request->start_date;
        $data['end_date'] = $request->end_date;
        $data['holidays'] = Holiday::all();
        $data['public_holiday_records'] = PublicHoliday::where("from_date", "<=", $data['date'])
            ->where("to_date", ">=", $data['date'])
            ->first();
        $data['start_date_in_format'] = $data['date_value'];
        $data['max_date'] = date('m/d/Y',strtotime('+30 days',strtotime($data['date_value'])));
        $data['to_date_in_format'] = $data['date_value'];
        if($data['public_holiday_records']){
            $data['start_date_in_format'] = date('m/d/Y',strtotime($data['public_holiday_records']->from_date));
            $data['min_date'] = $data['start_date_in_format'];
            $data['max_date'] = date('m/d/Y',strtotime('+30 days',strtotime($data['public_holiday_records']->from_date)));
            $data['to_date_in_format'] = date('m/d/Y',strtotime($data['public_holiday_records']->to_date));
            $data['date_range'] = $data['start_date_in_format'].' - '.$data['to_date_in_format'];
        }
        $data['relax_days'] = RelaxDay::where("date", "=", $data['date'])->get();
        $filter_obj = new FilterController();
        $departmentIds = $filter_obj->getDepartmentIds();
        $officeDepartments = Department::select("departments.id", "departments.office_division_id", "departments.name", "office_divisions.name AS office_division_name")
            ->join('office_divisions','office_divisions.id','=','departments.office_division_id')
            ->whereIn('departments.id',$departmentIds)
            ->orderBy('departments.office_division_id')
            ->get();
        $data['officeDivisions']=[];
        $data['officeDepartments']=[];
        foreach ($officeDepartments as $officeDepartment){
            $data['officeDivisions'][$officeDepartment->office_division_id]=$officeDepartment->office_division_name;
            $data['officeDepartments'][$officeDepartment->office_division_id][$officeDepartment->id] = $officeDepartment->name;
        }
        $html = '';
        $html .= \Illuminate\Support\Facades\View::make('relax-day.details.modal', $data);
        $data['html'] = $html;
        return response()->json($data);
    }

    public function getCalender(Request $request){
        $data['monthly_holiday']=[];
        $year_month = $request->year_month;
        $year_month_arr = explode('-',$request->year_month);
        $data['current_date'] = date('Y-m-d');
        $data['current_year_month'] = $year_month;
        $data['current_year'] = $year_month_arr[0];
        $data['current_month'] = $year_month_arr[1];
        $dateObj = DateTime::createFromFormat('!m', $data['current_month']);
        $data['current_month_name'] = $dateObj->format('F');
        $data['first_date'] = $data['current_year_month'] . "-" . "01";
        $data['first_date_time'] = new DateTime($data['first_date']);
        $data['first_day_number'] = (int) $data['first_date_time']->format('N') + 2;
        $data['first_day_number'] = ($data['first_day_number'] > 7) ? ($data['first_day_number'] - 7) : $data['first_day_number'];
        $data['days'] =cal_days_in_month(CAL_GREGORIAN,$data['current_month'],$data['current_year']);
        $data['last_date'] = $data['current_year_month'] . "-" . $data['days'];
        $data['public_holidays'] = [];
        $holiday_sql = "SELECT public_holidays.*, holidays.`name` FROM `public_holidays` INNER JOIN holidays ON holidays.id=public_holidays.holiday_id WHERE( `from_date` BETWEEN '$data[first_date]' AND '$data[last_date]' OR `to_date` BETWEEN '$data[first_date]' AND '$data[last_date]')";
        $holiday_sql_records = DB::select($holiday_sql);
        foreach ($holiday_sql_records as $record){
            $begin = new DateTime($record->from_date);
            $end = new DateTime($record->to_date);
            for($i = $begin; $i <= $end; $i->modify('+1 day')){
                if($data['current_month']==$i->format("m")){
                    if(!array_key_exists($i->format("d"),$data['public_holidays'])){
                        $data['public_holidays'][(int) $i->format("d")]['holiday_id']=$record->id;
                        $data['public_holidays'][(int) $i->format("d")]['holiday_name']=$record->name;
                        $data['monthly_holiday'][(int) $i->format("d")]['public']['name']=$record->name;
                        $data['monthly_holiday'][(int) $i->format("d")]['public']['date']=$i->format("jS F, Y");
                    }
                }
            }
        }
        $data['relax_days'] = [];
        $relax_day_sql = "SELECT * FROM `relax_day` WHERE `date` BETWEEN '$data[first_date]' AND '$data[last_date]' AND `deleted_at` IS NULL";
        $relax_result = DB::select($relax_day_sql);
        foreach ($relax_result as $result){
            $d = (int) explode('-',$result->date)[2];
            $data['relax_days'][$d] = $result->note;
            $data['monthly_holiday'][$d]['relax']['name']='Relax Day';
            $data['monthly_holiday'][$d]['relax']['note']=$result->note;
            $data['monthly_holiday'][$d]['relax']['date']=date("jS F, Y",strtotime($result->date));
        }
        ksort($data['monthly_holiday']);
        $html = '';
        $html .= \Illuminate\Support\Facades\View::make('relax-day.details.calender', $data);
        $data['html'] = $html;
        return response()->json($data);
    }
    public function store(RequestRelaxDay $request)
    {
        try {
            $box = $request->all();
            $post_data =  array();
            parse_str($box['values'], $post_data);
            $date = date('Y-m-d',strtotime($post_data['date_value']));
            $rule['date_value'] = 'required';
            if(isset($post_data['public_holiday_radio']) && $post_data['public_holiday_radio']==1){
                $rule['public_holiday_id'] = 'required';
                $rule['daterange'] = 'required';
            }
            if(isset($post_data['relax_day_radio']) && $post_data['relax_day_radio']==1){
                $rule['department'] = 'required|array|min:1';
                $rule['department.*'] = 'required|integer|distinct|min:1';
            }
            $validator = Validator::make($post_data, $rule);
            if ($validator->passes()) {
                DB::beginTransaction();
                if(isset($post_data['public_holiday_record_id']) && !empty($post_data['public_holiday_record_id'])){
                    $holiday_record = PublicHoliday::find($post_data['public_holiday_record_id']);
                }
                if(isset($post_data['public_holiday_radio']) && $post_data['public_holiday_radio']==1){
                    $range_array = explode(' - ',$post_data['daterange']);
                    $from_date = date('Y-m-d',strtotime($range_array[0]));
                    $to_date = date('Y-m-d',strtotime($range_array[1]));
                    if(isset($holiday_record) && $holiday_record){
                        $holiday_record->holiday_id = $post_data['public_holiday_id'];
                        $holiday_record->from_date = $from_date;
                        $holiday_record->to_date = $to_date;
                        $holiday_record->save();
                    }else{
                        $new_public_holiday = [
                            'holiday_id'=>$post_data['public_holiday_id'],
                            'from_date'=>$from_date,
                            'to_date'=>$to_date
                        ];
                        PublicHoliday::create($new_public_holiday);
                    }
                }else{
                    if(isset($holiday_record) && $holiday_record){
                        $holiday_record->delete();
                    }
                }
                if(isset($post_data['relax_record_id']) && !empty($post_data['relax_record_id'])){
                    $relax_record = RelaxDay::whereIn('id',$post_data['relax_record_id'])->get();
                }
                if(isset($post_data['relax_day_radio']) && $post_data['relax_day_radio']==1){
                    $delete_relax_ids = [];
                    $update_relax_ids = [];
                    $update_relax_departments = [];
                    $new_relax_day=[];
                    if(isset($relax_record) && $relax_record){
                        foreach($relax_record as $r_record){
                            if(!in_array($r_record->department_id,$post_data['department'])){
                                $delete_relax_ids[]=$r_record->id;
                            }else{
                                $update_relax_ids[]=$r_record->id;
                                $update_relax_departments[]=$r_record->department_id;
                            }
                        }
                    }
                    if($delete_relax_ids){
                        AssignRelaxDay::whereIn('relax_day_id',$delete_relax_ids)->delete();
                        RelaxDay::whereIn('id',$delete_relax_ids)->delete();
                    }
                    if($update_relax_ids){
                        RelaxDay::whereIn('id',$update_relax_ids)->update(['note'=>$post_data['note']]);
                    }
                    $date_time = date('Y-m-d H:i:s');
                    foreach($post_data['department'] as $depart){
                        if(!in_array($depart,$delete_relax_ids) && !in_array($depart,$update_relax_departments)){
                            $new_relax_day[] = [
                                'department_id'=>$depart,
                                'date'=>$date,
                                'note'=>$post_data['note'],
                                'created_by'=>Auth::id(),
                                'created_at'=>$date_time
                            ];
                        }
                    }
                    if($new_relax_day){
                        RelaxDay::insert($new_relax_day);
                    }
                }else{
                    if(isset($relax_record) && $relax_record){
                        AssignRelaxDay::whereIn('relax_day_id',$post_data['relax_record_id'])->delete();
                        RelaxDay::whereIn('id',$post_data['relax_record_id'])->delete();
                    }
                }
                DB::commit();
                return response()->json(['status'=>true,'message'=>'Holiday saved successfully!']);
            }else{
                foreach($validator->errors()->all() as $error){
                    return response()->json(['status'=>false,'message'=>$error]);
                }
                return response()->json(['status'=>false,'message'=>'Something went wrong!']);
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json(['status'=>false,'message'=>$exception->getMessage()]);
        }
    }
}
