@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Division Supervisor</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('division-supervisor.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('division-supervisor.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="input-group">

                        <div class="col-md-4">
                                <label for="supervised_by">Name</label>
                                <select class="form-control" id="supervised_by" name="supervised_by">
                                    <option value="" selected disabled>Choose an option</option>
                                </select>
                                @error("supervised_by")
                                <p class="text-danger"> {{ $errors->first("supervised_by") }} </p>
                                @enderror
                        </div>
                            <div class="col-md-4">
                                    <label for="office_division_id">Division</label>
                                    <select class="form-control" id="office_division_id" name="office_division_id[]" required
                                            multiple>
                                        <option value="" disabled>Choose an option</option>
                                        @foreach($data["officeDivisions"] as $officeDivision)
                                            <option
                                                value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                                {{ $officeDivision->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("office_division_id")
                                    <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                    @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="assign"></label>
                                <button type="submit" class="form-control btn btn-primary mr-2 btn-sm">Assign</button>
                            </div>

                        </div>
                    </div>
                </form>
                <div class="card-body">
                    <div id="tbl-data-division-supervisor"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script type="text/javascript">
        $("select").select2({
            theme: "classic",
        });
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function () {
            $("#supervised_by").select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('supervisor.users.getEmployees') }}",
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

            $('#supervised_by').on('change', function () {
                let _employeeID = $(this).val();
                $.ajax({
                    url: '{{route("division-supervisor.divisionSupervisorHistory")}}',
                    type: 'POST',
                    data: {
                        employee_id: _employeeID,
                    },
                    success: function (res) {
                        $('#tbl-data-division-supervisor').html(res)
                    },
                    error: function (err) {
                        console.log(err)
                    }
                })
            });
        });
    </script>
@endsection


