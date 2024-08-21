<style>
    .swal2-icon{
        width: 3em !important;
        height: 3em !important;
    }

    .swal2-icon .swal2-icon-content {
        font-size: 27px !important;
    }
</style>
<div class="swal2-icon swal2-warning swal2-icon-show mb-2" style="display: flex;"><div class="swal2-icon-content">!</div></div>
<input class="previous_roaster_id" type="hidden" name="roaster_id" value="{{$roasterInfo->id}}">
<input class="previous_approval_status" type="hidden" name="approval_status" value="{{$roasterInfo->approval_status}}">

@php 
    $active_from = date("Y-m-d", strtotime($roasterInfo->active_from));
@endphp

@if($roasterInfo->approval_status==1 && $active_from<=$currentDate)
    <h4 class="text-center" id="swal2-title">{{$message}}</h4>
    <input class="btn_name" type="hidden" name="btn_name" value="Close">
    <style>
        #roaster-approval-status-update-btn {
            display: none !important;
        }
    </style>
@else 
    <h3 class="text-center" id="swal2-title">Are you sure?</h4>
    <div class="text-center">You want to {{$message}} this roaster!</div>
    <input class="roaster_id" type="hidden" name="roaster_id" value="{{$roaster_id}}">
    <input class="approval_status" type="hidden" name="approval_status" value="{{$approval_status}}">
    <input class="btn_name" type="hidden" name="btn_name" value="{{$message}}">

    @if($approval_status==2)
    <div class="form-group mb-2 text-center mt-3" style="padding: 0 10px;">
        <textarea class="form-control remarks" name="remarks" rows="3" placeholder="Remarks..."></textarea>
    </div>
    @endif

@endif

<script>
    $(document).ready(function(){
        var btnName = $('.btn_name').val();
        $('.roaster_approval_status_modal').find('#roaster-approval-status-update-btn').text(btnName);
    });
</script>





