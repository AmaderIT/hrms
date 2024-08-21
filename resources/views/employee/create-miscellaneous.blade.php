@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('/assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('/assets/css/custom-datepicker.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="mt-n5">
                <div class="card card-custom card-stretch gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">{{ $employee->name }}</h3>
                    </div>
                </div>
            </div>

            <form action="{{ route('employee.storeMiscellaneous', ['employee' => $employee->id]) }}" method="POST"
                  enctype="multipart/form-data" class="form">
                @csrf

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">PERSONAL INFORMATION</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="row">
                                        <div class="col-lg-3">
                                            <label>Phone Number (Official)</label>
                                            <input name="phone" value="{{ old('phone') }}" type="text"
                                                   class="form-control" placeholder="Phone Number"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
                                            @error("phone")
                                            <p class="text-danger"> {{ $errors->first("phone") }} </p>
                                            @enderror
                                        </div>
                                        <div class="col-lg-3">
                                            <label>Email (Official)</label>
                                            <input type="email" value="{{ old('email') }}" name="email"
                                                   class="form-control" placeholder="Email"/>
                                            <span
                                                class="form-text text-right text-custom-muted">Must be type email</span>
                                            @error("email")
                                            <p class="text-danger"> {{ $errors->first("email") }} </p>
                                            @enderror
                                        </div>
                                        {{-- NID --}}
                                        <div class="col-lg-3">
                                            <label>NID</label>
                                            <input name="nid" value="{{ old('nid') }}" type="text" class="form-control"
                                                   placeholder="NID"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 13 character</span>
                                            @error("nid")
                                            <p class="text-danger"> {{ $errors->first("nid") }} </p>
                                            @enderror
                                        </div>

                                        {{-- TIN --}}
                                        <div class="col-lg-3">
                                            <label>TIN</label>
                                            <input type="number" value="{{ old('tin') }}"
                                                   name="tin" class="form-control" placeholder="TIN"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 12 character</span>
                                            @error("tin")
                                            <p class="text-danger"> {{ $errors->first("tin") }} </p>
                                            @enderror
                                        </div>
                                        <div class="col-lg-4">
                                            <label>Email (Personal)</label>
                                            <input type="email" value="{{ old('personal_email') }}"
                                                   name="personal_email" class="form-control"
                                                   placeholder="Personal Email ID"/>
                                            @error("relation")
                                            <p class="text-danger"> {{ $errors->first("relation") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        {{-- Address --}}
                                        <div class="col-lg-4">
                                            <label>
                                                Present Address
                                                {{--<span class="text-danger">*</span>--}}
                                            </label>
                                            <textarea class="form-control present_address_address"
                                                      name="present_address[address]" rows="6"
                                                      placeholder="Present Address">{{ old('present_address.address') }}</textarea>
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
                                            <select name="present_address[division_id]"
                                                    class="form-control present_address_division_id"
                                                    id="present_address_division_id">
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["divisions"] as $division)
                                                    <option
                                                        value="{{ $division->id }}" {{ old("present_address.division_id") == $division->id ? 'selected' : '' }}>
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
                                                <input type="number" value="{{ old('present_address.zip') }}"
                                                       name="present_address[zip]"
                                                       class="form-control present_address_zip" placeholder="Zip Code"/>
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
                                            <select class="form-control present_address_district_id"
                                                    name="present_address[district_id]"
                                                    id="present_address_district_id">
                                                <option value="" disabled selected>Select an option</option>
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
                                                    <input type="checkbox" name="same_as_present"
                                                           class="same_as_present">
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
                                            <label>
                                                Permanent Address
                                                {{--<span class="text-danger">*</span>--}}
                                            </label>
                                            <textarea class="form-control permanent_address_address"
                                                      name="permanent_address[address]" rows="6"
                                                      placeholder="Permanent Address">{{ old('permanent_address.address') }}</textarea>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 10 character</span>
                                            @error("permanent_address.address")
                                            <p class="text-danger"> {{ $errors->first("permanent_address.address") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Division --}}
                                        <div class="col-lg-4">
                                            <label>
                                                Division
                                                {{--<span class="text-danger">*</span>--}}
                                            </label>
                                            <select name="permanent_address[division_id]"
                                                    class="form-control permanent_address_division_id"
                                                    id="permanent_address_division_id">
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["divisions"] as $division)
                                                    <option
                                                        value="{{ $division->id }}" {{ old("permanent_address.division_id") == $division->id ? 'selected' : '' }}>
                                                        {{ $division->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("permanent_address.division_id")
                                            <p class="text-danger"> {{ $errors->first("permanent_address.division_id") }} </p>
                                            @enderror

                                            {{-- Zip code --}}
                                            <div class="col-lg-12 p-0 pt-9">
                                                <label>Zip Code</label>
                                                <input type="number" value="{{ old('permanent_address.zip') }}"
                                                       name="permanent_address[zip]"
                                                       class="form-control permanent_address_zip"
                                                       placeholder="Zip Code"/>
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
                                            <select class="form-control permanent_address_district_id"
                                                    name="permanent_address[district_id]"
                                                    id="permanent_address_district_id">
                                                <option value="" disabled selected>Select an option</option>
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
                                            {{--<input type="text"  name="bank_id" id="selectBanks" class="form-control" placeholder="Hi"/>--}}
                                            <select name="bank_id" id='selectBanks' class="form-control">
                                                <option selected disabled value="">Choose an option</option>
                                                @foreach($data["banks"] as $bank)
                                                    <option
                                                        value="{{ $bank->id }}" {{ old("bank_id") == $bank->id ? "selected" : "" }}>
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
                                                    <option
                                                        value="{{ $branch->id }}" {{ old("branch_id") == $branch->id ? "selected" : "" }}>
                                                        {{ $branch->name }}
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
                                                <option
                                                    value="Saving" {{ old("account_type") == "Saving" ? "selected" : "" }}>
                                                    Saving
                                                </option>
                                                <option
                                                    value="Current" {{ old("account_type") == "Current" ? "selected" : "" }}>
                                                    Current
                                                </option>
                                                <option
                                                    value="Deposit" {{ old("account_type") == "Deposit" ? "selected" : "" }}>
                                                    Deposit
                                                </option>
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
                                            <input type="text" value="{{ old('account_name') ?? $employee->name }}"
                                                   name="account_name" class="form-control account_name"
                                                   placeholder="Account Name"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 5 character</span>
                                            @error("account_name")
                                            <p class="text-danger"> {{ $errors->first("account_name") }} </p>
                                            @enderror
                                        </div>

                                        {{-- A/C No. --}}
                                        <div class="col-lg-4">
                                            <label>Account Number</label>
                                            <input type="text" value="{{ old('account_number') }}"
                                                   name="account_number" class="form-control"
                                                   placeholder="Account Number"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 10 character</span>
                                            @error("account_number")
                                            <p class="text-danger"> {{ $errors->first("account_number") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Opening Balance (Tax) --}}
                                        <div class="col-lg-4">
                                            <label>Opening Balance (Tax)</label>
                                            <input type="number" value="{{ old('tax_opening_balance') }}"
                                                   name="tax_opening_balance" class="form-control"
                                                   placeholder="Tax Opening Balance"/>
                                            @error("tax_opening_balance")
                                            <p class="text-danger"> {{ $errors->first("tax_opening_balance") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        {{-- Nominee Name --}}
                                        <div class="col-lg-4">
                                            <label>Nominee Name </label>
                                            <input type="text" value="{{ old('nominee') }}"
                                                   name="nominee" class="form-control" placeholder="Nominee Name"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("nominee")
                                            <p class="text-danger"> {{ $errors->first("nominee") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Relation with Nominee --}}
                                        <div class="col-lg-4">
                                            <label>Relation with Nominee</label>
                                            <input type="text" value="{{ old('relation_with_nominee') }}"
                                                   name="relation_with_nominee" class="form-control"
                                                   placeholder="Relation with Nominee"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 3 character</span>
                                            @error("relation_with_nominee")
                                            <p class="text-danger"> {{ $errors->first("relation_with_nominee") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Nominee Contact --}}
                                        <div class="col-lg-4">
                                            <label>Nominee Contact</label>
                                            <input type="text" value="{{ old('nominee_contact') }}"
                                                   name="nominee_contact" class="form-control"
                                                   placeholder="Nominee Contact"/>
                                            <span
                                                class="form-text text-right text-custom-muted">At least 11 character</span>
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
                                    <div id="kt_repeater_1">
                                        <div data-repeater-list="">
                                            <div data-repeater-item="" class="section-repeater">
                                                <div class="form-group row float-right">
                                                    <div class="col-lg-4 section-repeater-delete-btn">
                                                        <a href="javascript:;" data-repeater-delete=""
                                                           class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    {{-- Degree Achieved --}}
                                                    <div class="col-lg-3">
                                                        <label>Degree Achieved</label>
                                                        <select name="degree_id[]" class="form-control">
                                                            <option value="" selected disabled>Choose an option</option>
                                                            @foreach($data["degrees"] as $degree)
                                                                <option
                                                                    value="{{ $degree->id }}" {{ old("degree_id") == $degree->id ? "selected" : "" }}>
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
                                                        <label>Name of Institute</label>
                                                        <select name="institute_id[]"
                                                                class="form-control selectInstitute">
                                                            <option value="" selected disabled>Choose an option</option>
                                                            @foreach($data["institutes"] as $institute)
                                                                <option
                                                                    value="{{ $institute->id }}">{{ $institute->name }}</option>
                                                            @endforeach
                                                        </select>

                                                        @error("institute_id")
                                                        <p class="text-danger"> {{ $errors->first("institute_id") }} </p>
                                                        @enderror
                                                    </div>
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
                                                        <input name="result[]" type="text" class="form-control"
                                                               placeholder="Result"/>
                                                        @error("result")
                                                        <p class="text-danger"> {{ $errors->first("result") }} </p>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-5">
                                            <div class="col-lg-4">
                                                <a href="javascript:;" data-repeater-create=""
                                                   class="btn btn-sm font-weight-bolder btn-light-primary">
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
                                    <div id="kt_repeater_2">
                                        <div data-repeater-list="">
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
                                                        <select name="designation[]"
                                                                class="form-control form-control-light">
                                                            <option value="" selected disabled>Choose an option</option>
                                                            @foreach($data["designations"] as $designation)
                                                                <option
                                                                    value="{{ $designation->id }}">{{ $designation->title }}</option>
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
                                        </div>

                                        <div class="form-group row mt-5">
                                            <div class="col-lg-4">
                                                <a href="javascript:;" data-repeater-create=""
                                                   class="btn btn-sm font-weight-bolder btn-light-primary">
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
                                <div class="col-lg-10"></div>
                                <div class="col-lg-2">
                                    <button type="reset" class="btn btn-secondary ml-lg-16">Reset</button>
                                    <button type="submit" class="btn btn-primary float-right ml-0">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>
    <script type="text/javascript">
        // SAME AS PRESENT
        $(".same_as_present").change(function () {
            if (this.checked) {
                const present_address_address = $(".present_address_address").val();
                const present_address_district_id = $(".present_address_district_id").val();
                const present_address_division_id = $(".present_address_division_id").val();
                const present_address_zip = $(".present_address_zip").val();
                permanentAddressDivisionId(present_address_division_id);
                $(".permanent_address_address").val(present_address_address)
                $(".permanent_address_division_id").val(present_address_division_id)
                $(".permanent_address_district_id").val(present_address_district_id)
                $(".permanent_address_zip").val(present_address_zip)
            } else {
                $(".permanent_address_address").val("")
                $(".permanent_address_district_id").empty()
                $(".permanent_address_division_id").val("")
                $(".permanent_address_zip").val("")
            }
        });

        /**
         *
         * @param _divisionID
         */
        function permanentAddressDivisionId(_divisionID) {
            let url = "{{ route('employee.districtByDivision', ':division') }}";
            url = url.replace(":division", _divisionID);
            $.get(url, {}, function (response, status) {
                $("#permanent_address_district_id").empty();
                $("#permanent_address_district_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data.districts, function (key, value) {
                    $("#permanent_address_district_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                });
                const present_address_district_id = $(".present_address_district_id").val();
                $(".permanent_address_district_id").val(present_address_district_id)
            });
        }

        // PRESENT ADDRESS:: Get district by division
        $('#present_address_division_id').change(function () {
            var _presentAddressDivisionID = $(this).val();
            let url = "{{ route('employee.districtByDivision', ':division') }}";
            url = url.replace(":division", _presentAddressDivisionID);
            $.get(url, {}, function (response, status) {
                $("#present_address_district_id").empty();
                $("#present_address_district_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data.districts, function (key, value) {
                    $("#present_address_district_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            })
        });

        // PERMANENT ADDRESS:: Get district by division
        $('#permanent_address_division_id').change(function () {
            var _permanentAddressDivisionID = $(this).val();
            permanentAddressDivisionId(_permanentAddressDivisionID);
        });
    </script>
    <script type="text/javascript">
        // CSRF Token
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function () {
            $("#selectBanks").select2({
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

            $("#selectBranch").select2({
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

            /*jQuery('#selectBanks').autocomplete({
                source: function( request, response ) {
                    $.ajax( {
                        url: "{{ route('getBanks') }}",
                        type: "post",
                        dataType: "json",
                        contentType:"application/json; charset=utf-8",
                        data: {
                            search: request.term
                        },
                        success: function( data ) {
                            response( data );
                        }
                    } );
                },
                autoFocus: true,
                minLength: 0,
            });*/

        });
    </script>
@endsection
