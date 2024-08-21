@extends('layouts.app')

@php
    $isEmptyMale = true;
    $isEmptyFemale = true;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Pay Grade</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('paygrade.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('paygrade.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="name">Pay Grade Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Pay grade name" {{ old('name') }} required>
                                @error('name')
                                    <p class="text-danger"> {{ $errors->first("name") }} </p>
                                @enderror
                            </div>

                            {{-- Salary Range --}}
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="range_start_from">Salary Range <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="range_start_from" name="range_start_from" {{ old('range_start_from') }}>
                                        @error('range_start_from')
                                        <p class="text-danger"> {{ $errors->first("range_start_from") }} </p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="range_end_to">To <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="range_end_to" name="range_end_to" {{ old('range_end_to') }}>
                                        @error('range_end_to')
                                        <p class="text-danger"> {{ $errors->first("range_end_to") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="percentage_of_basic">Basic Salary (In Percentage of Gross Salary) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Basic Salary (In Percentage of Gross Salary)"
                                       name="percentage_of_basic" list="percentage_of_basic"/>
                                <datalist id="percentage_of_basic">
                                    @for($i = 5; $i <= 100; $i += 5)
                                        <option {{ old("percentage_of_basic") == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </datalist>
                                @error("percentage_of_basic")
                                <p class="text-danger"> {{ $errors->first("percentage_of_basic") }} </p>
                                @enderror
                            </div>

                            {{-- Based On --}}
                            <input type="hidden" name="based_on" value="{{ \App\Models\PayGrade::BASED_ON_GROSS }}" required/>

                            {{-- Overtime Formula --}}
                            <div class="form-group">
                                <label for="overtime_formula">Overtime Formula</label>
                                <input type="text" class="form-control" id="overtime_formula" name="overtime_formula" {{ old('overtime_formula') }}>
                                <span class="form-text text-muted">For basic: "$basic", for gross: "$gross", for overtime hours: "$othours". Example: "($basic / 208) * 2 * $othours"</span>
                                @error('overtime_formula')
                                <p class="text-danger"> {{ $errors->first("overtime_formula") }} </p>
                                @enderror
                            </div>

                            {{-- Weekend Allowance Formula --}}
                            <div class="form-group">
                                <label for="weekend_allowance_formula">Weekend Allowance Formula</label>
                                <input type="text" class="form-control" id="weekend_allowance_formula" name="weekend_allowance_formula" {{ old('weekend_allowance_formula') }}>
                                <span class="form-text text-muted">For basic: "$basic", for gross: "$gross". Example: "($basic / 30) * 2"</span>
                                @error('weekend_allowance_formula')
                                <p class="text-danger"> {{ $errors->first("weekend_allowance_formula") }} </p>
                                @enderror
                            </div>

                            {{-- Holiday Allowance Formula --}}
                            <div class="form-group">
                                <label for="holiday_allowance_formula">Holiday Allowance Formula</label>
                                <input type="text" class="form-control" id="holiday_allowance_formula" name="holiday_allowance_formula" {{ old('holiday_allowance_formula') }}>
                                <span class="form-text text-muted">For basic: "$basic", for gross: "$gross". Example: "($basic / 30) * 3"</span>
                                @error('holiday_allowance_formula')
                                <p class="text-danger"> {{ $errors->first("holiday_allowance_formula") }} </p>
                                @enderror
                            </div>

                            <div class="form-group row">
                                @if($data["earnings"]->count() > 0)
                                    <div class="col-lg-12 col-form-label" style="border-bottom: 1px solid black">
                                        <h3 class="mt-7">Earning</h3>
                                        <div class="checkbox-inline">
                                            <div class="col-12 col-form-label">
                                                <div class="checkbox-list">
                                                    <div class="row pt-5 pb-2" style="border-bottom: 1px solid black">
                                                        <div class="col-lg-3"><b>Earning Name</b></div>
                                                        <div class="col-lg-2"><b>Type</b></div>
                                                        <div class="col-lg-2"><b>Value</b></div>
                                                        <div class="col-lg-2"><b>Tax Exempted (Amount)</b></div>
                                                        <div class="col-lg-2"><b>Tax Exempted (Percentage)</b></div>
                                                        <div class="col-lg-1"><b>Cash</b></div>
                                                    </div>
                                                    @foreach($data["earnings"] as $key => $earning)
                                                        <div class="row pt-5">
                                                            <div class="col-lg-3">
                                                                <input type="hidden" name="earning_id[]" value="{{ $earning->id }}">
                                                                {{ $earning->name }}
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <select name="earning_type[]" style="width: 100%;">
                                                                    <option value="" selected>Type</option>
                                                                    <option value="{{ \App\Models\PayGradeEarning::TYPE_FIXED }}">Fixed</option>
                                                                    <option value="{{ \App\Models\PayGradeEarning::TYPE_PERCENTAGE }}">Percentage</option>
                                                                    <option value="{{ \App\Models\PayGradeEarning::TYPE_REMAINING }}">Remaining</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <input type="text" style="width: 100%;" name="earning_value[]" placeholder="Value"/>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <input type="text" style="width: 100%;" name="earning_tax_exempted[]" placeholder="Amount"/>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <input type="text" style="width: 100%;" name="earning_tax_exempted_percentage[]" placeholder="Percentage"/>
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <label class="checkbox">
                                                                    <input type="hidden" name="earning_non_taxable[{{ $key }}]" value="0"/>
                                                                    <input type="checkbox" style="width: 100%;" name="earning_non_taxable[{{ $key }}]" value="1"/>
                                                                    <span></span> Cash
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @error("earning_id")
                                    <p class="text-danger"> {{ $errors->first("earning_id") }} </p>
                                    @enderror
                                </div>
                                @endif

                                @if($data["deductions"]->count() > 0)
                                    <div class="col-lg-12 col-form-label" style="border-bottom: 1px solid black">
                                        <h3 class="mt-7">Deduction</h3>
                                        <div class="checkbox-inline">
                                            <div class="col-12 col-form-label">
                                                <div class="checkbox-list">
                                                    <div class="row pt-5 pb-2" style="border-bottom: 1px solid black">
                                                        <div class="col-lg-3"><b>Deduction Name</b></div>
                                                        <div class="col-lg-2"><b>Deduction Type</b></div>
                                                        <div class="col-lg-3"><b>Deduction Value</b></div>
                                                    </div>
                                                    @foreach($data["deductions"] as $key => $deductions)
                                                        <div class="row pt-5">
                                                            <div class="col-lg-3">
                                                                <input type="hidden" name="deduction_id[]" value="{{ $deductions->id }}">
                                                                {{ $deductions->name }}
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <select name="deduction_type[]" style="width: 100%;">
                                                                    <option value="" selected>Type</option>
                                                                    <option value="{{ \App\Models\PayGradeDeduction::TYPE_FIXED }}">Fixed</option>
                                                                    <option value="{{ \App\Models\PayGradeDeduction::TYPE_PERCENTAGE }}">Percentage</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <input type="text" style="width: 100%;" name="deduction_value[]" placeholder="Value"/>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @error("deduction_id")
                                        <p class="text-danger"> {{ $errors->first("deduction_id") }} </p>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            @if(isset($data["tax"]->name))
                                <div class="form-group row">
                                    <div class="col-lg-6 col-form-label">
                                        <p class="mb-5">Tax</p>
                                        <label class="checkbox ml-3">
                                            <input type="checkbox" name="tax_id" value="1" {{ old("tax_id") != \App\Models\Tax::STATUS_INACTIVE ? "checked" : "" }}>
                                            <span></span>&nbsp;&nbsp;&nbsp;<a href="#" data-toggle="modal" data-target="#taxRuleModal">Tax for: {{ $data["tax"]->name }}</a>
                                        </label>
                                    </div>
                                </div>
                            @endif
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

    {{-- Tax Modal --}}
    @if(isset($data["tax"]->name))
        <div class="modal fade" id="taxRuleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeLg" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Tax for: {{ $data["tax"]->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-responsive-lg">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-lg-300px">For Male</th>
                                    <th scope="col" class="w-lg-200px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data["tax"]->rules as $taxRule)
                                    @php($isEmptyMale = false)
                                    @if($taxRule->gender == \App\Models\TaxRule::GENDER_MALE AND $taxRule->slab !== 111)
                                        <tr>
                                            <th scope="row">{{ $taxRule->slab }}</th>
                                            <td>{{ $taxRule->rate }}%</td>
                                        </tr>
                                    @elseif($taxRule->gender == \App\Models\TaxRule::GENDER_MALE AND $taxRule->slab === 111)
                                        <tr>
                                            <th scope="row">Remaining</th>
                                            <td>{{ $taxRule->rate }}%</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <table class="table table-bordered table-responsive-lg">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-lg-300px">For Female</th>
                                    <th scope="col" class="w-lg-200px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data["tax"]->rules as $taxRule)
                                    @php($isEmptyFemale = false)
                                    @if($taxRule->gender == \App\Models\TaxRule::GENDER_FEMALE AND $taxRule->slab !== 111)
                                        <tr>
                                            <th scope="row">{{ $taxRule->slab }}</th>
                                            <td>{{ $taxRule->rate }}%</td>
                                        </tr>
                                    @elseif($taxRule->gender == \App\Models\TaxRule::GENDER_FEMALE AND $taxRule->slab === 111)
                                        <tr>
                                            <th scope="row">Remaining</th>
                                            <td>{{ $taxRule->rate }}%</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
