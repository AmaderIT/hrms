<?php

namespace App\Http\Requests\workslot;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RequestWorkSlot extends FormRequest
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
        $this->modifyWorkSlotRecords();
        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $workSlot = $this->route('workSlot');
        if ($workSlot) {
            $validation = "required|min:3|max:50|string|unique:work_slots,title," . $this->route('workSlot')->id;
        } else {
            $validation = "required|min:3|max:50|string|unique:work_slots,title";
        }
        $validation_total_work_hour = "nullable";
        $validation_overtime_count = "nullable";
        $end_time = "nullable";
        $late_count_time = "nullable";
        if(isset($this->is_flexible) && $this->is_flexible==1){
            $validation_total_work_hour = "required";
        }else{
            $end_time = "required|regex:/(\d+\:\d+)/";
            $late_count_time = "regex:/(\d+\:\d+)/";
            if($this->over_time == 'Yes'){
                $validation_overtime_count = "required";
            }
        }
        return [
            "title"               => $validation,
            "start_time"          => "required|regex:/(\d+\:\d+)/",
            "end_time"            => $end_time,
            "late_count_time"     => $late_count_time,
            "over_time"           => "sometimes|in:Yes,No",
            "is_flexible"         => "sometimes|in:1,0",
            "overtime_count"      => $validation_overtime_count,
            "total_work_hour" => $validation_total_work_hour,
        ];
    }

    /**
     *
     * @return void
     */
    protected function modifyWorkSlotRecords(): void
    {
        if(isset($this->is_flexible)){
            $this->request->add([
                "is_flexible" => 1,
            ]);
        }else{
            $this->request->add([
                "is_flexible" => 0,
            ]);
        }
        if ($this->request->get("over_time") == null){
            $this->request->add([
                "over_time" => "No",
                "overtime_count" => null
            ]);
        }else{
            $this->request->add([
                "over_time" => "Yes",
            ]);
        }
    }
}
