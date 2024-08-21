@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <!--begin::Card-->
        <div class="card card-custom gutter-b example example-compact">
            <div class="card-header">
                <h3 class="card-title">Add District</h3>
                <div class="card-toolbar">
                    <div class="example-tools justify-content-center">
                        <a href="{{ route('district.index') }}" class="btn btn-primary mr-2">Back</a>
                    </div>
                </div>
            </div>
            <!--begin::Form-->
            <form action="{{ route('district.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="col-md-8 offset-md-2">
                        <div class="form-group">
                            <label for="division">Division</label>
                            <select class="form-control" id="division" name="division_id">
                                <option value="" disabled selected>Select an option</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{$item->id == old('division_id') ? 'selected' : ''}}>{{ $item->name }}</option>
                                @endforeach
                            </select>

                            @error('division_id')
                            <p class="text-danger"> {{ $errors->first("division_id") }} </p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">District Name</label>
                            <input type="text" value="{{ old('name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="name" placeholder="Enter district name here" required>
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
                            <button type="submit" class="btn btn-primary mr-2">Save</button>
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
