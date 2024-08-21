@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Late Management</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('late-management.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>

                <!--begin::Form-->
                <form action="{{ route('late-management.update', ['lateDeduction' => $late_deduction->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="Department Name">Department Name</label>
                                <select class="form-control select2" id="board" name="department_id">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($items as $item)
                                        @php($id = old('department_id') ?: $item->id)
                                        <option {{ $item->id == $late_deduction->department_id ? 'selected' : '' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="total_days">Days Late</label>
                                <input type="number" min="1" step="1" class="form-control" name="total_days" value="{{ $late_deduction->total_days }}" required>
                                @error('total_days')
                                <p class="text-danger"> {{ $errors->first("total_days") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="deduction_day">Equivalent Working Day</label>
                                <input type="number" min="1" step="1" class="form-control" name="deduction_day" value="{{ $late_deduction->deduction_day }}" required>
                                @error('deduction_day')
                                <p class="text-danger"> {{ $errors->first("deduction_day") }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="type">Deduction Method</label>
                                <select name="type" class="form-control">
                                    <option value="leave"
                                    @if(isset($late_deduction->type))
                                        {{ $late_deduction->type === \App\Models\LateDeduction::TYPE_LEAVE ? 'selected' : '' }}
                                    @endif
                                        >Leave</option>
                                    <option value="salary"
                                    @if(isset($late_deduction->type))
                                        {{ $late_deduction->type === \App\Models\LateDeduction::TYPE_SALARY ? 'selected' : '' }}
                                    @endif
                                        >Salary</option>

                                </select>
                                @error("type")
                                <p class="text-danger"> {{ $errors->first("type") }} </p>
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

@section('footer-js')
<script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
<script type="text/javascript">
    $("select").select2({
        theme: "classic",
    });
</script>
@endsection
