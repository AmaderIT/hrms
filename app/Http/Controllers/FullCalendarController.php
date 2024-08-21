<?php

namespace App\Http\Controllers;

use App\Models\AssignRelaxDay;
use App\Models\PublicHoliday;
use App\Models\RelaxDay;
use App\Models\User;
use App\Models\WeeklyHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Roster;
use Carbon\Carbon;

class FullCalendarController extends Controller
{
    public function index(Request $request)
    {
        try {
            $authUser = auth()->user();
            if(empty($authUser->id)){
                throw new \Exception("Auth User ID Not Found!!!");
            }
            $upcomingHolidays = PublicHoliday::with("holiday")
                ->orderBy("from_date", "asc")
                ->get();
            $events = [];
            if (!empty($upcomingHolidays) && count($upcomingHolidays) > 0) {
                foreach ($upcomingHolidays as $upHoliday) {
                    $arrHoliday['title'] = $upHoliday->holiday->name;
                    $arrHoliday['start'] = !empty($upHoliday->from_date) ? date('Y-m-d', strtotime($upHoliday->from_date)) : "";
                    $arrHoliday['end'] = !empty($upHoliday->to_date) ? date('Y-m-d', strtotime('+1 day', strtotime($upHoliday->to_date))) : "";
                    $events[] = $arrHoliday;
                }
            }
            $todayDate = date("Y-m-d");
            $departmentIDs = User::with([
                "currentPromotion" => function ($query) {
                    $query->with("department", "officeDivision");
                },
                "currentStatus"
            ])
                ->join("promotions", function ($join) use ($todayDate) {
                    $join->on('promotions.user_id', 'users.id');
                    $join->on('promotions.id', DB::raw("(select max(p.id) from promotions p where p.user_id = users.id and p.promoted_date <= '" . $todayDate . "' limit 1)"));
                })->select([
                    "users.id",
                    "office_division_id",
                    "department_id"
                ])->where(['users.id' => $authUser->id])->pluck('department_id');
            if (!empty($departmentIDs) && count($departmentIDs) > 0) {
                $relaxDayResult = RelaxDay::whereIn('department_id', $departmentIDs)->orderByDesc("id")->get();
            }
            if (!empty($relaxDayResult) && count($relaxDayResult) > 0) {
                foreach ($relaxDayResult as $rDay) {
                    $arrRelaxDay['start'] = !empty($rDay->date) ? date('Y-m-d', strtotime($rDay->date)) : "";
                    $arrRelaxDay['end'] = !empty($rDay->date) ? date('Y-m-d', strtotime('+1 day', strtotime($rDay->date))) : "";
                    //$arrRelaxDay['title'] = 'relax_day';
                    $arrRelaxDay['display'] = 'background';
                    $arrRelaxDay['color'] = 'rgb(143, 223, 130)';
                    $arrRelaxDay['classNames'] = 'bysl-relax-day';
                    $events[] = $arrRelaxDay;
                }
            }

            $assignRelaxDays = AssignRelaxDay::with('relaxDate')->where(['user_id' => $authUser->id, 'approval_status' => AssignRelaxDay::APPROVAL_CONFIRMED])->get();
            if (!empty($assignRelaxDays) && count($assignRelaxDays) > 0) {
                foreach ($assignRelaxDays as $arDay) {
                    $arraRelaxDay['start'] = !empty($arDay->relaxDate->date) ? date('Y-m-d', strtotime($arDay->relaxDate->date)) : "";
                    $arraRelaxDay['end'] = !empty($arDay->relaxDate->date) ? date('Y-m-d', strtotime('+1 day', strtotime($arDay->relaxDate->date))) : "";
                    //$arrRelaxDay['title'] = 'approve_relax_day';
                    //$arraRelaxDay['display'] = 'background';
                    $arraRelaxDay['classNames'] = 'bysl-relax-day';
                    $arraRelaxDay['color'] = 'rgb(143, 223, 130)';
                    $events[] = $arraRelaxDay;
                }
            }

            $weekendHolidays = [];
            if (!empty($departmentIDs) && count($departmentIDs) > 0) {
                $weekendHolidays = WeeklyHoliday::select(['days', 'effective_date', 'end_date'])->whereIn('department_id', $departmentIDs)->orderByDesc("id")->get();
            }
            $weekDays = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
            if (!empty($weekendHolidays) && count($weekendHolidays) > 0) {
                foreach ($weekendHolidays as $wDay) {
                    $weekDayData = json_decode($wDay->days, true);
                    if (!empty($weekDayData) && count($weekDayData) > 0) {
                        $weekDayIndex = [];
                        foreach ($weekDayData as $weekDay) {
                            $weekDayIndex[] = $weekDays[$weekDay];
                        }
                    }
                    $weekendHoliDay['startRecur'] = date('Y-m-d', strtotime($wDay->effective_date));
                    if (empty($wDay->end_date)) {
                        //date('Y-m-d',strtotime('last day of december',strtotime($wDay->effective_date)));
                    }
                    $weekendHoliDay['endRecur'] = !empty($wDay->end_date) ? date('Y-m-d', strtotime('+1 day', strtotime($wDay->end_date))) : "";
                    $weekendHoliDay['daysOfWeek'] = $weekDayIndex;
                    $weekendHoliDay['display'] = 'background';
                    $weekendHoliDay['classNames'] = 'bysl-weekend-day';
                    //$weekendHoliDay['color'] = '#ff000099';
                    $events[] = $weekendHoliDay;
                }
            }
            // roster
            $work_slot_obj = auth()->user()->currentPromotion->workSlot;
            $userRosterData = $this->getRosters($request);
            $departmentRosterData = $this->getRosters($request, 2);
            $data = $work_slot = [];

            if( isset($departmentRosterData['events']) && !empty($departmentRosterData['events']) && isset($userRosterData['events']) && !empty($userRosterData['events']) ) {
                $data = array_merge($departmentRosterData['events'], $userRosterData['events']);
            } else if( isset($departmentRosterData['events']) && !empty($departmentRosterData['events']) ) {
                $data = $departmentRosterData['events'];
            } else if (isset($userRosterData['events']) && !empty($userRosterData['events'])) {
                $data = $userRosterData['events'];
            }
            $events = array_merge($events, $data);
            $events = array_values($events);

            $work_slot['default'] = ['id' => $work_slot_obj->id, 'title' => $work_slot_obj->title, 'start' => $work_slot_obj->start_time, 'end' => $work_slot_obj->end_time, 'color' => $this->getWorkSlotColors($work_slot_obj->id)];

            if( isset($departmentRosterData['work_slot']) && !empty($departmentRosterData['work_slot']) && isset($userRosterData['work_slot']) && !empty($userRosterData['work_slot']) ) {
                $work_slot = array_merge($work_slot, $departmentRosterData['work_slot'], $userRosterData['work_slot']);
            } else if( isset($departmentRosterData['work_slot']) && !empty($departmentRosterData['work_slot']) ) {
                $work_slot =  array_merge($work_slot, $departmentRosterData['work_slot']);
            } else if (isset($userRosterData['work_slot']) && !empty($userRosterData['work_slot'])) {
                $work_slot =  array_merge($work_slot, $userRosterData['work_slot']);
            }

            if(!empty($events)){
                $events[1]['work_slots'] = $work_slot;
            }

            return response()->json($events);

        } catch (\Exception $exp) {
            Log::error("###FullCalendar Exception Start###");
            //$msg = "FullCalendar Error by ID: - " . auth()->user()->id . " Office ID: - " . auth()->user()->fingerprint_no . " - at - " . date('Y-m-d h:i:sA', strtotime(now()));
            Log::error("Exception Msg ".$exp->getMessage());
            //Log::error($msg);
            Log::error("###End###");
        }
    }

