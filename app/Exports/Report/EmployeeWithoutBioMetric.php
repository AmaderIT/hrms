<?php

namespace App\Exports\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeWithoutBioMetric implements FromCollection, WithHeadings
{
    protected $employees;

    /**
     * EmployeeWithoutBioMetric constructor.
     * @param $employeeWithoutBiometric
     */
    public function __construct($employeeWithoutBiometric)
    {
        $this->employees = $employeeWithoutBiometric;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $data = [];

        foreach ($this->employees as $employee) {
            array_push($data, [
                "office_id"         => $employee->fingerprint_no,
                "name"              => $employee->name,
                "office_division"   => $employee->currentPromotion->officeDivision->name,
                "department"        => $employee->currentPromotion->department->name,
                "designation"       => $employee->currentPromotion->designation->title,
                "email"             => $employee->email,
                "phone"             => $employee->phone
            ]);
        }

        return collect($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ["Office ID", "Name", "Office Division", "Department", "Designation", "Email", "Phone"];
    }
}
