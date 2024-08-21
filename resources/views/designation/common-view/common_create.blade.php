<div class="card-body">
    <div class="col-md-8 offset-md-2">
        <div class="form-group">
            <label for="name">Designation Name</label>
            <input type="text" value="{{ old('title') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="100" name="title" placeholder="Enter designation name here" required>
            @error('title')
            <p class="text-danger"> {{ $errors->first("title") }} </p>
            @enderror
        </div>
    </div>
</div>
