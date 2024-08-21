
@extends('layouts.app')

@section("top-css")
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
    <style>
        #attendanceReportView {
            display: flex;
            /*font-family: -apple-system;*/
            /*font-size: 14px;*/
            color: #333;
            justify-content: center;
        }

        .table-wrapper {
            max-width: 1220px;
            overflow: scroll;
        }

        table {
            text-align: center;
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        td, th {
            white-space: nowrap;
            border: 1px solid #ddd;
            padding: 5px;
        }

        th {
            /*background-color: #eee;*/
            position: sticky;
            top: -1px;
            z-index: 2;

        &:first-of-type {
             left: 0;
             z-index: 3;
         }
        }

        tbody tr td:first-of-type, td:nth-of-type(2) {
            /*background-color: #eee;*/
            position: sticky;
            left: -1px;
            z-index: 1;
        }
    </style>
{{--    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />--}}
@endsection

@php
    $holidays = [
        "fri"   => "Friday",
        "sat"   => "Saturday",
        "sun"   => "Sunday",
        "mon"   => "Monday",
        "tue"   => "Tuesday",
        "wed"   => "Wednesday",
        "thu"   => "Thursday"
    ];
@endphp

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-header">
            <h3 class="card-title">Create Roaster</h3>
            <div class="card-toolbar">
                <div class="example-tools justify-content-center">
                    <a href="{{ route('roaster.create') }}" class="btn btn-primary mr-2">Back</a>
                </div>
            </div>
        </div>
        <form action="{{ route('roaster.store') }}" method="POST">
        @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <h2>{{ $data["department"]->name }}
                            <div class="float-left"></div>
                        </h2>
                    </div>
                </div>

                <!-- <input type="text" class="currentDate" value="{{date('d-m-Y')}}"> -->

                @if($data['param']['type']=='emp')
                    @php
                        $headers = ["Employee Name", "WorkSlot", "Start Date", "End Date", "Weekly Holiday"];
                    @endphp
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                @foreach($headers as $key => $header)
                                    <th class="text-center" width="16%">{{ $header }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data["users"] as $empKey=>$employee)
                                <input type="hidden" name="type" value="emp"/>
                                <input type="hidden" name="user_id[]" value="{{ $employee->id }}"/>
                                <input type="hidden" name="office_division_id[]" value="{{ $employee->office_division_id }}"/>
                                <input type="hidden" name="department_id[]" value="{{ $employee->department_id }}"/>

                                <tr>
                                    <td width="20%">{{ $employee->fingerprint_no . ' - ' . $employee->name }}</td>
                                    
                                    <td width="30%">
                                        <select class="form-control" name="work_slot_id[]" id="workSlotId">
                                            <option value="" selected>Select an option</option>
                                            @foreach($data['workSlots'] as $workSlot)
                                                <option @if( old('work_slot_id.'.$empKey) == $workSlot->id ) selected @endif value="{{ $workSlot->id }}">{{ $workSlot->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td width="15%">
                                        <div class="input-group mb-3">
                                            <input class="form-control datepicker start_date_calender start_date_calender{{$employee->id}}" autocomplete="off" data="{{$employee->id}}" type="text" name="active_from[]" value="{{old('active_from.'.$empKey)}}" placeholder="mm/dd/yyyy"/>
                                           
                                        </div>                                        
                                    </td>

                                    <td width="15%">
                                        <div class="input-group mb-3">
                                            <input class="form-control datepicker end_date_calender end_date_calender{{$employee->id}}"" type="text" autocomplete="off" data="{{$employee->id}}" name="end_date[]" value="{{old('end_date.'.$empKey)}}" placeholder="mm/dd/yyyy"/>
                                        </div>                                        
                                    </td>

                                    <td width="20%">
                                        <select class="form-control weeklyHolidays" name="weekly_holidays[{{$empKey}}][]" multiple>
                                            <option value="" disabled>Select an option</option>
                                            @foreach($holidays as $key => $holiday)
                                                <option @foreach((array) old('weekly_holidays.'.$empKey) as $k => $w) fuck{{$k}}  @if( $w == $key ) selected @endif @endforeach value="{{ $key }}">{{ $holiday }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    @php
                        $headers = ["WorkSlot", "Start Date", "End Date", "Weekly Holiday"];
                    @endphp
                    <div class="table-wrapper">
                        <table>
                            <thead>
                            <tr>
                                @foreach($headers as $key => $header)
                                    <th class="text-center" width="25%">{{ $header }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                                <input type="hidden" name="type" value="dept"/>
                                <input type="hidden" name="office_division_id" value="{{ $data["department"]->office_division_id }}"/>
                                <input type="hidden" name="department_id" value="{{ $data["department"]->id }}"/>
                                <tr>
                                    <td>
                                        <select class="form-control" name="work_slot_id" id="workSlotId">
                                            <option value="" selected>Select an option</option>
                                            @foreach($data['workSlots'] as $workSlot)

                                                <option
                                                    @if(old('work_slot_id') > 0 && $workSlot->id == old('work_slot_id') )
                                                    selected
                                                    @endif
                                                    value="{{ $workSlot->id }}">
                                                    {{ $workSlot->title }}
                                                </option>

                                                
                                            @endforeach
                                        </select>
                                        @error("work_slot_id")
                                        <p class="text-danger"> {{ $errors->first("work_slot_id") }} </p>
                                        @enderror
                                    </td>
                                    <td>
                                        <input class="form-control" id="department_start_date_calender" type="text" autocomplete="off" placeholder="dd-mm-yyyy" name="active_from" value="{{ old('active_from') }}"/>
                                        
                                        @error("active_from")
                                        <p class="text-danger"> {{ $errors->first("active_from") }} </p>
                                        @enderror
                                    </td>
                                    <td>
                                        <input class="form-control department_end_date_calender" id="department_end_date_calender" type="text" autocomplete="off" placeholder="dd-mm-yyyy" name="end_date" value="{{ old('end_date') }}"/>

                                        @error("end_date")
                                        <p class="text-danger"> {{ $errors->first("end_date") }} </p>
                                        @enderror
                                    </td>
                                    <td>
                                        <select class="form-control weeklyHolidays" name="weekly_holidays[]" id="weeklyHolidays" placeholder="select weekly" multiple>
                                            <option value="" disabled>Select Option</option>
                                            @foreach($holidays as $key => $holiday)
                                                <option value="{{ $key }}" {{ (collect(old('weekly_holidays'))->contains($key)) ? 'selected':'' }}>{{ $holiday }}</option>
                                            @endforeach
                                        </select>                                        
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-lg-12 text-lg-right">
                        <button type="submit" class="btn btn-primary w-100px mr-2">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        $( ".weeklyHolidays" ).select2({
            width: '100%'
        });

        $(document).ready(function(){
            $('.weeklyHolidays').select2({
                placeholder: "Please select holiday"
            });
            
            $('.currentDate').datepicker({
                format: 'dd-mm-yyyy',
                startDate: '+1d',
            });

            

            let empUpdateStartDate = null;
            $('.start_date_calender').on('click', function(){
                var userId = $(this).attr('data');
                $.ajax({
                    url: '{{route("roaster.newRoasterDateCheck")}}',
                    type: 'GET',
                    data: {userId: userId},
                    success: function (res) {                        
                        $('.start_date_calender'+userId).datepicker({
                            format: 'dd-mm-yyyy',
                            startDate: '+'+res.startAllowDateCount+'d',
                            endDate: (res.endAllowDateCount)?'+'+res.endAllowDateCount+'d':0
                        }).on('changeDate', function(selected) {
                            var minDate = new Date(selected.date.valueOf());
                            let endDate =$('.end_date_calender'+userId).datepicker('getDate');

                            if (minDate>endDate) {
                                $('.end_date_calender'+userId).val(null);
                            }

                            $('.end_date_calender'+userId).datepicker('setStartDate', minDate);
                        });
                        $('.start_date_calender'+userId).datepicker('show');
                    },
                    error: function (res) {
                        toastr.success('Something went wrong!!')
                    }
                });
            });


            //EMPLOYEE WISE ROASTER
            $(".start_date_calender").on('change', function(){
                var userId = $(this).attr('data');             
                var currentDate = $('.currentDate').datepicker('getDate');
                var startDate  = empUpdateStartDate = $(this).datepicker('getDate'); 
                var days   = ((startDate-currentDate)/1000/60/60/24)+1;

                $('.end_date_calender'+userId).datepicker({
                    format: 'dd-mm-yyyy',
                    startDate: '+'+days+'d',
                });

                let endDate = $('.end_date_calender'+userId).datepicker('getDate');
                if (startDate>endDate) {
                    $('.end_date_calender'+userId).val(null);
                }                
            });


            $("#end_date_calender").datepicker({
                format: 'dd-mm-yyyy',
                startDate: '+1d',
            }).on('changeDate', function(selected) {
                var minDate = new Date(selected.date.valueOf());
            });
            //END EMPLOYEE WISE ROASTER


            //DEPARTMENT WISE ROASTER
            $("#department_start_date_calender").datepicker({
                format: 'dd-mm-yyyy',
                startDate: '+1d',
                autoclose: true,
                autoclose: true,
            }).on('changeDate', function(selected) {
                var minDate = new Date(selected.date.valueOf());
                let endDate = $('#department_end_date_calender').datepicker('getDate');

                if (minDate>endDate) {
                    $('#department_end_date_calender').val(null);
                }

                console.log(minDate);
                $('#department_end_date_calender').datepicker('setStartDate', minDate);
            });

            $("#department_end_date_calender").datepicker({
                format: 'dd-mm-yyyy',
                startDate: '+1d',
            }).on('changeDate', function(selected) {
                var minDate = new Date(selected.date.valueOf());
            });
            //END DEPARTMENT WISE ROASTER
        });
    </script>
@endsection
