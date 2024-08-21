@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Challan Item</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('other-requisition-item.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('other-requisition-item.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Item Name<span style="color:red;">*</span></label>
                                <input type="text" value="{{ old('name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="250" name="name" placeholder="Enter requisition item name here" required>
                                @error('name')
                                    <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Item Code</label>
                                <input type="text" value="{{ old('code') }}" class="form-control" id="kt_maxlength_1" minlength="0" maxlength="50" name="code" placeholder="Enter requisition item code here">
                                @error('code')
                                <p class="text-danger"> {{ $errors->first("code") }} </p>
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
