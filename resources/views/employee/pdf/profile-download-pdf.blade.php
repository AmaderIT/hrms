@php
    $imgSrc = file_exists("photo/" . $data["employee"]['fingerprint_no'] . ".jpg") ? public_path("photo/" . $data["employee"]['fingerprint_no'] . ".jpg") : public_path('assets/media/svg/avatars/001-boy.svg');
@endphp
<div class="row">
    <div class="col-lg-2" style="float: right">
        <img src='{{$imgSrc}}' width="110" height="110"/><br>
        {{--<img src='{{ public_path("photo/".$data["employee"]['fingerprint_no'].".jpg"). "?" . uniqid() }}'
             onerror="this.onerror=null; this.src='{{ public_path('assets/media/svg/avatars/001-boy.svg')}}';"
             width="110"/><br>--}}
        <span><b>{{ !empty($data["employee"]["name"])?$data["employee"]["name"]:""}}</b></span>
    </div>
    <div class="col-lg-7">
        {{--<span><b>Name: </b>{{ !empty($data["employee"]["name"])?$data["employee"]["name"]:""}}</span><br/>--}}
        <span><b>ID: </b>{{ !empty($data["employee"]["fingerprint_no"])?$data["employee"]["fingerprint_no"]:"" }}</span><br/>
        @if(!is_null($data["employee"]->currentPromotion) AND $data["employee"]->employeeStatus->count() > 0)
            <span><b>Designation: </b>{{ optional($data["employee"]->currentPromotion->designation)->title }}</span>
            <br/>
            <span><b>Department: </b>{{ optional($data["employee"]->currentPromotion->department)->name }}</span><br/>
            <span><b>Division: </b>{{ optional($data["employee"]->currentPromotion->officeDivision)->name }}</span><br/>
            <span><b>Joining Date: </b>{{ \Carbon\Carbon::createFromDate($data["employee"]->employeeStatus()->where("action_reason_id", 2)->orderByDesc('action_date')->first()->action_date->toDateTimeString())->format("M d, Y") }}</span>
            <br/>
        @endif
        <span><b>Office E-mail: </b>{{ !empty($data["employee"]["email"])?$data["employee"]["email"]:"" }}</span><br/>
        <span><b>Office Contact Number: </b>{{ !empty($data["employee"]["phone"])?$data["employee"]["phone"]:"" }}</span><br/>
        @if(!is_null($data["employee"]->currentPromotion) AND $data["employee"]->employeeStatus->count() > 0)
            {{--<span><b>Movement Type: </b>{{ optional($data["employee"]->currentPromotion)->type }}</span><br/>--}}
            <span><b>Employment Type: </b>{{ optional($data["employee"]->getEmploymentStatus())->employment_type }}</span>
            <br/>
            <span><b>WorkSlot: </b>{{ optional($data["employee"]->currentPromotion->workSlot)->title }}</span><br/>
            <span><b>PayGrade: </b>{{ optional($data["employee"]->currentPromotion->payGrade)->name }}</span><br/>
        @endif
        @if(auth()->user()->can("View Salary") OR auth()->user()->id == $data["employee"]->id)
            <span><b>Current Salary: </b>{{ optional($data["employee"]->currentPromotion)->salary }}</span><br/>
        @endcan
    </div>
</div>

