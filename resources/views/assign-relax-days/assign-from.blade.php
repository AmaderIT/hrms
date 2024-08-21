@extends('layouts.app')
@section('content')
    <div class="row">

        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Assign relax day to employee [{{$relax_date}}]</h3>
                    <div class="card-toolbar">
                        <a href="{{route('assign-relax-day.index')}}" class="button btn btn-primary mr-3">Back</a>
                    </div>
                </div>


                @if($html != '')
                    <form action="{{ $route }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="col-md-10 offset-md-1">
                                <div class="form-group">
                                    <label class="mr-8"><span style="font-weight: bold">Division :</span> {{$division_name}}</label>
                                    <label class="mr-8"><span style="font-weight: bold">Department :</span> {{$department_name}}</label>
                                    <label><span style="font-weight: bold">Type :</span> {{$type}}</label>
                                </div>
                                <div class="form-group employee_div">
                                    <input type="checkbox" id="all_employee" class="employee_checkbox" name="all_employee" value="all_employee">
                                    <label for="all_employee">All Employee</label>
                                </div>
                                <div class="form-group employee_div">
                                    {!! $html !!}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-lg-10 text-lg-right">
                                    <button type="submit" class="btn btn-primary mr-2">Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif

            </div>

        </div>

    </div>
@endsection

@section('footer-js')
    <script type="text/javascript">
        $(document).ready( function () {
            $('#all_employee').click(function(){
                if(!$(this).is(':checked')){
                    $('.employee_checkbox').each(function() {
                        $(this).prop('checked', false);
                    });
                }else{
                    $('.employee_checkbox').each(function() {
                        $(this).prop('checked', true);
                    });
                }
            });
        });
    </script>
@endsection
