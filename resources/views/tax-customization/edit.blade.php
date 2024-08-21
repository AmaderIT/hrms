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
                    <h3 class="card-title">Edit Tax Customization</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('tax-customization.update', ['taxCustomization' => $taxCustomization->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee <span class="text-danger">*</span></label>
                                <select class="form-control" name="user_id" id="user_id">
                                    <option selected disabled>Select an option</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $user->id == $taxCustomization->user_id ? 'selected' : '' }}>
                                            {{ $user->name . " - " . $user->fingerprint_no }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Requested Amount --}}
                            <div class="form-group">
                                <label for="requested_amount">Requested Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="requested_amount" value="{{ $taxCustomization->requested_amount }}" placeholder="Enter requested amount here" required>
                                @error("requested_amount")
                                <p class="text-danger"> {{ $errors->first("requested_amount") }} </p>
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
