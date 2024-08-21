@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Create {{ $data['type'] == 'employee' ? 'Employee' : 'Department' }} Roster</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('rosters.index', ['type' => $data['type']]) }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form id="frm-roster-create" action="{{ route('rosters.post-create-form', ['type' => $data['type']]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <input type="hidden" id="type" name="type" value="{{$data['type']}}">
                            @if ($data['type'] == 'employee')
                                @include('filter.division-department-employee-filter')
                            @else
                                @include('filter.division-department-filter')
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="submit" class="btn btn-primary btn-submit float-right ml-0">Next<i class="pl-2 fa fa-angle-double-right"></i></button>
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
