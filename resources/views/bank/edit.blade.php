@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Bank</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('bank.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('bank.update', ['bank' => $bank->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Bank Name</label>
                                <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="name" placeholder="Enter bank name here"
                                       value="{{ old('name') ?: $bank->name }}" required>

                                @error('name')
                                <p class="text-danger"> {{ $errors->first("name") }} </p>
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
@endsection
