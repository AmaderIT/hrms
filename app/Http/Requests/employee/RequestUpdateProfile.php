<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestUpdateProfile extends FormRequest
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
            # Employee
            "photo" => "nullable|mimes:jpeg,jpg,png|max:10000",

            # Profile
            "gender" => "required|string|in:Male,Female,Other",
            "religion" => "required|string|in:Islam,Hinduism,Christianity,Buddhism,Other",
            "marital_status" => "required|string|in:Single,Married",
            "blood_group" => "required|string|in:A+,A-,B+,B-,O+,O-,AB+,AB-",
            //"personal_phone"                => "required|string|min:11,max:14|unique:profiles,personal_phone",
            "personal_phone" => "required|string|min:11,max:14|" . Rule::unique('profiles')->ignore($this->user()->id, 'user_id'),

            # Address
            # <Present Address>
            "present_address.address" => "nullable|string|min:10,max:255",
            "present_address.district_id" => "nullable|exists:districts,id",
            "present_address.division_id" => "nullable|exists:divisions,id",
            "present_address.zip" => "nullable|string|min:4,max:10",

            # Education
//                        "degree_id.*"                   => "nullable|exists:degrees,id|required_with_all:degree_id,institute_id,passing_year,result",
//                        "institute_id.*"                => "nullable|exists:institutes,id|required_with_all:degree_id,institute_id,passing_year,result",
//                        "passing_year.*"                => "nullable|required_with_all:degree_id,institute_id,passing_year,result|digits:4|integer|min:1950|max:" . (date('Y') + 1),
//                        "result.*"                      => "nullable|string|min:1|max:20|required_with_all:degree_id,institute_id,passing_year,result",
//            "degree_id" => "nullable",
//            "institute_id" => [
//                Rule::requiredIf(function () {
//                    return !empty($this->offsetGet('degree_id'));
//                }),
//                "nullable",
//                "array",
//                "min:1"
//            ],
//            "institute_id.*" => [
//                "required"
//            ],
//            "passing_year"                   => [
//                Rule::requiredIf(function () {
//                    return !empty($this->offsetGet('degree_id'));
//                }),
//                "nullable",
//                "array",
//                "min:1"
//            ],
//            "passing_year.*"                => "required|digits:4|integer|min:1950|max:" . (date('Y') + 1),
            //"institute_id.*"                => "nullable|exists:institutes,id|required_with_all:degree_id,institute_id,passing_year,result",

            //"result.*"                      => "nullable|string|min:1|max:20|required_with_all:degree_id,institute_id,passing_year,result",

            # Job History
            //"organization.*"                => "nullable|string|min:3|max:100|required_with_all:organization,designation,start_date,end_date",
            //"designation.*"                 => "nullable|exists:designations,id|required_with_all:organization,designation,start_date,end_date",
            //"start_date.*"                  => "nullable|date|required_with_all:organization,designation,start_date,end_date",
            //"end_date.*"                    => "nullable|date|required_with_all:organization,designation,start_date,end_date",
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {

        return [
            # Present Address
            "present_address.address" => "Present Address",
            "present_address.district_id" => "Present Address District",
            "present_address.division_id" => "Present Address Division",
            "present_address.zip" => "Present Address Zip code",

            # Permanent Address
            "permanent_address.address" => "Permanent Address",
            "permanent_address.district_id" => "Permanent Address District",
            "permanent_address.division_id" => "Permanent Address Division",
            "permanent_address.zip" => "Permanent Address Zip code",
        ];
    }

}
