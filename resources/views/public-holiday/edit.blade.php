@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Public Holiday</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('public-holiday.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('public-holiday.update', ['publicHoliday' => $publicHoliday->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="holiday_id">Name</label>
                                <select class="form-control" name="holiday_id">
                                    <option selected disabled>Choose an option</option>
                                    @foreach($holidays as $holiday)
                                    <option value="{{ $holiday->id }}" {{ $holiday->id == $publicHoliday->holiday_id ? 'selected' : '' }}>{{ $holiday->name }}</option>
                                    @endforeach
                                </select>
                                @error("holiday_id")
                                <p class="text-danger"> {{ $errors->first("holiday_id") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" name="from_date" value="{{ date('Y-m-d', strtotime($publicHoliday->from_date)) }}">

                                @error("from_date")
                                <p class="text-danger"> {{ $errors->first("from_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" value="{{ date('Y-m-d', strtotime($publicHoliday->to_date)) }}"/>

                                @error("to_date")
                                <p class="text-danger"> {{ $errors->first("to_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="3">{{ $publicHoliday->remarks }}</textarea>
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
                                <button type="submit" class="btn btn-primary mr-2">Update</button>
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