<div class="row">
    <div class="col-lg-5" style="width:50%;float: left;">
        <h3 style="font-style: italic;text-decoration: underline;">Personal Information</h3>
        <span><b>Date of Birth: </b>{{ !empty($data["employee"]->profile->dob)?\Carbon\Carbon::createFromDate($data["employee"]->profile->dob->toDateTimeString())->format("M d, Y"):"" }}</span><br/>
        <span><b>Gender: </b>{{ optional($data["employee"]->profile)->gender }}</span><br/>
        <span><b>Religion: </b>{{ optional($data["employee"]->profile)->religion }}</span><br/>
        <span><b>Marital Status: </b>{{ optional($data["employee"]->profile)->marital_status }}</span><br/>
        <span><b>Personal Contact Number: </b>{{ optional($data["employee"]->profile)->personal_phone }}</span><br/>
        <span><b>Emergency Contact Number: </b>{{ optional($data["employee"]->profile)->emergency_contact }}</span><br/>
        <span><b>Relation with Emergency Contact: </b>{{ optional($data["employee"]->profile)->relation }}</span><br/>
        <span><b>NID/ Passport: </b>{{ optional($data["employee"]->profile)->nid }}</span><br/>
        <span><b>TIN: </b>{{ optional($data["employee"]->profile)->tin }}</span><br/>
        <div>&nbsp;</div>

        @if(isset($data["employee"]->presentAddress))
            <span><b>Present Address: </b>{{ $data["employee"]->presentAddress->address }}</span><br/>
            <span><b>Division: </b>{{ optional($data["employee"]->presentAddress->division)->name }}</span><br/>
            <span><b>District: </b>{{ optional($data["employee"]->presentAddress->district)->name }}</span><br/>
            <span><b>Zip Code: </b>{{ optional($data["employee"]->presentAddress)->zip }}</span><br/>
            <div>&nbsp;</div>
        @endif

        @if(isset($data["employee"]->permanentAddress))
            <span><b>Permanent Address: </b>{{ $data["employee"]->permanentAddress->address }}</span><br/>
            <span><b>Division: </b>{{ optional($data["employee"]->permanentAddress->division)->name }}</span><br/>
            <span><b>District: </b>{{ optional($data["employee"]->permanentAddress->district)->name }}</span><br/>
            <span><b>Zip Code: </b>{{ optional($data["employee"]->permanentAddress)->zip }}</span><br/>
            <div>&nbsp;</div>
        @endif
        @if(!empty($data['employee']->degrees->toArray()) && count($data['employee']->degrees->toArray())>0)
            <h3 style="font-style: italic;text-decoration: underline;">Educational Information</h3>
        @endif
        @foreach($data['employee']->degrees as $degree)
            <?php
            $institute = $data["institutes"]->filter(function ($query) use ($degree) {
                return $query->id === $degree->pivot->institute_id;
            })->values()->first();
            ?>
            <span><b>Degree: </b>{{ $degree->name }}</span><br/>
            <span><b>Institute: </b>{{ $institute->name }}</span><br/>
            <span><b>Passing Year: </b>{{ $degree->pivot->passing_year }}</span><br/>
            <span><b>Result: </b>{{ $degree->pivot->result }}</span><br/>
            <div>&nbsp;</div>
        @endforeach
    </div>
    <div class="col-lg-5" style="width:50%;float: left;">
        @if(count($data["employee"]->jobHistories) > 0)
            <h3 style="font-style: italic;text-decoration: underline;">Professional Experience</h3>
            @foreach($data["employee"]->jobHistories as $jobHistory)
                <span><b>Organization: </b>{{ $jobHistory->organization_name }}</span><br/>
                <span><b>Designation: </b>{{ optional($jobHistory->designationEmployee)->title }}</span><br/>
                <span><b>Start Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->start_date)->format("M d, Y") }}</span>
                <br/>
                <span><b>End Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->end_date)->format("M d, Y") }}</span><br/>
                <div>&nbsp;</div>
            @endforeach
        @endif
        @if(!is_null($data['employee']->currentBank))
            <h3 style="font-style: italic;text-decoration: underline;">Bank Information</h3>
            <?php
            $employee = $data['employee'];
            $bank = $data["banks"]->filter(function ($query) use ($employee) {
                return $query->id === $employee->currentBank->bank_id;
            })->values()->first();
            $branch = $data["branches"]->filter(function ($query) use ($employee) {
                return $query->id === $employee->currentBank->branch_id;
            })->values()->first();
            ?>
            <span><b>Bank Name: </b>{{ optional($bank)->name }}</span><br/>
            <span><b>Branch: </b>{{ optional($branch)->name }}</span><br/>
            <span><b>Account Type: </b>{{ optional($data['employee']->currentBank)->account_type }}</span><br/>
            <span><b>Account Name: </b>{{ optional($data['employee']->currentBank)->account_name }}</span><br/>
            <span><b>Account No: </b>{{ optional($data['employee']->currentBank)->account_no }}</span><br/>
            <span><b>Nominee Name: </b>{{ optional($data['employee']->currentBank)->nominee_name }}</span><br/>
            <span><b>Relation with nominee: </b>{{ optional($data['employee']->currentBank)->relation_with_nominee }}</span>
            <br/>
            <span><b>Nominee Contact: </b>{{ optional($data['employee']->currentBank)->nominee_contact }}</span><br/>
            <div>&nbsp;</div>
        @endif
    </div>
</div>
