<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Employee Information</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-2">
            <img src='{{ asset("photo/".$employee->fingerprint_no.".jpg") . "?" . uniqid() }}'
                 onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';"
                 width="110"/>
        </div>
        <div class="col-lg-5">


            <span><b>Name: </b>{{ $employee->name }}</span><br/>
            <span><b>ID: </b>{{ $employee->fingerprint_no }}</span><br/>
            @if(!is_null($employee->currentPromotion) AND $employee->employeeStatus->count() > 0)
            <span><b>Designation: </b>{{ optional($employee->currentPromotion->designation)->title }}</span><br/>
            <span><b>Department: </b>{{ optional($employee->currentPromotion->department)->name }}</span><br/>
            <span><b>Division: </b>{{ optional($employee->currentPromotion->officeDivision)->name }}</span><br/>
                <span><b>Joining Date: </b>{{ \Carbon\Carbon::createFromDate($employee->employeeStatus()->where("action_reason_id", 2)->orderByDesc('action_date')->first()->action_date->toDateTimeString())->format("M d, Y") }}</span><br/>
            @endif
            <span><b>Office E-mail: </b>{{ !empty($employee->email)?$employee->email:"" }}</span><br/>
            <span><b>Office Contact Number: </b>{{ !empty($employee->phone)?$employee->phone:"" }}</span><br/>
            @if(!is_null($employee->currentPromotion) AND $employee->employeeStatus->count() > 0)
            <span><b>Employment Type: </b>{{ optional($employee->getEmploymentStatus())->employment_type }}</span><br/>
            <span><b>WorkSlot: </b>{{ optional($employee->currentPromotion->workSlot)->title }}</span><br/>
            <span><b>PayGrade: </b>{{ optional($employee->currentPromotion->payGrade)->name }}</span><br/>
            @endif
            @if(auth()->user()->can("View Salary") OR auth()->user()->id == $employee->id)
                <span><b>Current Salary: </b>{{ optional($employee->currentPromotion)->salary }}</span><br/>
            @endcan

            <hr/>
            <span class="font-size-h4">Personal Information</span><br>
            <span><b>Date of Birth: </b>{{ !empty($employee->profile->dob)?\Carbon\Carbon::createFromDate($employee->profile->dob->toDateTimeString())->format("M d, Y"):"" }}</span><br/>
            <span><b>Gender: </b>{{ optional($employee->profile)->gender }}</span><br/>
            <span><b>Religion: </b>{{ optional($employee->profile)->religion }}</span><br/>
            <span><b>Marital Status: </b>{{ optional($employee->profile)->marital_status }}</span><br/>
            <span><b>Personal Contact Number: </b>{{ optional($employee->profile)->personal_phone }}</span><br/>
            <span><b>Emergency Contact Number: </b>{{ optional($employee->profile)->emergency_contact }}</span><br/>
            <span><b>Relation with Emergency Contact: </b>{{ optional($employee->profile)->relation }}</span><br/>
            <span><b>NID/ Passport: </b>{{ optional($employee->profile)->nid }}</span><br/>
            <span><b>TIN: </b>{{ optional($employee->profile)->tin }}</span><br/>
            <br/>

            @if(isset($employee->presentAddress))
                <span><b>Present Address: </b>{{ $employee->presentAddress->address }}</span><br/>
                <span><b>Division: </b>{{ optional($employee->presentAddress->division)->name }}</span><br/>
                <span><b>District: </b>{{ optional($employee->presentAddress->district)->name }}</span><br/>
                <span><b>Zip Code: </b>{{ optional($employee->presentAddress)->zip }}</span><br/>
            @endif

            <br/>

            @if(isset($employee->permanentAddress))
                <span><b>Permanent Address: </b>{{ $employee->permanentAddress->address }}</span><br/>
                <span><b>Division: </b>{{ optional($employee->permanentAddress->division)->name }}</span><br/>
                <span><b>District: </b>{{ optional($employee->permanentAddress->district)->name }}</span><br/>
                <span><b>Zip Code: </b>{{ optional($employee->permanentAddress)->zip }}</span><br/>
            @endif

            <hr/>

            @if(count($employee->jobHistories) > 0)
                <span class="font-size-h4">Professional Experience</span><br>
                @foreach($employee->jobHistories as $jobHistory)
                    <span><b>Organization: </b>{{ $jobHistory->organization_name }}</span><br/>
                    <span><b>Designation: </b>{{ optional($jobHistory->designationEmployee)->title }}</span><br/>
                    <span><b>Start Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->start_date)->format("M d, Y") }}</span>
                    <br/>
                    <span><b>End Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->end_date)->format("M d, Y") }}</span>
                    <br/>
                    <p></p>
                @endforeach
            @endif
        </div>
        <div class="col-lg-5">
            @if(!empty($employee->degrees->toArray()) && count($employee->degrees->toArray())>0)
            <span class="font-size-h4">Education Information</span><br>
            @endif
            @foreach($employee->degrees as $degree)
                <?php
                $institute = $data["institutes"]->filter(function ($query) use ($degree) {
                    return $query->id === $degree->pivot->institute_id;
                })->values()->first();
                ?>
                <span><b>Degree: </b>{{ $degree->name }}</span><br/>
                <span><b>Institute: </b>{{ $institute->name }}</span><br/>
                <span><b>Passing Year: </b>{{ $degree->pivot->passing_year }}</span><br/>
                <span><b>Result: </b>{{ $degree->pivot->result }}</span><br/>
                <p></p>
            @endforeach


            @if(!is_null($employee->currentBank))

                <?php
                $bank = $data["banks"]->filter(function ($query) use ($employee) {
                    return $query->id === $employee->currentBank->bank_id;
                })->values()->first();
                ?>

                <?php
                $branch = $data["branches"]->filter(function ($query) use ($employee) {
                    return $query->id === $employee->currentBank->branch_id;
                })->values()->first();
                ?>

                <span class="font-size-h4">Bank Information</span><br>
                <span><b>Bank Name: </b>{{ optional($bank)->name }}</span><br/>
                <span><b>Branch: </b>{{ optional($branch)->name }}</span><br/>
                <span><b>Account Type: </b>{{ optional($employee->currentBank)->account_type }}</span><br/>
                <span><b>Account Name: </b>{{ optional($employee->currentBank)->account_name }}</span><br/>
                <span><b>Account No: </b>{{ optional($employee->currentBank)->account_no }}</span><br/>
                <span><b>Nominee Name: </b>{{ optional($employee->currentBank)->nominee_name }}</span><br/>
                <span><b>Relation with nominee: </b>{{ optional($employee->currentBank)->relation_with_nominee }}</span><br/>
                <span><b>Nominee Contact: </b>{{ optional($employee->currentBank)->nominee_contact }}</span><br/>
                <hr/>
            @endif
        </div>
    </div>
</div>

