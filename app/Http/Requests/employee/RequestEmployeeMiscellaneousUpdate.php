<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class RequestEmployeeMiscellaneousUpdate extends FormRequest
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

        return [
            # Address
            # <Present Address>
            "present_address.address"       => "required|string|min:10,max:255",
            "present_address.district_id"   => "required|exists:districts,id",
            "present_address.division_id"   => "required|exists:divisions,id",
            "present_address.zip"           => "nullable|string|min:4,max:10",

            # <Permanent Address>
            "permanent_address.address"     => "required|string|min:10,max:255",
            "permanent_address.district_id" => "required|exists:districts,id",
            "permanent_address.division_id" => "required|exists:divisions,id",
            "permanent_address.zip"         => "nullable|string|min:4,max:10",

            # Bank
            "bank_id"                       => "nullable|exists:banks,id",
            "branch_id"                     => "nullable|exists:branches,id",
            "account_type"                  => "nullable|string|in:Current,Deposit,Saving",
            "account_name"                  => "nullable|string|min:5,max:50",
            "account_number"                => "nullable|string|min:10,max:20",
            "nominee"                       => "nullable|string|min:3,max:50",
            "relation_with_nominee"         => "nullable|string|min:3,max:20",
            "nominee_contact"               => "nullable|string|min:11,max:50",

            # Education
            "degree_id.*"                   => "nullable|exists:degrees,id|required_with_all:degree_id,institute_id,passing_year,result",
            "institute_id.*"                => "nullable|exists:institutes,id|required_with_all:degree_id,institute_id,passing_year,result",
            "passing_year.*"                => "nullable|required_with_all:degree_id,institute_id,passing_year,result|digits:4|integer|min:1950|max:" . (date('Y') + 1),
            "result.*"                      => "nullable|string|min:1|max:20|required_with_all:degree_id,institute_id,passing_year,result",

            # Job History
            "organization.*"                => "nullable|string|min:3|max:100|required_with_all:organization,designation,start_date,end_date",
            "designation.*"                 => "nullable|exists:designations,id|required_with_all:organization,designation,start_date,end_date",
            "start_date.*"                  => "nullable|date|required_with_all:organization,designation,start_date,end_date",
            "end_date.*"                    => "nullable|date|required_with_all:organization,designation,start_date,end_date",
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            # Present Address
            "present_address.address"       => "Present Address",
            "present_address.district_id"   => "Present Address District",
            "present_address.division_id"   => "Present Address Division",
            "present_address.zip"           => "Present Address Zip code",

            # Permanent Address
            "permanent_address.address"     => "Present Address",
            "permanent_address.district_id" => "Present Address District",
            "permanent_address.division_id" => "Present Address Division",
            "permanent_address.zip"         => "Present Address Zip code",
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            # Education
            "degree_id.*.required_with_all" => "Degree field is required when Institute, Passing Year and Result are present.",
            "institute_id.*.required_with_all" => "Institute field is required when Degree, Passing Year and Result are present.",
            "passing_year.*.required_with_all" => "Passing Year field is required when Degree, Institute and Result are present.",
            "result.*.required_with_all" => "Result field is required when Degree, Institute and Passing Year are present.",

            # Job History
            "organization.*.required_with_all" => "Organization field is required when Designation, Start Date and End Date are present.",
            "designation.*.required_with_all" => "Designation field is required when Organization, Start Date and End Date are present.",
            "start_date.*.required_with_all" => "Start Date field is required when Organization, Designation and End Date are present.",
            "end_date.*.required_with_all" => "End Date field is required when Organization, Designation and Start Date are present.",
        ];
    }
}
