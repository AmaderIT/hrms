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
                    <h3 class="card-title">Leave History Report</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('report.generateLeaveHistory') }}" method="GET">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Division</label>
                                <select class="form-control" id="office_division_id" name="office_division_id">
                                    <option value="all" selected="selected">All Divisions</option>
                                    @if(!empty($data["officeDivisions"]))
                                    @foreach($data["officeDivisions"] as $officeDivision)
                                        <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                            {{ $officeDivision->name }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>

                                @error("office_division_id")
                                <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                @enderror
                            </div>

                            {{-- Department --}}
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control" name="department_id[]" id="department_id" multiple>
                                    <option value="all" selected >All Departments</option>
                                    @if(!empty($data["officeDepartments"]))
                                    @foreach($data["officeDepartments"] as $officeDepartment)
                                        <option value="{{ $officeDepartment->id }}" {{ $officeDepartment->id == old("department_id") ? 'selected' : '' }}>
                                            {{ $officeDepartment->name }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>

                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>

                            {{-- Employee --}}
                            <div class="form-group" id="div_employee_field">
                                <label for="user_id">Employee</label>
                                <select class="form-control" name="user_id[]" id="user_id" multiple>
                                    <option value="all" selected >All Employees</option>
                                    @if(!empty($data["employees"]))
                                    @foreach($data["employees"] as $employee)
                                        <option value="{{ $employee->id }}" {{ $employee->id == old("user_id") ? 'selected' : '' }}>
                                            {{ $employee->fingerprint_no.' - '.$employee->name }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="user_id">Status</label>
                                <select id="leave-status" name="status" class="form-control">
                                    <option value="all">-All-</option>
                                    <option value="{{\App\Models\LeaveRequest::STATUS_APPROVED}}">Approved</option>
                                    <option value="{{\App\Models\LeaveRequest::STATUS_REJECTED}}">Rejected</option>
                                </select>
                            </div>

                            {{-- Month --}}
                            <div class="form-group">
                                <label for="year">Month<span style="color: red"> *</span></label>
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
                                <input type="submit" name="type" value="Export Excel" class="btn btn-primary mr-2"/>
                                <input type="submit" name="type" value="Export PDF" class="btn btn-primary mr-2"/>
                                <button type="submit" class="btn btn-primary mr-2">View</button>
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
        $('#office_division_id').change(function(){
            var _officeDivisionID = $(this).val();
            let url = "{{ route('report.getDepartmentAndEmployeeByOfficeDivision') }}";
            $.get(url, {office_division_id:_officeDivisionID}, function (response, status) {
                $("#department_id").empty();
                $("#department_id").append('<option value="all" selected="selected">All Departments</option>');
                $.each(response.departments, function(key, value) {
                    $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
                $("#user_id").empty();
                $("#user_id").append('<option value="all" selected="selected">All Employees</option>');
                $.each(response.employees, function(key, value) {
                    $("#user_id").append('<option value="' + value.id + '">'+ value.fingerprint_no+' - '+value.name + '</option>');
                });
                $("select").select2({
                    theme: "classic",
                });
            })
        });
        var old_selected_value = '';
        $('#department_id').on("select2:selecting", function(e) {
            old_selected_value = $(this).val();
        });
        $('#department_id').change(function() {
            let latest_select = $("#department_id option:selected").last().val();
            if(typeof latest_select == 'undefined'){
                $("#department_id option:selected").prop("selected", false);
                $('#department_id').trigger('change.select2');
                $('#department_id option[value="all"]').prop('selected', true);
                $('#department_id').trigger('change.select2');
            }else{
                var missing = false;
                $.each($(this).val(), function(key,val) {
                    if(val == 'all'){
                        missing = true;
                        $.each(old_selected_value, function(key1,val1) {
                            if(val1 == 'all'){
                                missing = false;
                            }
                        });
                    }
                });
                if (missing){
                    $("#department_id option:selected").prop("selected", false);
                    $('#department_id').trigger('change.select2');
                    $('#department_id option[value="all"]').prop('selected', true);
                    $('#department_id').trigger('change.select2');
                }else{
                    $('#department_id option[value="all"]').prop('selected', false);
                    $('#department_id').trigger('change.select2');
                }
            }
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            let data = {
                "_token": CSRF_TOKEN,
                "department_id": $(this).val(),
                "office_division_id": $('#office_division_id').val()
            }
            let url = "{{ route('report.getEmployeesByDepartmentOrDivision') }}";
            $.post(url, data, function (response, status) {
                $("#user_id").empty();
                $("#user_id").append('<option value="all" selected="selected">All Employees</option>');
                $.each(response.data, function(key, value) {
                    $("#user_id").append('<option value="' + value.id + '">'+ value.fingerprint_no + ' - ' + value.name + '</option>');
                });
            })
        });
        var old_user_selected_value = '';
        $('#user_id').on("select2:selecting", function(e) {
            old_user_selected_value = $(this).val();
        });
        $('#user_id').change(function() {
            let latest_select = $("#user_id option:selected").last().val();
            if(typeof latest_select == 'undefined'){
                $("#user_id option:selected").prop("selected", false);
                $('#user_id').trigger('change.select2');
                $('#user_id option[value="all"]').prop('selected', true);
                $('#user_id').trigger('change.select2');
            }else{
                var missing = false;
                $.each($(this).val(), function(key,val) {
                    if(val == 'all'){
                        missing = true;
                        $.each(old_user_selected_value, function(key1,val1) {
                            if(val1 == 'all'){
                                missing = false;
                            }
                        });
                    }
                });
                if (missing){
                    $("#user_id option:selected").prop("selected", false);
                    $('#user_id').trigger('change.select2');
                    $('#user_id option[value="all"]').prop('selected', true);
                    $('#user_id').trigger('change.select2');
                }else{
                    $('#user_id option[value="all"]').prop('selected', false);
                    $('#user_id').trigger('change.select2');
                }
            }
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
