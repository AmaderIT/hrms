@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .select2-selection {
            height: 40px !important;
        }

        input[type="date"]::-webkit-datetime-edit, input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-clear-button {
            color: #fff;
            position: relative;
        }

        input[type="date"]::-webkit-datetime-edit-year-field {
            position: absolute !important;
            border-left:1px solid #8c8c8c;
            padding: 2px;
            color:#000;
            left: 56px;
        }

        input[type="date"]::-webkit-datetime-edit-month-field {
            position: absolute !important;
            border-left:1px solid #8c8c8c;
            padding: 2px;
            color:#000;
            left: 26px;
        }

        input[type="date"]::-webkit-datetime-edit-day-field {
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
                    <h3 class="card-title">Add Weekly Holiday</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('weekly-holiday.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('weekly-holiday.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control name" name="department_id[]" multiple="multiple">
                                    <option value="0">All Department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error("department_id")
                                <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                @enderror
                            </div>

                            <div class="form-group row">
                                <label class="col-1 col-form-label">Days</label>
                                <div class="col-9 col-form-label">
                                    <div class="checkbox-list">
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="fri">
                                            <span></span>Friday</label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="sat">
                                            <span></span>Saturday</label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="sun">
                                            <span></span>Sunday</label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="mon">
                                            <span></span>Monday</label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="tue">
                                            <span></span>Tuesday</label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="wed">
                                            <span></span>Wednesday</label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="days[]" value="thu">
                                            <span></span>Thursday</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="effective_date">Effective Date <span style="color: red">*</span></label>
                                <input type="date" class="form-control" name="effective_date" id="effective_date" required>
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
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script>
        $( ".name" ).select2({});
    </script>
@endsection
