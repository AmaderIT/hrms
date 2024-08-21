<div class="col-md">
{{-- Office Divisions --}}
    <div class="form-group">
        <label for="office_division_id">Office Divisions</label>
        <select class="form-control" id="office_division_id" name="office_division_id"
                required>
            <option value="0" disabled selected>Select an option</option>
            @foreach($data["officeDivisions"] as $officeDivision)
                <option
                    @if(old('office_division_id') > 0 && $officeDivision->id == old('office_division_id') )
                    selected
                    @endif
                    value="{{ $officeDivision->id }}">
                    {{ $officeDivision->name }}
                </option>
            @endforeach
        </select>
        @error("office_division_id")
        <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
        @enderror
    </div>
</div>

<div class="col-md">
    {{-- Department --}}
    <div class="form-group">
        <label for="department_id">Department</label>
        <select class="form-control" id="department_id" name="department_id" required>
            <option value="0" selected>Select an option</option>
            @if(old('department_id') > 0 )

                @php
                    $department = \App\Models\Department::find(old('department_id'));
                @endphp
                <option selected value="{{optional($department)->id}}">{{optional($department)->name}}</option>
            @endif
        </select>
        @error("department_id")
        <p class="text-danger"> {{ $errors->first("department_id") }} </p>
        @enderror
    </div>
</div>

@push('custom-scripts')
    <script>

        // Get department by division
        $('#office_division_id').change(function () {
            $.ajax({
                url: '{{route("filter.get-department")}}',
                data: {
                    office_division_id: this.value
                },
                success: function (res) {
                    $('#user_id').empty();
                    $('#department_id').empty();

                    var items = '<option value="0">Select an option</option>';

                    $.each(res.data, function (x, y) {
                        items += '<option value="' + y.id + '">' + y.name + '</option>';
                    })

                    $('#user_id').append('<option >Select an option</option>');
                    $('#department_id').append(items)

                },
                error: function (err) {
                    console.log(err)
                }
            })
        });


        $('#office_division_id').select2({
            placeholder: "Select an option"
        });

        $('#department_id').select2({
            placeholder: "Select an option"
        });

        $('#department_id').change()

    </script>
@endpush
