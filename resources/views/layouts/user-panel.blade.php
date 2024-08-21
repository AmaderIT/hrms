@php
    $employee = auth()->user()->load(
                    "profile", "currentPromotion.officeDivision", "currentPromotion.department", "currentPromotion.designation",
                    "currentPromotion.payGrade", "currentPromotion.workSlot", "currentStatus", "presentAddress.division", "presentAddress.district",
                    "permanentAddress.division", "permanentAddress.district", "jobHistories", "degrees", "currentBank",
                );

    $banks          = App\Models\Bank::orderByDesc("id")->select("id", "name")->get();
    $branches       = App\Models\Branch::orderByDesc("id")->select("id", "name")->get();
    $institutes     = App\Models\Institute::orderByDesc("id")->select("id", "name")->get();
    $officeDivisions= App\Models\OfficeDivision::select("id", "name")->get();
@endphp

<div id="kt_quick_user" class="offcanvas offcanvas-right p-10">
    <div class="offcanvas-header d-flex align-items-center justify-content-between pb-5">
        <h3 class="font-weight-bold m-0">Employee Profile</h3>
        <a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
            <i class="ki ki-close icon-xs text-muted"></i>
        </a>
    </div>
    <div class="offcanvas-content pr-5 mr-n5">
        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-100 mr-5">
                <div class="symbol-label"
                     style="background-image:url('{{ asset("photo/".auth()->user()->fingerprint_no.".jpg") . "?" . uniqid() }}')"></div>
                <i class="symbol-badge bg-success"></i>
            </div>
            <div class="d-flex flex-column">
                <a href="#" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary">
                    <a href="#" data-toggle="modal"
                       data-target="#myProfileModal-{{ auth()->user()->id }}">{{ auth()->user()->name }} -
                        ({{ auth()->user()->fingerprint_no }})</a>
                    <p>{{ $employee->currentPromotion->officeDivision->name . ", " . $employee->currentPromotion->department->name }}</p>
                    <a href="{{ route('employee.profile', ['employee' => auth()->user()->uuid]) }}">Edit Profile</a>
                </a>
            </div>
        </div>
        <div class="separator separator-dashed mt-8 mb-5"></div>
        <div class="navi navi-spacer-x-0 p-0">
            <a href="{{ route('employee.changePassword', ['employee' => auth()->user()->uuid]) }}" class="navi-item">
                <div class="navi-link">
                    <div class="symbol symbol-40 bg-light mr-3">
                        <div class="symbol-label">
                            <span class="svg-icon svg-icon-md svg-icon-success">
                                <!--begin::Svg Icon | path:assets/media/svg/icons/General/Settings-1.svg-->
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                     width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24"></rect>
                                        <path
                                            d="M7,3 L17,3 C19.209139,3 21,4.790861 21,7 C21,9.209139 19.209139,11 17,11 L7,11 C4.790861,11 3,9.209139 3,7 C3,4.790861 4.790861,3 7,3 Z M7,9 C8.1045695,9 9,8.1045695 9,7 C9,5.8954305 8.1045695,5 7,5 C5.8954305,5 5,5.8954305 5,7 C5,8.1045695 5.8954305,9 7,9 Z"
                                            fill="#000000"></path>
                                        <path
                                            d="M7,13 L17,13 C19.209139,13 21,14.790861 21,17 C21,19.209139 19.209139,21 17,21 L7,21 C4.790861,21 3,19.209139 3,17 C3,14.790861 4.790861,13 7,13 Z M17,19 C18.1045695,19 19,18.1045695 19,17 C19,15.8954305 18.1045695,15 17,15 C15.8954305,15 15,15.8954305 15,17 C15,18.1045695 15.8954305,19 17,19 Z"
                                            fill="#000000" opacity="0.3"></path>
                                    </g>
                                </svg>
                                <!--end::Svg Icon-->
                            </span>
                        </div>
                    </div>
                    <div class="navi-text">
                        <div class="font-weight-bold">Change Password</div>
                        <div class="text-muted">Change employee password</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="navi navi-spacer-x-0 p-0">
            @can("Generate Unpaid Leave Report")
                <span class="navi-item mt-2">
                <span class="navi-link">
                    <a href="{{ route('leave-unpaid.generate') }}"
                       class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">Generate Unpaid Leave</a>
                </span>
            </span>
            @endcan

            @can("Sync Employee Leave Balance")
                <span class="navi-item mt-2">
                <span class="navi-link">
                    <a href="{{ route('requested-application.syncBalance') }}"
                       class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">Sync Employee Leave Balance</a>
                </span>
            </span>
            @endcan

            @can("View User Late")
                <span class="navi-item mt-2">
                <span class="navi-link">
                    <a href="{{ route('late-management.user-late') }}"
                       class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">User Late</a>
                </span>
            </span>
            @endcan

            @can("Generate Daily Attendance")
                <span class="navi-item mt-2">
                <span class="navi-link">
                    <a href="{{ route('daily-attendance.generate') }}"
                       class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">Daily Attendance</a>
                </span>
            </span>
            @endcan

            <span class="navi-item mt-2">
                <span class="navi-link">
                    <a id="logout-btn" href="{{ route('logout') }}"
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();localStorage.clear();sessionStorage.clear();console.log('Local Storage cleared')"
                       class="btn btn-sm btn-light-primary font-weight-bolder py-3 px-6">Sign Out</a>
                </span>
            </span>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
        <div class="separator separator-dashed my-7"></div>
    </div>
</div>

