<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class RequestStoreEmployee extends FormRequest
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
        return [
            # Employee
            "name"                          => "required|string|min:3|max:100",
            "email"                         => "nullable|email|min:3|max:100|unique:users,email",
            "phone"                         => "nullable|string|min:3|max:30|unique:users,phone",
            "fingerprint_no"                => "required|integer|digits_between:3,10|unique:users,fingerprint_no",
            "photo"                         => "nullable|mimes:jpeg,jpg,png|max:10000",
            "password"                      => "required|confirmed|min:6|max:20|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{1,}/|different:current",

            # Profile
            "gender"                        => "required|string|in:Male,Female",
            "religion"                      => "required|string|in:Islam,Hinduism,Christianity,Buddhism,Other",
            "dob"                           => "required|date",
            "marital_status"                => "required|string|in:Single,Married",
            "emergency_contact"             => "required|string|min:11,max:14",
            "relation"                      => "required|string|min:3,max:30",
            "blood_group"                   => "required|string|in:A+,A-,B+,B-,O+,O-,AB+,AB-",
            "nid"                           => "nullable|string|min:8,max:20|unique:profiles,nid",
            "tin"                           => "nullable|string|min:12,max:20|unique:profiles,tin",
            "personal_email"                => "nullable|email|min:3|max:100|unique:profiles,personal_email",
            "personal_phone"                => "required|string|min:11,max:14|unique:profiles,personal_phone",

            # Promotions
            "office_division_id"            => "required|integer|exists:office_divisions,id",
            "department_id"                 => "required|integer|exists:departments,id",
            "designation_id"                => "required|integer|exists:designations,id",
            "pay_grade_id"                  => "required|integer",
            "salary"                        => "required|integer",
            "promoted_date"                 => "nullable|date",
            "type"                          => "required|string|in:Internee,Provision,Permanent,Promoted,Contractual",
            "workslot_id"                   => "required|exists:work_slots,id",

            # Action Reason
            "joining_date"                  => "required|date",

            # Roles
            "role_id"                       => "required|integer|exists:roles,id",
            # Provision
            "provision_duration"                       => "required|integer"
        ];
    }
    public function messages()
    {
        return [
            'password.regex' => 'The new password contains at least 1 lowercase, 1 uppercase,1 numeric number.'
        ];
    }
}
