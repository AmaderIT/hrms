
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="{{ asset('/assets/css/bootstrap-3.3.7.min.css') }}" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ $data["employee"]->name }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-2">
                <img src='{{ asset("photo/".$data["employee"]->fingerprint_no.".jpg") . "?" . uniqid() }}' onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';" width="110" />
            </div>
            <div class="col-lg-5">

                {{-- Employee Info --}}
                <span><b>Office ID: </b>{{ $data["employee"]->fingerprint_no }}</span><br/>
                <span><b>Email: </b>{{ $data["employee"]->email }}</span><br/>
                <span><b>Phone: </b>{{ $data["employee"]->phone }}</span><br/>

                @if(!is_null($data["employee"]->currentPromotion))
                    <span><b>Office Division: </b>{{ $data["employee"]->currentPromotion->officeDivision->name }}</span><br/>
                    <span><b>Department: </b>{{ $data["employee"]->currentPromotion->department->name }}</span><br/>
                    <span><b>Designation: </b>{{ $data["employee"]->currentPromotion->designation->title }}</span><br/>
                    <span><b>Joining Date: </b>{{ \Carbon\Carbon::createFromDate($data["employee"]->currentStatus->action_date->toDateTimeString())->format("M d, Y") }}</span><br/>
                    <span><b>Employment Type: </b>{{ $data["employee"]->currentPromotion->type }}</span><br/>
                    <span><b>WorkSlot: </b>{{ $data["employee"]->currentPromotion->workSlot->title }}</span><br/>
                    @if(auth()->user()->can("Show Salary") OR auth()->user()->id == $data["employee"]->id)
                        <span><b>Salary: </b>{{ $data["employee"]->currentPromotion->salary }}</span><br/>
                    @endcan
                    <span><b>PayGrade: </b>{{ $data["employee"]->currentPromotion->payGrade->name }}</span><br/>
                @endif

                {{-- Personal Info --}}
                <hr/>
                <span class="font-size-h4">Personal Information</span><br>
                <span><b>Gender: </b>{{ $data["employee"]->profile->gender }}</span><br/>
                <span><b>Religion: </b>{{ $data["employee"]->profile->religion }}</span><br/>
                <span><b>Date of Birth: </b>{{ \Carbon\Carbon::createFromDate($data["employee"]->profile->dob->toDateTimeString())->format("M d, Y") }}</span><br/>
                <span><b>Marital Status: </b>{{ $data["employee"]->profile->marital_status }}</span><br/>
                <span><b>Blood Group: </b>{{ $data["employee"]->profile->blood_group }}</span><br/>
                <span><b>Emergency Contact: </b>{{ $data["employee"]->profile->emergency_contact }}</span><br/>
                <span><b>Relation with Emergency Contact: </b>{{ $data["employee"]->profile->relation }}</span><br/>
                <span><b>NID: </b>{{ $data["employee"]->profile->nid }}</span><br/>
                <span><b>TIN: </b>{{ $data["employee"]->profile->tin }}</span><br/>
                <br/>

                @if(isset($data["employee"]->presentAddress))
                    <span><b>Present Address: </b>{{ $data["employee"]->presentAddress->address }}</span><br/>
                    <span><b>Division: </b>{{ $data["employee"]->presentAddress->division->name }}</span><br/>
                    <span><b>District: </b>{{ $data["employee"]->presentAddress->district->name }}</span><br/>
                    <span><b>Zip Code: </b>{{ $data["employee"]->presentAddress->zip }}</span><br/>
                @endif

                <br/>

                @if(isset($data["employee"]->permanentAddress))
                    <span><b>Permanent Address: </b>{{ $data["employee"]->permanentAddress->address }}</span><br/>
                    <span><b>Division: </b>{{ $data["employee"]->permanentAddress->division->name }}</span><br/>
                    <span><b>District: </b>{{ $data["employee"]->permanentAddress->district->name }}</span><br/>
                    <span><b>Zip Code: </b>{{ $data["employee"]->permanentAddress->zip }}</span><br/>
                @endif

                <hr/>

                @if(count($data["employee"]->jobHistories) > 0)
                    <span class="font-size-h4">Professional Experience</span><br>
                    @foreach($data["employee"]->jobHistories as $jobHistory)
                        <span><b>Organization: </b>{{ $jobHistory->organization_name }}</span><br/>
                        <span><b>Designation: </b>{{ $jobHistory->designationEmployee->title }}</span><br/>
                        <span><b>Start Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->start_date)->format("M d, Y") }}</span><br/>
                        <span><b>End Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->end_date)->format("M d, Y") }}</span><br/>
                        <p></p>
                    @endforeach
                @endif
            </div>
            <div class="col-lg-5">
                {{-- Employee Education --}}
                <span class="font-size-h4">Education Information</span><br>
                @foreach($data["employee"]->degrees as $degree)

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

                {{-- Employee Bank Info --}}
                @if(!is_null($data["employee"]->currentBank))

                    <?php
                    $employee = $data["employee"];
                    $bank = $data["banks"]->filter(function ($query) use ($employee) {
                        return $query->id === $employee->currentBank->bank_id;
                    })->values()->first();
                    ?>

                    <?php
                    $employee = $data["employee"];
                    $branch = $data["branches"]->filter(function ($query) use ($employee) {
                        return $query->id === $employee->currentBank->branch_id;
                    })->values()->first();
                    ?>

                    <span class="font-size-h4">Bank Information</span><br>
                    <span><b>Bank Name: </b>{{ $bank->name }}</span><br/>
                    <span><b>Branch: </b>{{ $branch->name }}</span><br/>
                    <span><b>Account Type: </b>{{ $data["employee"]->currentBank->account_type }}</span><br/>
                    <span><b>Account Name: </b>{{ $data["employee"]->currentBank->account_name }}</span><br/>
                    <span><b>Account No: </b>{{ $data["employee"]->currentBank->account_no }}</span><br/>
                    <span><b>Nominee Name: </b>{{ $data["employee"]->currentBank->nominee_name }}</span><br/>
                    <span><b>Relation with nominee: </b>{{ $data["employee"]->currentBank->relation_with_nominee }}</span><br/>
                    <span><b>Nominee Contact: </b>{{ $data["employee"]->currentBank->nominee_contact }}</span><br/>
                    <hr/>
                @endif
            </div>
        </div>
    </div>
</div>