    public function calendarDaysStatus(Request $request)
    {
        try {
            $authUser = auth()->user();
            if(empty($authUser->id)){
                throw new \Exception("Auth User ID Not Found!!!");
            }
            $daystatus = [];
            $holidays = PublicHoliday::select(
                'id',
                'from_date',
                'to_date',
                DB::raw("'holi_day' as type")
            );
            $holidays = $holidays->where(function($q) use ($request) {
                $q = $q->whereBetween('from_date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
                $q = $q->whereBetween('to_date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
            });
            $holidays = $holidays->orderBy("from_date", "asc")->get();

            if($holidays->isNotEmpty()) {
                foreach ($holidays as $item) {
                    $start = Carbon::parse($item->from_date);
                    $end = Carbon::parse($item->to_date);
                    if($start->toDateString() === $end->toDateString()){
                        $daystatus[] = $start->toDateString();
                    } else {
                        while ($start->lte($end)) {
                            $daystatus[] = $start->toDateString();
                            $start->addDay();
                        }
                    }
                }
            }

            $todayDate = date("Y-m-d");
            $departmentIDs = User::with([
                "currentPromotion" => function ($query) {
                    $query->with("department", "officeDivision");
                },
                "currentStatus"
            ])->join("promotions", function ($join) use ($todayDate) {
                    $join->on('promotions.user_id', 'users.id');
                    $join->on('promotions.id', DB::raw("(select max(p.id) from promotions p where p.user_id = users.id and p.promoted_date <= '" . $todayDate . "' limit 1)"));
            })->select([
                "users.id",
                "office_division_id",
                "department_id"
            ])->where(['users.id' => $authUser->id])->pluck('department_id');
            if (!empty($departmentIDs) && count($departmentIDs) > 0) {
                $relaxDays = RelaxDay::whereIn('department_id', $departmentIDs);
                $relaxDays = $relaxDays->select(
                    'id',
                    'date as start',
                    'date as end',
                    DB::raw("'relax_day' as type")
                );
                $relaxDays = $relaxDays->where(function($q) use ($request) {
                    $q = $q->whereBetween('date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
                    $q = $q->whereBetween('date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
                });
                $relaxDays = $relaxDays->orderByDesc("id")->get();
            }

            if($relaxDays->isNotEmpty()) {
                foreach ($relaxDays as $item) {
                    $daystatus[] = $item->start;
                }
            }

            $assignRelaxDays = RelaxDay::select(
                'relax_day.id',
                'assign_relax_day.user_id',
                'relax_day.date as start',
                'relax_day.date as end',
                DB::raw("'assign_relax_day' as type")
            );
            $assignRelaxDays = $assignRelaxDays->join('assign_relax_day', 'assign_relax_day.relax_day_id', 'relax_day.id');
            $assignRelaxDays = $assignRelaxDays->where(['assign_relax_day.user_id' => $authUser->id, 'assign_relax_day.approval_status' => AssignRelaxDay::APPROVAL_CONFIRMED]);
            $assignRelaxDays = $assignRelaxDays->whereNull('assign_relax_day.deleted_at');
            $assignRelaxDays = $assignRelaxDays->whereBetween('relax_day.date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
            $assignRelaxDays = $assignRelaxDays->get();

            if($assignRelaxDays->isNotEmpty()) {
                foreach ($assignRelaxDays as $item) {
                    $daystatus[] = $item->start;
                }
            }
            return response()->json(array_count_values($daystatus));

        } catch (\Exception $exp) {
            Log::error("###FullCalendar Exception Start###");
            //$msg = "FullCalendar Error by ID: - " . auth()->user()->id . " Office ID: - " . auth()->user()->fingerprint_no . " - at - " . date('Y-m-d h:i:sA', strtotime(now()));
            Log::error("Exception Msg ".$exp->getMessage());
            //Log::error($msg);
            Log::error("###End###");
        }
    }

    public function getSpecificDateEvent(Request $request)
    {
        $authUser = auth()->user();
        $eventHolidays = PublicHoliday::with("holiday")
            ->whereDate("from_date", "<=", $request->input('start'))
            ->whereDate("to_date", ">=", $request->input('start'))
            ->orderBy("from_date", "asc")
            ->get();
        $events = [];
        if (!empty($eventHolidays->toArray()) && count($eventHolidays->toArray()) > 0) {
            foreach ($eventHolidays as $upHoliday) {
                $arrHoliday['id'] = $upHoliday->id;
                $arrHoliday['title'] = $upHoliday->holiday->name;
                $arrHoliday['remarks'] = $upHoliday->remarks;
                $events[] = $arrHoliday;
            }
        }

        $todayDate = date("Y-m-d");
        $departmentIDs = User::with([
            "currentPromotion" => function ($query) {
                $query->with("department", "officeDivision");
            },
            "currentStatus"
        ])
            ->join("promotions", function ($join) use ($todayDate) {
                $join->on('promotions.user_id', 'users.id');
                $join->on('promotions.id', DB::raw("(select max(p.id) from promotions p where p.user_id = users.id and p.promoted_date <= '" . $todayDate . "' limit 1)"));
            })->select([
                "users.id",
                "office_division_id",
                "department_id"
            ])->where(['users.id' => $authUser->id])->pluck('department_id');
        if (!empty($departmentIDs) && count($departmentIDs) > 0) {
            $relaxDayResult = RelaxDay::whereDate("date", "=", $request->input('start'))->whereIn('department_id', $departmentIDs)->get();
        }
        if (!empty($relaxDayResult) && count($relaxDayResult) > 0) {
            foreach ($relaxDayResult as $rDay) {
                $arrRelaxDay['id'] = $rDay->id;
                $arrRelaxDay['title'] = 'Departmental Relax Day';
                $arrRelaxDay['remarks'] = '';
                $events[] = $arrRelaxDay;
            }
        }

        $assignRelaxDays = AssignRelaxDay::with("relaxDate")->whereHas("relaxDate", function ($query) use ($request) {
            $query->where("date", $request->input("start"));
        })->where(['user_id' => $authUser->id, 'approval_status' => AssignRelaxDay::APPROVAL_CONFIRMED])->get();
        if (!empty($assignRelaxDays) && count($assignRelaxDays) > 0) {
            foreach ($assignRelaxDays as $arDay) {
                $arraRelaxDay['id'] = $arDay->id;
                $arraRelaxDay['title'] = 'Approved Relax Day';
                $arraRelaxDay['remarks'] = '';
                $events[] = $arraRelaxDay;
            }
        }

        if (count($events) <= 0) {
            return json_encode(['status' => 'failed']);
        }

        $eventDate = $request->input('start');
        return view("full-calendar.event-view-with-specific-date", compact("events", 'eventDate'));
    }

    public function getRosters($request, $obj_type = 1)
    {
        $data = [];
        $rosters = Roster::from("rosters as r")->with(["user", "department", "workSlot"])->select(
            'r.type','r.department_id','r.work_slot_id','r.user_id','r.active_date', 'r.status', 'r.is_weekly_holiday', DB::raw("active_date as start"),  DB::raw("active_date as end")
        );
        $rosters = $rosters->where('r.type', '=', $obj_type);
        if($obj_type == 2) {
            $rosters = $rosters->where('r.department_id', '=', auth()->user()->currentPromotion->department_id);
        } else {
            $rosters = $rosters->where('r.user_id', '=', auth()->user()->id);
        }
        $rosters = $rosters->where('r.status', '=', Roster::STATUS_APPROVED);
        $rosters = $rosters->whereBetween('r.active_date', [Carbon::parse($request->start)->startOfMonth()->startOfDay()->toDateString(), Carbon::parse($request->start)->endOfMonth()->endOfDay()->toDateString()]);
        $rosters = $rosters->get();

        if($rosters->isNotEmpty()) {
            foreach ($rosters as $roster) {
                $data['events'][$roster->start] = [
                    'type' => $obj_type,
                    'title' => '',
                    'start' => $roster->start,
                    'end' => $roster->end,
                    'active_date' => $roster->start,
                    'custom_color' => $roster->is_weekly_holiday == 1 ? '#ff000099' : $this->getWorkSlotColors($roster->workSlot->id),
                    'is_weekly_holiay' => $roster->is_weekly_holiday,
                ];
                $data['work_slot']["id-{$roster->workSlot->id}"] = [
                    'id' => $roster->workSlot->id,
                    'title' => $roster->workSlot->title,
                    'start' => $roster->workSlot->start_time,
                    'end' => $roster->workSlot->end_time,
                    'custom_color' => $this->getWorkSlotColors($roster->workSlot->id)
                ];
            }
        }

        return $data;
    }

    protected function getWorkSlotColors($work_slot_id)
    {
        $colors = [
            '1'=> 'rgb(128, 193, 255)',
            '3'=> 'rgb(255, 128, 157)',
            '8'=> 'rgb(153 113 109)',
            '9'=> 'rgb(128, 142, 255)',
            '13'=> 'rgb(193, 128, 255)',
            '14'=> 'rgb(16, 112, 116)',
            '15'=> 'rgb(133, 118, 8)',
            '17'=> 'rgba(87, 155, 177, 0.8)',
            '20'=> 'rgba(94, 153, 68, 0.8)',
        ];

        if(array_key_exists($work_slot_id, $colors)) {
            return $colors[$work_slot_id];
        } else {
            $max_rgb_int = 1661324;
            $work_slot_id = $work_slot_id * $max_rgb_int;
            $blue = floor($work_slot_id % 256);
            $green = floor($work_slot_id / 256 % 256);
            $red = floor($work_slot_id / 256 / 256 % 256);
            return sprintf("rgba(%s,%s,%s,%s)",  $red, $green, $blue, 0.8);
        }


    }
    public function viewLeaveCalendar(){
        return view('full-calendar.view-leave-calendar');
    }
}
