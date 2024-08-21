@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Leave Encashment Generate</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('leave-encashment.leaveEncashmentGenerate') }}" method="GET">
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Office Divisions --}}
                            <div class="form-group">
                                <label for="office_division_id">Division</label>
                                <select class="form-control" id="office_division_id" name="office_division_id">
                                    <option value="all" selected="selected">All Divisions</option>
                                    @foreach($data["officeDivisions"] as $officeDivision)
                                        <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                            {{ $officeDivision->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error("office_division_id")
                                <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                @enderror
                            </div>

                            {{-- Department --}}
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control" name="department_id[]" id="department_id" multiple>
                                    <option value="all" selected >All Departments</option>
                                    @foreach($data["officeDepartments"] as $officeDepartment)
                                        <option value="{{ $officeDepartment->id }}" {{ $officeDepartment->id == old("department_id") ? 'selected' : '' }}>
                                            {{ $officeDepartment->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                @enderror
                            </div>

                            {{-- Eligible Month --}}
                            <div class="form-group">
                                <label for="eligible_month">Eligible Month</label>
                                <select class="form-control" name="eligible_month" id="eligible_month">
                                    @for($i=12;$i>5;$i--)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endfor
                                </select>
                                @error("eligible_month")
                                <p class="text-danger"> {{ $errors->first("eligible_month") }} </p>
                                @enderror
                            </div>

                            {{-- year --}}
                            <div class="form-group">
                                <label for="datepicker">Year</label>
                                <input type="text" class="form-control" name="datepicker" id="datepicker" value="{{date("Y",strtotime("-1 year"))}}" autocomplete="off" required/>
                                @error("year")
                                <p class="text-danger"> {{ $errors->first("year") }} </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="submit" class="btn btn-primary mr-2">Generate</button>
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
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        $('#office_division_id').change(function(){
            var _officeDivisionID = $(this).val();
            let url = "{{ route('report.getDepartmentAndEmployeeByOfficeDivision') }}";
            $.get(url, {office_division_id:_officeDivisionID}, function (response, status) {
                $("#department_id").empty();
                $("#department_id").append('<option value="all" selected="selected">All Departments</option>');
                $.each(response.departments, function(key, value) {
                    $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
                $("select").select2({
                    theme: "classic",
                });
            })
        });
        var old_selected_value = '';
        $('#department_id').on("select2:selecting", function(e) {
            old_selected_value = $(this).val();
        });
        $('#department_id').change(function() {
            let latest_select = $("#department_id option:selected").last().val();
            if(typeof latest_select == 'undefined'){
                $("#department_id option:selected").prop("selected", false);
                $('#department_id').trigger('change.select2');
                $('#department_id option[value="all"]').prop('selected', true);
                $('#department_id').trigger('change.select2');
            }else{
                var missing = false;
                $.each($(this).val(), function(key,val) {
                    if(val == 'all'){
                        missing = true;
                        $.each(old_selected_value, function(key1,val1) {
                            if(val1 == 'all'){
                                missing = false;
                            }
                        });
                    }
                });
                if (missing){
                    $("#department_id option:selected").prop("selected", false);
                    $('#department_id').trigger('change.select2');
                    $('#department_id option[value="all"]').prop('selected', true);
                    $('#department_id').trigger('change.select2');
                }else{
                    $('#department_id option[value="all"]').prop('selected', false);
                    $('#department_id').trigger('change.select2');
                }
            }
        });
        $("select").select2({
            theme: "classic",
        });
        $("#datepicker").datepicker( {
            format: "yyyy",
            startView: "years",
            minViewMode: "years"
        });
    </script>
@endsection
