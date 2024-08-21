<div class="card-body">
    <div class="col-md-8 offset-md-2">
        <div class="form-group">
            <label for="name">Institute Name</label>
            <input type="text" value="{{ old('name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="100" name="name" placeholder="Enter institute name here" required>
            @error('name')
            <p class="text-danger"> {{ $errors->first("name") }} </p>
            @enderror
        </div>
    </div>
</div>
