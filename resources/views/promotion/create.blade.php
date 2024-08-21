@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
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
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Promotion</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('promotion.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('promotion.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee Name</label>
                                <select class="form-control" id='selectUser' name="user_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                </select>
                                @error("user_id")
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Office Divisions</label>
                                <select class="form-control" id="office_division_id" name="office_division_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["officeDivisions"] as $officeDivision)
                                        <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? "selected" : "" }}>
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
                                <select class="form-control" id="department_id" name="department_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["departments"] as $department)
                                        <option value="{{ $department->id }}" {{ $department->id == old("department_id") ? "selected" : "" }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("department_id")
                                <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                @enderror
                            </div>

                            {{-- Designation --}}
                            <div class="form-group">
                                <label for="designation_id">Designation</label>
                                <select class="form-control" id="designation_id" name="designation_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["designations"] as $designation)
                                        <option value="{{ $designation->id }}" {{ $designation->id == old("designation_id") ? "selected" : "" }}>
                                            {{ $designation->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("designation_id")
                                <p class="text-danger"> {{ $errors->first("designation_id") }} </p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div class="form-group">
                                <label for="type">Promote Type</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach (\App\Models\Promotion::promoteTypes() as $key => $value)
                                        <option value="{{ $key }}">{{$value}}</option>
                                    @endforeach
                                </select>
                                @error("type")
                                <p class="text-danger"> {{ $errors->first("type") }} </p>
                                @enderror
                            </div>

                            {{-- Pay Grade --}}
                            <div class="form-group">
                                <label for="pay_grade_id">Pay Grade</label>
                                <select class="form-control" id="pay_grade_id" name="pay_grade_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["payGrades"] as $payGrade)
                                        <option value="{{ $payGrade->id }}" {{ $payGrade->id == old("pay_grade_id") ? "selected" : "" }}>
                                            {{ $payGrade->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("pay_grade_id")
                                <p class="text-danger"> {{ $errors->first("pay_grade_id") }} </p>
                                @enderror
                            </div>

                            {{-- Salary --}}
                            <div class="form-group">
                                <label for="salary">Salary</label>
                                <input type="number" value="{{ old('salary') }}" class="form-control" id="salary" name="salary"
                                       placeholder="Enter new salary here" required>
                                @error("salary")
                                <p class="text-danger"> {{ $errors->first("salary") }} </p>
                                @enderror
                            </div>

                            {{-- Work Slot --}}
                            <div class="form-group">
                                <label for="workslot_id">Work Slot</label>
                                <select class="form-control" id="workslot_id" name="workslot_id">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["workSlots"] as $workSlot)
                                        <option value="{{ $workSlot->id }}" {{ $workSlot->id == old("workslot_id") ? "selected" : "" }}>
                                            {{ $workSlot->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("workslot_id")
                                <p class="text-danger"> {{ $errors->first("workslot_id") }} </p>
                                @enderror
                            </div>

                            {{-- Promoted Date --}}
                            <div class="form-group">
                                <label for="promoted_date">Promoted Date</label>
                                <input class="form-control" type="date" name="promoted_date" value="{{ old('promoted_date') }}" id="promoted_date">
                                @error("promoted_date")
                                <p class="text-danger"> {{ $errors->first("promoted_date") }} </p>
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
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script type="text/javascript">
        // CSRF Token
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function() {
            $( "#selectUser" ).select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('users.getUsers') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });

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

            // Populate Employee data based on Employee selection
            $('#selectUser').on('change', function () {
                let _employee = $(this).val();

                let url = "{{ route('promotion.getEmployeeCurrentPromotion', ':employee') }}";
                url = url.replace(":employee", _employee);

                $.get(url, {}, function (response, status) {
                    let result = response.result.current_promotion;

                    // Office Division
                    $("#office_division_id").val(result.office_division_id).change();

                    // Department
                    setTimeout(function () {
                        $("#department_id").val(result.department_id).change();
                    }, 1000);

                    // Designation
                    $("#designation_id").val(result.designation_id).change();

                    // PayGrade
                    $("#pay_grade_id").val(result.pay_grade_id).change();

                    // Salary
                    //$("#salary").val(result.salary).change();

                    // WorkSlot
                    $("#workslot_id").val(result.workslot_id).change();
                })
            });
        });
    </script>
@endsection
