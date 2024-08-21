@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Apply for Loan / Advance</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('loan.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('loan.update', ['loan' => $loan->uuid]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee <span class="text-danger">*</span></label></label>
                                <select class="form-control" name="user_id" id="user_id">
                                    <option value="{{ $loan->user->uuid }}" {{ $loan->user->uuid == $loan->user->uuid ? "selected" : "" }}>{{ $loan->user->fingerprint_no . " - " . $loan->user->name }}</option>
                                </select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div class="form-group">
                                <label>Type</label>
                                <div class="radio-inline">
                                    <label class="radio">
                                        <input type="radio" {{ $loan->type == \App\Models\Loan::TYPE_LOAN ? "checked" : "" }} name="type" value="Loan">
                                        <span></span>Loan</label>
                                    <label class="radio">
                                        <input type="radio" {{ $loan->type == \App\Models\Loan::TYPE_ADVANCE ? "checked" : "" }} name="type" value="Advance">
                                        <span></span>Advance Salary</label>
                                </div>
                            </div>

                            {{-- Loan Amount --}}
                            <div class="form-group">
                                <label for="loan_amount">Loan / Advance Salary Amount <span class="text-danger">*</span></label></label>
                                <input type="number" class="form-control" name="loan_amount" id="loan_amount" value="{{ $loan->loan_amount }}"
                                       placeholder="Loan Amount" required>
                                @error("loan_amount")
                                <p class="text-danger"> {{ $errors->first("loan_amount") }} </p>
                                @enderror
                            </div>

                            {{-- Loan Tenure --}}
                            <div class="form-group">
                                <label for="loan_tenure">Loan / Advance Tenure(In Month)<span class="text-danger">*</span></label></label>
                                <input type="number" class="form-control" name="loan_tenure" id="loan_tenure" value="{{ $loan->loan_tenure }}"
                                       placeholder="Loan Tenure(In Month)" required/>
                                @error("loan_tenure")
                                <p class="text-danger"> {{ $errors->first("loan_tenure") }} </p>
                                @enderror
                            </div>

                            {{-- Installment Amount --}}
                            <div class="form-group">
                                <label for="installment_amount">Installment Amount <span class="text-danger">*</span></label></label>
                                <input type="number" class="form-control" name="installment_amount" id="installment_amount"
                                       value="{{ $loan->installment_amount }}" placeholder="Installment Amount" readonly required/>
                                @error("installment_amount")
                                <p class="text-danger"> {{ $errors->first("installment_amount") }} </p>
                                @enderror
                            </div>

                            {{-- Remarks --}}
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" name="remarks" placeholder="Remarks" rows="5">{{ $loan->remarks }}</textarea>
                                @error("remarks")
                                <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                @enderror
                            </div>

                            {{-- Approval --}}
                            <div class="form-group">
                                <label for="approval">Approval <span class="text-danger">*</span></label></label>
                                <select class="form-control" name="approval" id="approval">
                                    @can("Authorize Loan / Advance Salary")
                                    <option value="{{ auth()->user()->id }}" {{ is_null($loan->authorized_by) ? "selected" : "" }}>Authorize</option>
                                    @endcan
                                    @can("Approve Loan / Advance Salary")
                                    <option value="{{ auth()->user()->id }}" {{ !is_null($loan->authorized_by) AND is_null($loan->approved_by) ? "selected" : "" }}>Approve</option>
                                    @endcan
                                </select>
                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
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
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Policy</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center"></div>
                    </div>
                </div>
                <form action="{{ route('loan.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-12">
                            <h4>Loan</h4>
                            <hr/>
                            <ul>
                                <li>An employee will be eligible for the loan only after he/she has worked for the company for at least six
                                    months.</li>
                                <li>
                                    An eligible employee may borrow up to 100% of their monthly gross salary. In specific cases, the loan
                                    amount could be two times the amount of the applicant’s monthly gross salary.</li>
                                <li>The loan will be deducted from the monthly salaries of the employee within 12 (twelve) months from
                                    disbursement.</li>
                                <li>An employee cannot apply for a second loan before the first loan is repaid in full.</li>
                                <li>There shall be a minimum of 06 (six) months gap before applying for another loan from the date the
                                    previous loan is repaid in full.</li>
                                <li>After the application of the loan, it will take at least seven business days to do the paperwork.</li>
                                <li>In case of resignation or termination of employment for the employee who has received a loan but has
                                    not fully repaid, the remaining loan amount will be adjusted from that employee’s net payable amount.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4>Advance Salary</h4>
                                <hr/>
                                <ul>
                                    <li>An employee will be eligible for the loan only after he/she has worked for the company for at least six
                                        months.</li>
                                    <li>
                                        An eligible employee may borrow up to 100% of their monthly gross salary. In specific cases, the loan
                                        amount could be two times the amount of the applicant’s monthly gross salary.</li>
                                    <li>The loan will be deducted from the monthly salaries of the employee within 12 (twelve) months from
                                        disbursement.</li>
                                    <li>An employee cannot apply for a second loan before the first loan is repaid in full.</li>
                                    <li>There shall be a minimum of 06 (six) months gap before applying for another loan from the date the
                                        previous loan is repaid in full.</li>
                                    <li>After the application of the loan, it will take at least seven business days to do the paperwork.</li>
                                    <li>In case of resignation or termination of employment for the employee who has received a loan but has
                                        not fully repaid, the remaining loan amount will be adjusted from that employee’s net payable amount.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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
            minViewMode: "months",
            startDate: "+0d"
        });

        $("#loan_amount, #loan_tenure").on("keyup", function (e) {
            var _loan_amount = $("#loan_amount").val();
            var _loan_tenure = $("#loan_tenure").val();

            if(_loan_amount != "" && _loan_tenure != "") {
                var _installment_amount = _loan_amount / _loan_tenure;
                $("#installment_amount").val(_installment_amount.toFixed(2));
                $("#installment_amount").prop("readonly", true);
            }
        });
    </script>
@endsection
