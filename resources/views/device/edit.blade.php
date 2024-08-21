@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Device</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('zkteco-device.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('zkteco-device.update', ['device' => $device->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Device Name</label>
                                <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50" name="name"
                                       placeholder="Enter device name here" value="{{ old('name') ?: $device->name }}" required>
                                @error('name')
                                <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="ip">IP</label>
                                <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="ip" placeholder="Enter device ip here" value="{{ old('ip') ?: $device->ip }}" required>
                                @error('ip')
                                <p class="text-danger"> {{ $errors->first("ip") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="port">Port</label>
                                <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="port" placeholder="Enter device port here" value="{{ old('port') ?: $device->port }}" required>
                                @error('port')
                                <p class="text-danger"> {{ $errors->first("port") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="serial">Serial</label>
                                <input type="text" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="50"
                                       name="serial" placeholder="Enter device serial here" value="{{ old('serial') ?: $device->serial }}" required>
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
                                <button type="submit" class="btn btn-primary mr-2">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
