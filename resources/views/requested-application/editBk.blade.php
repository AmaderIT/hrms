<!--
This Blade is used for both apply leave application (add/edit) and approval leave application(edit)
Routes:-
1./apply-for-leave/create
2./apply-for-leave/edit/{uuid}
3./requested-application/edit/{uuid}
--!>


@extends('layouts.app')

@section('top-css')
    <style type="text/css">
        .requested-leave-summary-card {
            border: 1px dashed #3699ff;
            padding-top: 15px;
        }

        .list-group-item.active {
            z-index: 2;
            color: #1c1a1a;
            background-color: #ffffff;
            border-color: #3699FF;
            font-weight: bold;
        }

        .list-group-item {
            position: relative;
            display: block;
            padding: 0.25rem 0.25rem !important;
            background-color: #ffffff;
            border: 1px solid #EBEDF3;
        }

        .requested-leave-summary-card-font-weight {
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    @if($requestedApplication && !$room)
                        <h3 class="card-title">Edit Requested Application</h3>
                    @elseif($requestedApplication && $room && $room == 'employee')
                        <h3 class="card-title">Edit Leave Application</h3>
                    @else
                        <h3 class="card-title">New Leave Application</h3>
                    @endif

                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a @if($requestedApplication && !$room) href="{{ route('requested-application.index') }}"
                               @else href="{{ route('apply-for-leave.index') }}" @endif class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>

                <form
                    @if($requestedApplication && !$room)
                    action="{{ route('requested-application.manipulate', ['requestedApplication' => $requestedApplication->uuid]) }}"
                    @elseif($requestedApplication && $room && $room == 'employee')
                    action="{{ route('apply-for-leave.update',['applyForLeave' =>  $requestedApplication->uuid]) }}"
                    @else
                    action="{{ route('apply-for-leave.store') }}"
                    @endif
                    method="POST">
                    @csrf
                    <input type="hidden" name="leave_allocation_details_id"
                           value="{{old('leave_allocation_details_id') ?? $requestedApplication->leave_allocation_details_id?? 0}}"
                           id="leave_allocation_details_id" required/>

                    <div class="card-body" style="padding-top: 10px; padding-bottom:10px;">
                        <div class="row" style="margin-top:20px;">
                            <!-- START LEAVE APPLICATION FORM -->
                            <div class="col-lg-7 col-md-7 col-sm-12">
                                {{-- Leave Type --}}
                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="form-group">
                                            <label>Applied Date</label>
                                            <input type="text" disabled readonly class="form-control"
                                                   value=" @if($requestedApplication)  {{date('d M Y',strtotime($requestedApplication->created_at)) }} @else {{date('d M Y')}}@endif"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="form-group">
                                            <label for="leave_type_id">Leave Type</label>
                                            <select class="form-control" id="leave_type_id" name="leave_type_id"
                                                    required>
                                                <option value="">Select an option</option>
                                                @foreach($data["leaveTypes"] as $leaveType)
                                                    <option
                                                        value="{{ $leaveType->id }}"
                                                        @if( ($requestedApplication &&  $requestedApplication->leave_type_id === $leaveType->id) || (int) old('leave_type_id') === $leaveType->id)
                                                        selected
                                                        @endif
                                                    >
                                                    {{ $leaveType->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error("leave_type_id")
                                            <p class="text-danger"> {{ $errors->first("leave_type_id") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="form-group">
                                            <label for="from_date">Available Leave balance</label>
                                            <input type="text" readonly class="form-control"
                                                   name="available_leave_balance" id="current_balance"
                                                   value="{{$data['total_leave']}}"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 div-md-6">
                                        {{-- From Date --}}
                                        <div class="form-group">
                                            <label for="from_date">From Date</label>
                                            <input type="date" class="form-control" id="from_date" name="from_date"
                                                   required
                                                   @if($requestedApplication)
                                                   value="{{ date('Y-m-d', strtotime($requestedApplication->from_date)) }}"
                                                   @else
                                                   value="{{ old('to_date') }}"
                                                @endif
                                            />

                                            @error("from_date")
                                            <p class="text-danger"> {{ $errors->first("from_date") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 div-md-6">
                                        {{-- To Date --}}
                                        <div class="form-group">
                                            <label for="to_date">To Date</label>
                                            <input type="date" class="form-control" name="to_date" id="to_date" required
                                                   @if($requestedApplication)
                                                   value="{{ date('Y-m-d', strtotime($requestedApplication->to_date)) }}"
                                                   @else
                                                   value="{{ old('to_date') }}"
                                                @endif/>

                                            @error("to_date")
                                            <p class="text-danger"> {{ $errors->first("to_date") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-lg-6 div-md-6">
                                        {{-- Number of day --}}
                                        <div class="form-group">
                                            <label for="number_of_days">Number of day</label>
                                            <input type="text" class="form-control" name="number_of_days"
                                                   id="number_of_days" readonly placeholder="Number of day"
                                                   value="{{ $requestedApplication->number_of_days ?? old('number_of_days') }}"/>
                                            @error("number_of_days")
                                            <p class="text-danger"> {{ $errors->first("number_of_days") }} </p>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-lg-6 div-md-6">
                                        {{-- Half Day --}}
                                        <div class="form-group row half_day_class">
                                            <div class="col-9 col-form-label">
                                                <div class="checkbox-inline">
                                                    <label class="checkbox">
                                                        <input type="checkbox" value="true" id="half_day"
                                                               name="half_day"
                                                               @if( ($requestedApplication &&  $requestedApplication->half_day == true) || old('half_day') == true )
                                                               checked
                                                            @endif
                                                        />
                                                        <span></span>Half Day
                                                    </label>
                                                </div>
                                            </div>
                                        </div>


                                        @if($requestedApplication && !$room)
                                            <div
                                                class="form-group row paid_class @if($requestedApplication->number_of_days ?? null <=$data['total_leave']) d-none @endif">
                                                <div class="col-2 col-form-label">
                                                    <div class="checkbox-inline">
                                                        <label class="checkbox">
                                                            <input type="checkbox" value="true" id="paid_days"
                                                                   name="paid_days"
                                                                   @if($requestedApplication->number_of_paid_days ?? null>0) checked @endif />
                                                            <span></span>Paid
                                                        </label>
                                                    </div>
                                                </div>

                                                <div
                                                    class="col-10 col-form-label number_of_paid_days_div @if($requestedApplication->number_of_paid_days ?? null ==0) d-none @else d-flex @endif">
                                                    <label for="purpose">Number of paid days</label>
                                                    <select class="form-control" name="number_of_paid_days"
                                                            id="number_of_paid_days">
                                                        @for($i=0.5;$i<=($requestedApplication->number_of_days ?? null -$data['total_leave']);$i=$i+0.5)
                                                            <option value="{{$i}}"
                                                                    @if($requestedApplication->number_of_paid_days ?? null ==$i) selected @endif>{{$i}}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                        @endif


                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 div-md-6">
                                        <div
                                            class="form-group leave_start_time_class @if(!$requestedApplication || !$requestedApplication->half_day) d-none @endif">
                                            <label for="started_at">Leave Start Time</label>
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control time-picker"
                                                       name="leave_start_time" id="leave_start_time"
                                                       @if($requestedApplication && $requestedApplication->half_day) required
                                                       @endif value="{{ $requestedApplication->leave_start_time ?? old('leave_start_time') }}">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="leave_start_time"><i
                                                            class="glyphicon glyphicon-time"></i></span>
                                                </div>
                                            </div>

                                            @error('leave_start_time')
                                            <p class="text-danger"> {{ $errors->first("leave_start_time") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 div-md-6">
                                        <div
                                            class="form-group leave_end_time_class @if(!$requestedApplication || !$requestedApplication->half_day) d-none @endif">
                                            <label for="ended_at">Leave End Time</label>
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control time-picker"
                                                       name="leave_end_time" id="leave_end_time"
                                                       @if($requestedApplication && $requestedApplication->half_day) required
                                                       @endif value="{{ $requestedApplication->leave_end_time ?? old('leave_end_time') }}">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="leave_start_time"><i
                                                            class="glyphicon glyphicon-time"></i></span>
                                                </div>
                                            </div>
                                            @error('leave_end_time')
                                            <p class="text-danger"> {{ $errors->first("leave_end_time") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 div-md-6">
                                        {{-- Purpose --}}
                                        <div class="form-group">
                                            <label for="purpose">Purpose</label>
                                            <textarea class="form-control" name="purpose" rows="2" id="purpose"
                                                      placeholder="Purpose"
                                                      required>{!! $requestedApplication->purpose ?? old('purpose') !!}</textarea>

                                            @error("purpose")
                                            <p class="text-danger"> {{ $errors->first("purpose") }} </p>
                                            @enderror
                                        </div>
                                    </div>

                                    @if($requestedApplication && !$room)
                                        @if( $requestedApplication && (auth()->user()->can("Authorize Leave Requests") || auth()->user()->can("Approve Leave Requests") ) )
                                            <div class="col-lg-6 div-md-6">
                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select class="form-control" id="status" name="status">
                                                        @if(auth()->user()->can("Authorize Leave Requests") AND $requestedApplication->status === \App\Models\LeaveRequest::STATUS_PENDING)
                                                            <option
                                                                value="{{ \App\Models\LeaveRequest::STATUS_AUTHORIZED }}" {{ $requestedApplication->status === \App\Models\LeaveRequest::STATUS_AUTHORIZED ? "selected" : "" }}>
                                                                Approve
                                                            </option>
                                                        @elseif(auth()->user()->can("Approve Leave Requests") AND $requestedApplication->status === \App\Models\LeaveRequest::STATUS_AUTHORIZED)
                                                            <option
                                                                value="{{ \App\Models\LeaveRequest::STATUS_APPROVED }}" {{ $requestedApplication->status === \App\Models\LeaveRequest::STATUS_APPROVED ? "selected" : "" }}>
                                                                Approve
                                                            </option>
                                                        @endif
                                                        <option
                                                            value="{{ \App\Models\LeaveRequest::STATUS_CANCEL }}" {{ $requestedApplication->status === \App\Models\LeaveRequest::STATUS_CANCEL ? "selected" : "" }}>
                                                            Cancel
                                                        </option>
                                                    </select>

                                                    @error("status")
                                                    <p class="text-danger"> {{ $errors->first("status") }} </p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @else
                                            <input type="hidden" name="status" value="0">

                                        @endif
                                    @endif
                                </div>
                            </div>
                            <!-- END LEAVE APPLICATION FORM -->

                            <div class="col-lg-5 col-md-5 col-md-12 requested-leave-summary-card">
                                {{--                                <div class="card-header border-0 pt-2">--}}
                                {{--                                    <div class="col-lg-8 col-md-5 col-md-12" style="border-left:1px solid #ccc; padding-left:10px;">--}}
                                <h5 class="text-center" style="text-decoration: underline;">Employee Information</h5>
                                <table style="width: 100%;">
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Employee ID
                                        </td>
                                        <td width="70%">
                                            : {{ !empty($getEmployeeInfos->fingerprint_no)?$getEmployeeInfos->fingerprint_no:"" }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Name</td>
                                        <td width="70%">
                                            : {{ !empty($getEmployeeInfos->name)?$getEmployeeInfos->name:"" }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Joining Date
                                        </td>
                                        <td width="70%">
                                            : {{ \Carbon\Carbon::createFromDate($getEmployeeInfos->employeeStatus->where("action_reason_id", 2)->first()->action_date->toDateTimeString())->format("M d, Y") }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Division</td>
                                        <td width="70%">
                                            : {{ !empty($getEmployeeInfos->currentPromotion->officeDivision->name)?$getEmployeeInfos->currentPromotion->officeDivision->name:"" }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Department</td>
                                        <td width="70%">
                                            : {{ !empty($getEmployeeInfos->currentPromotion->department->name)?$getEmployeeInfos->currentPromotion->department->name:"" }}</td>
                                    </tr>
                                </table>
                                <ul class="list-group">
                                    @php
                                        $totalLeave = 0;
                                    @endphp
                                    @foreach($data['leaveCalculationsInitialState'] as $keyLeave=>$valueLeave)
                                        @php
                                            $arrKeyLeave[$keyLeave] = $keyLeave;
                                            $balanceLeave = $data['calculateLeaveBalance'][$keyLeave];
                                            $consumedLeave = $valueLeave-$balanceLeave;
                                        @endphp
                                        <div class="list-group">
           <span
               class="list-group-item list-group-item-action flex-column align-items-start active">
               @if(!empty($data['formatLeaveTypes'])) {{ $data['formatLeaveTypes'][$keyLeave] }} @else {{''}} @endif
           </span>
                                        </div>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Actual
                                            <span class="badge badge-primary badge-pill">{{  $valueLeave }} Days</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Consumed
                                            <span
                                                class="badge badge-primary badge-pill">{{ $consumedLeave }} Days</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Balance
                                            <span class="badge badge-primary badge-pill">{{ $balanceLeave }} Days</span>
                                        </li>
                                        @php
                                            $totalLeave += $consumedLeave;
                                        @endphp
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" id="applyDiv">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">

                                <button type="submit" class="btn btn-primary apply">Submit</button>

                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="{{$data['input_filters']}}">
                </form>
            </div>
        </div>
    </div>
@endsection

@section("footer-js")
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.time-picker').datetimepicker({
                format: 'hh:mm A'
            });

            var total_leaves = {!! $data['total_leave'] !!};
            let _noOfDay = 0;
            var number_of_paid_days_from_authorizer = {!! $requestedApplication->number_of_paid_days ?? 0 !!};
            let half_day_count = {!! $data['half_day_count'] !!};

            $('.apply').click(function (e) {
                if ($('#half_day').is(':checked')) {
                    var start_time = $('#leave_start_time').val();
                    var end_time = $('#leave_end_time').val();
                    if (start_time && end_time) {
                        var diff = (new Date("1970-1-1 " + end_time) - new Date("1970-1-1 " + start_time)) / 1000 / 60 / 60;
                        if (Number(diff) > half_day_count) {
                            e.preventDefault();
                            swal.fire({
                                title: "Leave time duration exceeds half day policy! Please apply for full day leave or change start or end time."
                            })
                        }
                    }
                }
            });

            $("#from_date, #to_date").change(function (e) {
                $("#half_day").prop("checked", false);
                var _from_date = $("#from_date").val();
                var _to_date = $("#to_date").val();
                if (_from_date != "" && _to_date != "") {
                    _from_date = new Date(_from_date);
                    _to_date = new Date(_to_date);
                    if (_from_date <= _to_date) {
                        var millisBetween = _from_date.getTime() - _to_date.getTime();
                        var days = millisBetween / (1000 * 3600 * 24);
                        var _number_of_days = Math.round(Math.abs(days)) + 1;
                        if (_number_of_days == 1) {
                            $('.half_day_class').removeClass('d-none');
                        } else {
                            $('.half_day_class').addClass('d-none');
                            $('.leave_start_time_class').addClass('d-none');
                            $('.leave_end_time_class').addClass('d-none');
                            $('#leave_start_time').removeAttr('required');
                            $('#leave_end_time').removeAttr('required');
                            $('#leave_start_time').val('');
                            $('#leave_end_time').val('');
                        }
                        $("#number_of_days").val(_number_of_days);
                        $("#number_of_days").prop("readonly", true);
                        _noOfDay = _number_of_days;
                        if (total_leaves < _number_of_days) {
                            var html = '';
                            for (var i = 0.5; i <= (_number_of_days - total_leaves); i = i + 0.5) {
                                html += '<option value="' + i + '">' + i + '</option>';
                            }
                            $('#number_of_paid_days').html(html);
                            $('.paid_class').removeClass('d-none');
                        } else {
                            $("#paid_days").attr("checked", false);
                            $('.paid_class').addClass('d-none');
                            $('#number_of_paid_days').html('');
                        }

                        $("#applyDiv").show();

                    } else {
                        $("#applyDiv").hide();
                        $("#paid_days").attr("checked", false);
                        $('.paid_class').addClass('d-none');
                        $('#number_of_paid_days').html('');
                        $('.half_day_class').addClass('d-none');
                        $('.leave_start_time_class').addClass('d-none');
                        $('.leave_end_time_class').addClass('d-none');
                        $('#leave_start_time').removeAttr('required');
                        $('#leave_end_time').removeAttr('required');
                        $('#leave_start_time').val('');
                        $('#leave_end_time').val('');
                    }
                }
                if (_from_date != _to_date) {
                    $("#half_day").attr("checked", false);
                    $('.leave_start_time_class').addClass('d-none');
                    $('.leave_end_time_class').addClass('d-none');
                    $('#leave_start_time').removeAttr('required');
                    $('#leave_end_time').removeAttr('required');
                    $('#leave_start_time').val('');
                    $('#leave_end_time').val('');
                }
            });
// Half day
            $("#half_day").change(function (e) {
                if (this.checked) {
                    var _from_date = $("#from_date").val();
                    $("#to_date").val(_from_date);
                    $("#number_of_days").val(0.5);
                    $("#to_date").prop("readonly", true);
                    $("#number_of_days").prop("readonly", true);
                    if (total_leaves < 0.5) {
                        $('#number_of_paid_days').html('<option value="0.5">0.5</option>');
                        $('.paid_class').removeClass('d-none');
                    } else {
                        $("#paid_days").attr("checked", false);
                        $('.paid_class').addClass('d-none');
                        $('#number_of_paid_days').html('');
                    }
                    $('.leave_start_time_class').removeClass('d-none');
                    $('.leave_end_time_class').removeClass('d-none');
                    $("#leave_start_time").prop('required', true);
                    $("#leave_end_time").prop('required', true);
                } else {
                    $('.leave_start_time_class').addClass('d-none');
                    $('.leave_end_time_class').addClass('d-none');
                    $('#leave_start_time').removeAttr('required');
                    $('#leave_end_time').removeAttr('required');
                    $('#leave_start_time').val('');
                    $('#leave_end_time').val('');
                    @if (Route::getCurrentRoute()->getName() != 'requested-application.edit' && Route::getCurrentRoute()->getName() != 'apply-for-leave.edit')
                        $("#number_of_days").val(1);
                    @endif
                    if (total_leaves == 0) {
                        $('#number_of_paid_days').html('<option value="1">1</option>');
                        $('.paid_class').removeClass('d-none');
                    } else {
                        $("#paid_days").attr("checked", false);
                        $('.paid_class').addClass('d-none');
                        $('#number_of_paid_days').html('');
                    }
                    $("#to_date").prop("readonly", false)
                    $("#number_of_days").prop("readonly", true);
                }
            });

            $("#paid_days").change(function (e) {
                if (this.checked) {
                    $('.number_of_paid_days_div').removeClass('d-none');
                    $('.number_of_paid_days_div').addClass('d-flex');
                } else {
                    $('.number_of_paid_days_div').addClass('d-none');
                    $('.number_of_paid_days_div').removeClass('d-flex');
                }
            });

// Current Balance
            $("[name='leave_type_id']").on("change", function () {
                let _leaveType = $("#leave_type_id").val();
                if (_leaveType != null) {
                    @if($requestedApplication)
                    let url = "{{ route('requested-application.availableBalance', ['leaveType' => ':leaveType','requestedApplication' => $requestedApplication]) }}";
                    @else
                    let url = "{{ route('apply-for-leave.balance', ['leaveType' => ':leaveType']) }}";
                    @endif
                        url = url.replace(":leaveType", _leaveType);
                    $.get(url, function (data, status) {
                        total_leaves = Number(data.balance);
                        var number_of_days = Number($('#number_of_days').val());
                        if (status === "success") $("#current_balance").val(data.balance);
                        if (total_leaves < number_of_days) {
                            var html = '';
                            for (var i = 0.5; i <= (number_of_days - total_leaves); i = i + 0.5) {
                                if (number_of_paid_days_from_authorizer == i) {
                                    html += '<option value="' + i + '" selected>' + i + '</option>';
                                } else {
                                    html += '<option value="' + i + '">' + i + '</option>';
                                }
                            }
                            if (number_of_paid_days_from_authorizer > 0) {
                                $("#paid_days").attr("checked", true);
                            }
                            $('#number_of_paid_days').html(html);
                            $('.paid_class').removeClass('d-none');
                        } else {
                            $("#paid_days").attr("checked", false);
                            $('.paid_class').addClass('d-none');
                            $('#number_of_paid_days').html('');
                        }
                        $("#leave_allocation_details_id").val(data.leave_allocation_details_id);
                    });
                }
            });


            @if(old('leave_type_id') )
            $("#leave_type_id").val("{{old('leave_type_id')}}").change()
            @elseif($requestedApplication && !$room)
            $("#leave_type_id").val("{{$requestedApplication->leave_type_id}}").change()
            @endif

            $('#half_day').trigger('change')
            //$("#leave_type_id").trigger('change')
            $('#paid_days').trigger('change')

        });

    </script>
@endsection
