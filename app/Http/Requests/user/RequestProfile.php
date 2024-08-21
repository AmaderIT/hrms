<?php

namespace App\Http\Requests\user;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestProfile extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $this->modifyProfileRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $profile = $this->route('profile')->id;

        if ($profile) {
            $nid = "required|integer|min:13,max:20|unique:profiles,nid," . $this->route('profile')->id;
            $tin = "required|string|min:12,max:20|unique:profiles,tin," . $this->route('profile')->id;
        } else {
            $nid = "required|integer|min:13,max:20|unique:profiles,nid";
            $tin = "required|string|min:12,max:20|unique:profiles,tin";
        }

        return [
            "user_id"           => "required|exists:users,id",
            "gender"            => "required|string|in:Male,Female",
            "religion"          => "required|string|in:Islam,Hinduism,Christianity,Buddhism,Other",
            "dob"               => "required|date",
            "marital_status"    => "required|string|in:Married,Unmarried",
            "blood_group"       => "required|string|in:A+,A-,B+,B-,O+,O-,AB+,AB-",
            "emergency_contact" => "required|string|min:11,max:14",
            "nid"               => $nid,
            "tin"               => $tin
        ];
    }

    /**
     * @return void
     */
    protected function modifyProfileRecords(): void
    {
        $this->request->add([
            "user_id"     => $this->route("profile")->user_id
        ]);
    }
}
