@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="#" method="POST" enctype="multipart/form-data" class="form">
                @csrf
                <div class="mt-n0">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header flex-wrap">
                            <div class="card-title">
                                <h3 class="card-label">PERSONAL INFORMATION</h3>
                            </div>
                            <div class="card-title">
                                <a href="{{ route('employee.profileDownload', ['employee' => $employee->uuid]) }}" class="btn btn-light-primary font-weight-bolder" target="_blank">
                                    <span class="navi-icon">
                                                    <i class="la la-file-excel-o"></i>
                                                </span>
                                    <span class="navi-text">PDF</span>
                                </a>
                            </div>
                        </div>
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
                                    </div>
                                    @error("photo")
                                    <p class="text-danger"> {{ $errors->first("photo") }} </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        <table class="table table-responsive-lg table-bordered table-hover">
                                            <tr>
                                                <td width="30%"><b>Name</b></td>
                                                <td>{{ $employee->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>Email</td>
                                                <td>{{ $employee->email }}</td>
                                            </tr>
                                            <tr>
                                                <td>Phone</td>
                                                <td>{{ $employee->phone }}</td>
                                            </tr>

                                            <tr>
                                                <td>Office ID</td>
                                                <td>{{ $employee->fingerprint_no }}</td>
                                            </tr>
                                            <tr>
                                                <td>Joining Date</td>
                                                <td>{{ date('M d, Y', strtotime($employee->employeeStatus()->where("action_reason_id", 2)->orderByDesc('action_date')->first()->action_date ?? '')) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Supervisor</td>
                                                <td>{{ $employee->is_supervisor == 1 ? 'Yes' : 'No' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Personal Phone</td>
                                                <td>{{ $employee->profile->personal_phone }}</td>
                                            </tr>
                                            <tr>
                                                <td>Personal Email</td>
                                                <td>{{ $employee->profile->personal_email }}</td>
                                            </tr>
                                            <tr>
                                                <td>Gender</td>
                                                <td>{{ $employee->profile->gender }}</td>
                                            </tr>
                                            <tr>
                                                <td>Religion</td>
                                                <td>{{ $employee->profile->religion }}</td>
                                            </tr>
                                            <tr>
                                                <td>DoB</td>
                                                <td>{{ $employee->profile->dob ? date('M d, Y', strtotime($employee->profile->dob)) : '' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Marital Status</td>
                                                <td>{{ $employee->profile->marital_status }}</td>
                                            </tr>
                                            <tr>
                                                <td>Emergency Contact</td>
                                                <td>{{ $employee->profile->emergency_contact }}</td>
                                            </tr>
                                            <tr>
                                                <td>Relation with Emergency Contact</td>
                                                <td>{{ $employee->profile->relation }}</td>
                                            </tr>
                                            <tr>
                                                <td>Blood Group</td>
                                                <td>{{ $employee->profile->blood_group }}</td>
                                            </tr>
                                            <tr>
                                                <td>NID</td>
                                                <td>{{ $employee->profile->nid }}</td>
                                            </tr>
                                            <tr>
                                                <td>TIN</td>
                                                <td>{{ $employee->profile->tin }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($employee->presentAddress))
                    <div class="mt-n5">
                        <div class="card card-custom card-stretch gutter-b">
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">PRESENT ADDRESS</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <td width="30%">Area</td>
                                                    <td>{{ $employee->presentAddress->address  }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Zip</td>
                                                    <td>{{ $employee->presentAddress->zip }}</td>
                                                </tr>
                                                <tr>
                                                    <td>District</td>
                                                    <td>{{ optional($employee->presentAddress->district)->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Division</td>
                                                    <td>{{ optional($employee->presentAddress->division)->name }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($employee->permanentAddress))
                    <div class="mt-n5">
                        <div class="card card-custom card-stretch gutter-b">
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">PERMANENT ADDRESS</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <td width="30%">Area</td>
                                                    <td>{{ $employee->permanentAddress->address  }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Zip</td>
                                                    <td>{{ $employee->permanentAddress->zip }}</td>
                                                </tr>
                                                <tr>
                                                    <td>District</td>
                                                    <td>{{ optional($employee->permanentAddress->district)->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Division</td>
                                                    <td>{{ optional($employee->permanentAddress->division)->name }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header flex-wrap">
                            <div class="card-title">
                                <h3 class="card-label">PROFESSIONAL INFORMATION</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        <table class="table table-responsive-lg table-bordered table-hover">
                                            <tr>
                                                <td width="30%">Office Division</td>
                                                <td>{{ $employee->currentPromotion->officeDivision->name  }}</td>
                                            </tr>
                                            <tr>
                                                <td>Department</td>
                                                <td>{{ $employee->currentPromotion->department->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>Designation</td>
                                                <td>{{ $employee->currentPromotion->designation->title }}</td>
                                            </tr>
                                            <tr>
                                                <td>Promoted Date</td>
                                                <td>{{ $employee->currentPromotion->promoted_date }}</td>
                                            </tr>
                                            <tr>
                                                <td>MovementType</td>
                                                <td>{{ $employee->currentPromotion->type }}</td>
                                            </tr>
                                            <tr>
                                                <td>EmploymentType</td>
                                                <td>{{ optional($employee->getEmploymentStatus())->employment_type }}</td>
                                            </tr>

                                            @if(auth()->user()->id == $employee->id || auth()->user()->can("View Salary"))
                                                <tr>
                                                    <td>Salary</td>
                                                    <td>{{ $employee->currentPromotion->salary }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>WorkSlot</td>
                                                <td>{{ $employee->currentPromotion->workSlot->title }}</td>
                                            </tr>
                                            <tr>
                                                <td>PayGrade</td>
                                                <td>{{ $employee->currentPromotion->payGrade->name }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header flex-wrap">
                            <div class="card-title">
                                <h3 class="card-label">PROMOTIONAL INFORMATION</h3>
                            </div>
                            <div class="card-toolbar">
                                @can("Create Promotion")
                                    <a href="{{ route('promotion.create') }}" class="btn btn-primary font-weight-bolder" target="_blank">
                                    <span class="svg-icon svg-icon-default svg-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                                            </g>
                                        </svg>
                                    </span>
                                        Add Promotion
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        <table class="table table-responsive-lg table-bordered table-hover">
                                            <tr>
                                                <th>Office Division</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Promoted Date</th>
                                                <th>Movement Type</th>
                                                <th>Employment Type</th>
                                                @if(auth()->user()->id == $employee->id || auth()->user()->can("View Salary"))
                                                    <th>Salary</th>
                                                @endif
                                                <th>PayGrade</th>
                                                <th>WorkSlot</th>
                                            </tr>
                                            @foreach($employee->promotions->sortByDesc("id") as $promotion)
                                                <tr>
                                                    <td>{{ $promotion->officeDivision->name }}</td>
                                                    <td>{{ $promotion->allDepartment->name }}</td>
                                                    <td>{{ $promotion->designation->title }}</td>
                                                    <td>{{ $promotion->promoted_date ? date('M d, Y', strtotime($promotion->promoted_date)) : date('M d, Y', strtotime($employee->employeeStatus->where("action_reason_id", 2)->first()->action_date)) }}</td>
                                                    <td>{{ $promotion->type?$promotion->type:"" }}</td>
                                                    <td>{{ !empty($promotion->employment_type)?$promotion->employment_type:"" }}</td>
                                                    @if(auth()->user()->id == $employee->id || auth()->user()->can("View Salary"))
                                                        <td>{{ $promotion->salary }}</td>
                                                    @endif
                                                    <td>{{ $promotion->payGrade->name }}</td>
                                                    <td>{{ $promotion->workSlot->title }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($employee->currentBank))
                    <div class="mt-n5">
                        <div class="card card-custom card-stretch gutter-b">
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">BANK INFORMATION</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <td width="30%">Name</td>
                                                    <td>{{ $employee->currentBank->bank->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">Branch</td>
                                                    <td>{{ $employee->currentBank->branch->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">Account</td>
                                                    <td>{{ $employee->currentBank->account_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">Type</td>
                                                    <td>{{ $employee->currentBank->account_type }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">A/C No</td>
                                                    <td>{{ $employee->currentBank->account_no }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">Nominee</td>
                                                    <td>{{ $employee->currentBank->nominee_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">Relation with Nominee</td>
                                                    <td>{{ $employee->currentBank->relation_with_nominee }}</td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">Nominee Contact</td>
                                                    <td>{{ $employee->currentBank->nominee_contact }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($employee->loans->count() > 0)
                    <div class="mt-n5">
                        <div class="card card-custom card-stretch gutter-b">
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">LOAN INFORMATION</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <th>Amount</th>
                                                    <th>Tenure</th>
                                                    <th>Installment Amount</th>
                                                    <th>Approved Date</th>
                                                    <th>Status</th>
                                                </tr>
                                                @foreach($employee->loans->sortByDesc("id") as $loan)
                                                    <tr>
                                                        <td>{{ $loan->loan_amount }}</td>
                                                        <td>{{ $loan->loan_tenure }}</td>
                                                        <td>{{ $loan->installment_amount }}</td>
                                                        <td>{{ $loan->loan_approved_date ? date('M d, Y', strtotime($loan->loan_approved_date)) : '' }}</td>
                                                        <td>{{ $loan->status }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>

                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <th>Installment Amount</th>
                                                    <th>Month</th>
                                                    <th>Year</th>
                                                </tr>
                                                @foreach($employee->userLoans->sortByDesc("id") as $userLoan)
                                                    <tr>
                                                        <td>{{ $userLoan->amount_paid }}</td>
                                                        <td>{{ $userLoan->month ?? null }}</td>
                                                        <td>{{ $userLoan->year ?? null }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if((auth()->user()->id == $employee->id || auth()->user()->can("View Salary")) && $employee->salaries->count() > 0)
                    <div class="mt-n5">
                        <div class="card card-custom card-stretch gutter-b">
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">SALARY INFORMATION</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <th>Salary</th>
                                                    <th>Basic</th>
                                                    <th>Earning</th>
                                                    <th>Cash</th>
                                                    <th>Deduction</th>
                                                    <th>Overtime</th>
                                                    <th>Holiday</th>
                                                    <th>Leave Unpaid</th>
                                                    <th>Tax (Payable)</th>
                                                    <th>Taxable</th>
                                                    <th>Loan</th>
                                                    <th>Bonus</th>
                                                    <th>Payable</th>
                                                    <th>Status</th>
                                                    <th>Month</th>
                                                    <th>Year</th>
                                                </tr>
                                                @foreach($employee->salaries->sortByDesc("id") as $salaries)
                                                    <tr>
                                                        <td>{{ $salaries->salary }}</td>
                                                        <td>{{ $salaries->basic }}</td>
                                                        <td>{{ $salaries->total_earning }}</td>
                                                        <td>{{ $salaries->total_cash_earning }}</td>
                                                        <td>{{ $salaries->total_deduction }}</td>
                                                        <td>{{ $salaries->overtime_amount }}</td>
                                                        <td>{{ $salaries->total_holiday_amount }}</td>
                                                        <td>{{ $salaries->leave_unpaid_amount }}</td>
                                                        <td>{{ $salaries->payable_tax_amount }}</td>
                                                        <td>{{ $salaries->taxable_amount }}</td>
                                                        <td>{{ $salaries->loan_amount }}</td>
                                                        <td>{{ $salaries->bonus_amount }}</td>
                                                        <td>{{ $salaries->payable_amount }}</td>
                                                        <td>{{ $salaries->status == 1 ? 'Paid' : 'Unpaid' }}</td>
                                                        <td>{{ date('F', mktime(0, 0, 0, $salaries->month, 10))  }}</td>
                                                        <td>{{ $salaries->year }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($employee->leaveRequests->count() > 0)
                    <div class="mt-n5">
                        <div class="card card-custom card-stretch gutter-b">
                            <div class="card-header flex-wrap">
                                <div class="card-title">
                                    <h3 class="card-label">LEAVE INFORMATION</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <table class="table table-responsive-lg table-bordered table-hover">
                                                <tr>
                                                    <th>Total Leaves</th>
                                                    <th>Leaves Consumed</th>
                                                    <th>Leave Remaining</th>
                                                    <th>Year</th>
                                                </tr>
                                                <tr>
                                                    <td>{{ $userLeave->total_initial_leave }}</td>
                                                    <td>{{ $userLeave->total_initial_leave - $userLeave->total_leaves }}</td>
                                                    <td>{{ $userLeave->total_leaves }}</td>
                                                    <td>{{ $userLeave->year }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-10"></div>
                                <div class="col-lg-2">
                                    {{--                                    <button type="reset" class="btn btn-secondary ml-lg-12">Reset</button>--}}
                                    {{--                                    <button type="submit" class="btn btn-primary float-right ml-0">Update</button>--}}
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
