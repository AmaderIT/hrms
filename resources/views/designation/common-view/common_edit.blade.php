<div class="row">
    <div class="col-md-12">
        <!--begin::Card-->
        <div class="card card-custom gutter-b example example-compact">
            <div class="card-header">
                <h3 class="card-title">Edit Designation</h3>
                <div class="card-toolbar">
                    <div class="example-tools justify-content-center">
                        <a href="{{ route('designation.index') }}" class="btn btn-primary mr-2">Back</a>
                    </div>
                </div>
            </div>
            <!--begin::Form-->
            <form action="{{ route('designation.update', ['designation' => $designation->id]) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="col-md-8 offset-md-2">
                        <div class="form-group">
                            <label for="name">Designation Name</label>
                            <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="100" name="title" placeholder="Enter designation name here"
                                   value="{{ old('name') ?: $designation->title }}" required>

                            @error('title')
                            <p class="text-danger"> {{ $errors->first("title") }} </p>
                            @enderror
                        </div>
                    </div>
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
