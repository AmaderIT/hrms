@extends('layouts.app')
@section('top-css')
    <link rel="stylesheet" href="{{asset('assets/css/custom-checkbox.css')}}">
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Role</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('roles.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" value="{{ old('name') }}" class="form-control" name="name"
                                       placeholder="Enter role name here" required>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-custom card-stretch">
                                        <div class="card-body">
                                            <h4>Permissions</h4>
                                            <div class="row">
                                                @php
                                                    $group_name = "#";
                                                @endphp
                                                @foreach($permissions as $key=> $permission)
                                                    @if($permission->group_name!=$group_name)
                                                        <div class="col-md-12 border"
                                                             style="background: #5e6278;color: white;">
                                                            <label
                                                                style="margin-top:7px;">{{$permission->group_name?? 'General'}}</label>

                                                        </div>
                                                    @endif

                                                    <div class="col-md-6 border">
                                                        @php
                                                            $group_name = $permission->group_name;
                                                        @endphp
                                                        <div class="selectBox">
                                                            <div class="row">
                                                                <div class="col-md-10">
                                                                    <input id="chk-{{$permission->id}}"
                                                                           type="checkbox"
                                                                           name="permission[]"
                                                                           value="{{$permission->id}}">
                                                                    <label
                                                                        for="chk-{{$permission->id}}">
                                                                        {{$permission->name}}
                                                                    </label>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    @if($permission->description != '')

                                                                        <i class="fa fa-info-circle popup-icon"

                                                                           data-toggle="popover"
                                                                           title="Permission Description"
                                                                           data-content="{{$permission->description}}"></i>

                                                                    @endif
                                                                </div>
                                                            </div>

                                                        </div>


                                                    </div>
                                                @endforeach
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">
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
@section('footer-js')

@endsection
