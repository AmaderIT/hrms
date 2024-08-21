@extends('layouts.app')

@php
    $isEmptyMale = true;
    $isEmptyFemale = true;
    $isEmptyRebate = true;

    $remaining_rate_male = 0;
    $remaining_rate_female = 0;
    $remaining_rate_rebate = 0;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('tax-rule.update', ['tax' => $tax->id]) }}" method="POST">
                @csrf
                {{-- Name --}}
                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-body d-flex align-items-center py-5 py-lg-10">
                            <div class="m-0 text-dark-50 font-weight-bold font-size-lg">
                                <h2>Tax for: {{ $tax->name }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Male --}}
                <div class="mt-n5">
                    <!--begin::Mixed Widget 3-->
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header">
                            <h3 class="card-title">Rate of Income tax (Male)</h3>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_1">
                                        <div data-repeater-list="">
                                            @foreach($tax->rules as $taxRule)
                                                @php($isEmptyMale = false)
                                                @if($taxRule->gender == \App\Models\TaxRule::GENDER_MALE AND $taxRule->slab !== 111)
                                                    <div data-repeater-item="" class="section-repeater">
                                                        <div class="form-group row float-right">
                                                            <div class="col-lg-4 section-repeater-delete-btn">
                                                                <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            {{-- Slab --}}
                                                            <div class="col-lg-6">
                                                                <label>Slab<span class="text-danger">*</span></label>
                                                                <input class="form-control" type="number" name="slab_male"
                                                                       value="{{ $taxRule->slab }}" placeholder="Slab" required/>

                                                                @error("slab_male")
                                                                <p class="text-danger"> {{ $errors->first("slab_male") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Rate --}}
                                                            <div class="col-lg-6">
                                                                <label for="tax_rate_male">Tax Rate <span class="text-danger">*</span></label>
                                                                <select class="form-control rate required" name="tax_rate_male"
                                                                        tabindex="-1" aria-hidden="true">
                                                                    <option value="" selected disabled>Choose an option</option>
                                                                    <option value="0" {{ 0 === $taxRule->rate ? "selected" : "" }}>Nil</option>
                                                                    @for($i = 5; $i <= 50; $i += 5)
                                                                        <option value="{{ $i }}" {{ $i === $taxRule->rate ? "selected" : "" }}>{{ $i }}%</option>
                                                                    @endfor
                                                                </select>

                                                                @error("tax_rate_male")
                                                                <p class="text-danger"> {{ $errors->first("tax_rate_male") }} </p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($taxRule->gender == \App\Models\TaxRule::GENDER_MALE AND $taxRule->slab === 111)
                                                    @php($remaining_rate_male = $taxRule->rate)
                                                @endif
                                            @endforeach

                                            @if($isEmptyMale === true)
                                                <div data-repeater-item="" class="section-repeater">
                                                    <div class="form-group row float-right">
                                                        <div class="col-lg-4 section-repeater-delete-btn">
                                                            <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        {{-- Slab --}}
                                                        <div class="col-lg-6">
                                                            <label>Slab<span class="text-danger">*</span></label>
                                                            <input class="form-control" type="number" name="slab_male" placeholder="Slab" required/>

                                                            @error("slab_male")
                                                            <p class="text-danger"> {{ $errors->first("slab_male") }} </p>
                                                            @enderror
                                                        </div>

                                                        {{-- Rate --}}
                                                        <div class="col-lg-6">
                                                            <label for="tax_rate_male">Tax Rate <span class="text-danger">*</span></label>
                                                            <select class="form-control rate required" name="tax_rate_male"
                                                                    tabindex="-1" aria-hidden="true">
                                                                <option value="" selected disabled>Choose an option</option>
                                                                <option value="0">Nil</option>
                                                                @for($i = 5; $i <= 50; $i += 5)
                                                                    <option value="{{ $i }}">{{ $i }}%</option>
                                                                @endfor
                                                            </select>

                                                            @error("tax_rate_male")
                                                            <p class="text-danger"> {{ $errors->first("tax_rate_male") }} </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Remaining Income --}}
                                        <div class="col-lg-12">
                                            <div class="form-group row">
                                                {{-- Remaining --}}
                                                <div class="col-lg-6">
                                                    <label>Remaining</label>
                                                    <input class="form-control" type="text" placeholder="Remaining total income ---" readonly/>
                                                </div>

                                                {{-- Tax Rate --}}
                                                <div class="col-lg-6">
                                                    <label for="remaining_rate_male">Tax Rate <span class="text-danger">*</span></label>
                                                    <select class="form-control percentage_of_tax required" name="remaining_rate_male" id="remaining_rate_male"
                                                            tabindex="-1" aria-hidden="true">
                                                        <option value="" selected disabled>Choose an option</option>
                                                        <option value="0" {{ 0 === $remaining_rate_male ? "selected" : "" }}>Nil</option>
                                                        @for($i = 5; $i <= 50; $i += 5)
                                                            <option value="{{ $i }}" {{ $i === $remaining_rate_male ? "selected" : "" }}>{{ $i }}%</option>
                                                        @endfor
                                                    </select>

                                                    @error("remaining_rate_male")
                                                    <p class="text-danger"> {{ $errors->first("remaining_rate_male") }} </p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-5">
                                            <div class="col-lg-4">
                                                <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                                    <i class="la la-plus"></i>Add
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 3-->
                </div>

                {{-- Female --}}
                <div class="mt-n5">
                    <!--begin::Mixed Widget 3-->
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header">
                            <h3 class="card-title">Rate of Income tax (Female)</h3>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_2">
                                        <div data-repeater-list="">
                                            @foreach($tax->rules as $taxRule)
                                                @php($isEmptyFemale = false)
                                                @if($taxRule->gender == \App\Models\TaxRule::GENDER_FEMALE AND $taxRule->slab !== 111)
                                                    <div data-repeater-item="" class="section-repeater">
                                                        <div class="form-group row float-right">
                                                            <div class="col-lg-4 section-repeater-delete-btn">
                                                                <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            {{-- Slab --}}
                                                            <div class="col-lg-6">
                                                                <label>Slab <span class="text-danger">*</span></label>
                                                                <input class="form-control" type="number" name="slab_female" value="{{ $taxRule->slab }}"
                                                                       placeholder="Slab" required/>

                                                                @error("slab_female")
                                                                <p class="text-danger"> {{ $errors->first("slab_female") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Rate --}}
                                                            <div class="col-lg-6">
                                                                <label>Tax Rate <span class="text-danger">*</span></label>
                                                                <select class="form-control rate required" name="tax_rate_female" tabindex="-1" aria-hidden="true">
                                                                    <option value="" selected disabled>Choose an option</option>
                                                                    <option value="0" {{ 0 === $taxRule->rate ? "selected" : "" }}>Nil</option>
                                                                    @for($i = 5; $i <= 50; $i += 5)
                                                                        <option value="{{ $i }}" {{ $i === $taxRule->rate ? "selected" : "" }}>{{ $i }}%</option>
                                                                    @endfor
                                                                </select>

                                                                @error("tax_rate_female")
                                                                <p class="text-danger"> {{ $errors->first("tax_rate_female") }} </p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($taxRule->gender == \App\Models\TaxRule::GENDER_FEMALE AND $taxRule->slab === 111)
                                                    @php($remaining_rate_female = $taxRule->rate)
                                                @endif
                                            @endforeach

                                            @if($isEmptyFemale === true)
                                                <div data-repeater-item="" class="section-repeater">
                                                    <div class="form-group row float-right">
                                                        <div class="col-lg-4 section-repeater-delete-btn">
                                                            <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        {{-- Slab --}}
                                                        <div class="col-lg-6">
                                                            <label>Slab<span class="text-danger">*</span></label>
                                                            <input class="form-control" type="number" name="slab_female" placeholder="Slab" required/>

                                                            @error("slab_female")
                                                            <p class="text-danger"> {{ $errors->first("slab_female") }} </p>
                                                            @enderror
                                                        </div>

                                                        {{-- Rate --}}
                                                        <div class="col-lg-6">
                                                            <label for="tax_rate_female">Tax Rate <span class="text-danger">*</span></label>
                                                            <select class="form-control rate required" name="tax_rate_female"
                                                                    tabindex="-1" aria-hidden="true">
                                                                <option value="" selected disabled>Choose an option</option>
                                                                <option value="0">Nil</option>
                                                                @for($i = 5; $i <= 50; $i += 5)
                                                                    <option value="{{ $i }}">{{ $i }}%</option>
                                                                @endfor
                                                            </select>

                                                            @error("tax_rate_female")
                                                            <p class="text-danger"> {{ $errors->first("tax_rate_female") }} </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Remaining Income --}}
                                        <div class="col-lg-12">
                                            <div class="form-group row">
                                                {{-- Remaining --}}
                                                <div class="col-lg-6">
                                                    <label>Remaining</label>
                                                    <input class="form-control" type="text" placeholder="Remaining total income ---" readonly/>

                                                    @error("remaining_female")
                                                    <p class="text-danger"> {{ $errors->first("remaining_female") }} </p>
                                                    @enderror
                                                </div>

                                                {{-- Tax Rate --}}
                                                <div class="col-lg-6">
                                                    <label>Tax Rate <span class="text-danger">*</span></label>
                                                    <select class="form-control percentage_of_tax required" name="remaining_rate_female" tabindex="-1" aria-hidden="true">
                                                        <option value="" selected disabled>Choose an option</option>
                                                        <option value="0" {{ 0 === $remaining_rate_female ? "selected" : "" }}>Nil</option>
                                                        @for($i = 5; $i <= 50; $i += 5)
                                                            <option value="{{ $i }}" {{ $i === $remaining_rate_female ? "selected" : "" }}>{{ $i }}%</option>
                                                        @endfor
                                                    </select>

                                                    @error("rate")
                                                    <p class="text-danger"> {{ $errors->first("rate") }} </p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-5">
                                            <div class="col-lg-4">
                                                <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                                    <i class="la la-plus"></i>Add
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 3-->
                </div>

                <div class="mt-n5">
                    <!--begin::Mixed Widget 3-->
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Body-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group row">
                                        {{-- Eligible Rebate --}}
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="eligible_rebate">Eligible Percentage of Rebate</label>
                                                <input type="number" class="form-control" value="{{ $tax->eligible_rebate }}" name="eligible_rebate" placeholder="Enter eligible rebate here">
                                                @error('eligible_rebate')
                                                <p class="text-danger"> {{ $errors->first("eligible_rebate") }} </p>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Tax Rebate --}}
                                        {{--<div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="tax_rebate">Eligible Rebate Amount</label>
                                                <input type="number" class="form-control" value="{{ $tax->tax_rebate }}" name="tax_rebate" placeholder="Enter tax rebate here">
                                                @error('tax_rebate')
                                                <p class="text-danger"> {{ $errors->first("tax_rebate") }} </p>
                                                @enderror
                                            </div>
                                        </div>--}}

                                        {{-- Minimum Tax Amount --}}
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="min_tax_amount">Minimum Tax Amount <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" value="{{ $tax->min_tax_amount }}" name="min_tax_amount" placeholder="Enter min tax amount here" required>
                                                @error('min_tax_amount')
                                                <p class="text-danger"> {{ $errors->first("min_tax_amount") }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 3-->
                </div>

                {{-- Tax Rebate --}}
                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">Rate of Tax Rebate</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_3">
                                        <div data-repeater-list="">
                                            @foreach($tax->rules as $taxRule)
                                                @if($taxRule->gender == \App\Models\TaxRule::TYPE_REBATE AND $taxRule->slab !== 111)
                                                    @php($isEmptyRebate = false)
                                                    <div data-repeater-item="" class="section-repeater">
                                                        <div class="form-group row float-right">
                                                            <div class="col-lg-4 section-repeater-delete-btn">
                                                                <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            {{-- Slab --}}
                                                            <div class="col-lg-6">
                                                                <label>Slab <span class="text-danger">*</span></label>
                                                                <input class="form-control" type="number" name="slab_rebate" value="{{ $taxRule->slab }}"
                                                                       placeholder="Slab" required/>

                                                                @error("slab_rebate")
                                                                <p class="text-danger"> {{ $errors->first("slab_rebate") }} </p>
                                                                @enderror
                                                            </div>

                                                            {{-- Rate --}}
                                                            <div class="col-lg-6">
                                                                <label>Tax Rate <span class="text-danger">*</span></label>
                                                                <select class="form-control rate required" name="tax_rate_rebate" tabindex="-1" aria-hidden="true">
                                                                    <option value="" selected disabled>Choose an option</option>
                                                                    <option value="0" {{ 0 === $taxRule->rate ? "selected" : "" }}>Nil</option>
                                                                    @for($i = 5; $i <= 50; $i += 5)
                                                                        <option value="{{ $i }}" {{ $i === $taxRule->rate ? "selected" : "" }}>{{ $i }}%</option>
                                                                    @endfor
                                                                </select>

                                                                @error("tax_rate_rebate")
                                                                <p class="text-danger"> {{ $errors->first("tax_rate_rebate") }} </p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($taxRule->gender == \App\Models\TaxRule::TYPE_REBATE AND $taxRule->slab === 111)
                                                    @php($remaining_rate_rebate = $taxRule->rate)
                                                @endif
                                            @endforeach

                                            @if($isEmptyRebate === true)
                                                <div data-repeater-item="" class="section-repeater">
                                                    <div class="form-group row float-right">
                                                        <div class="col-lg-4 section-repeater-delete-btn">
                                                            <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        {{-- Slab --}}
                                                        <div class="col-lg-6">
                                                            <label>Slab<span class="text-danger">*</span></label>
                                                            <input class="form-control" type="number" name="slab_rebate" placeholder="Slab" required/>

                                                            @error("slab_rebate")
                                                            <p class="text-danger"> {{ $errors->first("slab_rebate") }} </p>
                                                            @enderror
                                                        </div>

                                                        {{-- Rate --}}
                                                        <div class="col-lg-6">
                                                            <label for="tax_rate_female">Tax Rate <span class="text-danger">*</span></label>
                                                            <select class="form-control rate required" name="tax_rate_rebate"
                                                                    tabindex="-1" aria-hidden="true">
                                                                <option value="" selected disabled>Choose an option</option>
                                                                <option value="0">Nil</option>
                                                                @for($i = 5; $i <= 50; $i += 5)
                                                                    <option value="{{ $i }}">{{ $i }}%</option>
                                                                @endfor
                                                            </select>

                                                            @error("tax_rate_rebate")
                                                            <p class="text-danger"> {{ $errors->first("tax_rate_rebate") }} </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Remaining Income --}}
                                        <div class="col-lg-12">
                                            <div class="form-group row">
                                                {{-- Remaining --}}
                                                <div class="col-lg-6">
                                                    <label>Remaining</label>
                                                    <input class="form-control" type="text" placeholder="Remaining total income ---" readonly/>

                                                    @error("remaining_rebate")
                                                    <p class="text-danger"> {{ $errors->first("remaining_rebate") }} </p>
                                                    @enderror
                                                </div>

                                                {{-- Tax Rate --}}
                                                <div class="col-lg-6">
                                                    <label>Tax Rate <span class="text-danger">*</span></label>
                                                    <select class="form-control percentage_of_tax required" name="remaining_rate_rebate" tabindex="-1" aria-hidden="true">
                                                        <option value="" selected disabled>Choose an option</option>
                                                        <option value="0" {{ 0 === $remaining_rate_rebate ? "selected" : "" }}>Nil</option>
                                                        @for($i = 5; $i <= 50; $i += 5)
                                                            <option value="{{ $i }}" {{ $i === $remaining_rate_rebate ? "selected" : "" }}>{{ $i }}%</option>
                                                        @endfor
                                                    </select>

                                                    @error("rate")
                                                    <p class="text-danger"> {{ $errors->first("rate") }} </p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-5">
                                            <div class="col-lg-4">
                                                <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                                    <i class="la la-plus"></i>Add
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-n5">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <button type="submit" class="btn btn-primary mr-2">Update</button>
                                            <button type="reset" class="btn btn-secondary">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>
@endsection
