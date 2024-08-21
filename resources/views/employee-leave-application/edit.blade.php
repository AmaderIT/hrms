@extends('layouts.app')
@section("top-css")
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
    <style>
        input[type="date"]::-webkit-datetime-edit, input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-clear-button {
            color: #fff;
            position: relative;
        }

        input[type="date"]::-webkit-datetime-edit-year-field{
            position: absolute !important;
            border-left:1px solid #8c8c8c;
            padding: 2px;
            color:#000;
            left: 56px;
        }

        input[type="date"]::-webkit-datetime-edit-month-field{
            position: absolute !important;
            border-left:1px solid #8c8c8c;
            padding: 2px;
            color:#000;
            left: 26px;
        }

        input[type="date"]::-webkit-datetime-edit-day-field{
            position: absolute !important;
            color:#000;
            padding: 2px;
            left: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Apply for Leave</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('employee-leave-application.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('employee-leave-application.update', ['employeeLeaveApplication' => $employeeLeaveApplication->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee</label>
                                <select class="form-control" name="user_id" id="user_id">
                                    @foreach($data["employees"] as $employee)
                                        <option value="{{ $employee->id }}" {{ $employee->id == $employeeLeaveApplication->user_id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Leave Type --}}
                            <div class="form-group">
                                <label for="leave_type_id">Leave Type</label>
                                <select class="form-control" id="leave_type_id" name="leave_type_id">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["leaveTypes"] as $leaveType)
                                        <option value="{{ $leaveType->id }}" {{ $employeeLeaveApplication->leave_type_id === $leaveType->id ? "selected" : "" }}>
                                            {{ $leaveType->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error("leave_type_id")
                                <p class="text-danger"> {{ $errors->first("leave_type_id") }} </p>
                                @enderror
                            </div>

                            <input type="hidden" name="leave_allocation_details_id" value="{{ $employeeLeaveApplication->leave_allocation_details_id }}" id="leave_allocation_details_id" required/>

                            {{-- Current Balance --}}
                            <div class="form-group">
                                <label for="current_balance">Current Balance</label>
                                <input type="number" class="form-control" name="current_balance" id="current_balance" value="{{ $data['balance'] }}"
                                       readonly placeholder="Current Balance"/>
                            </div>

                            {{-- From Date --}}
                            <div class="form-group">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" required
                                       value="{{ date('Y-m-d', strtotime($employeeLeaveApplication->from_date)) }}" />

                                @error("from_date")
                                <p class="text-danger"> {{ $errors->first("from_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" id="to_date" required
                                       value="{{ date('Y-m-d', strtotime($employeeLeaveApplication->to_date)) }}"  />

                                @error("to_date")
                                <p class="text-danger"> {{ $errors->first("to_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="number_of_days">Number of day</label>
                                <input type="number" class="form-control" name="number_of_days" id="number_of_days" placeholder="Number of day"
                                    value="{{ $employeeLeaveApplication->number_of_days }}" step="0.5"/>

                                @error("number_of_days")
                                <p class="text-danger"> {{ $errors->first("number_of_days") }} </p>
                                @enderror
                            </div>

                            <div class="form-group row">
                                <div class="col-9 col-form-label">
                                    <div class="checkbox-inline">
                                        <label class="checkbox">
                                            <input type="checkbox" value="true" id="half_day" name="half_day" {{ $employeeLeaveApplication->half_day === 1 ? "checked" : "" }} />
                                            <span></span>Half Day
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="purpose">Purpose</label>
                                <textarea class="form-control" name="purpose" rows="6" id="purpose" placeholder="Purpose" required>{!! $employeeLeaveApplication->purpose !!}</textarea>

                                @error("purpose")
                                <p class="text-danger"> {{ $errors->first("purpose") }} </p>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Update Application</button>
                            </div>
                        </div>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection

@section("footer-js")
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            let _currentBalance = 0;

            // Calculation "number of days" according to the difference from the "from_date" to the "to_date"
            $("#from_date, #to_date").change(function (e) {
                var _from_date = $("#from_date").val();
                var _to_date = $("#to_date").val();

                if(_from_date != "" && _to_date != "") {
                    _from_date = new Date(_from_date);
                    _to_date = new Date(_to_date);

                    var millisBetween = _from_date.getTime() - _to_date.getTime();
                    var days = millisBetween / (1000 * 3600 * 24);

                    var _number_of_days = Math.round(Math.abs(days)) + 1;

                    $("#number_of_days").val(_number_of_days);
                    $("#number_of_days").prop("readonly", true);
                }

                if(_from_date != _to_date)
                {
                    $("#half_day").attr("checked", false);
                }
            });

            // Half day
            $("#half_day").change(function (e) {
                if(this.checked) {
                    var _from_date = $("#from_date").val();
                    $("#to_date").val(_from_date);
                    $("#number_of_days").val(0.5);
                    $("#to_date").prop("readonly", true)
                    $("#number_of_days").prop("readonly", true)
                } else {
                    $("#to_date").prop("readonly", false)
                    $("#number_of_days").prop("readonly", false)
                }
            });

            // Current Balance
            $("[name='leave_type_id'], [name='user_id']").on("change", function() {
                let _leaveType = $("#leave_type_id").val();
                let _employee = $("#user_id").val();

                if (_leaveType != null) {
                    let url = "{{ route('employee-leave-application.balance', ['leaveType' => ':leaveType', 'employee' => ':employee']) }}";
                    url = url.replace(":leaveType", _leaveType);
                    url = url.replace(":employee", _employee);

                    $.get(url, function (data, status) {
                        if (status === "success") {
                            $("#current_balance").val(data.balance);

                            _currentBalance = data.balance;
                            $("#leave_allocation_details_id").val(data.leave_allocation_details_id);
                        }
                    });
                }
            });

            $("select").select2({
                theme: "classic",
            });
        });
    </script>
@endsection
