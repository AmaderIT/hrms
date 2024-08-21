@extends('layouts.app')
@section("top-css")
<style>
    input[type="date"]::-webkit-datetime-edit, input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-clear-button {
        color: #fff;
        position: relative;
    }

    input[type="date"]::-webkit-datetime-edit-year-field{
        position: absolute !important;
        border-left:1px solid #8c8c8c;
        padding: 2px;
        color:#000;
        left: 56px;
    }

    input[type="date"]::-webkit-datetime-edit-month-field{
        position: absolute !important;
        border-left:1px solid #8c8c8c;
        padding: 2px;
        color:#000;
        left: 26px;
    }


    input[type="date"]::-webkit-datetime-edit-day-field{
        position: absolute !important;
        color:#000;
        padding: 2px;
        left: 4px;

    }
    .instraction{
        display: inline-block;
        float: right;
        padding: 0;
        position: absolute;
        right: 4px;
        bottom: 4px;
    }
    .relax{
        background: deepskyblue;
        color: #fff;
        padding: 1px 4px !important;
        font-size: 10px;
        text-align: center;
        border-radius: 3px;
    }
    .public{
        background: #0BB7AF;
        color: #fff;
        padding: 1px 4px !important;
        font-size: 10px;
        text-align: center;
        border-radius: 3px;

    }
    .customTbale tbody tr .pointer-td:hover{
        background-color: lightgrey;
        cursor: pointer;
    }

    .customTbale tbody tr td{
        position: relative;
        width: 90px;
        height: 90px;
    }
    .customTbale thead .day-class {
        background-color: lightskyblue;
    }
    .customTbale thead .month-class {
        font-weight: bold;
        background-color: black;
    }
    .customTbale thead .month-class td {
        font-size: 20px;
        color: white;
    }
    .customTbale thead .day-class td{
      text-align: center;
    }
    .customTbale tbody tr td .day{
        font-weight: 600;
        font-size: 20px;
        margin-top: 17px;
    }
    .red-color{
        color: red;
    }
    a {
        text-decoration: none;
        display: inline-block;
        padding: 8px 16px;
    }

    a:hover {
        background-color: #04AA6D;
        color: black;
    }

    .previous {
        background-color: #ddd;
        color: black;
        font-weight: bold;
    }

    .next {
        background-color: #ddd;
        color: black;
        font-weight: bold;
    }
    .less_pading {
        margin-bottom: 0.75rem;
    }
    .error-div{
        background-color: #f3b6b6;
        margin: auto;
    }
    #error-text{
        color: black;
        margin: auto;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <!--begin::Form-->
                <form action="{{ route('relax-day.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-4 offset-md-1 mb-5">
                            <div class="form-group less_pading">
                                <label for="year_month">Year-Month</label>
                                <input type="text" class="form-control" name="year_month" value="{{$current_year_month}}" id="year_month" autocomplete="off" required/>
                                <input type="hidden" id="current_year" value="{{$current_year}}">
                                <input type="hidden" id="current_month" value="{{$current_month}}">
                                @error("year_month")
                                <p class="text-danger"> {{ $errors->first("year_month") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-10 offset-md-1">
                            <div class="form-group float-left less_pading">
                                <a href="#" class="previous">Previous</a>
                            </div>
                            <div class="form-group float-right less_pading">
                                <a href="#" class="next">Next</a>
                            </div>
                            <div class="form-group less_pading calender_div">
                                @include('relax-day.details.calender')
                            </div>
                        </div>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>


    <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Relax Day and Public Holiday Settings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form name="relax-form" id="relax-form" action="#" method="POST">
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection
@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script>
        $("#year_month").datepicker({
            format: "yyyy-mm",
            viewMode: "months",
            minViewMode: "months"
        });
    </script>
    <script>
        $(document).ready( function () {
            $('#year_month').change(function (e) {
                let year_month = $(this).val();
                let year_month_arr =  year_month.split("-");
                $('#current_year').val(year_month_arr[0]);
                $('#current_month').val(year_month_arr[1]);
                let data = {
                    '_token':'{{csrf_token()}}',
                    'year_month':year_month,
                };
                var url = '{{route('relax-day.getCalender')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: "json",
                    success: function(result){
                        $('.calender_div').html(result.html);
                    }
                });
            });

            $('.previous').click(function (e) {
                e.preventDefault();
                let year = $('#current_year').val();
                let month = $('#current_month').val();
                if(Number(month) > 1){
                    month = Number(month) - 1;
                    if(month.toString().length==1){
                        month = '0'+month;
                    }
                }else{
                    month = 12;
                    year = Number(year) - 1;
                }
                $('#current_month').val(month);
                $('#current_year').val(year);
                let month_string = '';
                if(month.toString().length==1){
                    month_string = '0'+month;
                }else{
                    month_string = month;
                }
                $('#year_month').val(year+'-'+month_string);
                let data = {
                    '_token':'{{csrf_token()}}',
                    'year_month':year+'-'+month,
                };
                var url = '{{route('relax-day.getCalender')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: "json",
                    success: function(result){
                        $('.calender_div').html(result.html);
                    }
                });
            });

            $('.next').click(function (e) {
                e.preventDefault();
                let year = $('#current_year').val();
                let month = $('#current_month').val();
                if(Number(month) != 12){
                    month = Number(month) + 1;
                    if(month.toString().length==1){
                        month = '0'+month;
                    }
                }else{
                    month = 1;
                    year = Number(year) + 1;
                }
                $('#current_month').val(month);
                $('#current_year').val(year);
                let month_string = '';
                if(month.toString().length==1){
                    month_string = '0'+month;
                }else{
                    month_string = month;
                }
                $('#year_month').val(year+'-'+month_string);
                let data = {
                    '_token':'{{csrf_token()}}',
                    'year_month':year+'-'+month,
                };
                var url = '{{route('relax-day.getCalender')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: "json",
                    success: function(result){
                        $('.calender_div').html(result.html);
                    }
                });
            });
            $('#public_holiday_radio').click(function () {
                if($(this).val()==1){
                    $(this).val(0);
                    $(this).prop("checked", false);
                    $('.public_holiday_item').addClass('d-none');
                    $('#daterange').prop('required',false);
                    $('#public_holiday_select').prop('required',false);
                }else{
                    $(this).val(1);
                    $(this).prop("checked", true);
                    $('.public_holiday_item').removeClass('d-none');
                    $('#daterange').prop('required',true);
                    $('#public_holiday_select').prop('required',true);
                }
            });
            $('#relax_day_radio').click(function () {
                if($(this).val()==1){
                    $(this).val(0);
                    $(this).prop("checked", false);
                    $('.relax_day_item').addClass('d-none');
                }else{
                    $(this).val(1);
                    $(this).prop("checked", true);
                    $('.relax_day_item').removeClass('d-none');
                }
            });
            $(document).on('click', '.pointer-td', function(e) {
                let day_number = $(this).data('day-value');
                if(day_number.length==1){
                    day_number = '0'+day_number;
                }
                let current_year = $('#current_year').val();
                let current_month = $('#current_month').val();
                let start_date = current_month+'/'+day_number+'/'+current_year;
                let today = new Date(start_date);
                let nextDate = new Date(new Date().setDate(today.getDate() + 30));
                let end_date = (nextDate.getMonth()+1)+'/'+nextDate.getDate()+'/'+nextDate.getFullYear();
                let data = {
                    '_token':'{{csrf_token()}}',
                    'day_number':day_number,
                    'current_year':current_year,
                    'current_month':current_month,
                    'start_date':start_date,
                    'end_date':end_date,
                };
                console.log(data);
                var url = '{{route('relax-day.getModalForm')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: "json",
                    success: function(result){
                        $('.modal-body').html(result.html);
                        $(function() {
                            $('input[name="daterange"]').daterangepicker({
                                "startDate": result.start_date_in_format,
                                "endDate": result.to_date_in_format,
                                "minDate": result.min_date,
                                "maxDate": result.max_date,
                                opens: 'right'
                            }, function(start, end, label) {
                            });
                        });
                        $('#exampleModalLong').modal('toggle');
                    }
                });
            });
            $('#relax-form').submit(function (e) {
                e.preventDefault();
                if(($('#relax_day_radio').val()==1 || $('#public_holiday_radio').val()==1) || ($('#public_holiday_record_id').val()!='') || ($('#relax_record_id').val()!='')){
                    let data = {
                        '_token':'{{csrf_token()}}',
                        'values':$('#relax-form').serialize()
                    };
                    var url = '{{route('relax-day.store')}}';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: "json",
                        success: function(result){
                            if(result.status==false){
                                $('#error-text').html(result.message);
                                $('.error-div').removeClass('d-none');
                                const myTimeout = setTimeout(function (){
                                    $('.error-div').addClass('d-none');
                                    $('#error-text').html('');
                                }, 2000);
                            }else{
                                $('#exampleModalLong').modal('toggle');
                                let alertHeader = 'Success';
                                let alertStatus = 'success';
                                let alertMessage = result.message;
                                successAlert(alertHeader, alertMessage, alertStatus);
                            }
                        }
                    });
                }else{
                    $('#error-text').html('Please set holiday type!');
                    $('.error-div').removeClass('d-none');
                    const myTimeout = setTimeout(function (){
                        $('.error-div').addClass('d-none');
                        $('#error-text').html('');
                    }, 2000);
                }
            });
        });
    </script>
@endsection
