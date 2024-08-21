@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Device</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('bank.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('zkteco-device.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Device Name</label>
                                <input type="text" value="{{ old('name') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="name" placeholder="Enter device name here" required>
                                @error('name')
                                    <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="ip">IP</label>
                                <input type="text" value="{{ old('ip') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="ip" placeholder="Enter device ip here" required>
                                @error('ip')
                                <p class="text-danger"> {{ $errors->first("ip") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="port">Port</label>
                                <input type="text" value="{{ old('port') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="port" placeholder="Enter device port here" required>
                                @error('port')
                                <p class="text-danger"> {{ $errors->first("port") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="serial">Serial</label>
                                <input type="text" value="{{ old('serial') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="serial" placeholder="Enter device serial here" required>
                                @error('serial')
                                <p class="text-danger"> {{ $errors->first("serial") }} </p>
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
