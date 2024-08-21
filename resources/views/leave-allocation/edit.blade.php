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
                    <h3 class="card-title">Edit Leave Allocation</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('leave-allocation.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('leave-allocation.update', ['leaveAllocation' => $leaveAllocation->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Division</label>
                                <select class="form-control" id="office_division_id" name="office_division_id[]">
                                    <option selected disabled>Choose an option</option>
                                    @foreach($data["officeDivisions"] as $officeDivision)
                                        <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == $leaveAllocation->office_division_id ? 'selected' : '' }}>
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
                                <select class="form-control" name="department_id[]" id="department_id">
                                    <option selected disabled>Select an option</option>
                                    @foreach($data["departments"] as $department)
                                        <option value="{{ $department->id }}" {{ $department->id == $leaveAllocation->department_id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>

                            {{-- Year --}}
                            <div class="form-group">
                                <label for="datepicker">Year</label>
                                <input type="text" class="form-control" name="year" id="datepicker" value="{{ $leaveAllocation->year }}" autocomplete="off" required/>
                                @error('year')
                                <p class="text-danger"> {{ $errors->first("year") }} </p>
                                @enderror
                            </div>

                            <table class="table table-borderless">
                                <thead>
                                <tr>
                                    <th scope="col">Leave Type</th>
                                    <th scope="col" width="50%">Total days(Yearly)</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($data["leaveAllocationDetails"]->leaveAllocationDetails as $leaveAllocationDetails)
                                        <tr>
                                            <th scope="row">
                                                <input type="hidden" name="leave_type_id[]" value="{{ $leaveAllocationDetails->leaveType->id }}">
                                                <label for="{{ $leaveAllocationDetails->leaveType->name }}">{{ $leaveAllocationDetails->leaveType->name }}</label>
                                            </th>
                                            <td>
                                                <input type="number" id="{{ $leaveAllocationDetails->leaveType->name }}" class="form-control" name="days[]"
                                                       value="{{ $leaveAllocationDetails->total_days }}" required/>
                                                @error('days')
                                                <p class="text-danger"> {{ $errors->first("days") }} </p>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="form-group">
                                <label for="short_day_count">Short Day Count (In hour)</label>
                                <input type="number" class="form-control" name="short_day_count" id="short_day_count" value="{{$leaveAllocation->short_day_count ?? ''}}"/>
                                @error('short_day_count')
                                <p class="text-danger"> {{ $errors->first("short_day_count") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="short_day_count">half Day Count (In hour)</label>
                                <input type="number" class="form-control" name="half_day_count" id="half_day_count" value="{{$leaveAllocation->half_day_count ?? ''}}"/>
                                @error('half_day_count')
                                <p class="text-danger"> {{ $errors->first("half_day_count") }} </p>
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

    // Enable Select2
    $("select").select2({
        theme: "classic",
    });

    // Year Picker
    $("#datepicker").datepicker( {
        format: "yyyy",
        startView: "years",
        minViewMode: "years"
    });
</script>
@endsection
