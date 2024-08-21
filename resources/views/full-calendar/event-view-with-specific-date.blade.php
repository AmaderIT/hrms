<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
    <h4 id="modalTitle" class="modal-title">Events <span class="badge badge-danger">{{!empty($eventDate)?date('Y-m-d',strtotime($eventDate)):'---'}}</span></h4>
</div>
<div id="modalBody" class="modal-body">
    <table class="table table-bordered">
        <thead class="custom-thead">
        <tr>
            <th scope="col">Sl.</th>
            <th scope="col">Title</th>
            {{--<th scope="col">Descriptions</th>--}}
        </tr>
        </thead>
        <tbody>
        @if(count($events) > 0)
            @foreach($events as $key=>$item)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ !empty($item['title'])?$item['title']:"---" }}</td>
                    {{--<td>{{ !empty($item['remarks'])?$item['remarks']:"---" }}</td>--}}
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2" style="text-align: center;">Event Not Available!!!</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
