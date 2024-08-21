<div class="row">
    <div class="col-md-12">
        <!--begin::Card-->
        <div class="card card-custom gutter-b example example-compact">
            <div class="card-header">
                <h3 class="card-title">Edit Department</h3>
                <div class="card-toolbar">
                    <div class="example-tools justify-content-center">
                        <a href="{{ route('department.index') }}" class="btn btn-primary mr-2">Back</a>
                    </div>
                </div>
            </div>
            <!--begin::Form-->
            <form action="{{ route('department.update', ['department' => $department->id]) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="col-md-8 offset-md-2">
                        {{-- Office Divisions --}}
                        <div class="form-group">
                            <label for="office_division_id">Division</label>
                            <select class="form-control" id="office_division_id" name="office_division_id" required>
                                <option value="" disabled selected>Select an option</option>
                                @foreach($officeDivisions as $officeDivision)
                                    <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == $department->office_division_id ? 'selected' : '' }}>
                                        {{ $officeDivision->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error("office_division_id")
                            <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                            @enderror
                        </div>

                        {{-- Department Name --}}
                        <div class="form-group">
                            <label for="name">Department Name</label>
                            <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="name" placeholder="Enter department name here"
                                   value="{{ old('name') ?: $department->name }}" required>

                            @error('name')
                            <p class="text-danger"> {{ $errors->first("name") }} </p>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-8 offset-md-2">
                        <div class="row">
                            <div class="col-4 m-auto">
                                <div class="form-group">
                                    <div class="radio-inline">
                                        <label class="radio radio-default">
                                            <input type="radio" id="is_warehouse" name="is_warehouse" @if($department->is_warehouse) checked value="1" @else value="0" @endif class="transfer_from">
                                            <span></span>Is Warehouse</label>
                                    </div>
                                    @error('is_warehouse')
                                    <p class="text-danger"> {{ $errors->first("is_warehouse") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-8 warehouse_id_div @if(!$department->is_warehouse) d-none @endif">
                                <div class="form-group" style="display: flex">
                                    <label for="warehouse_id" style="margin:auto;width: 40%">Select Warehouse</label>
                                    <select class="form-control @if(!$department->is_warehouse) disabled @endif" id="warehouse_id" name="warehouse_id">
                                        <option value="">Choose Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $warehouse->id == $department->warehouse_id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("warehouse_id")
                                    <p class="text-danger"> {{ $errors->first("warehouse_id") }} </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 offset-md-2">

                        @php
                            $wh_days = json_decode($department->weeklyHoliday->days);
                        @endphp
                        <div class="border position-relative mb-5">
                            <span class="position-absolute p-2 h4" style="top: -15px; left: 20px; background-color: #fff;">Weekly Holidays</span>
                            <div class="row justify-content-center py-4">
                                <div class="col-10 row justify-content-around py-2">
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="fri" {{$wh_days && in_array('fri', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Friday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="sat" {{$wh_days && in_array('sat', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Saturday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="sun" {{$wh_days && in_array('sun', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Sunday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="mon" {{$wh_days && in_array('mon', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Monday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="tue" {{$wh_days && in_array('tue', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Tuesday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="wed" {{$wh_days && in_array('wed', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-1"></span>Wednesday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="thu" {{$wh_days && in_array('thu', $wh_days) ? 'checked' : ''}} onchange="onChangeCheckBox()">
                                        <span class="mx-1"></span>Thursday</label>
                                </div>
                            </div>
                        </div>

                        <div class="border position-relative">
                            <span class="position-absolute p-2 h4" style="top: -15px; left: 20px; background-color: #fff;">Leaves</span>
                            <div class="p-8">
                                <div class="form-group">
                                    <label for="datepicker">Year </label>
                                    <input type="text" value="{{optional($department->leaveAllocation)->year}}" class="form-control" name="year" id="datepicker" autocomplete="off" readonly placeholder="YYYY"/>
                                    @error('year')
                                    <p class="text-danger"> {{ $errors->first("year") }} </p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Total Leaves <span class="text-danger">*</span></label>
                                    <table class="table table-borderless">
                                        <thead>
                                        <tr>
                                            <th scope="col">Leave Type</th>
                                            <th scope="col" width="50%">Total days(Yearly)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaveTypes as $key => $leaveType)
                                                <tr>
                                                    <th scope="row">
                                                        <label for="{{ $leaveType->name }}">{{ $leaveType->name }} <span class="text-danger">*</span></label>
                                                    </th>
                                                    <td>
                                                        @if ($department->leaveAllocation->leaveAllocationDetails->isNotEmpty())
                                                            @php $flag = 0; @endphp
                                                            @foreach ($department->leaveAllocation->leaveAllocationDetails as $item)
                                                            @if ($item->leave_type_id != $leaveType->id) @continue @endif
                                                                <input type="hidden" name="leave_days[{{ $key }}][leave_type_id]" value="{{ $item->leave_type_id }}">
                                                                <input type="hidden" name="leave_days[{{ $key }}][id]" value="{{ $item->id }}">
                                                                <input type="number" name="leave_days[{{ $key }}][value]" value="{{ $item->total_days }}" id="{{ $item->leaveType->name }}" class="form-control" min="0" required/>
                                                                @php $flag = 1; @endphp
                                                                @break
                                                            @endforeach
                                                        @else
                                                            <input type="hidden" name="leave_days[{{ $key }}][leave_type_id]" value="{{ $leaveType->id }}">
                                                            <input type="hidden" name="leave_days[{{ $key }}][id]" value="">
                                                            <input type="number" name="leave_days[{{ $key }}][value]" value="" id="{{ $item->leaveType->name }}" class="form-control" min="0" required/>
                                                        @endif
                                                        @if ($flag != 1)
                                                            <input type="hidden" name="leave_days[{{ $key }}][leave_type_id]" value="{{ $leaveType->id }}">
                                                            <input type="hidden" name="leave_days[{{ $key }}][id]" value="">
                                                            <input type="number" name="leave_days[{{ $key }}][value]" value="" id="{{ $item->leaveType->name }}" class="form-control" min="0" required/>
                                                        @endif

                                                        @error("leave_days.{$key}.value")
                                                        <p class="text-danger"> {{ $errors->first("leave_days.{$key}.value") }} </p>
                                                        @enderror
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="border position-relative mb-5">
                            <span class="position-absolute p-2 h4" style="top: -15px; left: 20px; background-color: #fff;">Late Deduction</span>
                            <br>
                            <div class="form-group row fv-plugins-icon-container" style="padding: 0 2%">
                                <div class="col-md-4">
                                    <label for="total_days">Days Late <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="total_days" placeholder="Days Late" value="{{ optional($department->lateDeduction)->total_days }}" required>
                                    @error('total_days')
                                    <p class="text-danger"> {{ $errors->first("total_days") }} </p>
                                    @enderror
                                    <div class="fv-plugins-message-container"></div>
                                </div>
                                <div class="col-md-4">
                                    <label for="deduction_day">Equivalent Working Day<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="deduction_day" placeholder="Equivalent working day" value="{{ optional($department->lateDeduction)->deduction_day }}" required>
                                    @error('deduction_day')
                                    <p class="text-danger"> {{ $errors->first("deduction_day") }} </p>
                                    @enderror
                                    <div class="fv-plugins-message-container"></div>
                                </div>
                                <div class="col-md-4">
                                    <label for="type">Deduction Method <span class="text-danger">*</span></label>
                                    <select class="form-control" name="type" required>
                                        <option value="">Select</option>
                                        <option value="leave" {{ $department->lateDeduction && $department->lateDeduction->type == 'leave' ? 'selected' : '' }} >Leave</option>
                                        <option value="salary" {{ $department->lateDeduction && $department->lateDeduction->type == 'salary' ? 'selected' : '' }}>Salary</option>
                                    </select>
                                    @error('type')
                                    <p class="text-danger"> {{ $errors->first("type") }} </p>
                                    @enderror
                                    <div class="fv-plugins-message-container">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('department.common-view.relax_day')

                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-10 text-lg-right">
                            <button type="reset" class="btn btn-default mr-2">Reset</button>
                            <button type="submit" class="btn btn-primary mr-2">Update</button>
                        </div>
                    </div>
                </div>
            </form>
            <!--end::Form-->
        </div>
        <!--end::Card-->
    </div>
</div>
