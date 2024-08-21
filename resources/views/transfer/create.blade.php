@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/transfer.css') }}" rel="stylesheet"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Transfer</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('transfer.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>


                <form action="{{ route('transfer.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Employee --}}
                                    <input type="hidden" id="e_name" name="e_name" value="{{old("e_name")}}">
                                    <div class="form-group">
                                        <label for="user_id">Employee Name</label>
                                        <select class="form-control" id='selectUser' name="user_id" required>
                                            <option value="" disabled selected>Select an option</option>
                                        </select>
                                        @error("user_id")
                                        <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    {{-- Office Divisions --}}
                                    <div class="form-group">
                                        <label for="office_division_id">Office Divisions</label>
                                        <select class="form-control" id="office_division_id" name="office_division_id"
                                                required>
                                            <option value="" disabled selected>Select an option</option>
                                            @foreach($data["officeDivisions"] as $officeDivision)
                                                <option
                                                    value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? "selected" : "" }}>
                                                    {{ $officeDivision->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("office_division_id")
                                        <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-4">

                                    {{-- Department --}}
                                    <div class="form-group">
                                        <label for="department_id">Department</label>
                                        <select class="form-control" id="department_id" name="department_id" required>
                                            <option value="" disabled selected>Select an option</option>
                                            @foreach($data["departments"] as $department)
                                                <option
                                                    value="{{ $department->id }}" {{ $department->id == old("department_id") ? "selected" : "" }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("department_id")
                                        <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    {{-- Work Slot --}}
                                    <div class="form-group">
                                        <label for="workslot_id">Work Slot</label>
                                        <select class="form-control" id="workslot_id" name="workslot_id">
                                            <option value="" disabled selected>Select an option</option>
                                            @foreach($data["workSlots"] as $workSlot)
                                                <option
                                                    value="{{ $workSlot->id }}" {{ $workSlot->id == old("workslot_id") ? "selected" : "" }}>
                                                    {{ $workSlot->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("workslot_id")
                                        <p class="text-danger"> {{ $errors->first("workslot_id") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    {{-- Transfer Date --}}
                                    <div class="form-group">
                                        <label for="promoted_date">Transfer Date</label>
                                        <input class="form-control" type="date" name="promoted_date"
                                               value="{{ old('promoted_date') }}" id="promoted_date">
                                        @error("promoted_date")
                                        <p class="text-danger"> {{ $errors->first("promoted_date") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
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


                @include('transfer.current-position')

            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script type="text/javascript">
        // CSRF Token
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var division_click_flag = 0;

        $(document).ready(function () {

            $("#selectUser").select2({
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

            var currentDpt = 0;

            // Get department by division
            $('#office_division_id').change(function () {

                var _officeDivisionID = $(this).val();

                let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                url = url.replace(":officeDivision", _officeDivisionID);

                $.get(url, {}, function (response, status) {
                    $("#department_id").empty();
                    $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                    $.each(response.data.departments, function (key, value) {
                        if (currentDpt != value.id) {
                            $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                })
            });


            // Populate Employee data based on Employee selection
            $('#selectUser').on('change', function () {

                let _employee = $(this).val();
                emp_id = _employee;

                $('#e_name').val($('#selectUser option:selected').text())

                loadTransferHistory();

                let url = "{{ route('promotion.getEmployeeCurrentPromotion', ':employee') }}";

                url = url.replace(":employee", _employee);

                $.get(url, {}, function (response, status) {
                    let result = response.result.current_promotion;

                    // Office Division
                    $("#office_division_id").val(result.office_division_id).change();

                    // Department

                    setTimeout(function () {
                        $("#department_id").val(result.department_id).change();
                    }, 1000)


                    // WorkSlot
                    $("#workslot_id").val(result.workslot_id).change();
                })
            });
        });


        emp_id = '{{old("user_id")}}';

        setTimeout(function () {
            if (emp_id > 0) {
                loadTransferHistory();
                $('#selectUser').html('<option value="' + emp_id + '">' + $('#e_name').val() + '<option>');
            }
        }, 1000);

        $('#office_division_id').select2()
        $('#department_id').select2()
        $('#workslot_id').select2()

    </script>
@endsection
