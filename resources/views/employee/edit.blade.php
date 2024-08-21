@extends('layouts.app')
@section('top-css')
    <style type="text/css">
        .datepicker table tr td.disabled, .datepicker table tr td.disabled:hover {
            background: none;
            color: #eb1111 !important;
            cursor: default;
        }
        .datepicker table tr td.old, .datepicker table tr td.new {
            /* color: #3699ff !important; */
        }
        .datepicker tbody tr > td.day {
            color: #1a37df;
            font-weight: 400;
        }
        .datepicker tbody tr > td.day:hover {
            background: #F3F6F9;
            color: #3F4254;
        }
        .datepicker tbody tr > td.day.new {
            color: #3699ff;
        }
        .datepicker tbody tr > td.day.old {
            color: #3699ff;
        }

        .datepicker table tr td span.disabled, .datepicker table tr td span.disabled:hover {
            background: none;
            color: #e70e0e !important;
            cursor: default;
        }
        .datepicker tbody tr > td span.year,.datepicker tbody tr > td span.month {
            color: #3699ff;
        }
        .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom {
            border: 1px dashed #3699ff !important;
        }
        .datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top {
            border: 1px dashed #3699ff !important;
        }
        @media (min-width: 1200px) {
            .modal-dialog.modal-xl {
                max-width: 1240px !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('employee.update', ['employee' => $employee->uuid]) }}" method="POST" enctype="multipart/form-data" class="form" id="employee-edit-form">
                @csrf
                <div class="mt-n0">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header flex-wrap">
                            <div class="card-title">
                                <h3 class="card-label">BASIC INFORMATION</h3>
                            </div>
                            <div class="card-toolbar">
                                <nav class="font-weight-bolder">
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <a class="nav-item nav-link active" style="background-color: #eee" href="{{ route('employee.edit', ['employee' => $employee->uuid]) }}" role="tab">BASIC INFORMATION</a>
                                        <a class="nav-item nav-link" href="{{ route('employee.editMiscellaneous', ['employee' => $employee->uuid]) }}">PERSONAL INFORMATION</a>
                                    </div>
                                </nav>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-lg-4">
                                    <div class="image-input image-input-outline" id="kt_image_4"
                                         style="background-image: url({{ asset('/assets/media/users/blank.png') }} ">
                                        <div class="image-input-wrapper"
                                             style="background-image: url('{{
                                                                            file_exists(public_path()."/photo/".$employee->fingerprint_no.".jpg")
                                                                            ? asset("/photo/".$employee->fingerprint_no.".jpg")
                                                                             : asset("assets/media/svg/avatars/001-boy.svg")}}')"></div>
                                        <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                               data-action="change" data-toggle="tooltip" title="Upload" data-original-title="Change avatar">
                                            <i class="fa fa-pen icon-sm text-muted"></i>
                                            <input type="file" name="photo" accept=".png, .jpg, .jpeg"/>
                                            <input type="hidden" name="profile_avatar_remove"/>
                                        </label>
                                    </div>
                                    @error("photo")
                                    <p class="text-danger"> {{ $errors->first("photo") }} </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Full name --}}
                                        <div class="col-lg-4">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old("name") ?: $employee->name }}" name="name" class="form-control name" placeholder="Full Name" required/>
                                            <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("name")
                                            <p class="text-danger"> {{ $errors->first("name") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Phone Number --}}
                                        <div class="col-lg-4">
                                            <label>Phone Number (Official)</label>
                                            <input name="phone" value="{{ old("phone") ?: $employee->phone }}"
                                                   type="text" class="form-control" placeholder="Phone Number"/>
                                            <span class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("phone")
                                            <p class="text-danger"> {{ $errors->first("phone") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Fingerprint ID --}}
                                        <div class="col-lg-4">
                                            <label>Fingerprint ID <span class="text-danger">*</span></label>
                                            <input type="number" value="{{ old("fingerprint_no") ?: $employee->fingerprint_no }}"
                                                   class="form-control set_fingerprint_no" placeholder="Fingerprint ID" readonly/>
                                            <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("fingerprint_no")
                                            <p class="text-danger"> {{ $errors->first("fingerprint_no") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {{-- Date of Birth --}}
                                        <div class="col-lg-4">
                                            <label>Date of Birth
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" value="{{ date('Y-m-d', strtotime(old("dob") ?: $employee->profile->dob)) }}" name="dob" id="dob" class="form-control" autocomplete="off" placeholder="yyyy-mm-dd" readonly required/>
                                            @error("dob")
                                            <p class="text-danger"> {{ $errors->first("dob") }} </p>
                                            @enderror
                                        </div>

                                        {{-- NID --}}
                                        <div class="col-lg-4">
                                            <label>NID</label>
                                            <input name="nid" value="{{ old("nid") ?: $employee->profile->nid ?? '' }}"
                                                   type="text" class="form-control" placeholder="NID"/>
                                            <span class="form-text text-right text-custom-muted">At least 13 character</span>
                                            @error("nid")
                                            <p class="text-danger"> {{ $errors->first("nid") }} </p>
                                            @enderror
                                        </div>

                                        {{-- TIN --}}
                                        <div class="col-lg-4">
                                            <label>TIN</label>
                                            <input type="number" value="{{ old("tin") ?: $employee->profile->tin ?? '' }}" name="tin" class="form-control" placeholder="TIN"/>
                                            <span class="form-text text-right text-custom-muted">At least 12 character</span>
                                            @error("tin")
                                            <p class="text-danger"> {{ $errors->first("tin") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {{-- Email --}}
                                        <div class="col-lg-4">
                                            <label>Email (Official)</label>
                                            <input type="email" value="{{ old("email") ?: $employee->email }}"
                                                   name="email" class="form-control" placeholder="Email"/>
                                            @error("email")
                                            <p class="text-danger"> {{ $errors->first("email") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Gender --}}
                                        <div class="col-lg-4">
                                            <label>Gender <span class="text-danger">*</span></label>
                                            <select name="gender" class="form-control">
                                                <option value="Male"
                                                @if(isset($employee->profile->gender))
                                                    {{ $employee->profile->gender === \App\Models\Profile::GENDER_MALE ? 'selected' : '' }}
                                                    @endif
                                                >Male</option>
                                                <option value="Female"
                                                @if(isset($employee->profile->gender))
                                                    {{ $employee->profile->gender === \App\Models\Profile::GENDER_FEMALE ? 'selected' : '' }}
                                                    @endif
                                                >Female</option>
                                                <option value="Other"
                                                @if(isset($employee->profile->gender))
                                                    {{ $employee->profile->gender === \App\Models\Profile::GENDER_OTHER ? 'selected' : '' }}
                                                    @endif
                                                >Other</option>
                                            </select>
                                            @error("gender")
                                            <p class="text-danger"> {{ $errors->first("gender") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Religion --}}
                                        <div class="col-lg-4">
                                            <label>Religion <span class="text-danger">*</span></label>
                                            <select name="religion" class="form-control">
                                                <option value="Islam"
                                                @if(isset($employee->profile->religion))
                                                    {{ $employee->profile->religion === \App\Models\Profile::RELIGION_ISLAM ? 'selected' : '' }}
                                                    @endif
                                                >Islam</option>
                                                <option value="Hinduism"
                                                @if(isset($employee->profile->religion))
                                                    {{ $employee->profile->religion === \App\Models\Profile::RELIGION_HINDU ? 'selected' : '' }}
                                                    @endif
                                                >Hinduism</option>
                                                <option value="Christianity"
                                                @if(isset($employee->profile->religion))
                                                    {{ $employee->profile->religion === \App\Models\Profile::RELIGION_CHRISTIANITY ? 'selected' : '' }}
                                                    @endif
                                                >Christianity</option>
                                                <option value="Buddhism"
                                                @if(isset($employee->profile->religion))
                                                    {{ $employee->profile->religion === \App\Models\Profile::RELIGION_BUDDHISM ? 'selected' : '' }}
                                                    @endif
                                                >Buddhism</option>
                                                <option value="Other"
                                                @if(isset($employee->profile->religion))
                                                    {{ $employee->profile->religion === \App\Models\Profile::RELIGION_OTHER ? 'selected' : '' }}
                                                    @endif
                                                >Other</option>
                                            </select>
                                            @error("religion")
                                            <p class="text-danger"> {{ $errors->first("religion") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {{-- Marital Status --}}
                                        <div class="col-lg-4">
                                            <label>Marital Status <span class="text-danger">*</span></label>
                                            <select name="marital_status" class="form-control">
                                                <option value="Single"
                                                @if(isset($employee->profile->marital_status))
                                                    {{ $employee->profile->marital_status === \App\Models\Profile::MARITAL_STATUS_SINGLE ? 'selected' : '' }}
                                                    @endif
                                                >Single</option>
                                                <option value="Married"
                                                @if(isset($employee->profile->marital_status))
                                                    {{ $employee->profile->marital_status === \App\Models\Profile::MARITAL_STATUS_MARRIED ? 'selected' : '' }}
                                                    @endif
                                                >Married</option>
                                            </select>
                                            @error("marital_status")
                                            <p class="text-danger"> {{ $errors->first("marital_status") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Blood Group --}}
                                        <div class="col-lg-4">
                                            <label>Blood Group <span class="text-danger">*</span></label>
                                            <select name="blood_group" class="form-control">
                                                <option value="A+"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_A_POSITIVE ? 'selected' : '' }}
                                                    @endif
                                                >A+</option>
                                                <option value="A-"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_A_NEGATIVE ? 'selected' : '' }}
                                                    @endif
                                                >A-</option>
                                                <option value="B+"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_B_POSITIVE ? 'selected' : '' }}
                                                    @endif
                                                >B+</option>
                                                <option value="B-"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_B_NEGATIVE ? 'selected' : '' }}
                                                    @endif
                                                >B-</option>
                                                <option value="O+"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_O_POSITIVE ? 'selected' : '' }}
                                                    @endif
                                                >O+</option>
                                                <option value="O-"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_O_NEGATIVE ? 'selected' : '' }}
                                                    @endif
                                                >O-</option>
                                                <option value="AB+"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_AB_POSITIVE ? 'selected' : '' }}
                                                    @endif
                                                >AB+</option>
                                                <option value="AB-"
                                                @if(isset($employee->profile->blood_group))
                                                    {{ $employee->profile->blood_group === \App\Models\Profile::BLOOD_GROUP_AB_NEGATIVE ? 'selected' : '' }}
                                                    @endif
                                                >AB-</option>
                                            </select>
                                            @error("blood_group")
                                            <p class="text-danger"> {{ $errors->first("blood_group") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Emergency Contact --}}
                                        <div class="col-lg-4">
                                            <label>Emergency Contact No <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old("emergency_contact") ?: $employee->profile->emergency_contact ?? "" }}"
                                                   name="emergency_contact" class="form-control" placeholder="Emergency Contact No" required/>
                                            <span class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("emergency_contact")
                                            <p class="text-danger"> {{ $errors->first("emergency_contact") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Relation with Energency Contact --}}
                                        <div class="col-lg-4">
                                            <label>Relation with Emergency Contact <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old("relation") ?: $employee->profile->relation ?? "" }}"
                                                   name="relation" class="form-control" placeholder="Relation with Emergency Contact" required/>
                                            <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("relation")
                                            <p class="text-danger"> {{ $errors->first("relation") }} </p>
                                            @enderror
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Phone (Personal)<span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old("personal_phone") ?: $employee->profile->personal_phone ?? "" }}"
                                                   name="personal_phone" class="form-control" placeholder="Personal Phone Number" required/>
                                            <span class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("personal_phone")
                                            <p class="text-danger"> {{ $errors->first("personal_phone") }} </p>
                                            @enderror
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Email (Personal)</label>
                                            <input type="text" value="{{ old("personal_email") ?: $employee->profile->personal_email ?? "" }}"
                                                   name="personal_email" class="form-control" placeholder="Personal Email ID"/>

                                            @error("personal_email")
                                            <p class="text-danger"> {{ $errors->first("personal_email") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 3-->
                </div>

                <div class="mt-n5">
                    <!--begin::Mixed Widget 3-->
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header">
                            <h3 class="card-title">PROFESSIONAL INFORMATION</h3>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Office Division --}}
                                        <div class="col-lg-4">
                                            <label>Office Division <span class="text-danger">*</span></label>
                                            <select name="office_division_id" id="office_division_id" class="form-control" disabled>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["officeDivisions"] as $officeDivision)
                                                    <option value="{{ $officeDivision->id }}" {{ $employee->currentPromotion->office_division_id == $officeDivision->id ? 'selected' : '' }}>
                                                        {{ $officeDivision->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("office_division_id")
                                            <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-lg-4">
                                            <label>Department
                                                <span class="text-danger">*</span>
                                                {{--<a href="javascript:;" data-toggle="modal" data-target="#dept-modal"><span
                                                        class="plus-icon-color" id="dept-add-modal"><i class="fa fa-plus-square"></i></span></a>--}}
                                            </label>
                                            <select name="department_id" id="department_id" class="form-control" disabled>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}"
                                                    @if(isset($employee->currentPromotion->department_id))
                                                        {{ $employee->currentPromotion->department_id === $department->id ? 'selected' : '' }}
                                                        @endif
                                                    >{{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("department_id")
                                            <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Designation --}}
                                        <div class="col-lg-4">
                                            <label>
                                                Designation
                                                <span class="text-danger">*</span>
                                                <a href="javascript:;" data-toggle="modal" data-target="#designation-modal"><span
                                                        class="plus-icon-color" id="designation-add-modal"><i class="fa fa-plus-square"></i></span></a>
                                            </label>
                                            <select name="designation_id" class="form-control" id="custom_designation_id">
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["designations"] as $designation)
                                                    <option value="{{ $designation->id }}"
                                                    @if(isset($employee->currentPromotion->designation_id))
                                                        {{ $employee->currentPromotion->designation_id === $designation->id ? 'selected' : '' }}
                                                        @endif
                                                    >{{ $designation->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("designation_id")
                                            <p class="text-danger"> {{ $errors->first("designation_id") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    @php
                                        $permissionJoiningDate = 'disabled required';
                                        $permissionEmploymentType = 'disabled required';
                                        $permissionWorkSlot = 'disabled required';
                                        $permissionPayGrade = 'disabled required';
                                    @endphp
                                    @if(auth()->user()->can('Employee Edit Joining Date'))
                                        @php
                                            $permissionJoiningDate = 'required';
                                        @endphp
                                    @endif
                                    @if(auth()->user()->can('Employee Edit Employment Type'))
                                        @php
                                            $permissionEmploymentType = 'required';
                                        @endphp
                                    @endif

                                    @if(auth()->user()->can('Employee Edit Work Slot'))
                                        @php
                                            $permissionWorkSlot = 'required';
                                        @endphp
                                    @endif

                                    @if(auth()->user()->can('Employee Edit Pay Grade'))
                                        @php
                                            $permissionPayGrade = 'required';
                                        @endphp
                                    @endif

                                    <div class="form-group row">
                                        {{-- Joining Date --}}
                                        <div class="col-lg-2">
                                            <label>Joining Date <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ date('Y-m-d', strtotime(old("joining_date") ?: $employee->currentStatus->action_date ?? "")) }}" name="joining_date" placeholder="yyyy-mm-dd" id="joining-date" class="form-control" {{$permissionJoiningDate}} />
                                            @error("joining_date")
                                            <p class="text-danger"> {{ $errors->first("joining_date") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Provision Duration --}}
                                        <div class="col-lg-2">
                                            <label id="pay_grade_id">Provision Duration<span
                                                    class="text-danger">*</span></label>
                                            {{--<select name="provision_duration" id="provision_duration"
                                                    class="form-control" {{(!empty($employee->getEmploymentStatus()->employment_type) && $employee->getEmploymentStatus()->employment_type == \App\Models\Promotion::TYPE_PERMANENT)?"disabled":""}}>--}}
                                            <select name="provision_duration" id="provision_duration" class="form-control" required>
                                                <option selected disabled value="">Choose an option</option>
                                                @for($i=0;$i<=12;$i++)
                                                    <option @if($employee->provision_duration == $i) selected @endif value="{{$i}}">{{$i}}</option>
                                                @endfor
                                            </select>
                                            @error("provision_duration")
                                            <p class="text-danger"> {{ $errors->first("provision_duration") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Employment Type --}}
                                        <div class="col-lg-4">
                                            <label>Employment Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-control" {{$permissionEmploymentType}}>
                                                <option selected disabled value="">Choose an option</option>
                                                {{--<option value="Internee"
                                                @if(isset($employee->currentPromotion->type))
                                                    {{ $employee->currentPromotion->type === \App\Models\Promotion::TYPE_INTERNEE ? "selected" : "" }}
                                                    @endif
                                                >Internee</option>
                                                <option value="Provision"
                                                @if(isset($employee->currentPromotion->type))
                                                    {{ $employee->currentPromotion->type === \App\Models\Promotion::TYPE_PROVISION ? "selected" : "" }}
                                                    @endif
                                                >Provision</option>
                                                <option value="Permanent"
                                                @if(isset($employee->currentPromotion->type))
                                                    {{ $employee->currentPromotion->type === \App\Models\Promotion::TYPE_PERMANENT ? "selected" : "" }}
                                                    @endif
                                                >Permanent</option>
                                                <option value="Promoted"
                                                @if(isset($employee->currentPromotion->type))
                                                    {{ $employee->currentPromotion->type === \App\Models\Promotion::TYPE_PROMOTED ? "selected" : "" }}
                                                    @endif
                                                >Promoted</option>
                                                <option value="Contractual"
                                                @if(isset($employee->currentPromotion->type))
                                                    {{ $employee->currentPromotion->type === \App\Models\Promotion::TYPE_CONTRACTUAL ? "selected" : "" }}
                                                    @endif
                                                >Contractual</option>--}}
                                                @foreach (\App\Models\Promotion::employmentType() as $key => $value)
                                                    <option value="{{ $key }}" {{ ($employee->currentPromotion->employment_type == $key) ? "selected" : "" }}>{{$value}}</option>
                                                @endforeach
                                            </select>
                                            @error("type")
                                            <p class="text-danger"> {{ $errors->first("type") }} </p>
                                            @enderror
                                        </div>

                                        {{-- WorkSlot --}}
                                        <div class="col-lg-4">
                                            <label id="workslot_id">Work Slot<span class="text-danger">*</span></label>
                                            <select name="workslot_id" class="form-control" {{$permissionWorkSlot}}>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["workSlots"] as $workSlot)
                                                    <option value="{{ $workSlot->id }}" {{ $employee->currentPromotion->workslot_id == $workSlot->id ? "selected" : "" }}>
                                                        {{ $workSlot->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("workslot_id")
                                            <p class="text-danger"> {{ $errors->first("workslot_id") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-lg-4">
                                            <label id="payment_mode">Payment Mode<span class="text-danger">*</span></label>
                                            <select name="payment_mode" class="form-control">
                                                @foreach (\App\Models\User::paymentModes() as $key => $value)
                                                    <option value="{{ $key }}" {{ ($employee->payment_mode == $key) ? "selected" : "" }}>{{$value}}</option>
                                                @endforeach
                                            </select>
                                            @error("payment_mode")
                                            <p class="text-danger"> {{ $errors->first("payment_mode") }} </p>
                                            @enderror
                                        </div>
                                        {{-- Salary --}}
                                        @if(auth()->user()->can("Edit Employee Salary"))
                                            <div class="col-lg-4">
                                                <label>Salary<span class="text-danger">*</span></label>
                                                <input type="text" value="{{ $employee->currentPromotion->salary ?? "" }}"  name="salary"
                                                       class="form-control" placeholder="Salary" required/>
                                                @error("salary")
                                                <p class="text-danger"> {{ $errors->first("salary") }} </p>
                                                @enderror
                                            </div>
                                        @endif

                                        {{-- PayGrade --}}
                                        <div class="col-lg-4">
                                            <label id="pay_grade_id">Pay Grade<span class="text-danger">*</span></label>
                                            <select name="pay_grade_id" class="form-control" {{$permissionPayGrade}}>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["payGrades"] as $payGrade)
                                                    <option value="{{ $payGrade->id }}" {{ $employee->currentPromotion->pay_grade_id == $payGrade->id ? "selected" : "" }}>
                                                        {{ $payGrade->name }} <{{ $payGrade->range_start_from }} - {{ $payGrade->range_end_to }}> BDT
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("pay_grade_id")
                                            <p class="text-danger"> {{ $errors->first("pay_grade_id") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 3-->
                </div>

                <div class="mt-n5">
                    <!--begin::Mixed Widget 3-->
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Body-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-10"></div>
                                <div class="col-lg-2">
                                    <button type="reset" class="btn btn-secondary ml-lg-12">Reset</button>
                                    <button type="submit" class="btn btn-primary float-right ml-0">Update</button>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 3-->
                </div>
            </form>
            <!--end::Form-->
        </div>
    </div>

    <div class="modal fade" id="dept-modal" tabindex="-1" role="dialog" aria-labelledby="deptModalTitle"
         aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dept-modal">Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form name="department-modal" id="creation-department" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        @include('department.common-view.common_create',['items'=>$deptInfos['items'],'officeDivisions'=>$deptInfos['officeDivisions'],'warehouses'=>$deptInfos['warehouses'],'leaveTypes'=>$deptInfos['leaveTypes'],'trackingType'=>$deptInfos['trackingType']])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="creation-department-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="designation-modal" tabindex="-1" role="dialog"
         aria-labelledby="designationModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="designation-modal">Designation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form name="designation-modal" id="creation-designation" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        @include('designation.common-view.common_create')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="creation-designation-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/image-input.js') }}"></script>
    <script type="text/javascript">
        // Get department by division
        $('#office_division_id').change(function(){
            var _officeDivisionID = $(this).val();

            let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
            url = url.replace(":officeDivision", _officeDivisionID);

            $.get(url, {}, function (response, status) {
                $("#department_id").empty();
                $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data.departments, function(key, value) {
                    $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
            })
        });
        $('#joining-date').datepicker({
            minDate: 0,
            format: 'yyyy-mm-dd',
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function (event) {
            var getDOBDate = $('#dob').val();
            if(!getDOBDate){
                toastr.error("At First Given Date of Birth!!!");
                $('#joining-date').val("");
            }
            var today = new Date($('#joining-date').val());
            var birthDate = new Date(getDOBDate);
            var age = today.getFullYear() - birthDate.getFullYear();
            var m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            if(age < 18){
                toastr.error('Below 18 years old! Invalid For Fill Up This Form!!!');
                $('#joining-date').val("");
            }
        });
        $('#dob').datepicker({
            minDate: 0,
            // startDate: '-40y',
            format: 'yyyy-mm-dd',
            autoclose: true,
            endDate: "-18y"
        });
    </script>

    <script>
        $(document).ready(function () {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "1000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            $('#creation-department-btn').on('click', function (e) {
                e.preventDefault();
                var url = '{{route('department.store-ajax')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: $('#creation-department').serialize(),
                    dataType: "json",
                    success: function (result) {
                        if(result.status === 'already_taken'){
                            toastr.error(result.message)
                        }else if(result.status === 'success'){
                            toastr.success(result.message)
                            $('#dept-modal').modal('toggle')
                            $('#office_division_id').change()
                        }else{
                            toastr.error("Something went wrong!!!")
                        }
                    },
                    error: function (result, y) {
                        if (result.responseJSON.errors) {
                            let msg = "";
                            $.each(result.responseJSON.errors, function (x, y) {
                                $.each(y, function (a, b) {
                                    msg += b + "<br>";
                                })
                            })
                            toastr.error(msg);
                        }
                    }
                });
            });
            $('#creation-designation-btn').on('click', function (e) {
                e.preventDefault();
                var url = '{{route('designation.store-ajax')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: $('#creation-designation').serialize(),
                    dataType: "json",
                    success: function (result) {
                        //console.log("Result",result);
                        if(result.status === 'success'){
                            toastr.success(result.message);
                            $('#designation-modal').modal('toggle');
                        }
                    },
                    complete:function(result,status){
                        if(status !== 'error') {
                            $('#custom_designation_id').empty();
                            $('#custom_designation_id').append('<option value="" "selected disabled"> Choose an option </option>');
                            $.each(result.responseJSON.designations, function (key, val) {
                                $('#custom_designation_id').append('<option value="' + val.id + '">' + val.title + '</option>');
                            })
                        }
                    },
                    error: function (result, y) {
                        if (result.responseJSON.errors) {
                            let msg = "";
                            $.each(result.responseJSON.errors, function (x, y) {
                                $.each(y, function (a, b) {
                                    msg += b + "<br>";
                                })
                            })
                            toastr.error(msg);
                        }
                    }
                });
            });
        });
    </script>
    <script>
        let length = 0;
        function onChangeCheckBox() {
            let chk_arr = document.querySelectorAll("input[name='days[]']:checked");
            length = chk_arr.length;
        }
        function submitCheck(event) {
            if (length <= 0) {
                event.preventDefault();
                notify().error("You have to check at least one weekly holiday.")
            }
        }
        $("#datepicker").datepicker({
            format: "yyyy",
            startView: "years",
            minViewMode: "years",
            autoclose: true
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#is_warehouse').on('click', function () {
                if (Number($(this).val()) == 0) {
                    $(this).val(1);
                    $(this).prop("checked", true);
                    $('#warehouse_id').removeClass('disabled');
                    $('.warehouse_id_div').removeClass('d-none');
                } else {
                    $(this).val(0);
                    $('#warehouse_id').val('');
                    $(this).prop("checked", false);
                    $('#warehouse_id').addClass('disabled');
                    $('.warehouse_id_div').addClass('d-none');
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '#dept-add-modal', function (e) {
            $.ajax({
                type: "POST",
                url: '{{route('employee.modalDepartment')}}',
                data: {
                    office_division_id: $('#office_division_id').val()
                },
                dataType: "json",
                success: function (result) {
                    $("#office_division_id_in_modal").empty();
                    $("#office_division_id_in_modal").append('<option value="" disabled selected>Select an option</option>');
                    $.each(result.officeDivisions, function (keyODiv, valueODiv) {
                        if (result.officeDivisionId == valueODiv.id) {
                            $("#office_division_id_in_modal").append('<option value="' + valueODiv.id + '" selected>' + valueODiv.name + '</option>');
                        } else {
                            $("#office_division_id_in_modal").append('<option value="' + valueODiv.id + '">' + valueODiv.name + '</option>');
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '#designation-add-modal', function (e) {
            $.ajax({
                type: "POST",
                url: '{{route('employee.modalDesignation')}}',
                data:{},
                dataType: "json",
                success: function (result) {
                    $('#creation-designation')[0].reset();
                }
            });
        });
        $('#employee-edit-form').submit(function(e) {
            $(':disabled').each(function(e) {
                $(this).removeAttr('disabled');
            })
        })
    </script>

@endsection
