<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class RequestEmployeeUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $employee = $this->route("employee");

        $rules = [
            # Employee
            "name"                          => "required|string|min:3|max:100",
            "email"                         => "nullable|min:3|max:100|email|unique:users,email,".$employee->id,
            "phone"                         => "nullable|string|min:3|max:30|unique:users,phone,".$employee->id,
            "photo"                         => "nullable|mimes:jpeg,jpg,png|max:10000",
            "password"                      => "nullable|confirmed|min:6",

            # Profile
            "gender"                        => "required|string|in:Male,Female,Other",
            "religion"                      => "required|string|in:Islam,Hinduism,Christianity,Buddhism,Other",
            "dob"                           => "required|date",
            "marital_status"                => "required|string|in:Single,Married",
            "emergency_contact"             => "required|string|min:11,max:14",
            "relation"                      => "required|string|min:3,max:30",
            "blood_group"                   => "required|string|in:A+,A-,B+,B-,O+,O-,AB+,AB-",
            "nid"                           => "nullable|string|min:8,max:20|unique:profiles,nid,".$employee->profile->id,
            "tin"                           => "nullable|string|min:12,max:20|unique:profiles,tin,".$employee->profile->id,
            "personal_email"                => "nullable|email|min:3|max:100",
            "personal_phone"                => "required|string|min:11,max:14",

            # Promotions
            "office_division_id"            => "required|integer|exists:office_divisions,id",
            "department_id"                 => "required|integer|exists:departments,id",
            "designation_id"                => "required|integer|exists:designations,id",
            "pay_grade_id"                  => "nullable|integer",
            "promoted_date"                 => "nullable|date",
            "type"                          => "required|string|in:Internee,Provision,Permanent,Promoted,Contractual",
            "workslot_id"                   => "required|exists:work_slots,id",

            # Action Reason
            "joining_date"                  => "required|date",
            # Provision
            "provision_duration"                       => "required|integer"
        ];
        if(auth()->user()->can("Edit Employee Salary")){
            $rules["salary"] = "required|integer";
        }
        if(auth()->user()->can('Employee Edit Pay Grade')){
            $rules["pay_grade_id"] = "required|integer";
        }
        return $rules;
    }
}
