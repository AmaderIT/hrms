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
                    <h3 class="card-title">Attendance Report</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('copy-data.copy') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            {{-- Copy From Year --}}
                            <div class="form-group">
                                <label for="year">Copy From Year</label>
                                <input type="text" class="form-control" name="from_year" id="from_year" autocomplete="off" required/>

                                @error("from_year")
                                <p class="text-danger"> {{ $errors->first("from_year") }} </p>
                                @enderror
                            </div>

                            {{-- Copy To Year --}}
                            <div class="form-group">
                                <label for="year">Copy To Year</label>
                                <input type="text" class="form-control" name="to_year" id="to_year" autocomplete="off" required/>

                                @error("to_year")
                                <p class="text-danger"> {{ $errors->first("to_year") }} </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
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
    $("select").select2({
        theme: "classic",
    });

    $("#from_year, #to_year").datepicker( {
        format: "yyyy",
        startView: "years",
        minViewMode: "years"
    });
</script>
@endsection
