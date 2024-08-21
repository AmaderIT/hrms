@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data" class="form">
            @csrf
            <div class="mt-n0">
                <div class="card card-custom card-stretch gutter-b">
                    <div class="card-header">
                        <h3 class="card-title">Application Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 offset-lg-4">
                                <div class="form-group row">
                                    {{-- Fingerprint ID --}}
                                    <div class="col-lg-12">
                                        <label for="per_page">Per Page <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ $data['per_page'] }}" name="per_page" class="form-control" placeholder="25" required/>
                                        <span class="form-text text-muted">How many data to show in a page?</span>

                                        @error("fingerprint_no")
                                        <p class="text-danger"> {{ $errors->first("fingerprint_no") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{-- Fingerprint ID --}}
                                    <div class="col-lg-12">
                                        <label for="attendance_count_start_time">Attendance Count Start Time <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ $data['attendance_count_start_time'] }}" name="attendance_count_start_time"
                                               class="form-control" placeholder="6am" required/>
                                        <span class="form-text text-muted">When to start the day (Time for attendance)</span>

                                        @error("attendance_count_start_time")
                                        <p class="text-danger"> {{ $errors->first("attendance_count_start_time") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{-- Fingerprint ID --}}
                                    <div class="col-lg-12">
                                        <label for="meal_request_end_time">Meal Request End Time <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ $data['meal_request_end_time'] }}" name="meal_request_end_time"
                                               class="form-control" placeholder="10am" required/>
                                        <span class="form-text text-muted">When to end the day (Time for meal request) </br> (Format:- HH:MM:SS 24HRS)</span>

                                        @error("meal_request_end_time")
                                        <p class="text-danger"> {{ $errors->first("meal_request_end_time") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-12">
                                        <label for="attendance_count_start_time">Short Day Leave Count (hour) <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ $data['short_day_leave_count_in_hr'] ?? 0 }}" name="short_day_leave_count_in_hr"
                                               class="form-control" placeholder="2" required/>

                                        @error("short_day_leave_count_in_hr")
                                        <p class="text-danger"> {{ $errors->first("short_day_leave_count_in_hr") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-12">
                                        <label for="attendance_count_start_time">Half Day Leave Count (hour) <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ $data['half_day_leave_count_in_hr'] ?? 0 }}" name="half_day_leave_count_in_hr"
                                               class="form-control" placeholder="5" required/>

                                        @error("half_day_leave_count_in_hr")
                                        <p class="text-danger"> {{ $errors->first("half_day_leave_count_in_hr") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-12">
                                        <label for="attendance_count_start_hour">Attendance Count Start (hour) <span class="text-danger">*</span></label>
                                        <input type="number" value="{{ $data['attendance_count_start_hour'] ?? 0 }}" name="attendance_count_start_hour"
                                               class="form-control" placeholder="5" required/>
                                        <span class="form-text text-muted">When to start the day (Time for attendance)</span>
                                        @error("attendance_count_start_hour")
                                        <p class="text-danger"> {{ $errors->first("attendance_count_start_hour") }} </p>
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
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-10"></div>
                            <div class="col-lg-2">
                                <button type="reset" class="btn btn-secondary ml-lg-12">Reset</button>
                                <button type="submit" class="btn btn-primary float-right ml-0">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
