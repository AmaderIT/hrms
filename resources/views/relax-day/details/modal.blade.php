<input type="hidden" id="date_value" name="date_value" value="{{$date_value}}">
<input type="hidden" id="public_holiday_record_id" name="public_holiday_record_id" @if(isset($public_holiday_records->id)) value="{{$public_holiday_records->id}}" @else value="" @endif>
@php $department_ids = []; @endphp
@if($relax_days->count()>0)
    @foreach($relax_days as $index=>$day)
        @php $department_ids[$index] = $day->department_id; @endphp
        <input type="hidden" id="relax_record_id" name="relax_record_id[]" value="{{$day->id}}">
    @endforeach
@endif
<div class="row">
    <div class="col-lg-1"></div>
    <div class="col-lg-10">
        <div class="form-group">
            <div class="radio-inline">
                <label class="radio radio-primary">
                    <input type="radio" name="public_holiday_radio" @if(isset($public_holiday_records->holiday_id)) value="1" checked @else value="0" @endif id="public_holiday_radio">
                    <span></span>Public Holiday</label>
            </div>
        </div>
        <div class="form-group public_holiday_item @if(!isset($public_holiday_records->holiday_id)) d-none @endif">
            <label for="public_holiday_radio">Public Holiday</label>
            <select class="form-control" id="public_holiday_select" name="public_holiday_id">
                <option value="">Choose holiday</option>
                @foreach($holidays as $day)
                    <option value="{{$day->id}}" @if(isset($public_holiday_records->holiday_id) && $public_holiday_records->holiday_id==$day->id) selected @endif >{{$day->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group public_holiday_item @if(!isset($public_holiday_records->holiday_id)) d-none @endif">
            <label for="public_holiday_date_range">Date Range</label>
            <input type="text" class="form-control" name="daterange" id="daterange" value="{{$date_range}}" />
        </div>
        <div class="form-group">
            <div class="radio-inline">
                <label class="radio radio-primary">
                    <input type="radio" name="relax_day_radio" @if($relax_days->count()>0) value="1" checked @else value="0" @endif id="relax_day_radio">
                    <span></span>Relax Day</label>
            </div>
        </div>

        <div class="form-group relax_day_item @if($relax_days->count()==0) d-none @endif">

            @foreach($officeDivisions as $officeDivision_id=>$officeDivision)
                <div class="form-group department_div mb-2">
                    <input type="checkbox" id="division_{{$officeDivision_id}}" data-division-id="{{$officeDivision_id}}" class="division_checkbox" name="division[]" value="{{$officeDivision_id}}">
                    <label for="division_{{$officeDivision_id}}"> {{$officeDivision}}</label>
                </div>
                <div class="form-group department_div_{{$officeDivision_id}} mt-0">
                    <div class="row">
                        @php $count_div = 0; @endphp
                        @foreach($officeDepartments[$officeDivision_id] as $officeDepartment_id=>$officeDepartment)
                            @php $count_div++; @endphp
                            @if($count_div==1)
                                <div class="col-6 specific_department_div_1">
                                    <input type="checkbox" class="department_checkbox_{{$officeDivision_id}}" data-department-id="{{$officeDepartment_id}}" id="department_{{$officeDepartment_id}}" name="department[{{$officeDepartment_id}}]" @if(in_array($officeDepartment_id,$department_ids)) checked @endif value="{{$officeDepartment_id}}">
                                    <label class="font-weight-normal" for="department_{{$officeDepartment_id}}"> {{ $officeDepartment }}</label>
                                </div>
                            @endif
                            @if($count_div==2)
                                <div class="col-6 specific_department_div_2">
                                    <input type="checkbox" class="department_checkbox_{{$officeDivision_id}}" data-department-id="{{$officeDepartment_id}}" id="department_{{$officeDepartment_id}}" name="department[{{$officeDepartment_id}}]" @if(in_array($officeDepartment_id,$department_ids)) checked @endif value="{{$officeDepartment_id}}">
                                    <label class="font-weight-normal" for="department_{{$officeDepartment_id}}"> {{ $officeDepartment }}</label>
                                </div>
                            @endif
                            @php if($count_div==2){ $count_div=0; } @endphp
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>

        <div class="form-group relax_day_item @if($relax_days->count()==0) d-none @endif">
            <label for="note">Note</label>
            <textarea class="form-control" name="note" id="note">{{$relax_days[0]->note ?? ''}}</textarea>
        </div>

        <div class="form-group text-center error-div d-none">
            <label id="error-text">Please set holiday type!</label>
        </div>

    </div>
    <div class="col-lg-1"></div>
</div>
<script>
    $(document).ready( function () {
        $('#public_holiday_radio').click(function () {
            if($(this).val()==1){
                $(this).val(0);
                $(this).prop("checked", false);
                $('.public_holiday_item').addClass('d-none');
                $('#daterange').prop('required',false);
                $('#public_holiday_select').prop('required',false);
            }else{
                $(this).val(1);
                $(this).prop("checked", true);
                $('.public_holiday_item').removeClass('d-none');
                $('#daterange').prop('required',true);
                $('#public_holiday_select').prop('required',true);
            }
        });
        $('#relax_day_radio').click(function () {
            if($(this).val()==1){
                $(this).val(0);
                $(this).prop("checked", false);
                $('.relax_day_item').addClass('d-none');
            }else{
                $(this).val(1);
                $(this).prop("checked", true);
                $('.relax_day_item').removeClass('d-none');
            }
        });
        $('.division_checkbox').click(function(){
            let division_id = $(this).data('division-id');
            if(!$(this).is(':checked')){
                $('.department_checkbox_'+division_id).each(function() {
                    $(this).prop('checked', false);
                });
            }else{
                $('.department_checkbox_'+division_id).each(function() {
                    $(this).prop('checked', true);
                });
            }
        });
    });
</script>
