@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Leave Type</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('leave-type.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('leave-type.update', ['leaveType' => $leaveType->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        {{--<div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Leave Type Name</label>
                                <input type="text" class="form-control" value="{{ old('name') ?: $leaveType->name }}" id="kt_maxlength_1" name="name" minlength="3" maxlength="50" aria-valuemax="">
                                @error('name')
                                <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>
                        </div>--}}

                        <div class="form-group row">
                            <div class="col-md-6 offset-1">
                                <label for="name">Leave Type Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ old('name') ?: $leaveType->name }}" id="kt_maxlength_1" name="name" minlength="3" maxlength="50" aria-valuemax="">
                                @error('name')
                                <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label>Paid/Unpaid ? <span class="text-danger">*</span></label>
                                <select name="is_paid" class="form-control" required>
                                    @foreach (\App\Models\LeaveType::leaveMode() as $key => $value)
                                        <option value="{{ $key }}" {{ $leaveType->is_paid == $key ? "selected" : "" }}>{{$value}}</option>
                                    @endforeach
                                </select>
                                @error("is_paid")
                                <p class="text-danger"> {{ $errors->first("is_paid") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label>Priority <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ old('priority') ?: $leaveType->priority }}" id="kt_maxlength_1" name="priority" minlength="1" maxlength="10" required>
                                @error("priority")
                                <p class="text-danger"> {{ $errors->first("priority") }} </p>
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
