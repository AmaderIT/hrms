@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Pay Loan</h3>
                </div>
                <form action="{{ route('user-loan.pay') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Division <span class="text-danger">*</span></label>
                                <select class="form-control" id="office_division_id" name="office_division_id">
                                    <option selected disabled>Select an option</option>
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
                                <label for="department_id">Department <span class="text-danger">*</span></label>
                                <select class="form-control" name="department_id" id="department_id">
                                    <option selected disabled>Select an option</option>
                                </select>

                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>

                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee <span class="text-danger">*</span></label>
                                <select class="form-control" name="user_id" id="user_id">
                                    <option selected disabled>Select an option</option>
                                </select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Active Loans --}}
                            <div class="form-group">
                                <label for="user_id">Loans <span class="text-danger">*</span></label>
                                <select class="form-control" name="loan_id" id="loan_id">
                                    <option selected disabled>Select an option</option>
                                </select>
                                @error('loan_id')
                                <p class="text-danger"> {{ $errors->first("loan_id") }} </p>
                                @enderror
                            </div>

                            {{-- Amount Paid --}}
                            <div class="form-group">
                                <label for="amount_paid">Amount Paid <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount_paid" placeholder="Enter paid amount here" required>
                                @error("amount_paid")
                                <p class="text-danger"> {{ $errors->first("amount_paid") }} </p>
                                @enderror
                            </div>

                            {{-- Month --}}
                            <div class="form-group">
                                <label for="year">Month <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="month" id="datepicker" placeholder="Select month and year" required/>
                                @error('month')
                                <p class="text-danger"> {{ $errors->first("month") }} </p>
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
            </div>
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
            $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
            $.each(response.data.departments, function(key, value) {
                $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
            });
        })
    });

    // Get user by department
    $('#department_id').change(function() {
        var _department_id = $(this).val();

        let url = "{{ route('salary.getEmployeeByDepartment', ':department') }}";
        url = url.replace(":department", _department_id);

        $.get(url, {}, function (response, status) {
            $("#user_id").empty();
            $("#user_id").append('<option value="" "selected disabled">Select an option</option>');
            $.each(response.data, function(key, value) {
                $("#user_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
            });
        })
    });

    // Get active loans by employee
    $('#user_id').change(function() {
        var employee_id = $(this).val();

        let url = "{{ route('loan.get_active_loans', ':employee') }}";
        url = url.replace(":employee", employee_id);

        $.get(url, {}, function (response, status) {
            $("#loan_id").empty();
            $("#loan_id").append('<option value="" "selected disabled">Select an option</option>');
            $.each(response.data, function(key, value) {
                let loanName = value.type + ' ' + value.loan_amount + ' TK, ' + 'Installment Amount ' + value.installment_amount + ' TK';
                $("#loan_id").append('<option value="' + value.id + '">'+ loanName + '</option>');
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
