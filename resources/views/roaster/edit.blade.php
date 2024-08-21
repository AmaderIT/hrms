@extends('layouts.app')
@section("top-css")
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" /> -->
    <style>
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
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Roaster</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('roaster.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form @if($data['formType']=='emp') action="{{ route('roaster.update', ['roaster' => $roaster->id]) }}" @else action="{{ route('roaster.updateDepartmentRoaster', ['roaster' => $roaster->department_roaster_id]) }}" @endif method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- <div class="col-md-8 offset-md-2"> -->
                            @if($data['formType']=='emp')
                                <div class="col-md-8 offset-md-2">
                                    <input type="hidden" name="type" value="emp">
                                    <input type="hidden" name="user_id[]" value="{{ $roaster->user_id }}">
                                    <input type="hidden" name="office_division_id[]" value="{{ $roaster->user->currentPromotion->office_division_id }}">
                                    <input type="hidden" name="department_id[]" value="{{ $roaster->user->currentPromotion->department_id }}">
                                    <input type="hidden" class="startAllowDateCount" value="{{$data['startAllowDateCount']}}">
                                    <input type="hidden" class="endAllowDateCount" value="{{$data['endAllowDateCount']}}">

                                    {{-- Employee --}}
                                    <div class="form-group">
                                        <label for="user_id">Employee</label>
                                        <select class="form-control" disabled>
                                            <option value="{{ $roaster->user_id }}" {{ $roaster->user->id == old("user_id") ? "selected" : "" }} selected>
                                                {{ $roaster->user->fingerprint_no . ' - ' . $roaster->user->name }}
                                            </option>
                                        </select>

                                        @error("user_id")
                                        <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                        @enderror
                                    </div>

                                    {{-- WorkSlot --}}
                                    <div class="form-group">
                                        <label for="work_slot_id">WorkSlot</label>
                                        <select class="form-control" name="work_slot_id[]" id="workSlotId">
                                            <option value="" disabled selected>Select an option</option>
                                            @foreach($data['workSlots'] as $workSlot)
                                                <option value="{{ $workSlot->id }}" {{ $roaster->work_slot_id == $workSlot->id ? "selected" : "" }}>
                                                    {{ $workSlot->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("work_slot_id")
                                        <p class="text-danger"> {{ $errors->first("work_slot_id") }} </p>
                                        @enderror
                                    </div>

                                    {{-- Active From --}}
                                    <div class="form-group">
                                        <label for="active_from">Active From</label>
                                        <input class="form-control datepicker" id="active_from" type="text" autocomplete="off" name="active_from[]" value="{{ date('d-m-Y', strtotime($roaster->active_from)) }}" placeholder="dd/mm/yyyy" style="width:100%; padding:10px;" required />
                                        

                                        @error("active_from")
                                        <p class="text-danger"> {{ $errors->first("active_from") }} </p>
                                        @enderror
                                    </div>

                                    {{-- End To --}}
                                    <div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input class="form-control datepicker" id="end_date" type="text" autocomplete="off" name="end_date[]" value="{{ date('d-m-Y', strtotime($roaster->end_date)) }}" placeholder="dd/mm/yyyy" style="width:100%; padding:10px;" required/>
                                        
                                        @error("end_date")
                                        <p class="text-danger"> {{ $errors->first("end_date") }} </p>
                                        @enderror
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Weekly Holiday</label>
                                        <div class="col-10 col-form-label">
                                            <div class="checkbox-list">
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="fri" {{ !is_null($roaster->weekly_holidays) && in_array("fri", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Friday</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="sat" {{ !is_null($roaster->weekly_holidays) && in_array("sat", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Saturday</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="sun" {{ !is_null($roaster->weekly_holidays) && in_array("sun", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Sunday</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="mon" {{ !is_null($roaster->weekly_holidays) && in_array("mon", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Monday</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="tue" {{ !is_null($roaster->weekly_holidays) && in_array("tue", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Tuesday</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="wed" {{ !is_null($roaster->weekly_holidays) && in_array("wed", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Wednesday</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" name="weekly_holidays[]" value="thu" {{ !is_null($roaster->weekly_holidays) && in_array("thu", $roaster->weekly_holidays) ? 'checked' : '' }}>
                                                    <span></span>Thursday</label>
                                            </div>
                                        </div>
                                        @error("days")
                                        <p class="text-danger"> {{ $errors->first("days") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            @else
                                @php
                                    $headers = ["WorkSlot", "Start Date", "End Date", "Weekly Holiday"];
                                @endphp

                                <input type="hidden" name="office_division_id" value="{{ $data['departmentRoaster']->office_division_id }}">
                                <input type="hidden" name="department_id" value="{{ $data['departmentRoaster']->department_id }}">

                                <div class="col-md-12">
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
                                                <tr>
                                                    <td>
                                                        <select class="form-control" name="work_slot_id" id="workSlotId">
                                                            <option value="" selected>Select an option</option>
                                                            @foreach($data['workSlots'] as $workSlot)
                                                                <option
                                                                    @if($data['departmentRoaster']->work_slot_id == $workSlot->id ) selected @endif value="{{ $workSlot->id }}"> {{ $workSlot->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error("work_slot_id")
                                                        <p class="text-danger"> {{ $errors->first("work_slot_id") }} </p>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input class="form-control" id="department_start_date_calender" type="text" autocomplete="off" placeholder="dd-mm-yyyy" name="active_from" value="{{ date('d-m-Y', strtotime($data['departmentRoaster']->active_from)) }}"/>
                                                        
                                                        @error("active_from")
                                                        <p class="text-danger"> {{ $errors->first("active_from") }} </p>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input class="form-control department_end_date_calender" id="department_end_date_calender" type="text" autocomplete="off" placeholder="dd-mm-yyyy" name="end_date" value="{{ date('d-m-Y', strtotime($data['departmentRoaster']->end_date)) }}"/>

                                                        @error("end_date")
                                                        <p class="text-danger"> {{ $errors->first("end_date") }} </p>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        @php 
                                                            $holidayData = json_decode($data['departmentRoaster']->weekly_holidays);
                                                        @endphp
                                                        
                                                        <select class="form-control weeklyHolidays" name="weekly_holidays[]" id="weeklyHolidays" placeholder="select weekly" multiple>
                                                            <option value="" disabled>Select Option</option>
                                                            @foreach($holidays as $key => $holiday)
                                                                <option value="{{ $key }}" {{ (collect($holidayData)->contains($key)) ? 'selected':'' }}>{{ $holiday }}</option>
                                                            @endforeach
                                                        </select>                                        
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        <!-- </div> -->
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('footer-js')
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            var startAllowDateCount = $('.startAllowDateCount').val();
            var endAllowDateCount = $('.endAllowDateCount').val();
            // $('.datepicker').datepicker({
            //     format: 'dd-mm-yyyy',
            //     startDate: '+'+startAllowDateCount+'d',
            //     endDate: (endAllowDateCount)?'+'+endAllowDateCount+'d':0
            // });

            $('.weeklyHolidays').select2({
                placeholder: "Please select holiday"
            });


            //ROASTER
            $("#active_from").datepicker({
                format: 'dd-mm-yyyy',
                startDate: '+'+startAllowDateCount+'d',
                autoclose: true,
                autoclose: true,
            }).on('changeDate', function(selected) {
                var minDate = new Date(selected.date.valueOf());
                let endDate = $('#end_date').datepicker('getDate');

                if (minDate>endDate) {
                    $('#end_date').val(null);
                }

                console.log(minDate);
                $('#end_date').datepicker('setStartDate', minDate);
            });

            $("#end_date").datepicker({
                format: 'dd-mm-yyyy',
                startDate: '+'+startAllowDateCount+'d',
                endDate: (endAllowDateCount)?'+'+endAllowDateCount+'d':0
            }).on('changeDate', function(selected) {
                let active_from = $('#active_from').datepicker('getDate');
                var minDate = new Date(selected.date.valueOf());
                if (minDate<active_from) {
                    $('#active_from').val(null);
                }
            });
            //END ROASTER

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
        })
        
    </script>
@endsection
