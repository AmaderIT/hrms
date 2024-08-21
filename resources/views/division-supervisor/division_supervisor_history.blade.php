<div class="card-body">
    <center>
        <h5>Division Supervisor History</h5>
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
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        @if(count($supervisorHistoryDatas) > 0)
            @foreach($supervisorHistoryDatas as $item)
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
                <td colspan="2" style="text-align: center;">Data Not Available!!!</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
