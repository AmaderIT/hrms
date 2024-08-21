@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="mt-n5">
                <form action="{{ route('tax-rule.store') }}" method="POST">
                    @csrf
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header">
                            <h3 class="card-title">Rate of Income tax (Male)</h3>
                        </div>
                        <!--end::Header-->

                        <!--begin::Body-->
                        <input type="hidden" name="gender" value="{{ \App\Models\TaxRule::GENDER_MALE }}"/>
                        <div class="card-body">
                            {{-- Name --}}
                            <div class="col-lg-12">
                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label for="slab">Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" id="slab" placeholder="Name" required/>

                                        @error("name")
                                        <p class="text-danger"> {{ $errors->first("name") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_1">
                                        <div data-repeater-list="">
                                            <div data-repeater-item="" class="section-repeater">
                                                <div class="form-group row float-right">
                                                    <div class="col-lg-4 section-repeater-delete-btn">
                                                        <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    {{-- Amount --}}
                                                    <div class="col-lg-6">
                                                        <label for="amount">Amount <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" name="amount"
                                                               id="amount" placeholder="Amount" required/>

                                                        @error("amount")
                                                        <p class="text-danger"> {{ $errors->first("amount") }} </p>
                                                        @enderror
                                                    </div>

                                                    {{-- Tax Rate --}}
                                                    <div class="col-lg-6">
                                                        <label for="rate">Tax Rate <span class="text-danger">*</span></label>
                                                        <select class="form-control percentage_of_tax required" id="rate" name="rate" tabindex="-1" aria-hidden="true">
                                                            <option value="" selected disabled>Choose an option</option>
                                                            @for($i = 0; $i <= 50; $i += 5)
                                                                <option value="{{ $i }}">{{ $i }}%</option>
                                                            @endfor
                                                        </select>

                                                        @error("rate")
                                                        <p class="text-danger"> {{ $errors->first("rate") }} </p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-lg-12">
                                            <div class="form-group row">
                                                {{-- Remaining --}}
                                                <div class="col-lg-6">
                                                    <label for="remaining">Remaining <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text" name="amount" value="Remaining total income ---"
                                                           id="remaining" placeholder="Remaining total income ---" readonly/>

                                                    @error("amount")
                                                    <p class="text-danger"> {{ $errors->first("amount") }} </p>
                                                    @enderror
                                                </div>

                                                {{-- Tax Rate --}}
                                                <div class="col-lg-6">
                                                    <label for="rate">Tax Rate <span class="text-danger">*</span></label>
                                                    <select class="form-control percentage_of_tax required" id="rate" name="rate" tabindex="-1" aria-hidden="true">
                                                        <option value="" selected disabled>Choose an option</option>
                                                        @for($i = 0; $i <= 50; $i += 5)
                                                            <option value="{{ $i }}">{{ $i }}%</option>
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

                        <!--begin::Footer-->
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-lg-12 text-lg-right">
                                    <button type="reset" class="btn btn-default mr-2">Reset</button>
                                    <button type="submit" class="btn btn-primary mr-2">Save</button>
                                </div>
                            </div>
                        </div>
                        <!--end::Footer-->
                    </div>
                </form>
            </div>
            <div class="mt-n5">
                <form action="{{ route('tax-rule.store') }}" method="POST">
                @csrf
                    <div class="card card-custom card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header">
                            <h3 class="card-title">Rate of Income tax (Female)</h3>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <input type="hidden" name="gender" value="{{ \App\Models\TaxRule::GENDER_FEMALE }}"/>
                        <div class="card-body">
                            {{-- Name --}}
                            <div class="col-lg-12">
                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label for="slab">Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name"
                                               id="slab" placeholder="Name" required/>

                                        @error("slab")
                                        <p class="text-danger"> {{ $errors->first("slab") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="kt_repeater_2">
                                        <div data-repeater-list="">
                                            <div data-repeater-item="" class="section-repeater">
                                                <div class="form-group row float-right">
                                                    <div class="col-lg-4 section-repeater-delete-btn">
                                                        <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    {{-- Slab --}}
                                                    <div class="col-lg-6">
                                                        <label for="slab">Slab <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" name="slab"
                                                               id="slab" placeholder="Slab" required/>

                                                        @error("slab")
                                                        <p class="text-danger"> {{ $errors->first("slab") }} </p>
                                                        @enderror
                                                    </div>

                                                    {{-- Tax Rate --}}
                                                    <div class="col-lg-6">
                                                        <label>Tax Rate <span class="text-danger">*</span></label>
                                                        <select class="form-control percentage_of_tax required" name="rate" tabindex="-1" aria-hidden="true">
                                                            <option value="" selected disabled>Choose an option</option>
                                                            @for($i = 0; $i <= 50; $i += 5)
                                                                <option value="{{ $i }}">{{ $i }}%</option>
                                                            @endfor
                                                        </select>

                                                        @error("institute_id")
                                                        <p class="text-danger"> {{ $errors->first("institute_id") }} </p>
                                                        @enderror
                                                    </div>
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
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-lg-12 text-lg-right">
                                        <button type="reset" class="btn btn-default mr-2">Reset</button>
                                        <button type="submit" class="btn btn-primary mr-2">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>
@endsection
