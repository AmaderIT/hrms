@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Attendance Report</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('report.generateAttendanceReport') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Division</label>
                                <select class="form-control" id="office_division_id" name="office_division_id">
                                    <option selected disabled>Choose an option</option>
                                    @foreach($data["officeDivisions"] as $officeDivision)
                                        <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                            {{ $officeDivision->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error("office_division_id")
                                <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                @enderror
                            </div>

                            {{-- Department --}}
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control" name="department_id[]" id="department_id" multiple>
                                </select>

                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>

                            {{-- Employee --}}
                            <div class="form-group" id="div_employee_field">
                                <label for="user_id">Employee</label>
                                <select class="form-control" name="user_id[]" id="user_id" multiple></select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Month --}}
                            <div class="form-group">
                                <label for="year">Month</label>
                                <input type="text" class="form-control" name="datepicker" id="datepicker" autocomplete="off" required/>
                                @error("month")
                                <p class="text-danger"> {{ $errors->first("month") }} </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Generate</button>
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
<script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">

    // Get department by division
    $('#office_division_id').change(function(){
        var _officeDivisionID = $(this).val();

        let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
        url = url.replace(":officeDivision", _officeDivisionID);

        $.get(url, {}, function (response, status) {
            $("#department_id").empty();
            $("#department_id").append('<option value="" disabled>Select an option</option>');
            $.each(response.data.departments, function(key, value) {
                $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
            });
        })
    });

    // Get employees by their corresponding department
    $('#department_id').change(function() {

        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        let data = {
            "_token": CSRF_TOKEN,
            "department_id": $(this).val()
        }

        if($(this).val().length > 1) $("#div_employee_field").css("display", "none");
        else if($(this).val().length == 1) $("#div_employee_field").css("display", "");

        let url = "{{ route('employee.getEmployeeByDepartment') }}";
        $.post(url, data, function (response, status) {
            $("#user_id").empty();
            $("#user_id").append('<option value="" "selected disabled">Select an option</option>');
            $.each(response.data, function(key, value) {
                $("#user_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
            });
        })
    });

    $("select").select2({
        theme: "classic",
    });

    $("#datepicker").datepicker( {
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    });
</script>
@endsection
