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
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Public Holiday</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('public-holiday.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('public-holiday.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <select class="form-control" name="holiday_id">
                                    <option selected disabled>Choose an option</option>
                                    @foreach($holidays as $holiday)
                                        <option value="{{ $holiday->id }}">{{ $holiday->name }}</option>
                                    @endforeach
                                </select>
                                @error("holiday_id")
                                    <p class="text-danger">{{ $errors->first("holiday_id") }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" name="from_date" required>
                                @error("from_date")
                                    <p class="text-danger"> {{ $errors->first("from_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" required>
                                @error("to_date")
                                    <p class="text-danger"> {{ $errors->first("to_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="3" placeholder="Type remarks here"></textarea>
                                @error("remarks")
                                    <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                @enderror
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
