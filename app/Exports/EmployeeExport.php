<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeExport implements FromCollection, WithHeadings
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @return array
     */
    public function headings(): array
    {
        return array(
            "Name", "Email", "Phone", "Office ID", "Status", "Is Supervisor",
            "Gender", "Religion", "DOB", "Marital Status", "Emergency Contact", "Relation", "Blood Group", "NID", "TIN",
            "Address Type", "Present Address", "Present Address ZIP", "Present Address District ID", "Present Address Division ID",
            "Address Type", "Permanent Address", "Permanent Address ZIP", "Permanent Address District ID", "Permanent Address Division ID",
            "Office Division ID", "Department ID", "Designation ID", "Pay Grade ID",
            //"Salary",
            "Promoted Date", "Employment Type", "WorkSlot ID",
            "Bank ID", "Branch ID", "Account Type", "Account Name", "Account Number", "Nominee Name", "Relation with Nominee", "Nominee Contact"
        );
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $users = User::with("profile", "currentPromotion", "presentAddress", "permanentAddress", "currentBank")
                    ->active()
                    ->select("id", "name", "email", "phone", "fingerprint_no", "status", "is_supervisor")
                    ->get();

        foreach ($users as $user) {
            $currentBank        = $user->currentBank;

            $employee = array(
                # users
                "name"                  => $user->name,
                "email"                 => $user->email,
                "phone"                 => $user->phone,
                "fingerprint_no"        => $user->fingerprint_no,
                "status"                => $user->status == 1 ? "Active" : "Disabled",
                "is_supervisor"         => $user->is_supervisor== 1 ? "YES" : "NO",

                # profiles
                "gender"                => $user->profile->gender,
                "religion"              => $user->profile->religion,
                "dob"                   => date("M d, Y", strtotime($user->profile->dob)),
                "marital_status"        => $user->profile->marital_status,
                "emergency_contact"     => $user->profile->emergency_contact,
                "relation"              => $user->profile->relation,
                "blood_group"           => $user->profile->blood_group,
                "nid"                   => $user->profile->nid ?? null,
                "tin"                   => $user->profile->tin ?? null,

                # Present Address
                "present_type"          => $user->presentAddress->type ?? null,
                "present_address"       => $user->presentAddress->address ?? null,
                "present_zip"           => $user->presentAddress->zip ?? null,
                "present_district_id"   => $user->presentAddress->district->name ?? null,
                "present_division_id"   => $user->presentAddress->division->name ?? null,

                # Permanent Address
                "permanent_type"        => $user->permanentAddress->type ?? null,
                "permanent_address"     => $user->permanentAddress->address ?? null,
                "permanent_zip"         => $user->permanentAddress->zip ?? null,
                "permanent_district_id" => $user->permanentAddress->district->name ?? null,
                "permanent_division_id" => $user->permanentAddress->division->name ?? null,

                # Promotion
                "office_division_id"    => $user->currentPromotion->officeDivision->name,
                "department_id"         => $user->currentPromotion->department->name,
                "designation_id"        => $user->currentPromotion->designation->name,
                "pay_grade_id"          => $user->currentPromotion->payGrade->name,
                //"salary"                => $user->currentPromotion->salary,
                "promoted_date"         => $user->currentPromotion->promoted_date,
                "employment_type"       => $user->currentPromotion->type,
                "workslot_id"           => $user->currentPromotion->workSlot->name,

                # Bank
                "bank_id"               => $currentBank->bank->name ?? null,
                "branch_id"             => $currentBank->branch->name ?? null,
                "account_type"          => $currentBank->account_type ?? null,
                "account_name"          => $currentBank->account_name ?? null,
                "account_no"            => $currentBank->account_no ?? null,
                "nominee_name"          => $currentBank->nominee_name ?? null,
                "relation_with_nominee" => $currentBank->relation_with_nominee ?? null,
                "nominee_contact"       => $currentBank->nominee_contact ?? null,
            );

            array_push($this->data, $employee);
        }

        return collect($this->data);
    }
}
