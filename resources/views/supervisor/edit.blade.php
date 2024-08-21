@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Update Supervisor
                        ({{!empty($getInfos->name)?$getInfos->name.'-'.$getInfos->fingerprint_no:"--"}})</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('supervisor.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('supervisor.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="input-group">
                            <div class="col-md-4">
                                <input type="hidden" name="supervised_by" id="supervised_by"
                                       value="{{$departmentSupervisor->supervised_by}}">
                                <label for="office_division_id">Division</label>
                                <select class="form-control" id="office_division_id" name="office_division_id" required>
                                    <option selected disabled>Choose an option</option>
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
                            <div class="col-md-4">
                                <label for="department_id">Department</label>
                                <select class="form-control" name="department_id[]" id="department_id" required
                                        multiple>
                                    <option value="" disabled>Select an option</option>
                                </select>
                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="assign"></label>
                                <button type="submit" class="form-control btn btn-primary mr-2 btn-sm">Assign</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="tbl-data"></div>
                    </div>
                </form>

                <div class="card-body">
                    <center>
                        <h5>Supervisor History</h5>
                        <p>
                        @if(!empty($supervisorHistoryDatas) && count($supervisorHistoryDatas) > 0)
                            <p style="font-weight: bold">{{optional($supervisorHistoryDatas[0]->supervisedBy)->name .' (Office ID- '.optional($supervisorHistoryDatas[0]->supervisedBy)->fingerprint_no.')' }}</p>
                            @endif
                            </p>
                    </center>
                    <table class="table table-responsive-lg" id="employeeTable">
                        <thead class="custom-thead">
                        <tr>
                            <th scope="col">Division</th>
                            <th scope="col">Department</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($supervisorHistoryDatas) > 0)
                            @foreach($supervisorHistoryDatas as $item)
                                <tr>
                                    <td>{{ $item->officeDivision->name ?? "---" }}</td>
                                    <td>{{ $item->department->name ?? "---" }}</td>
                                    <td>
                                        @can('Delete Supervisor')
                                            <a href="#" class="btn btn-sm font-weight-bolder btn-light-danger"
                                               onclick="deleteAlert('{{ route('supervisor.delete', ['departmentSupervisor' => $item->id]) }}')">
                                                X
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" style="text-align: center;">Data Not Available!!!</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
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
            $('#office_division_id').change(function () {
                var _officeDivisionID = $(this).val();

                let url = "{{ route('supervisor.getDepartmentByOfficeDivision', ':officeDivision') }}";
                url = url.replace(":officeDivision", _officeDivisionID);

                var items = '';
                $("#department_id :selected").map(function (i, el) {
                    var selectdItemValue = $(el).val();
                    var selectdItemText = $(el).text();
                    if (selectdItemValue > 0) {
                        items += '<option selected value="' + selectdItemValue + '">' + selectdItemText + '</option>';
                    }
                }).get();

                $.get(url, {}, function (response, status) {
                    $.each(response.data.departments, function (key, value) {
                        var f = 0;
                        $("#department_id :selected").map(function (i, el) {
                            if ($(el).val() == value.id) {
                                f = 1
                            }
                        });
                        if (f == 0) {
                            items += '<option value="' + value.id + '">' + value.name + '</option>';
                        }
                    });
                    $('#department_id').html(items)
                })
            });
        });
    </script>
@endsection
