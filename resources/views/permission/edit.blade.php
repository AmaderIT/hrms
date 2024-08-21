@extends('layouts.app')
@section('top-css')
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-duallistbox.min.css')}}">
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Permission</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('permission.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('permission.update', ['permission' => $permission->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" value="{{ $permission->name }}" class="form-control" name="name"
                                       required readonly>
                            </div>

                            <div class="form-group">
                                <label for="name">Description</label>
                                <textarea class="form-control" name="description" id="description"
                                          placeholder="Enter description here...">{{ $permission->description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="name">Group Name</label>
                                <select name="group_name" id="group_name" class="form-control">
                                    <option>Select an option</option>
                                    @foreach($groups as $group)
                                        <option @if($permission->group_name == $group) selected @endif value="{{$group}}">{{$group}}</option>
                                    @endforeach
                                </select>
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
@section('footer-js')
    <script src="{{asset('assets/js/jquery.bootstrap-duallistbox.min.js')}}"></script>
    <script>
        var demo1 = $('select[name="permission[]"').bootstrapDualListbox({
            moveOnSelect: false,
        });

        $('#group_name').select2({
            tags: true,
            formatNoMatches: "Nothing found"
        })
    </script>
@endsection
