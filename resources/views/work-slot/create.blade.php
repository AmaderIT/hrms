@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Work Slot</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('work-slot.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('work-slot.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="title">Slot Name</label>
                                <input type="text" value="{{ old('title') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="title" placeholder="Enter slot name here" required>
                                @error('title')
                                <p class="text-danger"> {{ $errors->first("title") }} </p>
                                @enderror
                            </div>

                            <div class="form-group row">
                                <div class="col-9 col-form-label">
                                    <div class="checkbox-inline">
                                        <label class="checkbox checkbox-success">
                                            <input type="checkbox" name="is_flexible" value="1" id="is_flexible">
                                            <span></span>Is Flexible</label>
                                    </div>
                                    @error('is_flexible')
                                    <p class="text-danger"> {{ $errors->first("is_flexible") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="started_at">Start Time</label>
                                <input type="time" class="form-control" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                <p class="text-danger"> {{ $errors->first("start_time") }} </p>
                                @enderror
                            </div>
                            <div class="form-group not_flexible_div">
                                <label for="ended_at">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                <p class="text-danger"> {{ $errors->first("end_time") }} </p>
                                @enderror
                            </div>
                            <div class="form-group not_flexible_div">
                                <label for="ended_at">Late Count Time</label>
                                <input type="time" class="form-control" id="late_count_time" name="late_count_time" value="{{ old('late_count_time') }}" required>
                                @error('late_count_time')
                                <p class="text-danger"> {{ $errors->first("late_count_time") }} </p>
                                @enderror
                            </div>
                            <div class="form-group row">
                                <div class="col-9 col-form-label">
                                    <div class="checkbox-inline">
                                        <label class="checkbox checkbox-success">
                                            <input type="checkbox" name="over_time" value="Yes" id="over_time">
                                            <span></span>Over Time</label>
                                    </div>
                                    @error('over_time')
                                    <p class="text-danger"> {{ $errors->first("over_time") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div style="display: none" class="form-group overtime_count_div">
                                <label for="ended_at">OT Count Time</label>
                                <input type="time" class="form-control overtime_count" name="overtime_count" value="{{ old('overtime_count') }}">
                                @error('overtime_count')
                                <p class="text-danger"> {{ $errors->first("overtime_count") }} </p>
                                @enderror
                            </div>
                            <div style="display: none" class="form-group total_work_hour_div">
                                <label for="ended_at">Total Work Hour</label>
                                <input type="number" class="form-control total_work_hour" min="1" max="18" name="total_work_hour" value="{{ old('total_work_hour') }}">
                                @error('total_work_hour')
                                <p class="text-danger"> {{ $errors->first("total_work_hour") }} </p>
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
@section("footer-js")
    <script type="text/javascript">
        $(document).ready(function() {
            $("#is_flexible").change(function (e) {
                if(this.checked) {
                    $('.not_flexible_div').hide();
                    $("#end_time").val('');
                    $("#end_time").prop('required',false);
                    $("#late_count_time").val('');
                    $("#late_count_time").prop('required',false);
                    $('.total_work_hour_div').show();
                    $(".total_work_hour").val('');
                    $(".total_work_hour").prop('required',true);
                    if($("#over_time").is(':checked')){
                        $('.overtime_count_div').hide();
                        $(".overtime_count").val('');
                        $(".overtime_count").prop('required',false);
                    }
                } else {
                    $('.not_flexible_div').show();
                    $("#end_time").val('');
                    $("#end_time").prop('required',true);
                    $("#late_count_time").val('');
                    $("#late_count_time").prop('required',true);
                    $('.total_work_hour_div').hide();
                    $(".total_work_hour").val('');
                    $(".total_work_hour").prop('required',false);
                    if($("#over_time").is(':checked')){
                        $('.overtime_count_div').show();
                        $(".overtime_count").val('');
                        $(".overtime_count").prop('required',true);
                    }
                }
            });
            $("#over_time").change(function (e) {
                if(this.checked) {
                    if($("#is_flexible").is(':checked')){
                        $('.overtime_count_div').hide();
                        $(".overtime_count").prop('required',false);
                    }else{
                        $('.overtime_count_div').show();
                        $(".overtime_count").prop('required',true);
                    }
                } else {
                    $('.overtime_count_div').hide();
                    $(".overtime_count").prop('required',false);
                }
            });
        });
    </script>
@endsection
