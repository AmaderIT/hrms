@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('/assets/css/select2.min.css') }}" rel="stylesheet"/>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('employee.updateMiscellaneous', ['employee' => $employee->uuid]) }}" method="POST" enctype="multipart/form-data" class="form">
                @csrf
                <div class="mt-n0">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header flex-wrap">
                            <div class="card-title">
                                <h3 class="card-label">PERSONAL INFORMATION</h3>
                            </div>
                            <div class="card-toolbar">
                                <nav class="font-weight-bolder">
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <a class="nav-item nav-link" href="{{ route('employee.edit', ['employee' => $employee->uuid]) }}" role="tab">BASIC INFORMATION</a>
                                        <a class="nav-item nav-link active" style="background-color: #eee" href="{{ route('employee.editMiscellaneous', ['employee' => $employee->uuid]) }}">PERSONAL INFORMATION</a>
                                    </div>
                                </nav>
                            </div>
                        </div>
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
                                        <div class="col-lg-4 col-form-label">
                                            <div class="checkbox-inline">
                                                <label class="checkbox checkbox-success">
                                                    <input type="checkbox" name="same_as_present" class="same_as_present">
                                                    <span></span>Same As Present Address
                                                </label>
                                            </div>
                                            @error("same_as_present")
                                            <p class="text-danger"> {{ $errors->first("same_as_present") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        {{-- Address --}}
                                        <div class="col-lg-4">
                                            <label>Permanent Address
                                                {{--<span class="text-danger">*</span>--}}
                                            </label>
                                            <textarea class="form-control permanent_address_address" name="permanent_address[address]" rows="6">{{ old("permanent_address.address") ?: $employee->permanentAddress->address ?? "" }}</textarea>
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
                                            <select name="permanent_address[division_id]" class="form-control permanent_address_division_id" id="permanent_address_division_id">
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
                                                       name="permanent_address[zip]" class="form-control permanent_address_zip" placeholder="Zip"/>
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
                                            <select name="permanent_address[district_id]" class="form-control permanent_address_district_id" id="permanent_address_district_id">
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
                    </div>
                </div>

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">BANK INFORMATION</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Bank --}}
                                        <div class="col-lg-4">
                                            <label>Bank</label>
                                            <select name="bank_id" id='selectBanks' class="form-control">
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
                                            <select name="branch_id" id='selectBranch' class="form-control">
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
                                            <select name="account_type" class="form-control">
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
                                            <input type="text" value="{{ old("account_name") ?: $employee->currentBank->account_name ?? "" }}"
                                                   name="account_name" class="form-control account_name" placeholder="Account Name"/>
                                            <span class="form-text text-right text-custom-muted">At least 5 character</span>
                                            @error("account_name")
                                            <p class="text-danger"> {{ $errors->first("account_name") }} </p>
                                            @enderror
                                        </div>

                                        {{-- A/C No. --}}
                                        <div class="col-lg-4">
                                            <label>Account Number</label>
                                            <input type="text" value="{{ old("account_number") ?: $employee->currentBank->account_no ?? "" }}"
                                                   name="account_number" class="form-control" placeholder="Account Number"/>
                                            <span class="form-text text-right text-custom-muted">At least 10 character</span>
                                            @error("account_number")
                                            <p class="text-danger"> {{ $errors->first("account_number") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Opening Balance (Tax) --}}
                                        <div class="col-lg-4">
                                            <label>Opening Balance (Tax)</label>
                                            <input type="number" value="{{ old('tax_opening_balance') ?: $employee->currentBank->tax_opening_balance ?? "" }}"
                                                   name="tax_opening_balance" class="form-control" placeholder="Tax Opening Balance"/>
                                            @error("tax_opening_balance")
                                            <p class="text-danger"> {{ $errors->first("tax_opening_balance") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        {{-- Nominee Name --}}
                                        <div class="col-lg-4">
                                            <label>Nominee Name </label>
                                            <input type="text" value="{{ old("nominee") ?: $employee->currentBank->nominee_name ?? "" }}"
                                                   name="nominee" class="form-control" placeholder="Nominee Name"/>
                                            <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("nominee")
                                            <p class="text-danger"> {{ $errors->first("nominee") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Relation with Nominee --}}
                                        <div class="col-lg-4">
                                            <label>Relation with Nominee</label>
                                            <input type="text" value="{{ old("relation_with_nominee") ?: $employee->currentBank->relation_with_nominee ?? "" }}"
                                                   name="relation_with_nominee" class="form-control" placeholder="Relation with Nominee"/>
                                            <span class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("relation_with_nominee")
                                            <p class="text-danger"> {{ $errors->first("relation_with_nominee") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Nominee Contact --}}
                                        <div class="col-lg-4">
                                            <label>Nominee Contact</label>
                                            <input type="text" value="{{ old("nominee_contact") ?: $employee->currentBank->nominee_contact ?? "" }}"
                                                   name="nominee_contact" class="form-control" placeholder="Nominee Contact"/>
                                            <span class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("nominee_contact")
                                            <p class="text-danger"> {{ $errors->first("nominee_contact") }} </p>
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
                            <h3 class="card-title">EDUCATIONAL QUALIFICATION</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_educational">
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
                                                                <label>Degree Achieved <span class="text-danger">*</span></label>
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
                                                                <label>
                                                                    Name of Institute
                                                                    <span class="text-danger">*</span>
                                                                    <a href="javascript:;" data-toggle="modal" data-target="#institution-modal"><span
                                                                            class="plus-icon-color institution-add-modal"><i class="fa fa-plus-square"></i></span></a>
                                                                </label>
                                                                <select name="institute_id[]" class="form-control selectInstitutes">
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
                                                                <label>Passing Year <span class="text-danger">*</span></label>
                                                                <input class="form-control" type="number" name="passing_year[]"
                                                                       placeholder="Passing Year" value="{{ $item->pivot->passing_year }}"/>
                                                                @error("passing_year")
                                                                <p class="text-danger"> {{ $errors->first("passing_year") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Result --}}
                                                            <div class="col-lg-3">
                                                                <label>Result <span class="text-danger">*</span></label>
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
                                                            <label>Degree Achieved</label>
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
                                                            <label>
                                                                Name of Institute
                                                                <a href="javascript:;" data-toggle="modal" data-target="#institution-modal"><span
                                                                        class="plus-icon-color institution-add-modal"><i class="fa fa-plus-square"></i></span></a>
                                                            </label>
                                                            <select name="institute_id[]" class="form-control selectInstitutes">
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
                                                            <label>Passing Year</label>
                                                            <input class="form-control" type="number" name="passing_year[]"
                                                                   placeholder="Passing Year"/>
                                                            @error("passing_year")
                                                            <p class="text-danger"> {{ $errors->first("passing_year") }} </p>
                                                            @enderror
                                                        </div>

                                                        {{-- Result --}}
                                                        <div class="col-lg-3">
                                                            <label>Result</label>
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
                    </div>
                </div>

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">PROFESSIONAL EXPERIENCES</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_professional">
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
                                                                        <option value="{{ $designation->id }}" {{(!empty($job->designationEmployee->id) && $job->designationEmployee->id == $designation->id) ? "selected" : "" }}>
                                                                            {{ $designation->title }}
                                                                        </option>
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
                                                                       value="{{!empty($job->start_date)?date('Y-m-d', strtotime($job->start_date)):null }}"/>
                                                                @error("start_date")
                                                                <p class="text-danger"> {{ $errors->first("start_date") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- To Date --}}
                                                            <div class="col-lg-3">
                                                                <label>End Date</label>
                                                                <input type="date" name="end_date[]" class="form-control"
                                                                       value="{{!empty($job->end_date)?date('Y-m-d', strtotime($job->end_date)):null }}"/>
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
                    </div>
                </div>

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-9"></div>
                                <div class="col-lg-3">
                                    <button type="reset" class="btn btn-secondary ml-lg-48">Reset</button>
                                    <button type="submit" class="btn btn-primary float-right ml-0">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="institution-modal" tabindex="-1" role="dialog"
         aria-labelledby="institutionModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="designation-modal">Institution</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form name="institution-modal" id="creation-institution" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        @include('institute.common-view.common_create')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="creation-institution-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    {{--<script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>--}}
    <script type="text/javascript">
        // Class definition
        var KTFormRepeater = function() {
            var demo = function() {
                $('#kt_repeater_educational').repeater({
                    initEmpty: false,
                    defaultValues: { 'text-input': 'foo' },
                    show: function () {
                        $(this).slideDown();
                        $(".selectInstitutes").select2({
                            theme: "classic",
                            ajax: {
                                url: "{{ route('getInstitutesFilter') }}",
                                type: "post",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        _token: CSRF_TOKEN,
                                        search: params.term
                                    };
                                },
                                processResults: function (response) {
                                    return {
                                        results: response
                                    };
                                },
                                cache: true,
                            }
                        });
                    },
                    hide: function (deleteElement) { $(this).slideUp(deleteElement); }
                });

                $('#kt_repeater_professional').repeater({
                    initEmpty: false,
                    defaultValues: { 'text-input': 'foo' },
                    show: function() { $(this).slideDown(); },
                    hide: function(deleteElement) { $(this).slideUp(deleteElement);}
                });

            };

            return {
                init: function() {
                    demo();
                }
            };
        }();

        jQuery(document).ready(function() {
            KTFormRepeater.init();
        });

    </script>

    <script type="text/javascript">
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

    <script type="text/javascript">
        // CSRF Token
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function () {
            $("#selectBanksBk").select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('getBanks') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });

            $("#selectBranchBk").select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('getBranches') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });
            $(".selectInstitutes").select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('getInstitutesFilter') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });
        });
    </script>
    {{--    <script>
            $(document).on('click', '.institution-add-modal', function(e) {
                $.ajax({
                    type: "POST",
                    url: '{{route('employee.modalInstitution')}}',
                    data: "",
                    dataType: "json",
                    success: function(result){
                        $('.modal-body').html(result.html);
                        $('#institution-modal').modal('toggle');
                    }
                });
            });
        </script>--}}
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
            $('#creation-institution-btn').on('click', function (e) {
                e.preventDefault();
                var url = '{{route('institute.store-ajax')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: $('#creation-institution').serialize(),
                    dataType: "json",
                    success: function (result) {
                        //console.log("Result",result);
                        if(result.status == 'success'){
                            toastr.success(result.message);
                            $('#institution-modal').modal('toggle');
                        }
                    },
                    complete:function(result,status){
                        if(status !== 'error'){
                            $('.selectInstitutes').empty();
                            $('.selectInstitutes').append('<option value="" "selected disabled"> Choose an option </option>');
                            $.each(result.responseJSON.institutes,function (key,val) {
                                $('.selectInstitutes').append('<option value="'+val.id+'">'+val.name+'</option>');
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
        let is_modal_loaded_inst = 0;
        $(document).on('click', '.institution-add-modal', function (e) {
            $.ajax({
                type: "POST",
                url: '{{route('employee.modalInstitution')}}',
                data:{},
                dataType: "json",
                success: function (result) {
                    if( is_modal_loaded_inst == 0) {
                        is_modal_loaded_inst = 1;
                    }
                    else{
                        $('#creation-institution')[0].reset();
                    }
                }
            });
        });
    </script>
@endsection
