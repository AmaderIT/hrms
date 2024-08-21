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

        .datepicker tbody tr > td span.year, .datepicker tbody tr > td span.month {
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
            <form action="{{ route('employee.store') }}" method="POST" enctype="multipart/form-data" class="form">
                @csrf
                <div class="mt-n0">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">LOGIN INFORMATION</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Fingerprint ID --}}
                                        <div class="col-lg-3">
                                            <label>Fingerprint ID <span class="text-danger">*</span></label>
                                            <input type="number" value="{{ old('fingerprint_no') }}"
                                                   name="fingerprint_no" class="form-control fingerprint_no"
                                                   placeholder="Fingerprint ID" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("fingerprint_no")
                                            <p class="text-danger"> {{ $errors->first("fingerprint_no") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Password --}}
                                        <div class="col-lg-3">
                                            <label>Password <span class="text-danger">*</span></label>
                                            <input type="password" value="{{ old("password") }}" name="password"
                                                   class="form-control" placeholder="Password" required
                                                   autocomplete="chrome-off"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 6 character</span>
                                            @error("password")
                                            <p class="text-danger"> {{ $errors->first("password") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Confirm Password --}}
                                        <div class="col-lg-3">
                                            <label>Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" value="{{ old('password_confirmation') }}"
                                                   name="password_confirmation" class="form-control"
                                                   placeholder="Confirm Password" required/>
                                            <p class="text-danger password_confirmation_validation"></p>
                                            @error('password_confirmation')
                                            <p class="text-danger"> {{ $errors->first("password_confirmation") }} </p>
                                            @enderror
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="image-input image-input-outline" id="kt_image_4"
                                                 style="background-image: url({{asset('/')}}assets/media/users/blank.png)">
                                                <div class="image-input-wrapper"
                                                     style="background-image: url({{asset('/')}}assets/media/users/blank.png)"></div>
                                                <label
                                                    class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                                    data-action="change" data-toggle="tooltip" title="Upload"
                                                    data-original-title="Change avatar">
                                                    <i class="fa fa-pen icon-sm text-muted"></i>
                                                    <input type="file" name="photo" accept=".png, .jpg, .jpeg"/>
                                                    <input type="hidden" name="profile_avatar_remove"/>
                                                </label>
                                                <span
                                                    class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                                    data-action="cancel" data-toggle="tooltip" title="Remove avatar">
                                            <i class="ki ki-bold-close icon-xs text-muted"></i>
                                        </span>
                                            </div>
                                            @error("photo")
                                            <p class="text-danger"> {{ $errors->first("photo") }} </p>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">BASIC INFORMATION</h3>
                        </div>
                        <div class="card-body">
                            {{--<div class="form-group row">
                                <div class="col-lg-4">
                                    <div class="image-input image-input-outline" id="kt_image_4"
                                         style="background-image: url({{asset('/')}}assets/media/users/blank.png)">
                                        <div class="image-input-wrapper"
                                             style="background-image: url({{asset('/')}}assets/media/users/blank.png)"></div>
                                        <label
                                            class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                            data-action="change" data-toggle="tooltip" title="Upload"
                                            data-original-title="Change avatar">
                                            <i class="fa fa-pen icon-sm text-muted"></i>
                                            <input type="file" name="photo" accept=".png, .jpg, .jpeg"/>
                                            <input type="hidden" name="profile_avatar_remove"/>
                                        </label>
                                        <span
                                            class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                            data-action="cancel" data-toggle="tooltip" title="Remove avatar">
                                            <i class="ki ki-bold-close icon-xs text-muted"></i>
                                        </span>
                                    </div>
                                    @error("photo")
                                    <p class="text-danger"> {{ $errors->first("photo") }} </p>
                                    @enderror
                                </div>
                            </div>--}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Full name --}}
                                        <div class="col-lg-4">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('name') }}" name="name"
                                                   class="form-control name" placeholder="Full Name" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("name")
                                            <p class="text-danger"> {{ $errors->first("name") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Phone Number --}}
                                        {{--<div class="col-lg-3">
                                            <label>Phone Number (Official)</label>
                                            <input name="phone" value="{{ old('phone') }}" type="text"
                                                   class="form-control" placeholder="Phone Number"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("phone")
                                            <p class="text-danger"> {{ $errors->first("phone") }} </p>
                                            @enderror
                                        </div>--}}

                                        {{-- Fingerprint ID --}}
                                        <div class="col-lg-4">
                                            <label>Fingerprint ID <span class="text-danger">*</span></label>
                                            <input type="number" value="{{ old('fingerprint_no') }}"
                                                   class="form-control set_fingerprint_no" placeholder="Fingerprint ID"
                                                   readonly/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("fingerprint_no")
                                            <p class="text-danger"> {{ $errors->first("fingerprint_no") }} </p>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4">
                                            <label>Date of Birth
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" value="{{ old("dob") }}" name="dob" id="dob"
                                                   class="form-control" autocomplete="off" placeholder="yyyy-mm-dd"
                                                   readonly required/>
                                            @error("dob")
                                            <p class="text-danger"> {{ $errors->first("dob") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {{-- Date of Birth --}}
                                        {{--<div class="col-lg-4">
                                            <label>Date of Birth
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" value="{{ old("dob") }}" name="dob" class="form-control"/>
                                            @error("dob")
                                            <p class="text-danger"> {{ $errors->first("dob") }} </p>
                                            @enderror
                                        </div>--}}

                                        {{-- NID --}}
                                        {{--<div class="col-lg-3">
                                            <label>NID</label>
                                            <input name="nid" value="{{ old('nid') }}" type="text" class="form-control"
                                                   placeholder="NID"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 13 character</span>
                                            @error("nid")
                                            <p class="text-danger"> {{ $errors->first("nid") }} </p>
                                            @enderror
                                        </div>--}}

                                        {{-- TIN --}}
                                        {{--<div class="col-lg-3">
                                            <label>TIN</label>
                                            <input type="number" value="{{ old('tin') }}"
                                                   name="tin" class="form-control" placeholder="TIN"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 12 character</span>
                                            @error("tin")
                                            <p class="text-danger"> {{ $errors->first("tin") }} </p>
                                            @enderror
                                        </div>--}}
                                        {{--<div class="col-lg-3">
                                            <label>Email (Official)</label>
                                            <input type="email" value="{{ old('email') }}" name="email"
                                                   class="form-control" placeholder="Email"/>
                                            <span
                                                class="form-text text-right text-custom-muted">Must be type email</span>
                                            @error("email")
                                            <p class="text-danger"> {{ $errors->first("email") }} </p>
                                            @enderror
                                        </div>--}}
                                        {{--<div class="col-lg-3">
                                            <label>Email (Personal)</label>
                                            <input type="email" value="{{ old('personal_email') }}"
                                                   name="personal_email" class="form-control"
                                                   placeholder="Personal Email ID"/>
                                            @error("relation")
                                            <p class="text-danger"> {{ $errors->first("relation") }} </p>
                                            @enderror
                                        </div>--}}
                                    </div>
                                    <div class="form-group row">
                                        {{-- Email --}}
                                        {{--<div class="col-lg-4">
                                            <label>Email (Official)</label>
                                            <input type="email" value="{{ old('email') }}" name="email"
                                                   class="form-control" placeholder="Email"/>
                                            <span
                                                class="form-text text-right text-custom-muted">Must be type email</span>
                                            @error("email")
                                            <p class="text-danger"> {{ $errors->first("email") }} </p>
                                            @enderror
                                        </div>--}}

                                        {{-- Gender --}}
                                        <div class="col-lg-3">
                                            <label>Gender <span class="text-danger">*</span></label>
                                            <select name="gender" class="form-control">
                                                <option value="Male" {{ old("gender") == "Male" ? 'selected' : '' }}>
                                                    Male
                                                </option>
                                                <option
                                                    value="Female" {{ old("gender") == "Female" ? 'selected' : '' }}>
                                                    Female
                                                </option>
                                                <option value="Other" {{ old("gender") == "Other" ? 'selected' : '' }}>
                                                    Other
                                                </option>
                                            </select>
                                            @error("gender")
                                            <p class="text-danger"> {{ $errors->first("gender") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Religion --}}
                                        <div class="col-lg-3">
                                            <label>Religion <span class="text-danger">*</span></label>
                                            <select name="religion" class="form-control">
                                                <option
                                                    value="Islam" {{ old("religion") == "Islam" ? 'selected' : '' }}>
                                                    Islam
                                                </option>
                                                <option
                                                    value="Hinduism" {{ old("religion") == "Hinduism" ? 'selected' : '' }}>
                                                    Hinduism
                                                </option>
                                                <option
                                                    value="Christianity" {{ old("religion") == "Christianity" ? 'selected' : '' }}>
                                                    Christianity
                                                </option>
                                                <option
                                                    value="Buddhism" {{ old("religion") == "Buddhism" ? 'selected' : '' }}>
                                                    Buddhism
                                                </option>
                                                <option
                                                    value="Other" {{ old("religion") == "Other" ? 'selected' : '' }}>
                                                    Other
                                                </option>
                                            </select>
                                            @error("religion")
                                            <p class="text-danger"> {{ $errors->first("religion") }} </p>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3">
                                            <label>Marital Status <span class="text-danger">*</span></label>
                                            <select name="marital_status" class="form-control">
                                                <option
                                                    value="Single" {{ old("marital_status") == "Single" ? 'selected' : '' }}>
                                                    Single
                                                </option>
                                                <option
                                                    value="Married" {{ old("marital_status") == "Married" ? 'selected' : '' }}>
                                                    Married
                                                </option>
                                            </select>
                                            @error("marital_status")
                                            <p class="text-danger"> {{ $errors->first("marital_status") }} </p>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3">
                                            <label>Blood Group <span class="text-danger">*</span></label>
                                            <select name="blood_group" class="form-control">
                                                <option value="A+" {{ old("blood_group") == "A+" ? 'selected' : '' }}>
                                                    A+
                                                </option>
                                                <option value="A-" {{ old("blood_group") == "A-" ? 'selected' : '' }}>
                                                    A-
                                                </option>
                                                <option value="B+" {{ old("blood_group") == "B+" ? 'selected' : '' }}>
                                                    B+
                                                </option>
                                                <option value="B-" {{ old("blood_group") == "B-" ? 'selected' : '' }}>
                                                    B-
                                                </option>
                                                <option value="O+" {{ old("blood_group") == "O+" ? 'selected' : '' }}>
                                                    O+
                                                </option>
                                                <option value="O-" {{ old("blood_group") == "O-" ? 'selected' : '' }}>
                                                    O-
                                                </option>
                                                <option value="AB+" {{ old("blood_group") == "AB+" ? 'selected' : '' }}>
                                                    AB+
                                                </option>
                                                <option value="AB-" {{ old("blood_group") == "AB-" ? 'selected' : '' }}>
                                                    AB-
                                                </option>
                                            </select>
                                            @error("blood_group")
                                            <p class="text-danger"> {{ $errors->first("blood_group") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        {{-- Marital Status --}}
                                        {{--<div class="col-lg-4">
                                            <label>Marital Status <span class="text-danger">*</span></label>
                                            <select name="marital_status" class="form-control">
                                                <option
                                                    value="Single" {{ old("marital_status") == "Single" ? 'selected' : '' }}>
                                                    Single
                                                </option>
                                                <option
                                                    value="Married" {{ old("marital_status") == "Married" ? 'selected' : '' }}>
                                                    Married
                                                </option>
                                            </select>
                                            @error("marital_status")
                                            <p class="text-danger"> {{ $errors->first("marital_status") }} </p>
                                            @enderror
                                        </div>--}}

                                        {{-- Blood Group --}}
                                        {{--<div class="col-lg-4">
                                            <label>Blood Group <span class="text-danger">*</span></label>
                                            <select name="blood_group" class="form-control">
                                                <option value="A+" {{ old("blood_group") == "A+" ? 'selected' : '' }}>
                                                    A+
                                                </option>
                                                <option value="A-" {{ old("blood_group") == "A-" ? 'selected' : '' }}>
                                                    A-
                                                </option>
                                                <option value="B+" {{ old("blood_group") == "B+" ? 'selected' : '' }}>
                                                    B+
                                                </option>
                                                <option value="B-" {{ old("blood_group") == "B-" ? 'selected' : '' }}>
                                                    B-
                                                </option>
                                                <option value="O+" {{ old("blood_group") == "O+" ? 'selected' : '' }}>
                                                    O+
                                                </option>
                                                <option value="O-" {{ old("blood_group") == "O-" ? 'selected' : '' }}>
                                                    O-
                                                </option>
                                                <option value="AB+" {{ old("blood_group") == "AB+" ? 'selected' : '' }}>
                                                    AB+
                                                </option>
                                                <option value="AB-" {{ old("blood_group") == "AB-" ? 'selected' : '' }}>
                                                    AB-
                                                </option>
                                            </select>
                                            @error("blood_group")
                                            <p class="text-danger"> {{ $errors->first("blood_group") }} </p>
                                            @enderror
                                        </div>--}}

                                        {{-- Emergency Contact --}}
                                        {{--<div class="col-lg-4">
                                            <label>Emergency Contact No <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('emergency_contact') }}"
                                                   name="emergency_contact" class="form-control"
                                                   placeholder="Emergency Contact No" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("emergency_contact")
                                            <p class="text-danger"> {{ $errors->first("emergency_contact") }} </p>
                                            @enderror
                                        </div>--}}
                                    </div>
                                    <div class="form-group row">
                                        {{--<div class="col-lg-3">
                                            <label>Emergency Contact No <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('emergency_contact') }}"
                                                   name="emergency_contact" class="form-control"
                                                   placeholder="Emergency Contact No" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("emergency_contact")
                                            <p class="text-danger"> {{ $errors->first("emergency_contact") }} </p>
                                            @enderror
                                        </div>--}}
                                        <div class="col-lg-4">
                                            <label>Emergency Contact No <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('emergency_contact') }}"
                                                   name="emergency_contact" class="form-control"
                                                   placeholder="Emergency Contact No" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("emergency_contact")
                                            <p class="text-danger"> {{ $errors->first("emergency_contact") }} </p>
                                            @enderror
                                        </div>
                                        {{-- Relation with Energency Contact --}}
                                        <div class="col-lg-4">
                                            <label>Relation with Emergency Contact <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('relation') }}"
                                                   name="relation" class="form-control"
                                                   placeholder="Relation with Emergency Contact" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("relation")
                                            <p class="text-danger"> {{ $errors->first("relation") }} </p>
                                            @enderror
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Phone (Personal) <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('personal_phone') }}"
                                                   name="personal_phone" class="form-control"
                                                   placeholder="Personal Phone Number" required/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("personal_phone")
                                            <p class="text-danger"> {{ $errors->first("personal_phone") }} </p>
                                            @enderror
                                        </div>

                                        {{--<div class="col-lg-4">
                                            <label>Email (Personal)</label>
                                            <input type="email" value="{{ old('personal_email') }}"
                                                   name="personal_email" class="form-control"
                                                   placeholder="Personal Email ID"/>
                                            @error("relation")
                                            <p class="text-danger"> {{ $errors->first("relation") }} </p>
                                            @enderror
                                        </div>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">PROFESSIONAL INFORMATION</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Office Division --}}
                                        <div class="col-lg-3">
                                            <label>
                                                Office Division <span class="text-danger">*</span>
                                                <a href="javascript:;" data-toggle="modal" data-target="#office-division-modal"><span
                                                        class="plus-icon-color" id="office-division-add-modal"><i class="fa fa-plus-square"></i></span></a>
                                            </label>
                                            <select name="office_division_id" id="office_division_id"
                                                    class="form-control" required>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["officeDivisions"] as $officeDivision)
                                                    <option
                                                        value="{{ $officeDivision->id }}" {{ old("office_division_id") == $officeDivision->id ? 'selected' : '' }}>
                                                        {{ $officeDivision->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("office_division_id")
                                            <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                            @enderror
                                        </div>

                                        @if(!empty(old("office_division_id")) && !empty(old("department_id")))

                                        @endif

                                        {{-- Department --}}
                                        <div class="col-lg-3">
                                            <label>
                                                Department
                                                <span class="text-danger">*</span>
                                                <a href="javascript:;" data-toggle="modal" data-target="#dept-modal"><span
                                                        class="plus-icon-color" id="dept-add-modal"><i class="fa fa-plus-square"></i></span></a>
                                            </label>
                                            <select name="department_id" id="department_id" class="form-control"
                                                    required>
                                                <option selected disabled value="">Choose an option</option>
                                            </select>
                                            @error("department_id")
                                            <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Designation --}}
                                        <div class="col-lg-3">
                                            <label>
                                                Designation
                                                <span class="text-danger">*</span>
                                                <a href="javascript:;" data-toggle="modal" data-target="#designation-modal"><span
                                                        class="plus-icon-color" id="designation-add-modal"><i class="fa fa-plus-square"></i></span></a>
                                            </label>
                                            <select name="designation_id" class="form-control" id="custom_designation_id" required>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["designations"] as $designation)
                                                    <option
                                                        value="{{ $designation->id }}" {{ old("designation_id") == $designation->id ? 'selected' : '' }}>
                                                        {{ $designation->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("designation_id")
                                            <p class="text-danger"> {{ $errors->first("designation_id") }} </p>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3">
                                            <label>Supervisor Type</label>
                                            <select name="supervisor_type" class="form-control">
                                                <option value="">Choose an option</option>
                                                @foreach (\App\Models\User::supervisorTypes() as $key => $value)
                                                    <option value="{{ $key }}" {{ old("supervisor_type") == $key ? "selected" : "" }}>{{$value}}</option>
                                                @endforeach
                                            </select>
                                            @error("supervisor_type")
                                            <p class="text-danger"> {{ $errors->first("supervisor_type") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        {{-- Joining Date --}}
                                        <div class="col-lg-2">
                                            <label>Joining Date <span class="text-danger">*</span></label>
                                            <input type="text" value="{{ old('joining_date') }}" name="joining_date"
                                                   class="form-control" id="joining-date" placeholder="yyyy-mm-dd"
                                                   autocomplete="off" readonly required/>
                                            @error("joining_date")
                                            <p class="text-danger"> {{ $errors->first("joining_date") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Provision Duration --}}
                                        <div class="col-lg-2">
                                            <label id="pay_grade_id">Provision Duration<span
                                                    class="text-danger">*</span></label>
                                            <select name="provision_duration" id="provision_duration"
                                                    class="form-control">
                                                <option selected disabled value="">Choose an option</option>
                                                @for($i=0;$i<=12;$i++)
                                                    <option  value="{{$i}}">{{$i}}</option>
                                                @endfor
                                            </select>
                                            @error("provision_duration")
                                            <p class="text-danger"> {{ $errors->first("provision_duration") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Employment Type --}}
                                        <div class="col-lg-4">
                                            <label>Employment Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-control" required>
                                                <option selected disabled value="">Choose an option</option>
{{--                                                <option
                                                    value="Internee" {{ old("type") == "Internee" ? "selected" : "" }}>
                                                    Internee
                                                </option>
                                                <option
                                                    value="Provision" {{ old("type") == "Provision" ? "selected" : "" }}>
                                                    Provision
                                                </option>
                                                <option
                                                    value="Permanent" {{ old("type") == "Permanent" ? "selected" : "" }}>
                                                    Permanent
                                                </option>
                                                <option
                                                    value="Promoted" {{ old("type") == "Promoted" ? "selected" : "" }}>
                                                    Promoted
                                                </option>
                                                <option
                                                    value="Contractual" {{ old("type") == "Contractual" ? "selected" : "" }}>
                                                    Contractual
                                                </option>--}}
                                                @foreach (\App\Models\Promotion::employmentType() as $key => $value)
                                                    <option value="{{ $key }}" {{ old("type") == $key ? "selected" : "" }}>{{$value}}</option>
                                                @endforeach
                                            </select>
                                            @error("type")
                                            <p class="text-danger"> {{ $errors->first("type") }} </p>
                                            @enderror
                                        </div>

                                        {{-- WorkSlot --}}
                                        <div class="col-lg-4">
                                            <label id="workslot_id">Work Slot<span class="text-danger">*</span></label>
                                            <select name="workslot_id" class="form-control" required>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["workSlots"] as $workSlot)
                                                    <option
                                                        value="{{ $workSlot->id }}" {{ old("workslot_id") == $workSlot->id ? "selected" : "" }}>
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
                                        <div class="col-lg-3">
                                            <label id="payment_mode">Payment Mode<span class="text-danger">*</span></label>
                                            <select name="payment_mode" class="form-control">
                                                @foreach (\App\Models\User::paymentModes() as $key => $value)
                                                    <option value="{{ $key }}" {{ old("payment_mode") == $key ? "selected" : "" }}>{{$value}}</option>
                                                @endforeach
                                            </select>
                                            @error("payment_mode")
                                            <p class="text-danger"> {{ $errors->first("payment_mode") }} </p>
                                            @enderror
                                        </div>
                                        {{-- Salary --}}
                                        <div class="col-lg-3">
                                            <label>Salary<span class="text-danger">*</span></label>
                                            <input type="number" value="{{ old('salary') }}" name="salary"
                                                   class="form-control" placeholder="Salary" required/>
                                            @error("salary")
                                            <p class="text-danger"> {{ $errors->first("salary") }} </p>
                                            @enderror
                                        </div>

                                        {{-- PayGrade --}}
                                        <div class="col-lg-3">
                                            <label id="pay_grade_id">Pay Grade<span class="text-danger">*</span></label>
                                            <select name="pay_grade_id" class="form-control">
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["payGrades"] as $payGrade)
                                                    <option
                                                        value="{{ $payGrade->id }}" {{ old("pay_grade_id") == $payGrade->id ? "selected" : "" }}>
                                                        {{ $payGrade->name }} <{{ $payGrade->range_start_from }}
                                                        - {{ $payGrade->range_end_to }}> BDT
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("pay_grade_id")
                                            <p class="text-danger"> {{ $errors->first("pay_grade_id") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Roles --}}
                                        <div class="col-lg-3">
                                            <label for="role_id">Role Type <span class="text-danger">*</span></label>
                                            <select name="role_id" class="form-control" required>
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["roles"] as $role)
                                                    <option
                                                        value="{{ $role->id }}" {{ old("role_id") == $role->id ? "selected" : "" }}>{{ $role->name }}</option>
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
                    </div>
                </div>
                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-10"></div>
                                <div class="col-lg-2">
                                    {{--<button type="reset" class="btn btn-secondary ml-lg-10">Reset</button>--}}
                                    {{--<button type="submit" class="btn btn-primary float-right ml-0">Next<i
                                            class="pl-2 fa fa-angle-double-right"></i></button>--}}
                                    <button type="submit" class="btn btn-primary float-right ml-0">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

        <div class="modal fade" id="office-division-modal" tabindex="-1" role="dialog"
             aria-labelledby="officeDivisionModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="office-division-title">Office Division</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form name="office-division-modal" id="creation-office-division" action="#" method="POST">
                        @csrf
                        <div class="modal-body">
                            @include('office-division.common-view.common_create')
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="creation-office-division-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endsection

        @section('footer-js')
            <script src="{{ asset('assets/js/image-input.js') }}"></script>
            <script type="text/javascript">
                $(".fingerprint_no").on("input", function (e) {
                    var _fingerprint_no = e.currentTarget.value;
                    $(".set_fingerprint_no").val(_fingerprint_no)
                });

                // match password confirmation
                $("[name='password'], [name='password_confirmation']").on("input", function () {
                    let _password = $("[name='password']").val().toString();
                    let _password_confirmation = $("[name='password_confirmation']").val().toString();

                    if (_password != "" && _password_confirmation != "") {
                        if (_password != _password_confirmation) {
                            $(".password_confirmation_validation").removeClass("text-success").addClass("text-danger").text("Password not matched yet!!")
                        } else {
                            $(".password_confirmation_validation").removeClass("text-danger").addClass("text-success").text("Matched")
                        }
                    }
                });

                // Get department by office division
                $('#office_division_id').change(function () {
                    var _officeDivisionID = $(this).val();

                    let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                    url = url.replace(":officeDivision", _officeDivisionID);

                    $.get(url, {}, function (response, status) {
                        $("#department_id").empty();
                        $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                        $.each(response.data.departments, function (key, value) {
                            $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    })
                });


                var division_id = '{!! old("office_division_id") !!}';
                var department_id = '{!! old("department_id") !!}';
                if (division_id != '' && department_id != '') {
                    console.log(division_id);
                    console.log(department_id);
                    let url1 = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                    url1 = url1.replace(":officeDivision", division_id);
                    $.get(url1, {}, function (response, status) {
                        $("#department_id").empty();
                        $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                        $.each(response.data.departments, function (key, value) {
                            if (department_id == value.id) {
                                $("#department_id").append('<option value="' + value.id + '" selected>' + value.name + '</option>');
                            } else {
                                $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                            }
                        });
                    });
                }
                //console.log('empty');

                $('#joining-date').datepicker({
                    minDate: 0,
                    format: 'yyyy-mm-dd',
                    startDate: '-59d',
                    todayHighlight: true,
                    autoclose: true,
                    endDate: "+59d"
                }).on('changeDate', function (event) {
                    let getDOBDate = $('#dob').val();
                    if (!getDOBDate) {
                        toastr.error("At First Given Date of Birth!!!");
                        $('#joining-date').val("");
                    }
                    let today = new Date($('#joining-date').val());
                    let birthDate = new Date(getDOBDate);
                    let age = today.getFullYear() - birthDate.getFullYear();
                    let m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    //alert(age);
                    if (age < 18) {
                        toastr.error('Below 18 years old! Invalid For Fill Up This Form!!!');
                        $('#joining-date').val("");
                    }
                });
                $('#dob').datepicker({
                    minDate: 0,
                    // startDate: '-40y',
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    /*defaultViewDate: {
                        year: 1990,
                        month: 1,
                        day: 1
                    },*/
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
                                //console.log("Result ",result,status);
                                if(status !== 'error'){
                                    $('#custom_designation_id').empty();
                                    $('#custom_designation_id').append('<option value="" "selected disabled"> Choose an option </option>');
                                    $.each(result.responseJSON.designations,function (key,val) {
                                        $('#custom_designation_id').append('<option value="'+val.id+'">'+val.title+'</option>');
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
                    $('#creation-office-division-btn').on('click', function (e) {
                        e.preventDefault();
                        var url = '{{route('office-division.store-ajax')}}';
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: $('#creation-office-division').serialize(),
                            dataType: "json",
                            success: function (result) {
                                //console.log("Result",result);
                                if(result.status === 'success'){
                                    toastr.success(result.message);
                                    $('#office-division-modal').modal('toggle');
                                }
                            },
                            complete:function(result,status){
                                //console.log("Result ",result,status);
                                if(status !== 'error'){
                                    $('#office_division_id').empty();
                                    $('#office_division_id').append('<option value="" "selected disabled"> Choose an option </option>');
                                    $.each(result.responseJSON.officeDivisions,function (key,val) {
                                        $('#office_division_id').append('<option value="'+val.id+'">'+val.name+'</option>');
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
                    //console.log('Office Division ID ',$('#office_division_id').val());
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
                            $.each(result.officeDivisions, function (key, value) {
                                if (result.officeDivisionId == value.id) {
                                    $("#office_division_id_in_modal").append('<option value="' + value.id + '" selected>' + value.name + '</option>');
                                } else {
                                    $("#office_division_id_in_modal").append('<option value="' + value.id + '">' + value.name + '</option>');
                                }
                            });
                        }
                    });
                });
            </script>
            <script>
                let is_modal_loaded_dsg = 0;
                $(document).on('click', '#designation-add-modal', function (e) {
                    $.ajax({
                        type: "POST",
                        url: '{{route('employee.modalDesignation')}}',
                        data:{},
                        dataType: "json",
                        success: function (result) {
                            if( is_modal_loaded_dsg == 0) {
                                is_modal_loaded_dsg = 1;
                            }
                            else{
                                $('#creation-designation')[0].reset();
                            }
                        }
                    });
                });
            </script>
            <script>
                let is_modal_loaded_office_division = 0;
                $(document).on('click', '#office-division-add-modal', function (e) {
                    $.ajax({
                        type: "POST",
                        url: '{{route('employee.modalOfficeDivision')}}',
                        data:{},
                        dataType: "json",
                        success: function (result) {
                            if( is_modal_loaded_office_division == 0) {
                                is_modal_loaded_office_division = 1;
                            }
                            else{
                                $('#creation-office-division')[0].reset();
                            }
                        }
                    });
                });
            </script>
@endsection
