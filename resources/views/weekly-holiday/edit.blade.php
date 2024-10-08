@extends('layouts.app')
@section("top-css")
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
                    <h3 class="card-title">Edit Weekly Holiday</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('weekly-holiday.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('weekly-holiday.update', ['weeklyHoliday' => $weeklyHoliday->id]) }}" method="POST">
                    @csrf

                    @php
                        $days = (array) $weeklyHoliday->days;
                        $selected = in_array("mon", json_decode($days[0], true));
                    @endphp

                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control" name="department_id[]">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}"{{ $department->id === $weeklyHoliday->department_id ? "selected" : "" }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("department_id")
                                <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="days">Days</label>
                                <select class="form-control" name="days[]" multiple="multiple">
                                    <option value="fri" {{ in_array("fri", json_decode($days[0], true)) === true ? "selected" : "" }}>Friday</option>
                                    <option value="sat" {{ in_array("sat", json_decode($days[0], true)) === true ? "selected" : "" }}>Saturday</option>
                                    <option value="sun" {{ in_array("sun", json_decode($days[0], true)) === true ? "selected" : "" }}>Sunday</option>
                                    <option value="mon" {{ in_array("mon", json_decode($days[0], true)) === true ? "selected" : "" }}>Monday</option>
                                    <option value="tue" {{ in_array("tue", json_decode($days[0], true)) === true ? "selected" : "" }}>Tuesday</option>
                                    <option value="wed" {{ in_array("wed", json_decode($days[0], true)) === true ? "selected" : "" }}>Wednesday</option>
                                    <option value="thu" {{ in_array("thu", json_decode($days[0], true)) === true ? "selected" : "" }}>Thursday</option>
                                </select>
                                @error("days")
                                <p class="text-danger"> {{ $errors->first("days") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="effective_date">Effective Date <span style="color: red">*</span></label>
                                <input type="date" class="form-control" name="effective_date" id="effective_date" value="{{ date('Y-m-d', strtotime($weeklyHoliday->effective_date)) }}" required>
                                @error("effective_date")
                                <p class="text-danger"> {{ $errors->first("effective_date") }} </p>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Update</button>
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
