@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Warehouse</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('warehouse.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('warehouse.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="name" placeholder="Enter warehouse name here" required>
                                @error('name')
                                    <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('company_name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="company_name" placeholder="Enter company name here" required>
                                @error('company_name')
                                <p class="text-danger"> {{ $errors->first("company_name") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bin">BIN <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('bin') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="bin" placeholder="Enter bin here" required>
                                @error('bin')
                                <p class="text-danger"> {{ $errors->first("bin") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="code">Code <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('code') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="code" placeholder="Enter code here" required>
                                @error('code')
                                <p class="text-danger"> {{ $errors->first("code") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" value="{{ old('email') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="email" placeholder="Enter email here" required>
                                @error('email')
                                <p class="text-danger"> {{ $errors->first("email") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('phone') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="phone" placeholder="Enter phone here" required>
                                @error('phone')
                                <p class="text-danger"> {{ $errors->first("phone") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="area">Area <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('area') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="area" placeholder="Enter area here" required>
                                @error('area')
                                <p class="text-danger"> {{ $errors->first("area") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="city">City <span class="text-danger">*</span></label>
                                <input type="text" value="{{ old('city') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="city" placeholder="Enter city here" required>
                                @error('city')
                                <p class="text-danger"> {{ $errors->first("city") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="address">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="address" rows="6" id="address" placeholder="Address" required></textarea>
                                @error("address")
                                <p class="text-danger"> {{ $errors->first("address") }} </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('footer-js')
    <script type="text/javascript">
        $('#department_id').select2();
    </script>
@endsection
