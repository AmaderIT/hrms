@extends('layouts.app')

@section('top-css')
    <style type="text/css">
        .requested-leave-summary-card{
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
        .requested-leave-summary-card-font-weight{
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Authorize|Approve Online Attendance</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('attendance.requested_online_attendances.index') }}" title="Requested Online Attendance" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form
                    action="{{ route('attendance.requested_online_attendances.approve', ['onlineAttendance' => $onlineAttendance->uuid]) }}"
                    method="POST">
                    @csrf
                    <div class="card-body" style="padding-top: 10px; padding-bottom:10px;">
                        <div class="row" style="margin-top:20px;">
                            <!-- START LEAVE APPLICATION FORM -->
                            <div class="col-lg-7 col-md-7 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-6 div-md-6">
                                        {{-- In Time --}}
                                        <div class="form-group">
                                            <b>Entry Date &amp; Time</b>
                                            <div class="input-group date" id="time_in" data-target-input="nearest">
                                                <input type="text" id="time_in" name="time_in" value="{{ date('d-m-Y h:i:s A', strtotime($onlineAttendance->time_in)) }}" class="form-control datetimepicker-input" autocomplete="off" placeholder="Select entry date &amp; time" data-target="#kt_datetimepicker_6" required>
                                                <div class="input-group-append" data-target="#time_in" data-toggle="datetimepicker">
                                                    <span class="input-group-text">
                                                        <i class="ki ki-calendar"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            @error('time_in')
                                            <p class="text-danger"> {{ $errors->first("time_in") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 div-md-6">
                                        {{-- Out Time --}}
                                        <div class="form-group">
                                            <b>Exit Date &amp; Time</b>
                                            <div class="input-group date" id="time_out" data-target-input="nearest">
                                                <input type="text" id="time_out" name="time_out" value="{{ !empty($onlineAttendance->time_out)?date('d-m-Y h:i:s A', strtotime($onlineAttendance->time_out)):null }}" class="form-control datetimepicker-input" autocomplete="off" placeholder="Select exit date &amp; time" data-target="#kt_datetimepicker_6" required>
                                                <div class="input-group-append" data-target="#time_out" data-toggle="datetimepicker">
                                        <span class="input-group-text">
                                            <i class="ki ki-calendar"></i>
                                        </span>
                                                </div>
                                            </div>
                                            @error('time_out')
                                            <p class="text-danger"> {{ $errors->first("time_out") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 div-md-6">
                                        {{-- Status --}}
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                @if(auth()->user()->can("Online Attendance Authorized") AND $onlineAttendance->status === \App\Models\OnlineAttendance::PENDING)
                                                    <option
                                                        value="{{ \App\Models\OnlineAttendance::AUTHORIZED }}" {{ $onlineAttendance->status === \App\Models\OnlineAttendance::AUTHORIZED ? "selected" : "" }}>
                                                        Authorize
                                                    </option>
                                                @elseif(auth()->user()->can("Online Attendance Approved") AND $onlineAttendance->status === \App\Models\OnlineAttendance::AUTHORIZED)
                                                    <option
                                                        value="{{ \App\Models\OnlineAttendance::APPROVED }}" {{ $onlineAttendance->status === \App\Models\OnlineAttendance::APPROVED ? "selected" : "" }}>
                                                        Approve
                                                    </option>
                                                @endif
                                                <option
                                                    value="{{ \App\Models\OnlineAttendance::REJECTED }}" {{ $onlineAttendance->status === \App\Models\OnlineAttendance::REJECTED ? "selected" : "" }}>
                                                    Cancel
                                                </option>
                                            </select>

                                            @error("status")
                                            <p class="text-danger"> {{ $errors->first("status") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END LEAVE APPLICATION FORM -->

                            <div class="col-lg-5 col-md-5 col-md-12 requested-leave-summary-card">
                                <h5 class="text-center" style="text-decoration: underline;">Employee Information</h5>
                                <table style="width: 100%;">
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Employee ID</td>
                                        <td width="70%">: {{ !empty($getEmployeeInfos->fingerprint_no)?$getEmployeeInfos->fingerprint_no:"" }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Name</td>
                                        <td width="70%">: {{ !empty($getEmployeeInfos->name)?$getEmployeeInfos->name:"" }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Joining Date</td>
                                        <td width="70%">: {{ \Carbon\Carbon::createFromDate($getEmployeeInfos->employeeStatus->where("action_reason_id", 2)->first()->action_date->toDateTimeString())->format("M d, Y") }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Division</td>
                                        <td width="70%">: {{ !empty($getEmployeeInfos->currentPromotion->officeDivision->name)?$getEmployeeInfos->currentPromotion->officeDivision->name:"" }}</td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="requested-leave-summary-card-font-weight">Department</td>
                                        <td width="70%">: {{ !empty($getEmployeeInfos->currentPromotion->department->name)?$getEmployeeInfos->currentPromotion->department->name:"" }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" id="applyDiv">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">
                                @if(auth()->user()->can("Authorize Leave Requests") OR auth()->user()->can("Approve Leave Requests"))
                                    <button type="submit" class="btn btn-primary apply">Submit</button>
                                @else
                                    <a href="{{ route('requested-application.index') }}"
                                       class="btn btn-primary">Back</a>
                                @endif
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
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        $('#time_in, #time_out').datetimepicker({
            format: "DD-MM-yy hh:mm:ss A"
        });
    </script>
@endsection
