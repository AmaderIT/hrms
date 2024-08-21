<div class="col-md-8 offset-md-2">
    <div class="border position-relative mb-5">
        <span class="position-absolute p-2 h4" style="top: -15px; left: 20px; background-color: #fff;">Relax Day Settings</span>
        <br>
        <div class="form-group row fv-plugins-icon-container" style="padding: 0 2%">
            <div class="col-md-12 my-4">
                <label class="checkbox"><input type="checkbox" name="is_relax_day_setting" value="1" {{ isset($department) && $department->is_relax_day_setting == 1 ? 'checked' : ''}} >
                    <span class="mr-2"></span>Enable
                </label>
            </div>

            <div class="enable_depand_fileds_wrap col-md-12">
                <div class="row">

                    <div class="col-md-3">
                        <label for="relax_day_type">Consumable Type</label>
                        <select class="form-control" name="relax_day_type" id="relax_day_type">
                            <option value="">Select type ...</option>
                            <option value="1" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->type == 1) ? 'selected' : ''}}>Employee</option>
                            <option value="2" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->type == 2) ? 'selected' : ''}}>Departmant</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="max_count_per_month">Max Consumable Day(s) Per Month</label>
                        <select class="form-control" name="max_count_per_month" id="max_count_per_month">
                            <option value="">Select number ...</option>
                            <option value="0" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->max_count_per_month == 0) ? 'selected' : ''}}>0 (Zero Day)</option>
                            <option value="1" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->max_count_per_month == 1) ? 'selected' : ''}}>1 (One day)</option>
                            <option value="2" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->max_count_per_month == 2) ? 'selected' : ''}}>2 (Two days)</option>
                            <option value="3" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->max_count_per_month == 3) ? 'selected' : ''}}>3 (Three days)</option>
                            <option value="4" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->max_count_per_month == 4) ? 'selected' : ''}}>4 (Four days)</option>
                            <option value="5" {{(isset($department) && $department->relaxDaySetting && $department->relaxDaySetting->max_count_per_month == 5) ? 'selected' : ''}}>5 (Five days)</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="weekly_days">Consumable Day(s) In Week</label>
                        <select class="form-control" name="weekly_days[]" id="weekly_days" multiple>
                            <option value="fri" {{ (isset($department) && $department->relaxDaySetting && in_array('fri', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Friday</option>
                            <option value="sat" {{ (isset($department) && $department->relaxDaySetting && in_array('sat', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Saturday</option>
                            <option value="sun" {{ (isset($department) && $department->relaxDaySetting && in_array('sun', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Sunday</option>
                            <option value="mon" {{ (isset($department) && $department->relaxDaySetting && in_array('mon', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Monday</option>
                            <option value="tue" {{ (isset($department) && $department->relaxDaySetting && in_array('tue', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Tuesday</option>
                            <option value="wed" {{ (isset($department) && $department->relaxDaySetting && in_array('wed', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Wednesday</option>
                            <option value="thu" {{ (isset($department) && $department->relaxDaySetting && in_array('thu', $department->relaxDaySetting->weekly_days)) ? 'selected' : '' }}>Thursday</option>
                        </select>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
