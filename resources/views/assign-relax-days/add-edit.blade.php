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
                @if(count($employees)>0)
                    <form action="{{ route('assign-relax-day.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="relax_day_id" id="relax_day_id" value="{{$relax_day}}">
                        <div class="card-body">
                            <div class="col-md-10 offset-md-1">
                                <div class="form-group">
                                    <label for="office_division_id"><span style="font-weight: bold">Division :</span> {{$employees[0]->division_name}}</label>
                                </div>
                                <div class="form-group">
                                    <label for="department_id"><span style="font-weight: bold">Department :</span> {{$employees[0]->department_name}}</label>
                                    <input type="hidden" name="department_id" id="department_id" value="{{$employees[0]->department_id}}">
                                </div>
                                <div class="form-group employee_div">
                                    <br>
                                    <input type="checkbox" id="all_employee" class="employee_checkbox" name="all_employee" value="all_employee">
                                    <label for="all_employee"> Select All Employee</label>
                                </div>
                                <div class="form-group employee_div">
                                    <div class="row">
                                        @php $count_div = 0; @endphp
                                        @foreach($employees as $emp)
                                            @php $count_div++; @endphp
                                            @if($count_div==1)
                                                <div class="col-4 specific_employee_div_1">
                                                    <input type="checkbox" @if(in_array($emp->id,$assigned_persons)) checked @endif class="employee_checkbox" id="employee_{{$emp->id}}" name="employee[{{$emp->id}}]" value="{{$emp->id}}">
                                                    <label for="employee_{{$emp->id}}"> {{$emp->fingerprint_no}} - {{$emp->name}}</label><br>
                                                </div>
                                            @endif
                                            @if($count_div==2)
                                                <div class="col-4 specific_employee_div_2">
                                                    <input type="checkbox" @if(in_array($emp->id,$assigned_persons)) checked @endif class="employee_checkbox" id="employee_{{$emp->id}}" name="employee[{{$emp->id}}]" value="{{$emp->id}}">
                                                    <label for="employee_{{$emp->id}}"> {{$emp->fingerprint_no}} - {{$emp->name}}</label><br>
                                                </div>
                                            @endif
                                            @if($count_div==3)
                                                <div class="col-4 specific_employee_div_3">
                                                    <input type="checkbox" @if(in_array($emp->id,$assigned_persons)) checked @endif class="employee_checkbox" id="employee_{{$emp->id}}" name="employee[{{$emp->id}}]" value="{{$emp->id}}">
                                                    <label for="employee_{{$emp->id}}"> {{$emp->fingerprint_no}} - {{$emp->name}}</label><br>
                                                </div>
                                            @endif
                                            @php if($count_div==3){ $count_div=0; } @endphp
                                        @endforeach
                                    </div>
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
