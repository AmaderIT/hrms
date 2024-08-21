@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Update Division Supervisor
                        ({{!empty($getInfos->name)?$getInfos->name.'-'.$getInfos->fingerprint_no:"--"}})</h3>
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
                            <div class="col-md-6 offset-md-1">
                                <input type="hidden" name="supervised_by" id="supervised_by"
                                       value="{{$divisionSupervisor->supervised_by}}">
                                    <label for="office_division_id">Division</label>
                                    <select class="form-control" id="office_division_id" name="office_division_id[]"
                                            required
                                            multiple>
                                        <option value="" disabled>Choose an option</option>
                                        @foreach($data["officeDivisions"] as $officeDivision)
                                            <option
                                                value="{{ $officeDivision->id }}">
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
                    <center>
                        <h5>Division Supervisor History</h5>
                        @if(!empty($divisionSupervisorHistoryDatas) && count($divisionSupervisorHistoryDatas) > 0)
                            <p style="font-weight: bold">{{optional($divisionSupervisorHistoryDatas[0]->supervisedBy)->name .' (Office ID- '.optional($divisionSupervisorHistoryDatas[0]->supervisedBy)->fingerprint_no.')' }}</p>
                            @endif
                    </center>
                    <table class="table table-responsive-lg" id="employeeTable">
                        <thead class="custom-thead">
                        <tr>
                            <th scope="col">Division</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($divisionSupervisorHistoryDatas) > 0)
                            @foreach($divisionSupervisorHistoryDatas as $item)
                                <tr>
                                    <td>{{ $item->officeDivision->name ?? "---" }}</td>
                                    <td>
                                        @can('Delete Supervisor')
                                            <a href="#" class="btn btn-sm font-weight-bolder btn-light-danger"
                                               onclick="deleteAlert('{{ route('division-supervisor.delete', ['divisionSupervisor' => $item->id]) }}')">
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
    </script>
@endsection


