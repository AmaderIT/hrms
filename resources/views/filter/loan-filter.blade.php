<div class="col-3">
    <span>Choose Division</span>
    <select class="select w-100" id="office_division_id" name="office_division_id"
            style="height: 30px;">
        <option value="0" selected disabled>Choose an option</option>

        @foreach($officeDivisions as $officeDivisionId => $officeDivisionTitle)
            <option
                value="{{ $officeDivisionId }}" {{ $officeDivisionId == request()->get("office_division_id") ? 'selected' : '' }}>
                {{ $officeDivisionTitle }}
            </option>
        @endforeach

    </select>
</div>

{{-- Department --}}
<div class="col-3">
    <span>Choose Department</span>
    <select class="form-control select w-100" name="department_id[]" id="department_id" multiple
            style="height: 30px;">
        @foreach($departments as $departmentId => $departmentTitle)
            <option value="{{ $departmentId }}"
            @if(!is_null(request()->get("department_id")))
                {{ in_array($departmentId, \request()->get("department_id")) ? 'selected' : '' }}
                @endif>
                {{ $departmentTitle }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-2">
    <span>Type</span>
    <select class="w-100 form-control" name="type">
        <option value="">All Type</option>
        @foreach(\App\Models\Loan::LOAN_TYPES as $typeKey => $typeVal)
            <option value="{{ $typeKey }}" {{ (!empty(request()->get("type")) && request()->get("type") == $typeKey)? 'selected': '' }}>{{ $typeVal }}</option>
        @endforeach
    </select>
</div>

<div class="col-2">
    <span>Status</span>
    <select class="w-100 form-control" name="status">
        <option value="">All Status</option>
        @foreach(\App\Models\Loan::LOAN_STATUS as $statusKey => $statusVal)
            <option value="{{ $statusKey }}" {{ (!empty(request()->get("status")) && request()->get("status") == $statusKey)? 'selected': '' }}>{{ $statusVal }}</option>
        @endforeach
    </select>
</div>

@push('custom-scripts')
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        // Get department by division
        $('#office_division_id').change(function () {
            var _officeDivisionID = $(this).val();
            let url = "{{ route('report.getDepartmentAndEmployeeByOfficeDivision', true) }}";
            $.get(url, {office_division_id: _officeDivisionID}, function (response, status) {
                $("#department_id").empty();
                var items = '<option value="0" selected disabled>Choose an option</option>';
                $.each(response.departments, function (key, value) {
                    items += '<option value="' + value.id + '">' + value.name + '</option>';
                });
                $('#department_id').append(items)
                $(" #office_division_id").select2({
                    placeholder: "Choose an option"
                });
                $('#department_id').select2({
                    placeholder: "Choose an option"
                });
            })
        });

        $('#office_division_id').select2({
            placeholder: "Choose an option"
        });

        $('#department_id').select2({
            placeholder: "Choose an option"
        });

        $(".datepicker").datepicker({
            format: "yyyy-mm-dd"
        });
    </script>
@endpush
