<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
    <h4 id="modalTitle" class="modal-title">Leave Date <span class="badge badge-danger">{{!empty($eventDate)?date('Y-m-d',strtotime($eventDate)):'---'}}</span></h4>
</div>
<div class="modal-body">
    <table class="table table-bordered">
        <thead class="custom-thead">
        <tr>
            <th scope="col">Sl.</th>
            <th scope="col">Name</th>
            <th scope="col">Department</th>
            <th scope="col">Duration</th>
            <th scope="col">No.of days</th>
            <th scope="col">Status</th>
        </tr>
        </thead>
        <tbody>
        @if(count($events) > 0)
            @foreach($events as $key=>$item)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td><a href="javascript:;"  class="get-employee-id" data-emp-id="{{$item['employee']['id']}}">{{ !empty($item['employee']['name'])?$item['employee']['name']:"---" }}</a></td>
                    <td>{{ !empty($item['employee']->currentPromotion->department->name)?$item['employee']->currentPromotion->department->name:"---" }}</td>
                    <td>{{ $item->from_date->format('Y-m-d') . ' to ' . $item->to_date->format('Y-m-d') }}</td>
                    <td>{{ !empty($item->number_of_days)?$item->number_of_days:"---" }}</td>
                    <td>
                        @if($item->status == \App\Models\LeaveRequest::STATUS_PENDING)
                            Pending
                        @elseif($item->status == \App\Models\LeaveRequest::STATUS_APPROVED)
                            Approved
                        @elseif($item->status == \App\Models\LeaveRequest::STATUS_REJECTED)
                            Rejected
                        @elseif($item->status == \App\Models\LeaveRequest::STATUS_AUTHORIZED)
                            Authorized
                        @endif
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="3" style="text-align: center;">Event Not Available!!!</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
