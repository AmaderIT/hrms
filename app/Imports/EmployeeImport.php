<?php

namespace App\Imports;

use App\Models\Address;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row)
        {
            $data = json_decode($row);

            # Employee
            $employee = User::create(array(
                "name"                  => $data->name,
                "email"                 => $data->email,
                "phone"                 => $data->phone,
                "fingerprint_no"        => $data->office_id,
                "status"                => $data->status,
                "is_supervisor"         => $data->is_supervisor === 1 ? 1 : 0,
                "password"              => bcrypt("12345678")
            ));

            # Profile
            $employee->profile()->create(array(
                "gender"                => $data->gender,
                "religion"              => $data->religion,
                "dob"                   => $data->dob,
                "marital_status"        => $data->marital_status,
                "emergency_contact"     => $data->emergency_contact,
                "relation"              => $data->relation,
                "blood_group"           => $data->blood_group,
                "nid"                   => $data->nid ?? null,
                "tin"                   => $data->tin ?? null
            ));

            # Addresses both Present & Permanent
            $employee->addresses()->createMany(array(
                array(
                    "type"              => Address::TYPE_PRESENT,
                    "address"           => $data->present_address,
                    "zip"               => $data->present_address_zip ?? null,
                    "division_id"       => $data->present_address_division_id,
                    "district_id"       => $data->present_address_district_id
                ), array(
                    "type"              => Address::TYPE_PERMANENT,
                    "address"           => $data->permanent_address,
                    "zip"               => $data->permanent_address_zip ?? null,
                    "division_id"       => $data->permanent_address_division_id,
                    "district_id"       => $data->permanent_address_district_id
                )
            ));

            # Employee Promotion
            $employee->promotions()->create(array(
                "office_division_id"    => $data->office_division_id,
                "department_id"         => $data->department_id,
                "designation_id"        => $data->designation_id,
                "pay_grade_id"          => $data->pay_grade_id ?? null,
                "salary"                => $data->salary ?? null,
                "promoted_date"         => $data->promoted_date ?? null,
                "type"                  => $data->employment_type,
                "workslot_id"           => $data->workslot_id,
            ));

            # Employee Status
            $employee->employeeStatus()->create(array(
                "action_reason_id"      => $data->action_reason_id,
                "action_taken_by"       => $data->action_taken_by,
                "action_date"           => $data->action_date
            ));

            # Employee Bank
            if(!is_null($data->account_number))
            {
                $employee->banks()->attach(Bank::find($data->bank_id), array(
                    "branch_id"             => $data->branch_id,
                    "account_type"          => $data->account_type,
                    "account_name"          => $data->account_name,
                    "account_no"            => $data->account_number,
                    "nominee_name"          => $data->nominee_name,
                    "relation_with_nominee" => $data->relation_with_nominee,
                    "nominee_contact"       => $data->nominee_contact
                ));
            }
        }
    }
}
