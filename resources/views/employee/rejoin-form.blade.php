    <div class="col-md-12">
       <div class="col-lg-8 offset-md-2">
            <div class="form-group">
                <input type="hidden" name="trackID" id="trackID">
                <label>Re-join Date <span class="text-danger">*</span></label>
                <input type="text" name="re_joining_date" class="form-control" id="re-joining-date" placeholder="yyyy-mm-dd"
                       autocomplete="off" required/>
            </div>
        </div>
        <div class="col-lg-8 offset-md-2">
            <div class="form-group">
            <label>Employment Type <span class="text-danger">*</span></label>
                <select name="employment_type" id="employment_type" class="form-control" required>
                    <option selected disabled value="">Choose an option</option>
                    @foreach (\App\Models\Promotion::employmentType() as $key => $value)
                        <option value="{{ $key }}">{{$value}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-8 offset-md-2">
            <div class="form-group">
                <label>Reasons</label>
                <textarea class="form-control" name="rejoin_reasons" rows="6"></textarea>
            </div>
        </div>
    </div>

