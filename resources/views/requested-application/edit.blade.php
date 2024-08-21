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
                        <input type="hidden" id="user_id" value="{{$requestedApplication->user_id}}">
                        <h3 class="card-title">Edit Requested Application @if($requestedApplication->is_reapply == 1)
                                <span class="text-danger">(Re-Applied)</span> @endif</h3>
                    @elseif($requestedApplication && $room && $room == 'employee')
                        <input type="hidden" id="user_id" value="{{auth()->user()->id}}">
                        <h3 class="card-title">Edit Leave Application @if($requestedApplication->is_reapply == 1)<span
                                class="text-danger">(Re-Applied)</span> @endif</h3>
                    @else
                        <input type="hidden" id="user_id" value="{{auth()->user()->id}}">
                        <h3 class="card-title">New Leave Application</h3>
                    @endif

                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a @if($requestedApplication && !$room) href="{{ route('requested-application.index') }}"
                               @else href="{{ route('apply-for-leave.index') }}" @endif class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>

                <form autocomplete="off"
                      @if($requestedApplication && !$room)
                      action="{{ route('requested-application.manipulate', ['requestedApplication' => $requestedApplication->uuid]) }}"
                      @elseif($requestedApplication && $room && $room == 'employee')
                      action="{{ route('apply-for-leave.update',['applyForLeave' =>  $requestedApplication->uuid]) }}"
                      @else
                      action="{{ route('apply-for-leave.store') }}"
                      @endif
                      method="POST">
                    @csrf
                    <input type="hidden" id="leave_application_id"
                           value="{{ $requestedApplication->id ?? 0}}"
                           id="leave_allocation_id" required/>
                    <input type="hidden" name="leave_allocation_details_id" value="" id="leave_allocation_details_id"
                           required/>
                    <input type="hidden" name="u_id" value="{{$requestedApplication->user_id ?? auth()->user()->id}}"/>

                    <div class="card-body" style="padding-top: 10px; padding-bottom:10px;">
                        <div class="row" style="margin-top:20px;">
                            @include('requested-application.leave-application-form')
                            @include('requested-application.leave-application-partial-employee-details')
                        </div>
                    </div>
                    <div class="card-footer" id="applyDiv">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">

                                <button disabled type="submit" class="btn btn-primary apply">Submit</button>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section("footer-js")
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript">

        let user_id = $('#user_id').val();

        $('#from_date').datepicker({
            minDate: 0,
            startDate: '-40y',
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#to_date').datepicker({
            minDate: 0,
            startDate: '-40y',
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $(document).ready(function () {

            $('.leave-request-type').change(function (e) {

                $('#half-day-slot-portion').addClass('d-none');
                $('#first_half_slots').removeAttr('required');
                $('#second_half_slots').removeAttr('required');

                var $dates = $('#form_date, #to_date').datepicker();
                $dates.datepicker('setDate', '');
                $('.half-day-slots').removeAttr('required');
                $('.half-day-slots').val('');
                $('.half-day-slots').prop('checked', false);
                let checkedVal = $("input[name='leave_request_type']:checked").val();
                if (checkedVal === 'half_day') {
                    $('#half-day-slot-portion').removeClass('d-none');
                    $("#number_of_days").val(0.5);
                    $("#to_date").val("");
                    $("#to_date").prop("readonly", true);
                    $("#number_of_days").prop("readonly", true);
                    $('#first_half_slots').prop('required', true);
                    $('#second_half_slots').prop('required', true);
                    $('#first_half_slots').val('1');
                    $('#second_half_slots').val('2');
                    let fromDate = $("#from_date").val();
                    if (fromDate == '') {
                        $('#half-day-slot-portion').addClass('d-none');
                        //toastr.error("At First Choose From Date!!!");
                        //document.getElementById('from_date').focus();
                        $('#half_slots_text_1').text('N/A');
                        $('#half_slots_text_2').text('N/A');
                        return false;
                    }
                    getHalfDaySlot();
                } else {
                    $("#applyDiv").show();
                    $('#half-day-slot-portion').addClass('d-none');
                    $("#number_of_days").val(1);
                    $("#number_of_days").prop("readonly", true);
                    $('#first_half_slots').removeAttr('required');
                    $('#second_half_slots').removeAttr('required');
                    $('#first_half_slots').val('');
                    $('#second_half_slots').val('');
                }
            });


            $("#from_date, #to_date").change(function (e) {
                let fromDate = $("#from_date").val();
                let toDate = $("#to_date").val();
                let checkedVal = $("input[name='leave_request_type']:checked").val();
                if (checkedVal === 'half_day') {
                    if (toDate == '') {
                        $("#applyDiv").hide();
                    }

                }
                if (fromDate != "" && toDate != "") {
                    fromDate = new Date(fromDate);
                    toDate = new Date(toDate);
                    if (fromDate <= toDate) {
                        let millisBetween = fromDate.getTime() - toDate.getTime();
                        let days = millisBetween / (1000 * 3600 * 24);
                        let numberOfDays = Math.round(Math.abs(days)) + 1;
                        if (numberOfDays == 1 && checkedVal === 'half_day') {
                            $("#number_of_days").val(0.5);
                            $('#half-day-slot-portion').removeClass('d-none');
                            $('#leave_request_half_day').prop('checked', true);
                            $('#leave_request_full_day').prop('checked', false);
                            $('#first_half_slots').prop('checked', false);
                            $('#second_half_slots').prop('checked', false);
                        } else {
                            $("#number_of_days").val(numberOfDays);
                            $('#half-day-slot-portion').addClass('d-none');
                            $('#leave_request_half_day').prop('checked', false);
                            $('#leave_request_full_day').prop('checked', true);
                            $('#first_half_slots').removeAttr('required');
                            $('#second_half_slots').removeAttr('required');
                            $('#first_half_slots').hide();
                            $('#second_half_slots').hide();
                        }
                        $("#number_of_days").prop("readonly", true);
                        $("#applyDiv").show();
                    } else {
                        $("#applyDiv").hide();
                        $('#half-day-slot-portion').addClass('d-none');
                        $('#first_half_slots').removeAttr('required');
                        $('#second_half_slots').removeAttr('required');
                    }
                }
                if (fromDate != '' && toDate != '' && fromDate > toDate) {
                    let startDate = $('#from_date').datepicker();
                    startDate.datepicker('setDate', '');
                    toastr.error("From date is not greater than to date!!!");
                    $('#half-day-slot-portion').addClass('d-none');
                    $('#first_half_slots').removeAttr('required');
                    $('#second_half_slots').removeAttr('required')
                }
                getHalfDaySlot();
            });


            $('.half-day-slots').change(function (e) {
                let checkedValHalfDaySlots = $("input[name='half_day_slot']:checked").val();
                let firstSlotStartDate = $('.applied_date_3').attr('data-applied_start_date');
                let firstSlotEndDate = $('.applied_date_3').attr('data-applied_end_date');
                let secondSlotStartDate = $('.applied_date_4').attr('data-applied_start_date');
                let secondSlotEndDate = $('.applied_date_4').attr('data-applied_end_date');
                if (checkedValHalfDaySlots == '1') {
                    $('#from_date').val(firstSlotStartDate);
                    $('#to_date').val(firstSlotEndDate);
                } else if (checkedValHalfDaySlots == '2') {
                    $('#from_date').val(secondSlotStartDate);
                    $('#to_date').val(secondSlotEndDate);
                }
                $("#applyDiv").show();
            });
        })


        $('#status').on('change', function () {
            $('#remarks').val('')
            $('.remarks-div').hide()
            if (this.value == 2) {
                $('.remarks-div').show()
            }
        })

        $('.leave-request-type').change()
        getHalfDaySlot()


        /*
        Get half day according to leave from date.
        First checking roster work slot then checking default work slot of an employee.
       */
        function getHalfDaySlot() {
            $.ajax({
                url: '{{ route('apply-for-leave.getSlotWiseTimeRange') }}',
                type: 'POST',
                data: {
                    user_id: user_id,
                    leave_request_type: $("input[name='leave_request_type']:checked").val(),
                    from_date: $('#from_date').val(),
                    leave_application_id: $('#leave_application_id').val()
                },
                success: function (response) {
                    if (response.status === true) {
                        $('#half-day-slot-portion').removeClass('d-none');
                        jQuery.each(response.timeSlots, function (i, val) {
                            $('#half_slots_text_' + i).text(val);
                        });
                        $('.applied_date_3').attr('data-applied_start_date', response.timeSlots[3]);
                        $('.applied_date_3').attr('data-applied_end_date', response.timeSlots[4]);
                        $('.applied_date_4').attr('data-applied_start_date', response.timeSlots[5]);
                        $('.applied_date_4').attr('data-applied_end_date', response.timeSlots[6]);
                    } else {
                        $('#half-day-slot-portion').addClass('d-none');
                        $('#half_slots_text_1').text('N/A');
                        $('#half_slots_text_2').text('N/A');
                    }
                },
                error: function (xhr, desc, err) {
                    console.log("error");
                }
            });
        }

        /*
         Checking leave balance and get balance with leave type by putting leave request from and to date
        */

        function dateRangeChecker() {
            $('#leave-balance-tbl').empty()
            $('#custom-alert').empty()
            $('#custom-alert').hide()
            $.ajax({
                url: '{{route("apply-for-leave.date-range-checker")}}',
                type: 'GET',
                data: {
                    user_id: user_id,
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    leave_request_type: $("input[name='leave_request_type']:checked").val(),
                    leave_application_id: $('#leave_application_id').val()
                },
                success: function (res) {

                    if (res.message != '') {
                        $('#custom-alert').show()
                        $('#custom-alert').html(res.message)
                    }


                    let row = '';
                    if (res.data.length > 0) {
                        let old_leave_type_id = '{{ old("leave_type_id") ?? 0}}'
                        let no_of_disabled = 0;
                        $.each(res.data, function (x, y) {

                            old_leave_checked = '';
                            if (old_leave_type_id == y.leave_type_id) {
                                old_leave_checked = 'checked'
                            }
                            if (y.enable_status == 'disabled') {
                                no_of_disabled++;
                            }
                            row += '<tr>';
                            row += `<td>
                                    <div class="radio-inline">
                                        <label class="radio radio-default">
                                            <input class="leave_type_id" ` + old_leave_checked + ` ` + y.checked_status + ` ` + y.enable_status + ` id="leave_type_` + y.leave_type_id + `" type="radio" name="leave_type_id" value="` + y.leave_type_id + `" required="">
                                            <span></span>` + y.name +
                                `</label>
                                    </div>
                                </td>`;
                            row += `<td>` + y.entitled + `</td>`;
                            row += `<td>` + y.consumed + `</td>`;
                            row += `<td>` + y.lock + `</td>`;
                            row += `<td>` + y.usable + `</td>`;
                            row += '</tr>';
                        })
                        $('#leave-balance-tbl').html(row)
                        $('.apply').attr('disabled', false)

                        if (no_of_disabled == res.data.length) {
                            $('.apply').attr('disabled', true)
                        }
                        $('#leave_allocation_details_id').val(res.leave_allocation_details_id);

                        $(".leave_type_id").on('change', function () {
                            $('#custom-alert').empty()
                            $('#custom-alert').hide()
                        })
                    }
                },
                error: function (err) {
                    console.log(err)
                }
            })
        }

        $('#from_date,#to_date').on('change', function () {
            dateRangeChecker();
        })
        dateRangeChecker()
        getEmployeeLeaveGraphData()


    </script>
@endsection


