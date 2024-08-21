@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Employee Bonus</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('user-bonus.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('user-bonus.update', ['userBonus' => $userBonus->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Division</label>
                                <select class="form-control" id="office_division_id" name="office_division_id">
                                    <option selected disabled>Choose an option</option>
                                    @foreach($data["officeDivisions"] as $officeDivision)
                                        <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == $userBonus->office_division_id ? 'selected' : '' }}>
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
                                <select class="form-control" name="department_id" id="department_id">
                                    <option selected disabled>Select an option</option>
                                    @foreach($data["departments"] as $department)
                                        <option value="{{ $department->id }}" {{ $department->id == $userBonus->department_id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>

                            {{-- Bonus --}}
                            <div class="form-group">
                                <label for="bonus_id">Bonus</label>
                                <select class="form-control" name="bonus_id" id="bonus_id">
                                    <option selected disabled>Select an option</option>
                                    @foreach($data["bonuses"] as $bonus)
                                        <option value="{{ $bonus->id }}" {{ $department->id == $userBonus->department_id ? 'selected' : '' }}>
                                            {{ $bonus->festival_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bonus_id')
                                <p class="text-danger"> {{ $errors->first("bonus_id") }}</p>
                                @enderror
                            </div>

                            {{-- Month --}}
                            <div class="form-group">
                                <label for="year">Month</label>
                                <input type="text" class="form-control" name="month" id="datepicker" value="{{ $userBonus->month }}" required/>
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
