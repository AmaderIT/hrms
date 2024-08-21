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
                    <h3 class="card-title">Employee Salary</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('salary.generateSalary') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8 offset-lg-2">
                                {{-- Month and Year --}}
                                <div class="form-group">
                                    <label for="year">Month</label>
                                    <input type="text" class="form-control" name="month_and_year" id="datepicker" autocomplete="off" required/>
                                    @error('year')
                                    <p class="text-danger"> {{ $errors->first("year") }} </p>
                                    @enderror
                                </div>

                                {{-- Manage Leave --}}
                                <div class="form-group">
                                    <label class="checkbox checkbox-success">
                                        <input type="checkbox" name="manageLeave" id="manageLeave" />
                                        <span></span> &nbsp; Calculate Including Leaves
                                    </label>
                                    @error('year')
                                    <p class="text-danger"> {{ $errors->first("year") }} </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-8 offset-lg-2 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <a href="#" class="btn btn-primary mr-2" onclick="salaryPrepareAlert('{{ route('salary.generateSalary') }}')">Generate</a>
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
    $("select").select2({
        theme: "classic",
    });

    $("#datepicker").datepicker( {
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    });
</script>
@endsection
