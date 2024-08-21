@extends('layouts.app')

@section("top-css")
    <link rel="stylesheet" href="{{ asset('https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css') }}">
    <style>
        body {
            font-family: Poppins, Helvetica, "sans-serif" !important;
            background: #EFF1F5 !important;
            color: #3F4254 !important;
        }

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

        .glyphicon {
            top: 0px !important;
        }

        .time-picker{
            border-radius: 5px 0px 0px 5px !important;
        }

        .glyphicon-time {
            border-radius: 0px 5px 5px 0px !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Apply for Leave</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('apply-for-leave.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('apply-for-leave.store') }}" method="POST" id="myForm">
                    @csrf
                    <input type="hidden" name="half_day_count" value="{{$data['half_day_count']}}">
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="leave_type_id">Leave Type</label>
                                <select class="form-control" id="leave_type_id" name="leave_type_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["leaveTypes"] as $key => $leaveType)
                                        <option value="{{ $leaveType->id }}" @if( old('leave_type_id') == $leaveType->id ) selected @endif>{{ $leaveType->name }}</option>
                                    @endforeach
                                </select>

                                @error("leave_type_id")
                                <p class="text-danger"> {{ $errors->first("leave_type_id") }} </p>
                                @enderror
                            </div>

                            <input type="hidden" name="leave_allocation_details_id"value="{{old('leave_allocation_details_id')}}" id="leave_allocation_details_id" required/>

                            <div class="form-group">
                                <label for="current_balance">Current Balance</label>
                                <input type="number" class="form-control" name="current_balance" id="current_balance" value="{{old('current_balance')}}" readonly placeholder="Current Balance"/>

                                @error("current_balance")
                                <p class="text-danger"> {{ $errors->first("current_balance") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" @if(isset($data['fromDate'])) value="{{ $data['fromDate']->leave_date }}" @else value="{{old('from_date')}}" @endif required/>

                                @error("from_date")
                                <p class="text-danger"> {{ $errors->first("from_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control"  name="to_date" id="to_date" @if(isset($data['fromDate'])) value="{{ $data['fromDate']->leave_date }}" @else value="{{old('to_date')}}" @endif required/>


                                @error("to_date")
                                <p class="text-danger"> {{ $errors->first("to_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="number_of_days">Number of day</label>
                                <input type="text" class="form-control" name="number_of_days" id="number_of_days" @if(isset($data['fromDate'])) @if($data['fromDate']->is_half_day==1) value="{{ '0.5' }}" @else value="{{ '1' }}" @endif @else value="{{old('number_of_days')}}" @endif  readonly placeholder="Number of day"/>

                                @error("number_of_days")
                                <p class="text-danger"> {{ $errors->first("number_of_days") }} </p>
                                @enderror
                            </div>

                            <div class="form-group row @if(empty($data['fromDate'])) d-none  @endif half_day_class">
                                <div class="col-9 col-form-label">
                                    <div class="checkbox-inline">
                                        <label class="checkbox">
                                            <input type="checkbox" value="true" name="half_day" id="half_day" @if(isset($data['fromDate'])) @if($data['fromDate']->is_half_day==1) checked @endif @else (old('half_day')==1) checked @endif>
                                            <span></span>Half Day
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group leave_start_time_class @if(empty($data['fromDate'])) d-none @else @if($data['fromDate']->is_half_day==0) d-none @endif  @endif">
                                <label for="started_at">Leave Start Time</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control time-picker" name="leave_start_time" id="leave_start_time" placeholder="--:--" @if(!empty($data['fromDate'])) value="{{ $data['lateCountTime'] }}" @else value="{{old('leave_start_time')}}" @endif aria-describedby="basic-addon2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="leave_start_time"><i class="glyphicon glyphicon-time" id="leave_start_time_icon"></i></span>
                                    </div>
                                </div>

                                @error('leave_start_time')
                                <p class="text-danger"> {{ $errors->first("leave_start_time") }} </p>
                                @enderror
                            </div>

                            <div class="form-group  leave_end_time_class @if(empty($data['fromDate'])) d-none @else @if($data['fromDate']->is_half_day==0) d-none @endif  @endif">
                                <label for="ended_at">Leave End Time</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control time-picker" name="leave_end_time" id="leave_end_time" placeholder="--:--" @if(!empty($data['fromDate'])) @if($data['fromDate']->is_half_day==1) value="{{ $data['punch_time'] }}" @endif @else value="{{old('leave_end_time')}}" @endif  aria-describedby="basic-addon2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="leave_start_time"><i class="glyphicon glyphicon-time" id="leave_end_time_icon"></i></span>
                                    </div>
                                </div>
                                @error('leave_end_time')
                                <p class="text-danger"> {{ $errors->first("leave_end_time") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="purpose">Purpose</label>
                                <textarea class="form-control" name="purpose" rows="6" id="purpose" placeholder="Purpose" required>{{old('purpose')}}</textarea>
                                @error("purpose")
                                <p class="text-danger"> {{ $errors->first("purpose") }} </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer" id="applyDiv">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2" id="apply">Send Application</button>
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
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //END FOR TIME PICKER
            let _currentBalance = 0;
            let _noOfDay = 0;
            let half_day_count = {!! $data['half_day_count'] !!};

            //START FOR TIME PICKER
            $('#leave_start_time').datetimepicker({
                format: 'hh:mm A'
            });
            $('#leave_end_time').datetimepicker({
                format: 'hh:mm A'
            });
            $('#leave_start_time_icon').click(function() {
                $("#leave_start_time").focus();
            });
            $('#leave_end_time_icon').click(function() {
                $("#leave_end_time").focus();
            });



            // Calculation "number of days" according to the difference from the "from_date" to the "to_date"
            $("#from_date, #to_date").change(function (e) {
                $("#half_day").prop( "checked", false );
                var _from_date = $("#from_date").val();
                var _to_date = $("#to_date").val();
                if(_from_date != "" && _to_date != "") {
                    _from_date = new Date(_from_date);
                    _to_date = new Date(_to_date);
                    if(_from_date <= _to_date) {
                        $("#applyDiv").show();
                        var millisBetween = _from_date.getTime() - _to_date.getTime();
                        var days = millisBetween / (1000 * 3600 * 24);
                        var _number_of_days = Math.round(Math.abs(days)) + 1;
                        if(_number_of_days==1){
                            $('.half_day_class').removeClass('d-none');
                        }else{
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
                    } else {
                        $("#applyDiv").hide();
                        $('.half_day_class').addClass('d-none');
                        $('.leave_start_time_class').addClass('d-none');
                        $('.leave_end_time_class').addClass('d-none');
                        $('#leave_start_time').removeAttr('required');
                        $('#leave_end_time').removeAttr('required');
                        $('#leave_start_time').val('');
                        $('#leave_end_time').val('');
                    }
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
                    $("#to_date").prop("readonly", true);
                    $("#number_of_days").prop("readonly", true);
                    _noOfDay = 0.5;
                    $('.leave_start_time_class').removeClass('d-none');
                    $('.leave_end_time_class').removeClass('d-none');
                    $("#leave_start_time").prop('required',true);
                    $("#leave_end_time").prop('required',true);
                    //checkForApply();
                } else {
                    $("#number_of_days").val(1);
                    $('.leave_start_time_class').addClass('d-none');
                    $('.leave_end_time_class').addClass('d-none');
                    $('#leave_start_time').removeAttr('required');
                    $('#leave_end_time').removeAttr('required');
                    $('#leave_start_time').val('');
                    $('#leave_end_time').val('');
                    $("#to_date").prop("readonly", false);
                    $("#number_of_days").prop("readonly", true);
                }
            });
            // Current Balance
            $("[name='leave_type_id']").on("change", function() {
                let _leaveType = $("#leave_type_id").val();
                if (_leaveType != null) {
                    let url = "{{ route('apply-for-leave.balance', ['leaveType' => ':leaveType']) }}";
                    url = url.replace(":leaveType", _leaveType);
                    $.get(url, function (data, status) {
                        if (status === "success") {
                            $("#current_balance").val(data.balance);
                            _currentBalance = data.balance;
                            $("#leave_allocation_details_id").val(data.leave_allocation_details_id);
                            //checkForApply();
                        }
                    });
                }
            });
            /**
             * Check whether employee can apply for leave or not
             */
            function checkForApply() {
                if(_noOfDay > _currentBalance) $("#applyDiv").hide();
                else if(_currentBalance > 0) $("#applyDiv").show();
            }
        });
    </script>
@endsection
