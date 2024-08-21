                    <div class="col-md-8 offset-md-2">
                        <div class="form-group">
                            <label for="office_division_id">Division <span class="text-danger">*</span></label>
                            @php
                                $getOfficeID = "office_division_id";
                            @endphp
                            @if(!empty($trackingType))
                                @php
                                    $getOfficeID = "office_division_id_in_modal";
                                @endphp
                            @endif
                            <select class="form-control" id="{{$getOfficeID}}" name="office_division_id" required>
                                <option value="" disabled selected>Select an option</option>
                                @foreach($officeDivisions as $officeDivision)
                                    <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                        {{ $officeDivision->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error("office_division_id")
                            <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name">Department Name <span class="text-danger">*</span></label>
                            <input type="text" value="{{ old('name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="name" placeholder="Enter department name here" required>
                            @error('name')
                            <p class="text-danger"> {{ $errors->first("name") }} </p>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-8 offset-md-2">
                        <div class="row">
                            <div class="col-4 flex-shrink-1">
                                <div class="form-group">
                                    <div class="radio-inline">
                                        <label class="radio radio-default">
                                            <input type="radio" id="is_warehouse" name="is_warehouse" value="0" class="transfer_from">
                                            <span></span>Is Warehouse</label>
                                    </div>
                                    @error('is_warehouse')
                                    <p class="text-danger"> {{ $errors->first("is_warehouse") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-8 d-none warehouse_id_div">
                                <div class="form-group" style="display: flex">
                                    <label for="warehouse_id" style="margin:auto;width: 40%">Select Warehouse</label>
                                    <select class="form-control disabled" id="warehouse_id" name="warehouse_id">
                                        <option value="">Choose Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $warehouse->id == old("source_warehouse_id") ? 'selected' : '' }}>
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

                        <div class="border position-relative mb-5">
                            <span class="position-absolute p-2 h4" style="top: -15px; left: 20px; background-color: #fff;">Weekly Holidays</span>
                            <div class="row justify-content-center py-4">
                                <div class="col-10 row justify-content-around py-2">
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="fri" onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Friday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="sat" onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Saturday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="sun" onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Sunday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="mon" onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Monday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="tue" onchange="onChangeCheckBox()">
                                        <span class="mx-2"></span>Tuesday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="wed" onchange="onChangeCheckBox()">
                                        <span class="mx-1"></span>Wednesday</label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="days[]" value="thu" onchange="onChangeCheckBox()">
                                        <span class="mx-1"></span>Thursday</label>
                                </div>
                            </div>
                        </div>

                        <div class="border position-relative">
                            <span class="position-absolute p-2 h4" style="top: -15px; left: 20px; background-color: #fff;">Leaves</span>
                            <div class="p-8">
                                <div class="form-group">
                                    <label for="datepicker">Year <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="year" id="datepicker" autocomplete="off" required placeholder="YYYY"/>
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
                                                    <input type="hidden" name="leave_type_id[]" value="{{ $leaveType->id }}">
                                                    <label for="{{ $leaveType->name }}">{{ $leaveType->name }} <span class="text-danger">*</span></label>
                                                </th>
                                                <td>
                                                    <input type="number" id="{{ $leaveType->name }}" class="form-control" name="leave_days[{{ $key }}]" required/>
                                                    @error('days')
                                                    <p class="text-danger"> {{ $errors->first("days") }} </p>
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
                                    <input type="number" class="form-control" name="total_days" placeholder="Days Late" value="0">
                                    @error('total_days')
                                    <p class="text-danger"> {{ $errors->first("total_days") }} </p>
                                    @enderror
                                    <div class="fv-plugins-message-container"></div>
                                </div>
                                <div class="col-md-4">
                                    <label for="deduction_day">Equivalent Working Day<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="deduction_day" placeholder="Equivalent working day" value="0">
                                    @error('deduction_day')
                                    <p class="text-danger"> {{ $errors->first("deduction_day") }} </p>
                                    @enderror
                                    <div class="fv-plugins-message-container"></div>
                                </div>
                                <div class="col-md-4">
                                    <label for="type">Deduction Method <span class="text-danger">*</span></label>
                                    <select class="form-control" name="type">
                                        <option value="">Select</option>
                                        <option value="leave" selected>Leave</option>
                                        <option value="salary">Salary</option>
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
