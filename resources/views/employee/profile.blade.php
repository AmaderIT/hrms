@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Form-->

            <form action="{{ route('employee.updateProfile', ['employee' => $employee->uuid]) }}" method="POST" enctype="multipart/form-data" class="form">
                @csrf
                <div class="mt-n0">
                    <!--begin::Mixed Widget 3-->
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header flex-wrap">
                            <div class="card-title">
                                <h3 class="card-label">PERSONAL INFORMATION</h3>
                            </div>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-lg-4">
                                    <div class="image-input image-input-outline" id="kt_image_4"
                                        style="background-image: url({{ asset('/assets/media/users/blank.png') }}?{{ time() }}">
                                        <div class="image-input-wrapper"
                                            style="background-image: url('{{
                                                file_exists(public_path()."/photo/".$employee->fingerprint_no.".jpg")
                                                ? asset("/photo/".$employee->fingerprint_no.".jpg") . "?" . uniqid()
                                                : asset("assets/media/svg/avatars/001-boy.svg")}}')">
                                        </div>
                                        <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                               data-action="change" data-toggle="tooltip" title="Upload" data-original-title="Change avatar">
                                            <i class="fa fa-pen icon-sm text-muted"></i>
                                            <input type="file" name="photo" accept=".png, .jpg, .jpeg"/>
{{--                                            <input type="hidden" name="profile_avatar_remove"/>--}}
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
                                            <input type="text" value="{{ old("name") ?: $employee->name }}" name="name" class="form-control name" placeholder="Full Name" readonly required/>
                                            <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("name")
                                            <p class="text-danger"> {{ $errors->first("name") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Phone Number --}}
                                        <div class="col-lg-4">
                                            <label>Phone Number (Official)</label>
                                            <input name="phone" value="{{ old("phone") ?: $employee->phone }}"
                                                   type="text" class="form-control" placeholder="Phone Number" readonly/>
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
                                            <input type="date" value="{{ date('Y-m-d', strtotime(old("dob") ?: $employee->profile->dob)) }}" name="dob" class="form-control" readonly required/>
                                            @error("dob")
                                            <p class="text-danger"> {{ $errors->first("dob") }} </p>
                                            @enderror
                                        </div>

                                        {{-- NID --}}
                                        <div class="col-lg-4">
                                            <label>NID</label>
                                            <input name="nid" value="{{ old("nid") ?: $employee->profile->nid ?? '' }}"
                                                   type="text" class="form-control" placeholder="NID" readonly/>
                                            <span class="form-text text-right text-custom-muted">At least 13 character</span>
                                            @error("nid")
                                            <p class="text-danger"> {{ $errors->first("nid") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-lg-4">
                                            <label>Email (Official)</label>
                                            <input type="email" value="{{ old("email") ?: $employee->email }}"
                                                   name="email" class="form-control" placeholder="Email" readonly/>
                                            @error("email")
                                            <p class="text-danger"> {{ $errors->first("email") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
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
                                    </div>
                                    <div class="form-group row">
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
                                                   name="emergency_contact" class="form-control" placeholder="Emergency Contact No" readonly required/>
                                            <span class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("emergency_contact")
                                            <p class="text-danger"> {{ $errors->first("emergency_contact") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Relation with Energency Contact --}}
                                        <div class="col-lg-4">
                                            <label>Relation with Emergency Contact <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old("relation") ?: $employee->profile->relation ?? "" }}"
                                                   name="relation" class="form-control" placeholder="Relation with Emergency Contact" readonly required/>
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
                                                   name="personal_email" class="form-control" placeholder="Personal Email ID" readonly/>

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
                                            <label>Department <span class="text-danger">*</span></label>
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
                                            <label>Designation <span class="text-danger">*</span></label>
                                            <select name="designation_id" class="form-control" disabled>
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

                                    <div class="form-group row">
                                        {{-- Joining Date --}}
                                        <div class="col-lg-4">
                                            <label>Joining Date <span class="text-danger">*</span></label>
                                            <input type="date" value="{{ date('Y-m-d', strtotime(old("joining_date") ?: $employee->currentStatus->action_date ?? "")) }}" name="joining_date" class="form-control" readonly required/>
                                            @error("joining_date")
                                            <p class="text-danger"> {{ $errors->first("joining_date") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Employment Type --}}
                                        <div class="col-lg-4">
                                            <label>Employment Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-control" disabled>
                                                <option selected disabled value="">Choose an option</option>
                                                <option value="Internee"
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
                                                >Contractual</option>
                                            </select>
                                            @error("type")
                                            <p class="text-danger"> {{ $errors->first("type") }} </p>
                                            @enderror
                                        </div>

                                        {{-- WorkSlot --}}
                                        <div class="col-lg-4">
                                            <label id="workslot_id">Work Slot<span class="text-danger">*</span></label>
                                            <select name="workslot_id" class="form-control" disabled>
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

                                    @if(auth()->user()->id == $employee->id || auth()->user()->can("View Salary"))
                                    <div class="form-group row">
                                        {{-- Salary --}}
                                        <div class="col-lg-4">
                                            <label>Salary<span class="text-danger">*</span></label>
                                            <input type="salary" value="{{ $employee->currentPromotion->salary ?? "" }}"  name="salary"
                                                   class="form-control" placeholder="Salary" readonly required/>
                                            @error("salary")
                                            <p class="text-danger"> {{ $errors->first("salary") }} </p>
                                            @enderror
                                        </div>
                                        @endif

                                        {{-- PayGrade --}}
                                        <div class="col-lg-4">
                                            <label id="pay_grade_id">Pay Grade<span class="text-danger">*</span></label>
                                            <select name="pay_grade_id" class="form-control" disabled>
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

                                        <div class="col-lg-4">
                                            <label for="role_id">Role Type</label>
                                            <select name="role_id" class="form-control" disabled>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["roles"] as $role)
                                                    <option value="{{ $role->id }}" {{ in_array($role->name, $getRoles->toArray()) == $role->name ? "selected" : "" }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("role_id")
                                            <p class="text-danger"> {{ $errors->first("role_id") }} </p>
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
                <div class="mt-n0">
                        <!--begin::Mixed Widget 3-->
                        <div class="card card-custom card-stretch gutter-b">
                            <!--begin::Header-->
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">PERSONAL INFORMATION</h3>
                                </div>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            {{-- Address --}}
                                            <div class="col-lg-4">
                                                <label>Present Address
                                                    {{--<span class="text-danger">*</span>--}}
                                                </label>
                                                <textarea class="form-control present_address_address" name="present_address[address]" rows="6">{{ old("present_address.address") ?: $employee->presentAddress->address ?? "" }}</textarea>
                                                <span class="form-text text-right text-custom-muted">At least 10 character</span>
                                                @error("present_address.address")
                                                <p class="text-danger"> {{ $errors->first("present_address.address") }} </p>
                                                @enderror
                                            </div>

                                            {{-- Division --}}
                                            <div class="col-lg-4">
                                                <label>Division
                                                    {{--<span class="text-danger">*</span>--}}
                                                </label>
                                                <select name="present_address[division_id]" class="form-control present_address_division_id" id="present_address_division_id">
                                                    <option selected disabled value="">Choose an option</option>
                                                    @foreach($data["divisions"] as $division)
                                                        <option value="{{ $division->id }}"
                                                        @if(isset($employee->presentAddress->division->id))
                                                            {{ $employee->presentAddress->division->id === $division->id ? 'selected' : '' }}
                                                            @endif
                                                        >
                                                            {{ $division->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("present_address.division_id")
                                                <p class="text-danger">{{ $errors->first("present_address.division_id") }}</p>
                                                @enderror

                                                {{-- Zip code --}}
                                                <div class="col-lg-12 p-0 pt-9">
                                                    <label>Zip Code</label>
                                                    <input type="number" value="{{ old("present_address.zip") ?: $employee->presentAddress->zip ?? "" }}"
                                                           name="present_address[zip]" class="form-control present_address_zip" placeholder="Zip Code"/>
                                                    <span class="form-text text-right text-custom-muted">At least 4 character</span>
                                                    @error("present_address.zip")
                                                    <p class="text-danger"> {{ $errors->first("present_address.zip") }} </p>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- District --}}
                                            <div class="col-lg-4">
                                                <label>District
                                                    {{--<span class="text-danger">*</span>--}}
                                                </label>
                                                <select name="present_address[district_id]" class="form-control present_address_district_id" id="present_address_district_id">
                                                    <option selected disabled value="">Choose an option</option>
                                                    @if(!is_null($presentAddressDistrict))
                                                        @foreach($presentAddressDistrict as $district)
                                                            <option value="{{ $district->id }}"
                                                            @if(isset($employee->presentAddress->district->id))
                                                                {{ $employee->presentAddress->district->id === $district->id ? 'selected' : '' }}
                                                                @endif
                                                            >{{ $district->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error("present_address.district_id")
                                                <p class="text-danger">{{ $errors->first("present_address.district_id") }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            {{-- Same as present address --}}
                                            {{--<div class="col-lg-4 col-form-label">
                                                <div class="checkbox-inline">
                                                    <label class="checkbox checkbox-success">
                                                        <input type="checkbox" name="same_as_present" class="same_as_present">
                                                        <span></span>Same As Present Address
                                                    </label>
                                                </div>
                                                @error("same_as_present")
                                                <p class="text-danger"> {{ $errors->first("same_as_present") }} </p>
                                                @enderror
                                            </div>--}}
                                        </div>

                                        <div class="row">
                                            {{-- Address --}}
                                            <div class="col-lg-4">
                                                <label>Permanent Address
                                                    {{--<span class="text-danger">*</span>--}}
                                                </label>
                                                <textarea class="form-control permanent_address_address" name="permanent_address[address]" rows="6"
                                                          readonly>{{ old("permanent_address.address") ?: $employee->permanentAddress->address ?? "" }}</textarea>
                                                <span class="form-text text-right text-custom-muted">At least 10 character</span>
                                                @error("permanent_address.address")
                                                <p class="text-danger"> {{ $errors->first("permanent_address.address") }} </p>
                                                @enderror
                                            </div>

                                            {{-- Division --}}
                                            <div class="col-lg-4">
                                                <label>Division
                                                    {{--<span class="text-danger">*</span>--}}
                                                </label>
                                                <select name="permanent_address[division_id]" class="form-control permanent_address_division_id" id="permanent_address_division_id" disabled>
                                                    <option selected disabled value="">Choose an option</option>
                                                    @foreach($data["divisions"] as $division)
                                                        <option value="{{ $division->id }}"
                                                        @if(isset($employee->permanentAddress->division->id))
                                                            {{ $employee->permanentAddress->division->id === $division->id ? 'selected' : '' }}
                                                            @endif
                                                        >{{ $division->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("permanent_address.division_id")
                                                <p class="text-danger"> {{ $errors->first("permanent_address.division_id") }} </p>
                                                @enderror

                                                {{-- Zip code --}}
                                                <div class="col-lg-12 p-0 pt-9">
                                                    <label>Zip Code</label>
                                                    <input type="number" value="{{ old("permanent_address.zip") ?: $employee->permanentAddress->zip ?? "" }}"
                                                           name="permanent_address[zip]" class="form-control permanent_address_zip" placeholder="Zip" readonly/>
                                                    <span class="form-text text-right text-custom-muted">At least 4 character</span>
                                                    @error("permanent_address.zip")
                                                    <p class="text-danger"> {{ $errors->first("permanent_address.zip") }} </p>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- District --}}
                                            <div class="col-lg-4">
                                                <label>District
                                                    {{--<span class="text-danger">*</span>--}}
                                                </label>
                                                <select name="permanent_address[district_id]" class="form-control permanent_address_district_id" id="permanent_address_district_id" disabled>
                                                    <option selected disabled value="">Choose an option</option>
                                                    @if(!is_null($permanentAddressDistrict))
                                                        @foreach($permanentAddressDistrict as $district)
                                                            <option value="{{ $district->id }}"
                                                                @if(isset($employee->permanentAddress->district->id))
                                                                    {{ $employee->permanentAddress->district->id === $district->id ? 'selected' : '' }}
                                                                @endif
                                                            >{{ $district->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>

                                                @error("permanent_address.district_id")
                                                <p class="text-danger"> {{ $errors->first("permanent_address.district_id") }} </p>
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
                                <h3 class="card-title">BANK INFORMATION</h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            {{-- Bank --}}
                                            <div class="col-lg-4">
                                                <label>Bank</label>
                                                <select name="bank_id" class="form-control" disabled>
                                                    <option selected disabled value="">Choose an option</option>
                                                    @foreach($data["banks"] as $bank)
                                                        <option value="{{ $bank->id }}"
                                                        @if(isset($employee->currentBank))
                                                            {{ $employee->currentBank->bank_id === $bank->id ? "selected" : "" }}
                                                            @endif
                                                        >
                                                            {{ $bank->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('bank_id')
                                                <p class="text-danger"> {{ $errors->first("bank_id") }} </p>
                                                @enderror
                                            </div>

                                            {{-- Branch --}}
                                            <div class="col-lg-4">
                                                <label>Branch</label>
                                                <select name="branch_id" class="form-control" disabled>
                                                    <option selected disabled value="">Choose an option</option>
                                                    @foreach($data["branches"] as $branch)
                                                        <option value="{{ $branch->id }}"
                                                        @if(isset($employee->currentBank->branch_id))
                                                            {{ $employee->currentBank->branch_id === $branch->id ? "selected" : "" }}
                                                            @endif
                                                        >{{ $branch->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("branch_id")
                                                <p class="text-danger"> {{ $errors->first("branch_id") }} </p>
                                                @enderror
                                            </div>

                                            {{-- Account Type --}}
                                            <div class="col-lg-4">
                                                <label>Account Type</label>
                                                <select name="account_type" class="form-control" disabled>
                                                    <option selected disabled value="">Choose an option</option>
                                                    <option value="Saving"
                                                    @if(isset($employee->currentBank->account_type))
                                                        {{ $employee->currentBank->account_type === \App\Models\BankUser::TYPE_SAVING ? "selected" : "" }}
                                                    @endif
                                                    >Saving</option>
                                                    <option value="Current"
                                                    @if(isset($employee->currentBank->account_type))
                                                        {{ $employee->currentBank->account_type === \App\Models\BankUser::TYPE_CURRENT ? "selected" : "" }}
                                                    @endif
                                                    >Current</option>
                                                    <option value="Deposit"
                                                    @if(isset($employee->currentBank->account_type))
                                                        {{ $employee->currentBank->account_type === \App\Models\BankUser::TYPE_DEPOSIT ? "selected" : "" }}
                                                    @endif
                                                    >Deposit</option>
                                                </select>
                                                @error("account_type")
                                                <p class="text-danger"> {{ $errors->first("account_type") }} </p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            {{-- A/C Holder Name --}}
                                            <div class="col-lg-4">
                                                <label>Account Name</label>
                                                <input type="text" value="{{ old("account_name") ?: $employee->currentBank->account_name ?? $employee->name }}"
                                                       name="account_name" class="form-control account_name" placeholder="Account Name" readonly/>
                                                <span class="form-text text-right text-custom-muted">At least 5 character</span>
                                                @error("account_name")
                                                <p class="text-danger"> {{ $errors->first("account_name") }} </p>
                                                @enderror
                                            </div>

                                            {{-- A/C No. --}}
                                            <div class="col-lg-4">
                                                <label>Account Number</label>
                                                <input type="text" value="{{ old("account_number") ?: $employee->currentBank->account_no ?? "" }}"
                                                       name="account_number" class="form-control" placeholder="Account Number" readonly/>
                                                <span class="form-text text-right text-custom-muted">At least 10 character</span>
                                                @error("account_number")
                                                <p class="text-danger"> {{ $errors->first("account_number") }} </p>
                                                @enderror
                                            </div>

                                            {{-- TIN --}}
                                            <div class="col-lg-4">
                                                <label>TIN</label>
                                                <input type="number" value="{{ old("tin") ?: $employee->profile->tin ?? '' }}" name="tin" class="form-control" placeholder="TIN" readonly/>
                                                <span class="form-text text-right text-custom-muted">At least 12 character</span>
                                                @error("tin")
                                                <p class="text-danger"> {{ $errors->first("tin") }} </p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            {{-- Nominee Name --}}
                                            <div class="col-lg-4">
                                                <label>Nominee Name </label>
                                                <input type="text" value="{{ old("nominee") ?: $employee->currentBank->nominee_name ?? "" }}"
                                                       name="nominee" class="form-control" placeholder="Nominee Name" readonly/>
                                                <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                                @error("nominee")
                                                <p class="text-danger"> {{ $errors->first("nominee") }} </p>
                                                @enderror
                                            </div>

                                            {{-- Relation with Nominee --}}
                                            <div class="col-lg-4">
                                                <label>Relation with Nominee</label>
                                                <input type="text" value="{{ old("relation_with_nominee") ?: $employee->currentBank->relation_with_nominee ?? "" }}"
                                                       name="relation_with_nominee" class="form-control" placeholder="Relation with Nominee" readonly/>
                                                <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                                @error("relation_with_nominee")
                                                <p class="text-danger"> {{ $errors->first("relation_with_nominee") }} </p>
                                                @enderror
                                            </div>

                                            {{-- Nominee Contact --}}
                                            <div class="col-lg-4">
                                                <label>Nominee Contact</label>
                                                <input type="text" value="{{ old("nominee_contact") ?: $employee->currentBank->nominee_contact ?? "" }}"
                                                       name="nominee_contact" class="form-control" placeholder="Nominee Contact" readonly/>
                                                <span class="form-text text-right text-custom-muted">At least 11 character</span>
                                                @error("nominee_contact")
                                                <p class="text-danger"> {{ $errors->first("nominee_contact") }} </p>
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
                                <h3 class="card-title">EDUCATIONAL QUALIFICATION</h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div id="kt_repeater_1">
                                            <div data-repeater-list="">

                                                @if(count($employee->degrees) > 0)
                                                    @foreach($employee->degrees as $item)
                                                        <div data-repeater-item="" class="section-repeater">
                                                            <div class="form-group row float-right">
                                                                <div class="col-lg-4 section-repeater-delete-btn">
                                                                    <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                {{-- Degree Achieved --}}
                                                                <div class="col-lg-3">
                                                                    <label>Degree Achieved
                                                                        {{--<span class="text-danger">*</span>--}}
                                                                    </label>
                                                                    <select name="degree_id[]" class="form-control">
                                                                        <option value="" selected disabled>Choose an option</option>
                                                                        @foreach($data["degrees"] as $degree)
                                                                            <option value="{{ $degree->id }}" {{ $item->id == $degree->id ? "selected" : "" }}>
                                                                                {{ $degree->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error("degree_id")
                                                                    <p class="text-danger"> {{ $errors->first("degree_id") }} </p>
                                                                    @enderror
                                                                </div>

                                                                {{-- Name of Institution --}}
                                                                <div class="col-lg-3">
                                                                    <label>Name of Institute
                                                                        {{--<span class="text-danger">*</span>--}}
                                                                    </label>
                                                                    <select name="institute_id[]" class="form-control">
                                                                        <option value="" selected disabled>Choose an option</option>
                                                                        @foreach($data["institutes"] as $institute)
                                                                            <option value="{{ $institute->id }}" {{ $item->pivot->institute_id == $institute->id ? "selected" : "" }}>
                                                                                {{ $institute->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error("institute_id")
                                                                    <p class="text-danger"> {{ $errors->first("institute_id") }} </p>
                                                                    @enderror
                                                                </div>

                                                                {{-- Passing Year --}}
                                                                <div class="col-lg-3">
                                                                    <label>Passing Year
                                                                        {{--<span class="text-danger">*</span>--}}
                                                                    </label>
                                                                    <input class="form-control" type="number" name="passing_year[]"
                                                                           placeholder="Passing Year" value="{{ $item->pivot->passing_year }}"/>
                                                                    @error("passing_year")
                                                                    <p class="text-danger"> {{ $errors->first("passing_year") }} </p>
                                                                    @enderror
                                                                </div>

                                                                {{-- Result --}}
                                                                <div class="col-lg-3">
                                                                    <label>Result
                                                                        {{--<span class="text-danger">*</span>--}}
                                                                    </label>
                                                                    <input name="result[]" type="text" class="form-control" value="{{ $item->pivot->result }}"
                                                                           placeholder="Result"/>
                                                                    @error("result")
                                                                    <p class="text-danger"> {{ $errors->first("result") }} </p>
                                                                    @enderror
                                                                </div>
                                                                <br/>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div data-repeater-item="" class="section-repeater">
                                                        <div class="form-group row float-right">
                                                            <div class="col-lg-4 section-repeater-delete-btn">
                                                                <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            {{-- Degree Achieved --}}
                                                            <div class="col-lg-3">
                                                                <label>Degree Achieved
                                                                    {{--<span class="text-danger">*</span>--}}
                                                                </label>
                                                                <select name="degree_id[]" class="form-control">
                                                                    <option value="" selected disabled>Choose an option</option>
                                                                    @foreach($data["degrees"] as $degree)
                                                                        <option value="{{ $degree->id }}" {{ old("degree_id") == $degree->id ? "selected" : "" }}>
                                                                            {{ $degree->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error("degree_id")
                                                                <p class="text-danger"> {{ $errors->first("degree_id") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Name of Institution --}}
                                                            <div class="col-lg-3">
                                                                <label>Name of Institute
                                                                    {{--<span class="text-danger">*</span>--}}
                                                                </label>
                                                                <select name="institute_id[]" class="form-control">
                                                                    <option value="" selected disabled>Choose an option</option>
                                                                    @foreach($data["institutes"] as $institute)
                                                                        <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error("institute_id")
                                                                <p class="text-danger"> {{ $errors->first("institute_id") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Passing Year --}}
                                                            <div class="col-lg-3">
                                                                <label>Passing Year
                                                                    {{--<span class="text-danger">*</span>--}}
                                                                </label>
                                                                <input class="form-control" type="number" name="passing_year[]"
                                                                       placeholder="Passing Year"/>
                                                                @error("passing_year")
                                                                <p class="text-danger"> {{ $errors->first("passing_year") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Result --}}
                                                            <div class="col-lg-3">
                                                                <label>Result
                                                                    {{--<span class="text-danger">*</span>--}}
                                                                </label>
                                                                <input name="result[]" type="text" class="form-control" placeholder="Result"/>
                                                                @error("result")
                                                                <p class="text-danger"> {{ $errors->first("result") }} </p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-lg-4">
                                                    <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                                        <i class="la la-plus"></i>Add
                                                    </a>
                                                </div>
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
                                <h3 class="card-title">PROFESSIONAL EXPERIENCES</h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div id="kt_repeater_2">
                                            <div data-repeater-list="">

                                                @if(count($employee->jobHistories) > 0)
                                                    @foreach($employee->jobHistories as $job)
                                                        <div data-repeater-item="" class="section-repeater">
                                                            <div class="form-group row  float-right">
                                                                <div class="col-lg-4 section-repeater-delete-btn">
                                                                    <a href="javascript:;" data-repeater-delete=""
                                                                       class="btn btn-sm font-weight-bolder btn-light-danger">X
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                {{-- Organization Name --}}
                                                                <div class="col-lg-3">
                                                                    <label>Organization Name</label>
                                                                    <input type="text" name="organization[]"
                                                                           class="form-control" placeholder="Organization Name" value="{{ $job->organization_name }}"/>

                                                                    @error("organization.*")
                                                                    <p class="text-danger"> {{ $message }} </p>
                                                                    @enderror
                                                                </div>
                                                                {{-- Designation --}}
                                                                <div class="col-lg-3">
                                                                    <label>Designation</label>
                                                                    <select name="designation[]" class="form-control form-control-light">
                                                                        <option value="" selected>Choose an option</option>
                                                                        @foreach($data["designations"] as $designation)
                                                                            @if(!empty($designation->id) && !empty($job->designationEmployee->id))
                                                                            <option value="{{ $designation->id }}" {{ $job->designationEmployee->id == $designation->id ? "selected" : "" }}>
                                                                                {{ $designation->title }}
                                                                            </option>
                                                                            @else
                                                                                <option value="{{ $designation->id }}">
                                                                                    {{ $designation->title }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                    @error("designation")
                                                                    <p class="text-danger"> {{ $errors->first("designation") }} </p>
                                                                    @enderror
                                                                </div>

                                                                {{-- From date --}}
                                                                <div class="col-lg-3">
                                                                    <label>Start Date</label>
                                                                    <input type="date" name="start_date[]" class="form-control"
                                                                           value="{{!empty($job->start_date)?date('Y-m-d', strtotime($job->start_date)):"" }}"/>
                                                                    @error("start_date")
                                                                    <p class="text-danger"> {{ $errors->first("start_date") }} </p>
                                                                    @enderror
                                                                </div>

                                                                {{-- To Date --}}
                                                                <div class="col-lg-3">
                                                                    <label>End Date</label>
                                                                    <input type="date" name="end_date[]" class="form-control"
                                                                           value="{{!empty($job->end_date)?date('Y-m-d', strtotime($job->end_date)):"" }}"/>
                                                                    @error("end_date")
                                                                    <p class="text-danger"> {{ $errors->first("end_date") }} </p>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <hr>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div data-repeater-item="" class="section-repeater">
                                                        <div class="form-group row  float-right">
                                                            <div class="col-lg-4 section-repeater-delete-btn">
                                                                <a href="javascript:;" data-repeater-delete=""
                                                                   class="btn btn-sm font-weight-bolder btn-light-danger">X
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            {{-- Organization Name --}}
                                                            <div class="col-lg-3">
                                                                <label>Organization Name</label>
                                                                <input type="text" name="organization[]"
                                                                       class="form-control" placeholder="Organization Name"/>
                                                                @error("organization")
                                                                <p class="text-danger"> {{ $errors->first("organization") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Designation --}}
                                                            <div class="col-lg-3">
                                                                <label>Designation</label>
                                                                <select name="designation[]" class="form-control form-control-light">
                                                                    <option value="" selected disabled>Choose an option</option>
                                                                    @foreach($data["designations"] as $designation)
                                                                        <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error("designation")
                                                                <p class="text-danger"> {{ $errors->first("designation") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- From date --}}
                                                            <div class="col-lg-3">
                                                                <label>Start Date</label>
                                                                <input type="date" name="start_date[]" class="form-control"/>
                                                                @error("start_date")
                                                                <p class="text-danger"> {{ $errors->first("start_date") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- To Date --}}
                                                            <div class="col-lg-3">
                                                                <label>End Date</label>
                                                                <input type="date" name="end_date[]" class="form-control"/>
                                                                @error("end_date")
                                                                <p class="text-danger"> {{ $errors->first("end_date") }} </p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group row mt-5">
                                                <div class="col-lg-4">
                                                    <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                                        <i class="la la-plus"></i>Add
                                                    </a>
                                                </div>
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
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>
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

        // Handle same as present address checkbox
        $(".same_as_present").change(function () {
            if(this.checked) {
                const present_address_address = $(".present_address_address").val();
                const present_address_district_id = $(".present_address_district_id").val();
                const present_address_division_id = $(".present_address_division_id").val();
                const present_address_zip = $(".present_address_zip").val();
                permanentAddressDivisionId(present_address_division_id);
                $(".permanent_address_address").val(present_address_address)
                $(".permanent_address_district_id").val(present_address_district_id)
                $(".permanent_address_division_id").val(present_address_division_id)
                $(".permanent_address_zip").val(present_address_zip)
            }
            else
            {
                $(".permanent_address_address").val("")
                $(".permanent_address_district_id").empty()
                $(".permanent_address_division_id").val("")
                $(".permanent_address_zip").val("")
            }
        });

        /**
         * @param _divisionID
         */
        function permanentAddressDivisionId(_divisionID)
        {
            let url = "{{ route('employee.districtByDivision', ':division') }}";
            url = url.replace(":division", _divisionID);
            $.get(url, {}, function (response, status) {
                $("#permanent_address_district_id").empty();
                $("#permanent_address_district_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data.districts, function(key, value) {
                    $("#permanent_address_district_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
                const present_address_district_id = $(".present_address_district_id").val();
                $(".permanent_address_district_id").val(present_address_district_id)
            });
        }

        // PRESENT ADDRESS:: Get district by division
        $('#present_address_division_id').change(function(){
            var _presentAddressDivisionID = $(this).val();
            let url = "{{ route('employee.districtByDivision', ':division') }}";
            url = url.replace(":division", _presentAddressDivisionID);
            $.get(url, {}, function (response, status) {
                $("#present_address_district_id").empty();
                $("#present_address_district_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data.districts, function(key, value) {
                    $("#present_address_district_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
            })
        });

        // PERMANENT ADDRESS:: Get district by division
        $('#permanent_address_division_id').change(function(){
            var _permanentAddressDivisionID = $(this).val();
            permanentAddressDivisionId(_permanentAddressDivisionID);
        });
    </script>

@endsection

