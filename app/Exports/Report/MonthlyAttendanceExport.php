<?php

namespace App\Exports\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlyAttendanceExport implements FromCollection, WithHeadings
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var null
     */
    protected $attendanceReport = null;

    /**
     * @var null
     */
    protected $lastDay = null;

    /**
     * Create a new controller instance
     *
     * MonthlyAttendanceExport constructor.
     * @param $attendanceReport
     */
    public function __construct($attendanceReport)
    {
        $this->attendanceReport = $attendanceReport;
        $this->setTotalDaysOfMonth();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            $this->attendanceReport[0]["department"] . ", " . $this->attendanceReport[0]["monthAndYear"]
        ];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $this->appendSubHeadings();

        foreach ($this->attendanceReport as $report) {
            # Entry
            $row = array(
                "fingerprint_no"    => $report["employee"]->fingerprint_no,
                "department"        => $report["employee"]->name,
                "time"              => "In",
            );

            if(count($report["report"]) > 0) {
                for ($day = 1; $day <= $this->lastDay; $day++) {
                    $data = reset($report["report"][$day - 1]);

                    $entry = null;
                    if (isset($data["entry"]->punch_time)) $entry = date("h:iA", strtotime($data["entry"]->punch_time));

                    array_push($row, $entry);
                }
            }
            array_push($this->data, $row);

            # Exit
            $row = array(
                "fingerprint_no" => null,
                "department" => null,
                "time" => "Out",
            );

            if(count($report["report"]) > 0) {
                for ($day = 1; $day <= $this->lastDay; $day++) {
                    $data = reset($report["report"][$day - 1]);

                    $exit = null;

                    if (isset($data["exit"]->punch_time)) $exit = date("h:iA", strtotime($data["exit"]->punch_time));

                    array_push($row, $exit);
                }
            }
            array_push($this->data, $row);
        }

        return collect($this->data);
    }

    /**
     * Set total days of month to generate the header
     *
     * @return void
     */
    protected function setTotalDaysOfMonth(): void
    {
        $this->lastDay = $this->attendanceReport[0]["lastDayOfMonth"];
    }

    /**
     * Append Sub Header to the Excel File
     *
     * @return void
     */
    protected function appendSubHeadings(): void
    {
        $row = array("ID", "Employee Name", "In/Out");
        for ($day = 1; $day <= $this->lastDay; $day++) {
            array_push($row, sprintf("%02d", $day));
        }

        array_push($this->data, $row);
    }
}
