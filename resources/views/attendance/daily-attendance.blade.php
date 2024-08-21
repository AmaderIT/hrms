@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Attendance</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('home') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('attendance.storeDailyAttendance') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="emp_code">Employee</label>
                                <select class="form-control" id="emp_code" name="emp_code" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->fingerprint_no }}" {{ $employee->id == old("emp_code") ? 'selected' : '' }}>
                                            {{ $employee->name . " - " . $employee->fingerprint_no }}
                                        </option>
                                    @endforeach
                                </select>

                                @error("emp_code")
                                <p class="text-danger"> {{ $errors->first("emp_code") }} </p>
                                @enderror
                            </div>

                            {{-- In Time --}}
                            <div class="form-group">
                                <b>Entry Date &amp; Time</b>
                                <div class="input-group date" id="time_in" data-target-input="nearest">
                                    <input type="text" id="time_in" name="time_in" class="form-control datetimepicker-input" autocomplete="off" placeholder="Select entry date &amp; time" data-target="#kt_datetimepicker_6" required>
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

                            {{-- Out Time --}}
                            <div class="form-group">
                                <b>Exit Date &amp; Time</b>
                                <div class="input-group date" id="time_out" data-target-input="nearest">
                                    <input type="text" id="time_out" name="time_out" class="form-control datetimepicker-input" autocomplete="off" placeholder="Select exit date &amp; time" data-target="#kt_datetimepicker_6" required>
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
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Save</button>
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

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        $("select").select2({
            theme: "classic",
        });

        $('#time_in, #time_out').datetimepicker({
            format: "yy-MM-DD HH:mm"
        });
    </script>
@endsection
