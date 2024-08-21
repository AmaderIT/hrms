<?php

namespace App\Helpers;

use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\Promotion;
use App\Models\Roster;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Common
{
    public static function modifyPromotionEmploymentTypeEmployeeWise(int $userID)
    {
        try {
            $errorMsg = "";
            $employmentTypes = Promotion::whereDate('promoted_date', '<=', date('Y-m-d'))
                ->where('user_id', $userID)
                ->orderBy("promoted_date", "ASC")
                ->select('id', 'type', 'employment_type', 'promoted_date')
                ->get();
            $employmentTypeArr = [];
            $sl = 1;
            foreach ($employmentTypes as $employmentType) {
                $employmentTypeArr[$sl] = $employmentType['type'];
                if ($sl == 1 && !in_array($employmentType['type'], ['Join']) && in_array($employmentType['type'], array_keys(Promotion::employmentType()))) {
                    Promotion::where('id', $employmentType['id'])->update(['type' => Promotion::TYPE_JOIN]);
                }
                if (empty($employmentType['employment_type']) && in_array($employmentType['type'], array_keys(Promotion::employmentType()))) {
                    Promotion::where('id', $employmentType['id'])->update(['employment_type' => $employmentType['type']]);
                } elseif (empty($employmentType['employment_type']) && !in_array($employmentType['type'], array_keys(Promotion::employmentType()))) {
                    for ($incK = $sl; $incK <= $sl; $incK--) {
                        if (!empty($employmentTypeArr[$incK]) && array_key_exists($employmentTypeArr[$incK], Promotion::employmentType())) {
                            $previousStatePosition = $employmentTypeArr[$incK];
                            Promotion::where('id', $employmentType['id'])->update(['employment_type' => $previousStatePosition]);
                            break;
                        }
                        if ($incK == 1) {
                            break;
                        }
                    }
                }
                $sl++;
            }
        } catch (\Exception $ex) {
            $errorMsg = $ex->getMessage();
        }
        return [
            'errorMsg' => $errorMsg
        ];
    }

    public static function calculateLeaveBalance($departmentID, $actionDate)
    {
        $actionYear = date("Y", strtotime($actionDate));
        # Leave Allocations for Current Year on the associated Department
        $leaveAllocation = LeaveAllocation::join('leave_allocation_details', 'leave_allocation_details.leave_allocation_id', '=', 'leave_allocations.id')
            ->where(["year" => $actionYear, 'department_id' => $departmentID])
            ->get();
        $department_wise_leave = [];
        foreach ($leaveAllocation as $leave) {
            $department_wise_leave[$leave->leave_type_id] = $leave->total_days;
        }
        $initialLeave = [];
        $initialLeaveBalance = [];
        $currentLeave = [];
        $currentLeaveBalance = [];
        $year_month_date = explode('-', $actionDate);
        $totalInitialLeave = 0;
        $totalCurrentLeave = 0;
        foreach ($department_wise_leave as $leave_type_id => $balance) {
            $initialLeave['leave_type_id'] = $leave_type_id;
            if ($year_month_date[0] < $actionYear) {
                $initialLeave['total_days'] = $balance;
            } else {
                if ($year_month_date[1] < 12) {
                    $calculate_month = 12 - $year_month_date[1];
                    $leave_amount_for_month = ($balance * $calculate_month) / 12;
                } else {
                    $leave_amount_for_month = 0;
                }
                $calculate_day = (30 - $year_month_date[2]) + 1;
                if ($calculate_day >= 15) {
                    $per_month_avg_leave = $balance / 12;
                    $leave_amount_for_day = ($per_month_avg_leave * $calculate_day) / 30;
                } else {
                    $leave_amount_for_day = 0;
                }
                $total_leave = $leave_amount_for_month + $leave_amount_for_day;
                $integer_leave = floor($total_leave);
                $fraction_leave = $total_leave - $integer_leave;
                if ($fraction_leave > .5) {
                    $fraction_leave = 1;
                } else {
                    if ($fraction_leave > 0) {
                        $fraction_leave = 0.5;
                    }
                }
                $initialLeave['total_days'] = $integer_leave + $fraction_leave;
            }
            $currentLeave['leave_type_id'] = $leave_type_id;
            $used = 0;
            $currentLeave['total_days'] = ($initialLeave['total_days'] - $used) < 0 ? 0 : ($initialLeave['total_days'] - $used);
            $totalInitialLeave = $totalInitialLeave + $initialLeave['total_days'];
            $totalCurrentLeave = $totalCurrentLeave + $currentLeave['total_days'];
            $initialLeaveBalance[] = $initialLeave;
            $currentLeaveBalance[] = $currentLeave;
        }
        return [
            'initial_leave' => json_encode($initialLeaveBalance),
            'total_initial_leave' => $totalInitialLeave,
            'leaves' => json_encode($currentLeaveBalance),
            'total_leaves' => $totalCurrentLeave,
            'year' => $actionYear
        ];
    }

    public static function getDesignations($search)
    {
        if ($search == '') {
            $designations = \App\Models\Designation::orderby('title', 'asc')->select('id', 'title')->limit(30)->get();
        } else {
            $designations = \App\Models\Designation::orderby('title', 'asc')->select('id', 'title')
                ->where('title', 'like', '%' . $search . '%')
                ->get();
        }
        $response = array();
        foreach ($designations as $designation) {
            $response[] = array(
                "id" => $designation->id,
                "text" => $designation->title,
            );
        }
        return response()->json($response);
    }

    public static function syncLeaveBalanceEmployeeWise($empID, $departmentID, $rejoinDate)
    {
        try {
            $errorMsg = "";
            $current_year = date("Y", strtotime($rejoinDate));
            $users = DB::select("SELECT users.id, users.`name`, users.email,users.fingerprint_no,prm.promoted_date,
                       prm.department_id, prm.office_division_id FROM `users`
                       INNER JOIN promotions AS prm ON prm.user_id = users.id
                       AND prm.id =( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id)
                       WHERE users.id=$empID");

            $leave_consumed = DB::select("SELECT user_id,leave_type_id, (SUM( number_of_days)-(SUM( IFNULL(number_of_unpaid_days, 0) )+SUM( IFNULL(number_of_paid_days, 0) ))) as leave_balance FROM `leave_requests` WHERE status = 1 AND YEAR( from_date ) = '$current_year' and user_id=$empID GROUP BY leave_type_id");

            # Leave Allocations for Current Year on the associated Department
            $leaveAllocation = LeaveAllocation::join('leave_allocation_details', 'leave_allocation_details.leave_allocation_id', '=', 'leave_allocations.id')
                ->where(["year" => $current_year, 'department_id' => $departmentID])
                ->get();
            $department_wise_leave = [];
            foreach ($leaveAllocation as $leave) {
                $department_wise_leave[$leave->department_id][$leave->leave_type_id] = $leave->total_days;
            }
            if (empty($department_wise_leave) && count($department_wise_leave) <= 0) {
                throw new \Exception("Leave allocation is not found for year " . $current_year);
            }
            $leave_consume_by_user = [];
            foreach ($leave_consumed as $l_c) {
                $leave_consume_by_user[$l_c->user_id][$l_c->leave_type_id] = $l_c->leave_balance;
            }

            foreach ($users as $user) {
                $initialLeave = [];
                $initialLeaveBalance = [];
                $currentLeave = [];
                $currentLeaveBalance = [];
                $year_month_date = explode('-', $rejoinDate);
                $totalInitialLeave = 0;
                $totalCurrentLeave = 0;
                foreach ($department_wise_leave[$user->department_id] as $leave_type_id => $balance) {
                    $initialLeave['leave_type_id'] = $leave_type_id;
                    if ($year_month_date[0] < $current_year) {
                        $initialLeave['total_days'] = $balance;
                    } else {
                        if ($year_month_date[1] < 12) {
                            $calculate_month = 12 - $year_month_date[1];
                            $leave_amount_for_month = ($balance * $calculate_month) / 12;
                        } else {
                            $leave_amount_for_month = 0;
                        }
                        $calculate_day = (30 - $year_month_date[2]) + 1;
                        if ($calculate_day >= 15) {
                            $per_month_avg_leave = $balance / 12;
                            $leave_amount_for_day = ($per_month_avg_leave * $calculate_day) / 30;
                        } else {
                            $leave_amount_for_day = 0;
                        }
                        $total_leave = $leave_amount_for_month + $leave_amount_for_day;
                        $integer_leave = floor($total_leave);
                        $fraction_leave = $total_leave - $integer_leave;
                        if ($fraction_leave > .5) {
                            $fraction_leave = 1;
                        } else {
                            if ($fraction_leave > 0) {
                                $fraction_leave = 0.5;
                            }
                        }
                        $initialLeave['total_days'] = $integer_leave + $fraction_leave;
                    }
                    $currentLeave['leave_type_id'] = $leave_type_id;
                    $used = $leave_consume_by_user[$user->id][$leave_type_id] ?? 0;
                    $currentLeave['total_days'] = ($initialLeave['total_days'] - $used) < 0 ? 0 : ($initialLeave['total_days'] - $used);
                    $totalInitialLeave = $totalInitialLeave + $initialLeave['total_days'];
                    $totalCurrentLeave = $totalCurrentLeave + $currentLeave['total_days'];
                    $initialLeaveBalance[] = $initialLeave;
                    $currentLeaveBalance[] = $currentLeave;
                }
                return [
                    'user_id' => $user->id,
                    'initial_leave' => json_encode($initialLeaveBalance),
                    'total_initial_leave' => $totalInitialLeave,
                    'leaves' => json_encode($currentLeaveBalance),
                    'total_leaves' => $totalCurrentLeave,
                    'year' => $current_year
                ];
            }
        } catch (\Exception $ex) {
            $errorMsg = $ex->getMessage();
        }
        if (!empty($errorMsg)) {
            return ['errorMsg' => $errorMsg];
        }
    }

    public static function checkEmployeeDeviceDataExistsOrNot($fingerPrintNo)
    {
        try {
            # Get JWT Token
            $jwtToken = self::getToken();
            $http_response_header = array(
                "Content-Type" => "application/json",
                "Authorization" => "JWT " . $jwtToken
            );
            $url = env("ZKTECO_SERVER_PORT") . "/personnel/api/employee/?emp_code=".$fingerPrintNo;
            $deviceEmployeeInfo = Http::withHeaders($http_response_header)->get($url);
            $deviceEmployeeInfo = $deviceEmployeeInfo->json()["data"];
            Log::info("#API Response Existing Employee Start#");
            Log::info($deviceEmployeeInfo);
            Log::info("#API Response Existing Employee Response End#");
            if (count($deviceEmployeeInfo) == 0) {
                $deviceEmployeeInfo = false;
            } else {
                $deviceEmployeeInfo = true;
            }
        } catch (\Exception $exception) {
            $deviceEmployeeInfo = false;
        }

        return $deviceEmployeeInfo;
    }

    /**
     * @return Response|mixed|null
     */
    protected static function getToken()
    {
        try {
            $http_response_header = array(
                "Content-Type" => "application/json"
            );
            $url = env("ZKTECO_SERVER_PORT") . "/jwt-api-token-auth/";
            $payLoad = array(
                "username" => env("ZKTECO_BIOTIME_USERNAME"),
                "password" => env("ZKTECO_BIOTIME_PASSWORD")
            );

            $response = Http::withHeaders($http_response_header)->post($url, $payLoad);
            $jwtToken = $response->json()["token"];
        } catch (\Exception $exception) {
            $jwtToken = null;
        }

        return $jwtToken;
    }

    public static function findOutWorkSlots($fromDate,$leaveRequestType,$user_id){
        $getRosterDatas = Roster::with("workSlot")->whereDate('active_date', $fromDate)->where(['user_id'=>$user_id,'status'=>Roster::STATUS_APPROVED])->orderBy('active_date','ASC')->first();
        if(!$getRosterDatas){
            $getRosterDatas = Roster::with("workSlot")->whereDate('active_date', $fromDate)->where(['department_id'=>auth()->user()->currentPromotion->department_id,'status'=>Roster::STATUS_APPROVED])->orderBy('active_date','ASC')->first();
        }
        $additionalDay = 0;
        if($getRosterDatas) {
            $s = date("H:i:s", strtotime($getRosterDatas->workSlot->start_time));
            $e = date("H:i:s", strtotime($getRosterDatas->workSlot->end_time));

            if($getRosterDatas->workSlot->is_flexible){
                $s = date("Y-m-d H:i:s", strtotime($fromDate.' '.$getRosterDatas->workSlot->start_time));
                $e = date("Y-m-d H:i:s", strtotime($fromDate.' '.$getRosterDatas->workSlot->start_time . " +". $getRosterDatas->workSlot->total_work_hour ." hours"));
            }
            if ($s > $e) {
                $additionalDay = 1;
            }
            if(!$getRosterDatas->workSlot->is_flexible){
                $slotStartTime = date("Y-m-d H:i:s", strtotime($fromDate.' '.$getRosterDatas->workSlot->start_time));
                $slotEndTime = date("Y-m-d H:i:s", strtotime($fromDate.' '.$getRosterDatas->workSlot->end_time . " +" . $additionalDay . " days"));
            }else{
                $slotStartTime = $s;
                $slotEndTime = $e;
            }
        }else{

            $user = User::find($user_id);
            $currentPromotion = $user->currentPromotion->workSlot;
            $s = date("H:i:s", strtotime($currentPromotion->start_time));
            $e = date("H:i:s", strtotime($currentPromotion->end_time));
            if ($s > $e) {
                $additionalDay = 1;
            }
            $slotStartTime = date("Y-m-d H:i:s", strtotime($fromDate.' '.$currentPromotion->start_time));
            $slotEndTime = date("Y-m-d H:i:s", strtotime($fromDate.' '.$currentPromotion->end_time . " +" . $additionalDay . " days"));
        }

        $countStartTime = strtotime($slotStartTime);
        $countEndTime = strtotime($slotEndTime);
        $difference = round(abs($countEndTime - $countStartTime) / 60,2);
        $calculateHalfTime = round($difference / 2,2);

        $firstSlotStart = date('Y-m-d H:i:s',strtotime($slotStartTime));
        $firstSlotEnd = date("Y-m-d H:i:s",strtotime("+".$calculateHalfTime." minutes", strtotime($slotStartTime)));

        $secondSlotStart = date('Y-m-d H:i:s',strtotime($firstSlotEnd));
        $secondSlotEnd = date("Y-m-d H:i:s",strtotime("+".$calculateHalfTime." minutes", strtotime($firstSlotEnd)));

        $firstSlotStartFormat = date("h:i a",strtotime($firstSlotStart)).'('.date("Y-m-d",strtotime($firstSlotStart)).')';
        $firstSlotEndFormat = date("h:i a",strtotime($firstSlotEnd)).'('.date("Y-m-d",strtotime($firstSlotEnd)).')';
        $secondSlotStartFormat = date("h:i a",strtotime($secondSlotStart)).'('.date("Y-m-d",strtotime($secondSlotStart)).')';
        $secondSlotEndFormat = date("h:i a",strtotime($secondSlotEnd)).'('.date("Y-m-d",strtotime($secondSlotEnd)).')';
        $timeSlots=[
            '1'=>'1st Half ['.$firstSlotStartFormat.'-'.$firstSlotEndFormat.']',
            '2'=>'2nd Half ['.$secondSlotStartFormat.'-'.$secondSlotEndFormat.']',
            '3' => date("Y-m-d",strtotime($firstSlotStart)),
            '4' => date("Y-m-d",strtotime($firstSlotEnd)),
            '5' => date("Y-m-d",strtotime($secondSlotStart)),
            '6' => date("Y-m-d",strtotime($secondSlotEnd)),
            '7'=>[$firstSlotStart,$firstSlotEnd],
            '8'=>[$secondSlotStart,$secondSlotEnd],
        ];
        return $timeSlots;
        }

    public function getEmployeeLateConsideredWithRosterHalfDays($todayTimeIn,$lateCountTime,$punchDate,$item){
        $getRosterDatas = Roster::with("workSlot")->whereDate('active_date', $punchDate)->where(['user_id' => $item->id, 'status' => Roster::STATUS_APPROVED])->orderBy('active_date', 'ASC')->get();
        $rosterWithWorkSlots = [];
        if ($getRosterDatas->count() > 0) {
            foreach ($getRosterDatas as $getRosterData) {
                $rosterWithWorkSlots[date('Y-m-d', strtotime($getRosterData['active_date']))] = !empty($getRosterData->workSlot->late_count_time) ? date("H:i:s", strtotime($getRosterData->workSlot->late_count_time)) : "";
            }
        }
        $leaveRequestHalfDayDatas = LeaveRequest::whereDate("from_date", ">=", $punchDate)->whereDate("to_date", "<=", $punchDate)->where(['status' => LeaveRequest::STATUS_APPROVED, 'half_day' => 1, 'user_id' => $item->id])->orderBy("id", "desc")->get();
        $leaveRequestHalfDayDataArr = [];
        if ($leaveRequestHalfDayDatas->count() > 0) {
            foreach ($leaveRequestHalfDayDatas as $leaveRequestHalfDayData) {
                $leaveRequestHalfDayDataArr[date('Y-m-d', strtotime($leaveRequestHalfDayData['from_date']))] = [!empty($leaveRequestHalfDayData['from_date']) ? date('Y-m-d', strtotime($leaveRequestHalfDayData['from_date'])) : ""];
            }
        }
        if (!empty($leaveRequestHalfDayDataArr) && array_key_exists($punchDate, $leaveRequestHalfDayDataArr)) {
            return false;
        }
        if (!empty($rosterWithWorkSlots[$punchDate]) && array_key_exists($punchDate, $rosterWithWorkSlots) && $todayTimeIn > $rosterWithWorkSlots[$punchDate]) {
            return $item;
        }
        if (empty($rosterWithWorkSlots[$punchDate]) && !empty($lateCountTime) && $todayTimeIn > $lateCountTime) {
            return $item;
        }
    }
}
