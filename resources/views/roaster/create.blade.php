@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Create Roaster</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('roaster.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('roaster.createForm') }}" method="GET">
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            @include('filter.division-department-filter')
                           

                            <!-- SET BY -->
                            <div class="form-group mb-2" style="padding: 0 10px;">
                                <label for="type">Set By</label>
                                <select class="form-control" name="type" id="type" required>
                                    <option value="emp">Employee</option>
                                    <option value="dept">Department</option>
                                </select>
                                @error('type')
                                <p class="text-danger"> {{ $errors->first("type") }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="submit" class="btn btn-primary float-right ml-0">Next<i class="pl-2 fa fa-angle-double-right"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
@stack('custom-scripts')
   
    <script type="text/javascript">
       
    </script>
@endsection