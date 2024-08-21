<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Department Wise Lists</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body">
    <div class="row">
            <table class="table table-responsive" id="employeeTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Fingerprint No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                </tr>
                </thead>
                <tbody>

                @foreach($items as $item)
                    <tr>
                        <th scope="row">{{ $item->supervisedBy->fingerprint_no }}</th>
                        <td>{{ $item->supervisedBy->name }}</td>
                        <td>{{ $item->officeDivision->name ?? "---" }}</td>
                        <td>{{ $item->department->name ?? "---" }}</td>
                        <td>{{ $item->supervisedBy->email ?? "---" }}</td>
                        <td>{{ $item->supervisedBy->phone }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
    </div>
</div>

