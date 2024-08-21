<style>
    .half-day-slots-cal-auto {
        margin: 0px 0px 7px 0px;
        padding: 7px;
        border: 1px solid black;
    }
</style>
<div class="col-lg-7 col-md-7 col-sm-12">
    <div class="row align-items-center">
        <div class="col-auto">
            <label for="leave-request-type" class="col-form-label">Leave Request For</label>
        </div>
        <div class="col-auto">
            <div class="radio-inline">
                @php $requestedFullDayCheckedVal = 'checked'; @endphp
                @php $requestedHalfDayCheckedVal = ''; @endphp
                @if($requestedApplication &&  $requestedApplication->half_day == true)
                    @php $requestedFullDayCheckedVal = ''; @endphp
                    @php $requestedHalfDayCheckedVal = 'checked'; @endphp
                @endif

                <label class="radio radio-default">
                    <input type="radio" name="leave_request_type" value="full_day" class="leave-request-type"
                           id="leave_request_full_day" {{$requestedFullDayCheckedVal}}>
                    <span></span>Full Day</label>
                <label class="radio radio-default">
                    <input type="radio" name="leave_request_type" value="half_day" class="leave-request-type"
                           id="leave_request_half_day" {{$requestedHalfDayCheckedVal}}>
                    <span></span>Half Day</label>
            </div>
            @error('leave_request_type')
            <p class="text-danger"> {{ $errors->first("leave_request_type") }} </p>
            @enderror
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-4 div-md-4">
            <div class="form-group">
                <label for="from_date">From Date</label>
                <input type="text" class="form-control" id="from_date" name="from_date" required
                       @if($requestedApplication)
                       value="{{ date('Y-m-d', strtotime($requestedApplication->from_date)) }}"
                       @else
                       value="{{ old('from_date') }}"
                    @endif
                />
                @error("from_date")
                <p class="text-danger"> {{ $errors->first("from_date") }} </p>
                @enderror
            </div>
        </div>
        <div class="col-lg-4 div-md-4">
            {{-- To Date --}}
            <div class="form-group">
                <label for="to_date">To Date</label>
                <input type="text" class="form-control" name="to_date" id="to_date" required
                       @if($requestedApplication)
                       value="{{ date('Y-m-d', strtotime($requestedApplication->to_date)) }}"
                       @else
                       value="{{ old('to_date') }}"
                    @endif/>

                @error("to_date")
                <p class="text-danger"> {{ $errors->first("to_date") }} </p>
                @enderror
            </div>
        </div>
        <div class="col-lg-4 div-md-4">
            <div class="form-group">
                <label for="number_of_days">Number of day</label>
                <input type="text" class="form-control" name="number_of_days"
                       id="number_of_days" readonly placeholder="Number of day"
                       value="{{ $requestedApplication->number_of_days ?? old('number_of_days') }}"/>
                @error("number_of_days")
                <p class="text-danger"> {{ $errors->first("number_of_days") }} </p>
                @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 div-md-12 {{($requestedHalfDayCheckedVal === 'checked') ?'':'d-none'}}"
             id="half-day-slot-portion">
            <h6>Half Day Slots</h6>

            <div class="col-auto half-day-slots-cal-auto">
                <div class="radio-inline half-day-slots-format">
                    <label class="radio radio-default">
                        <input @if($requestedApplication && $requestedApplication->half_day_slot == 1) checked
                               @endif  type="radio" name="half_day_slot" value="1" class="half-day-slots"
                               id="first_half_slots">
                        <span></span></label>
                    <span id="half_slots_text_1" class="applied_date_3" data-applied_start_date=""
                          data-applied_end_date=""></span>
                </div>
            </div>
            <div class="col-auto half-day-slots-cal-auto">
                <div class="radio-inline half-day-slots-format">
                    <label class="radio radio-default">
                        <input @if($requestedApplication && $requestedApplication->half_day_slot == 2) checked
                               @endif type="radio" name="half_day_slot" value="2" class="half-day-slots"
                               id="second_half_slots">
                        <span></span> </label>
                    <span id="half_slots_text_2" class="applied_date_4" data-applied_start_date=""
                          data-applied_end_date=""></span>
                </div>
            </div>

        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Entitled</th>
                    <th>Consumed</th>
                    <th>Locked</th>
                    <th>Usable</th>
                </tr>
                </thead>

                <tbody id="leave-balance-tbl"></tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 div-md-12">
            <div class="form-group">
                <label for="purpose">Purpose</label>
                <textarea class="form-control" name="purpose" rows="2" id="purpose"
                          placeholder="Purpose"
                          required>{!! $requestedApplication->purpose ?? old('purpose') !!}</textarea>

                @error("purpose")
                <p class="text-danger"> {{ $errors->first("purpose") }} </p>
                @enderror
            </div>
        </div>
    </div>
    <div id="custom-alert" style="display:none;background:transparent;color: #f64e60;border-color: #e4e6ef;"
         class="alert alert-warning" role="alert">

    </div>

    <div class="row">
        @if($requestedApplication && !$room)
            @if( $requestedApplication && (auth()->user()->can("Authorize Leave Requests") || auth()->user()->can("Approve Leave Requests") ) )
                <div class="col-4">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            @if(auth()->user()->can("Authorize Leave Requests") AND $requestedApplication->status === \App\Models\LeaveRequest::STATUS_PENDING)
                                <option
                                    value="{{ \App\Models\LeaveRequest::STATUS_AUTHORIZED }}" {{ $requestedApplication->status === \App\Models\LeaveRequest::STATUS_AUTHORIZED ? "selected" : "" }}>
                                    Approve
                                </option>
                            @elseif(auth()->user()->can("Approve Leave Requests") AND $requestedApplication->status === \App\Models\LeaveRequest::STATUS_AUTHORIZED)
                                <option
                                    value="{{ \App\Models\LeaveRequest::STATUS_APPROVED }}" {{ $requestedApplication->status === \App\Models\LeaveRequest::STATUS_APPROVED ? "selected" : "" }}>
                                    Approve
                                </option>
                            @endif
                            <option
                                value="{{ \App\Models\LeaveRequest::STATUS_CANCEL }}" {{ $requestedApplication->status === \App\Models\LeaveRequest::STATUS_CANCEL ? "selected" : "" }}>
                                Cancel
                            </option>
                        </select>

                        @error("status")
                        <p class="text-danger"> {{ $errors->first("status") }} </p>
                        @enderror
                    </div>
                </div>

                <div class="col-8 remarks-div" style="display: none">
                    <div class="form-group">
                        <label for="status">New Remarks</label>
                        <textarea id="remarks" rows="1" class="form-control" name="remarks"></textarea>
                    </div>
                </div>



            @else
                <input type="hidden" name="status" value="0">
            @endif
        @endif
        @if($requestedApplication)
            <div class="col-12">
                <div class="form-group">
                    <label for="status">Remarks</label>
                    <hr>
                    <p>{!!html_entity_decode($requestedApplication->remarks?? '')!!}</p>
                </div>
            </div>
        @endif
    </div>
</div>
