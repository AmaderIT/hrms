@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Bonus Setting</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('bonus.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('bonus.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-9 offset-md-1">
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Festival Name --}}
                                    <div class="form-group">
                                        <label for="festival_name">Bonus Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" value="{{ old('festival_name') }}" id="festival_name"
                                               class="form-control"
                                               name="festival_name" placeholder="Enter Bonus name here" required>
                                        @error('festival_name')
                                        <p class="text-danger"> {{ $errors->first("festival_name") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    {{-- Bonus Type --}}
                                    <div class="form-group">
                                        <label for="type">Payment Based on <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="type" name="type">
                                            <option
                                                value="{{ \App\Models\Bonus::TYPE_BASIC }}" {{ \App\Models\Bonus::TYPE_BASIC == old("type") }}>
                                                {{ \App\Models\Bonus::TYPE_BASIC }}
                                            </option>
                                            <option
                                                value="{{ \App\Models\Bonus::TYPE_GROSS }}" {{ \App\Models\Bonus::TYPE_GROSS == old("type") }}>
                                                {{ \App\Models\Bonus::TYPE_GROSS }}
                                            </option>
                                        </select>

                                        @error('type')
                                        <p class="text-danger"> {{ $errors->first("type") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="effective_date">Calculation Date<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control effective_date datepicker" style="width: 100%"
                                               value="{{ old('effective_date') }}"
                                               name="effective_date" autocomplete="off" required/>
                                        @error("effective_date")
                                        <p class="text-danger"> {{ $errors->first("effective_date") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employment_period_one">Employment Period <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="employment_period_one"
                                                name="employment_period_one">
                                            @foreach(\App\Models\Bonus::EMPLOYMENT_PERIODS as $periodId => $periodName)
                                                @if($periodId != \App\Models\Bonus::SIX_MONTH) @continue @endif
                                                <option
                                                    value="{{ $periodId }}" {{ ($periodId == 6? 'selected': '') }}>{{ $periodName }}</option>
                                            @endforeach
                                        </select>

                                        @error('type')
                                        <p class="text-danger"> {{ $errors->first("type") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="percentage_one">Percentage <span
                                                class="text-danger">*</span></label>
                                        <input type="number" value="{{ old('percentage_one') }}" min="1" max="100" id="percentage_one"
                                               class="form-control"
                                               name="percentage_one" placeholder="100"
                                               required>
                                        @error('percentage_one')
                                        <p class="text-danger"> {{ $errors->first("percentage_one") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employment_period_two">Employment Period <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="employment_period_two"
                                                name="employment_period_two">
                                            @foreach(\App\Models\Bonus::EMPLOYMENT_PERIODS as $periodId => $periodName)
                                                @if($periodId != \App\Models\Bonus::THREE_MONTH) @continue @endif
                                                <option
                                                    value="{{ $periodId }}" {{ ($periodId == 3? 'selected': '') }}>{{ $periodName }}</option>
                                            @endforeach
                                        </select>

                                        @error('type')
                                        <p class="text-danger"> {{ $errors->first("type") }} </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="percentage_two">Percentage <span
                                                class="text-danger">*</span></label>
                                        <input type="number" value="{{ old('percentage_two') }}" min="1" max="100" id="percentage_two"
                                               class="form-control"
                                               name="percentage_two" placeholder="50"
                                               required>
                                        @error('percentage_two')
                                        <p class="text-danger"> {{ $errors->first("percentage_two") }} </p>
                                        @enderror
                                    </div>
                                </div>
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

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript">
        $(".datepicker").datepicker({
            format: "yyyy-mm-dd",
            startView: "days",
            minViewMode: "days"
        });
    </script>
@endsection
