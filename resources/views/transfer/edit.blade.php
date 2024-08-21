@extends('layouts.app')
@section("top-css")
    <link href="{{ asset('assets/css/transfer.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Transfer</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('transfer.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('transfer.update', ['transfer' => $item->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-12 ">
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Employee --}}
                                    <div class="form-group">
                                        <label for="user_id">Employee Name</label>
                                        <input class="form-control"
                                               value="{{optional($item->user)->name}}({{optional($item->user)->email}})"
                                               readonly>
                                        <input type="hidden" name="user_id" id="selectUser" value="{{$item->user_id}}">
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
                                                    value="{{ $officeDivision->id }}" {{ $officeDivision->id == $item->office_division_id ? "selected" : "" }}>
                                                    {{ $officeDivision->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("office_division_id")
                                        <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    {{-- Department --}}
                                    <div class="form-group">
                                        <label for="department_id">Department</label>
                                        <select class="form-control" id="department_id" name="department_id">
                                            <option value="" disabled selected>Select an option</option>
                                            @foreach($data["departments"] as $department)
                                                <option
                                                    value="{{ $department->id }}" {{ $department->id == $item->department->id ? "selected" : "" }}>
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
                                                    value="{{ $workSlot->id }}" {{ $workSlot->id == $item->workslot_id ? "selected" : "" }}>
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
                                    {{-- Promoted Date --}}
                                    <div class="form-group">
                                        <label for="promoted_date">Transfer Date</label>
                                        <input class="form-control" type="date" name="promoted_date" id="promoted_date"
                                               value="{{ date('Y-m-d', strtotime($item->promoted_date ?? '')) }}">
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
                                <!--
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                -->
                                <button type="submit" class="btn btn-primary mr-2">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
                <!--end::Form-->

                @include('transfer.current-position')
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection

@section('footer-js')
    <script type="text/javascript">
        $(document).ready(function () {
            // Get department by division
            $('#office_division_id').change(function () {
                var _officeDivisionID = $(this).val();

                let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                url = url.replace(":officeDivision", _officeDivisionID);

                $.get(url, {}, function (response, status) {
                    $("#department_id").empty();
                    $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                    $.each(response.data.departments, function (key, value) {
                        $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                })
            });
        });

        emp_id = '{{ $item->user_id }}';
        //Loading the employee departmental movement history
        if (emp_id > 0) {
            loadTransferHistory();
        }

        $('#office_division_id').select2()
        $('#department_id').select2()
        $('#workslot_id').select2()

    </script>
@endsection
