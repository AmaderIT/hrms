@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Change Password</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('employee.updatePassword', ['employee' => $employee->uuid]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        {{-- Current Password --}}
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="current">Current Password</label>
                                <input type="password" value="{{ old('current') }}" class="form-control" id="current" name="current" placeholder="Enter current password here" required/>
                                @error("current")
                                <p class="text-danger">{{ $errors->first("current") }} </p>
                                @enderror
                            </div>
                        </div>
                        {{-- New Password --}}
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="new">New Password</label>
                                <input type="password" value="{{ old('new') }}" class="form-control" id="new" name="new" placeholder="Enter new password here" required/>
                                @error("new")
                                <p class="text-danger">{{ $errors->first("new") }} </p>
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