<div class="modal fade" id="myProfileModal-{{ $employee->id }}" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalSizeXl" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ $employee->name }}</h5>
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


                        <span><b>Office ID: </b>{{ $employee->fingerprint_no }}</span><br/>
                        <span><b>Email: </b>{{ $employee->email }}</span><br/>
                        <span><b>Phone: </b>{{ $employee->phone }}</span><br/>

                        <span><b>Office Division: </b>{{ $employee->currentPromotion->officeDivision->name }}</span><br/>
                        <span><b>Department: </b>{{ $employee->currentPromotion->department->name }}</span><br/>
                        <span><b>Designation: </b>{{ optional($employee->currentPromotion->designation)->title }}</span><br/>
                        <span><b>Joining Date: </b>{{ \Carbon\Carbon::createFromDate($employee->currentStatus->action_date->toDateTimeString())->format("M d, Y") }}</span><br/>
                        <span><b>Employment Type: </b>{{ optional($employee->getEmploymentStatus())->employment_type }}</span><br/>
                        <span><b>WorkSlot: </b>{{ $employee->currentPromotion->workSlot->title }}</span><br/>
                        @if(!empty($employee->last_login_at) && auth()->user()->id == $employee->id)
                            <span><b>Salary: </b>{{ $employee->currentPromotion->salary }}</span><br/>
                        @endcan
                        <span><b>PayGrade: </b>{{ $employee->currentPromotion->payGrade->name }}</span><br/>


                        <hr/>
                        <span class="font-size-h4">Personal Information</span><br>
                        <span><b>Gender: </b>{{ $employee->profile->gender }}</span><br/>
                        <span><b>Religion: </b>{{ $employee->profile->religion }}</span><br/>
                        <span><b>Date of Birth: </b>{{ \Carbon\Carbon::createFromDate($employee->profile->dob->toDateTimeString())->format("M d, Y") }}</span><br/>
                        <span><b>Marital Status: </b>{{ $employee->profile->marital_status }}</span><br/>
                        <span><b>Blood Group: </b>{{ $employee->profile->blood_group }}</span><br/>
                        <span><b>Emergency Contact: </b>{{ $employee->profile->emergency_contact }}</span><br/>
                        <span><b>Relation with Emergency Contact: </b>{{ $employee->profile->relation }}</span><br/>
                        <span><b>NID: </b>{{ $employee->profile->nid }}</span><br/>
                        <span><b>TIN: </b>{{ $employee->profile->tin }}</span><br/>
                        <br/>

                        @if(isset($employee->presentAddress))
                            <span><b>Present Address: </b>{{ $employee->presentAddress->address ?? "" }}</span><br/>
                            <span><b>Division: </b>{{ $employee->presentAddress->division->name ?? "" }}</span><br/>
                            <span><b>District: </b>{{ $employee->presentAddress->district->name ?? "" }}</span><br/>
                            <span><b>Zip Code: </b>{{ $employee->presentAddress->zip ?? "" }}</span><br/>
                        @endif

                        <br/>


                        @if(isset($employee->permanentAddress))
                            <span><b>Permanent Address: </b>{{ $employee->permanentAddress->address ?? "" }}</span><br/>
                            <span><b>Division: </b>{{ $employee->permanentAddress->division->name ?? "" }}</span><br/>
                            <span><b>District: </b>{{ $employee->permanentAddress->district->name ?? "" }}</span><br/>
                            <span><b>Zip Code: </b>{{ $employee->permanentAddress->zip ?? "" }}</span><br/>
                        @endif

                        <hr/>

                        @if(count($employee->jobHistories) > 0)
                            <span class="font-size-h4">Professional Experience</span><br>
                            @foreach($employee->jobHistories as $jobHistory)
                                <span><b>Organization: </b>{{ $jobHistory->organization_name ?? '' }}</span><br/>
                                <span><b>Designation: </b>{{ $jobHistory->designationEmployee->title ?? '' }}</span>
                                <br/>
                                <span><b>Start Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->start_date)->format("M d, Y") }}</span>
                                <br/>
                                <span><b>End Date: </b>{{ \Carbon\Carbon::createFromDate($jobHistory->end_date)->format("M d, Y") }}</span>
                                <br/>
                                <p></p>
                            @endforeach
                        @endif
                    </div>
                    <div class="col-lg-5">

                        <span class="font-size-h4">Education Information</span><br>
                        @foreach($employee->degrees as $degree)

                            <?php
                            $institute = $institutes->filter(function ($query) use ($degree) {
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
                            $bank = $banks->filter(function ($query) use ($employee) {
                                return $query->id === $employee->currentBank->bank_id;
                            })->values()->first();
                            ?>

                            <?php
                            $branch = $branches->filter(function ($query) use ($employee) {
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
                            <span><b>Relation with nominee: </b>{{ optional($employee->currentBank)->relation_with_nominee }}</span>
                            <br/>
                            <span><b>Nominee Contact: </b>{{ optional($employee->currentBank)->nominee_contact }}</span>
                            <br/>
                            <hr/>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .modalBlur > *:not(.modal) {
        -webkit-filter: blur(8px);
    }
</style>

<div class="modal fade" id="login_session_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">User Session Expired</h5>
            </div>
            <div class="modal-body">
                <h7>You have been logged out. Please log in again.</h7>
                <button onclick="location=''" type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Login</button>
            </div>
        </div>
    </div>
</div>
</div>


<script>
    let source = new EventSource('{{route("check-auth")}}');
    source.onmessage = function (event) {
        if (event.data == 0) {
            $('body').addClass('modalBlur')

            $('.modal').modal('hide');
            setTimeout(function (){
                $('#login_session_modal').modal('show');
                source.close();
                setTimeout(function (){
                    location = '';
                },2000)
            },500)
        }
    };
</script>

