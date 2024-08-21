<?php

namespace App\Exports\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlyMealExport implements FromCollection, WithHeadings
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var null
     */
    protected $mealReport = null;

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
    public function __construct($mealReport)
    {
        $this->mealReport = $mealReport;
        $this->setTotalDaysOfMonth();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            $this->mealReport[0]["department"] . ", " . $this->mealReport[0]["monthAndYear"]
        ];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $this->appendSubHeadings();

        foreach ($this->mealReport as $report) {
            # Entry
            $row = array(
                "fingerprint_no"    => $report["employee"]->fingerprint_no,
                "department"        => $report["employee"]->name,
            );

            if(count($report["report"]) > 0) {
                for ($day = 1; $day <= $this->lastDay; $day++) {
                    $data = reset($report["report"][$day - 1]);

                    $entry = null;

                    if (isset($data["status"]) && $data["status"]->status == 1) $entry = "Yes";

                    array_push($row, $entry);
                }
            }
            array_push($this->data, $row);

            # Exit

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
        $this->lastDay = $this->mealReport[0]["lastDayOfMonth"];
    }

    /**
     * Append Sub Header to the Excel File
     *
     * @return void
     */
    protected function appendSubHeadings(): void
    {
        $row = array("ID", "Employee Name");
        for ($day = 1; $day <= $this->lastDay; $day++) {
            array_push($row, sprintf("%02d", $day));
        }

        array_push($this->data, $row);
    }
}
