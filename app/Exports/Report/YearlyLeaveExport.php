<?php

namespace App\Exports\Report;

use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserLeave;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class YearlyLeaveExport implements FromCollection, WithHeadings
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $report = [];

    /**
     * YearlyLeaveExport constructor.
     * @param $report
     */
    function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = ["Office ID", "Name"];

        for ($i = 1; $i <= 30; $i++) {
            array_push($headings, $i);
        }
        array_push($headings, "Total", "Used", "Left");

        return $headings;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function collection()
    {
        $leaveRequests = User::with(["currentPromotion", "leaveRequests" => function ($query) {
                $query->whereYear("from_date", $this->report["datepicker"]);
            }])
            ->whereHas("currentPromotion", function($query) {
                $query->where("office_division_id", $this->report["office_division_id"])->whereIn("department_id", $this->report["department_id"]);
            })
            ->active();

        if(isset($this->report["user_id"])) {
            $leaveRequests = $leaveRequests->whereIn("id", $this->report["user_id"]);
        }

        $leaveRequests = $leaveRequests->select("id", "name", "fingerprint_no")->get();

        foreach ($leaveRequests as $leaveRequest)
        {
            $collection = [];
            array_push($collection, $leaveRequest->fingerprint_no, $leaveRequest->name);

            $total = 0;
            foreach($leaveRequest->leaveRequests as $leave) {
                $fromDate = new \DateTime( $leave->from_date );
                $toDate = new \DateTime( $leave->to_date );

                for($i = $fromDate; $i <= $toDate; $i->modify('+1 day')){
                    array_push($collection, $i->format("M d, Y"));
                    $total++;
                }
            }

            for ($i = $total; $i < 30; $i++) {
                array_push($collection, "---");
            }

            if(isset($leave->user_id)) {
                $totalAllocatedLeaveOfDepartment = $this->getTotalAllocatedLeaveOfDepartment($leave->user_id);

                array_push($collection, $totalAllocatedLeaveOfDepartment, (string)$total, (string)($totalAllocatedLeaveOfDepartment - $total));

                array_push($this->data, $collection);
            }
        }

        return collect($this->data);
    }

    /**
     * @param $userId
     * @return int
     */
    protected function getTotalAllocatedLeaveOfDepartment($userId) {
        return UserLeave::where("user_id", $userId)->where("year", $this->report["datepicker"])->first()->total_initial_leave;
    }
}
