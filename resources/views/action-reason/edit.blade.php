@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Action Reason</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('action-reason.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('action-reason.update', ['actionReason' => $actionReason->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="parent_id">Action Type</label>
                                <select class="form-control" id="parent_id" name="parent_id">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ $type->id == $actionReason->parent_id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                @error('parent_id')
                                <p class="text-danger"> {{ $errors->first("parent_id") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <input type="text" class="form-control" id="kt_maxlength_1" value="{{ old('reason') ?: $actionReason->reason }}" required
                                       minlength="3" maxlength="50" name="reason" placeholder="Enter reason here">

                                @error('reason')
                                <p class="text-danger"> {{ $errors->first("reason") }} </p>
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
