@php $readOnly = $isApproved? 'readonly': ''; @endphp
@extends('layouts.app')

@section("top-css")
    <style>
        #advance-policy {
            display: none;
        }

        .policy_ul {
            padding: 0;
        }

        .policy_ul li {
            list-style: none;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .policy_ul li i {
            font-size: 13px;
            display: inline-block;
            color: #3F4254;
        }

        .color-red {
            color: red !important;
        }

        .color-green {
            color: #00d300 !important;
        }

        .policy_violation_p {
            margin-top: 5px;
            font-size: 15px;
            display: none;
        }

        .installment_table {
            position: relative;
        }

        .installment_table tr {
            position: relative;
        }

        .installment_table td {
            text-align: center;
            vertical-align: middle;
        }

        .installment_table input {
            width: 100%;
            padding: 4px 5px;
        }

        .installment_table select {
            width: 100%;
            padding: 4px 0;
        }

        .installment_table .select2-container {
            width: 100% !important;
            text-align: left;
        }

        .add_new_icon {
            position: absolute;
            right: 13px;
            bottom: -20px;
            color: #fff;
            background: green;
            padding: 6px 8px;
            cursor: pointer;
        }

        .remove_icon {
            position: absolute;
            right: 10px;
            bottom: 13px;
            color: #fff;
            background: #d7041a;
            cursor: pointer;
            z-index: 9;
            border-radius: 2px;
            padding: 8px 6px;
        }

        .read_only_date {
            display: block;
            width: 100%;
            height: calc(1.5em + 1.3rem + 2px);
            padding: 0.65rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #3F4254;
            background-color: #ffffff;
            background-clip: padding-box;
            border: 1px solid #E4E6EF;
            border-radius: 0.42rem;
        }

        .datepicker {
            width: auto;
        }

        .month_warning {
            border: 1px solid #e38d8d;
            background: #ff00000d;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Loan / Advance Applications</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('requested-loan-advance.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form
                    action="{{ !$isApproved? route('requested-loan-advance.update', ['loan' => $loan->uuid]): route('requested-loan-advance.instalment-update', ['loan' => $loan->uuid]) }}"
                    id="policy-form" method="POST">
                    <input type="hidden" name="loan_uuid" value="{{ $loan->uuid }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="office_division_id">Office Division<span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="office_division_id" name="office_division_id"
                                            required="required">
                                        <option value="">Choose an option</option>
                                        @foreach($officeDivisions as $officeDivisionId => $officeDivisionName)
                                            @if($isApproved)
                                                @if($officeDivisionId != $loan->office_division_id)
                                                    @continue
                                                @endif
                                            @endif
                                            <option
                                                value="{{ $officeDivisionId }}" {{ $officeDivisionId == $loan->office_division_id ? 'selected' : '' }}>
                                                {{ $officeDivisionName }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error("office_division_id")
                                    <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                {{-- Department --}}
                                <div class="form-group">
                                    <label for="department_id">Department<span class="text-danger">*</span></label>
                                    <select class="form-control" name="department_id" id="department_id"
                                            required="required" {{ $readOnly }}>
                                        <option value="">Choose an option</option>
                                        @foreach($departments as $departmentId => $departmentName)
                                            @if($isApproved)
                                                @if($departmentId != $loan->department_id)
                                                    @continue
                                                @endif
                                            @endif
                                            <option
                                                value="{{ $departmentId }}" {{ $departmentId == $loan->department_id ? 'selected' : '' }}>
                                                {{ $departmentName }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('department_id')
                                    <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_id">Employee<span class="text-danger">*</span></label>
                                    <select class="form-control" id="user_id" name="user_id"
                                            required="required" {{ $readOnly }}>
                                        <option value="">Choose an option</option>
                                        @foreach($users as $user)
                                            @if($isApproved)
                                                @if($user->id != $loan->user_id)
                                                    @continue
                                                @endif
                                            @endif
                                            <option
                                                value="{{ $user->id }}" {{ ($user->id == $loan->user_id)? 'selected': '' }}>
                                                {{ $user->fingerprint_no. ' - ' .$user->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error("user_id")
                                    <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                {{-- Type --}}
                                <div class="form-group">
                                    <label>Type</label>
                                    <div class="radio-inline">
                                        <label class="radio">
                                            <input onclick="typeWisePolicy(this)" class="type" type="radio"
                                                   {{ $isApproved? 'disabled': '' }}
                                                   {{ $loan->type == 'Loan' ? 'checked': '' }}
                                                   name="type" value="Loan">
                                            <span></span>Loan</label>
                                        <label class="radio">
                                            <input type="radio" name="type" value="Advance" class="type"
                                                   {{ $isApproved? 'disabled': '' }}
                                                   {{ $loan->type == 'Advance' ? 'checked': '' }}
                                                   onclick="typeWisePolicy(this)">
                                            <span></span>Advance Salary</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="loan_tenure">Installment Start Month<span
                                            class="text-danger">*</span></label></label>

                                    @if($isApproved)
                                        <p class="read_only_date">{{ !empty($loan)? $loan->installment_start_month: old('installment_start_month') }}</p>
                                    @else
                                        <input type="text" class="form-control installment_start_month datepicker"
                                               value="{{ !empty($loan)? $loan->installment_start_month: old('installment_start_month') }}"
                                               name="installment_start_month" autocomplete="off" required/>
                                    @endif
                                    @error("installment_start_month")
                                    <p class="text-danger"> {{ $errors->first("installment_start_month") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="loan_amount">Amount <span class="text-danger">*</span></label></label>
                                    <input type="number" class="form-control" name="loan_amount" id="loan_amount"
                                           {{ $readOnly }}
                                           value="{{ !empty($loan)? $loan->loan_amount: old('loan_amount') }}"
                                           min="100"
                                           placeholder="Loan Amount" required>
                                    @error("loan_amount")
                                    <p class="text-danger"> {{ $errors->first("loan_amount") }} </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="loan_tenure">Total Installment(In Month)<span
                                            class="text-danger">*</span></label></label>
                                    <input type="number" class="form-control" name="loan_tenure" id="loan_tenure"
                                           {{ $readOnly }}
                                           min="1" max="60"
                                           placeholder="Loan Tenure(In Month)"
                                           value="{{ !empty($loan)? $loan->loan_tenure: old('loan_tenure') }}"
                                           required/>
                                    @error("loan_tenure")
                                    <p class="text-danger"> {{ $errors->first("loan_tenure") }} </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" name="remarks" placeholder="Remarks" {{ $readOnly }}
                                    rows="1">{{ !empty($loan)? $loan->remarks: old('remarks') }}</textarea>
                                    @error("remarks")
                                    <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="checkbox">
                                        <input id="accept_policy" type="checkbox" name="accept_policy" value="Y"
                                               {{ $isApproved? 'disabled': '' }}
                                               {{ !empty($loan)? 'checked': '' }}
                                               required>
                                        <span></span> &nbsp; Accept All Policy</label>
                                    @error("accept_policy")
                                    <p class="text-danger"> {{ $errors->first("accept_policy") }} </p>
                                    @enderror
                                    <p class="text-danger policy_violation_p">Some of Policy could not meet with your
                                        application</p>
                                </div>
                            </div>
                            <div class="col-md-12" id="installment_table_main" style="position: relative">
                                <table class="installment_table table table-bordered">
                                    <thead>
                                    <tr>
                                        <th width="10%">SL No.</th>
                                        <th width="30%">Month</th>
                                        <th width="30%">Installment Amount</th>
                                        <th width="30%">Remark</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @php $sl = 1 @endphp
                                    @foreach($loan->userLoans as $instalment)
                                        @php
                                            $month = !empty($instalment->month)? date('m-Y', strtotime(date('d-m-Y', strtotime("01-$instalment->month-$instalment->year")))): null;
                                            $background = (($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))? '#0080003b': '#fff';
                                            $title = (($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))? 'Paid': 'Unpaid';
                                        @endphp
                                        <tr data-id="{{ $sl }}"
                                            data-editable="{{ (($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))? 0: 1 }}"
                                            class="installment_tr" style="background: {{ $background }}"
                                            title="{{ $title }}">
                                            <td class="installment_serial" data-serial="{{ $sl }}">{{ $sl }}</td>
                                            <td>
                                                @if(($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))
                                                    <p class="read_only_date"
                                                       style="text-align: left; margin-bottom: 0">{{ $month }}</p>
                                                    <input type="hidden" class="form-control datepicker"
                                                           name="month[{{ $sl }}]"
                                                           value="{{ $month }}"
                                                           autocomplete="off" required/>
                                                @else
                                                    <input type="text" class="form-control datepicker datepicker_ins"
                                                           onchange="reorganizeInstalmentMonths(this)"
                                                           name="month[{{ $sl }}]"
                                                           value="{{ $month }}"
                                                           autocomplete="off" required/>
                                                @endif
                                                <input type="hidden" name="ids[{{ $sl }}]"
                                                       value="{{ $instalment->id }}">
                                            </td>
                                            <td><input type="number" data-serial="{{ $sl }}" min="0"
                                                       onchange="return reorganizeInstalmentAmountForManualChange(this)"
                                                       name="amount_paid[{{ $sl }}]"
                                                       value="{{ $instalment->amount_paid? round($instalment->amount_paid, 2): 0 }}"
                                                       required step="any"
                                                       {{ (($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))? 'readonly': '' }}
                                                       class="{{ (($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))? 'amount_paid_class_paid': 'amount_paid_class' }}"
                                                       max="{{ $loan->loan_amount }}"></td>
                                            <td>
                                                <input type="text" name="remark[{{ $sl }}]"
                                                       value="{{ $instalment->remark }}" {{ (($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING))? 'readonly': '' }}>
                                                @if(!(($instalment->status == \App\Models\UserLoan::DEDUCTED) || ($instalment->status == \App\Models\UserLoan::DEDUCTION_PENDING)))
                                                    <i class="fa fa-minus remove_icon"
                                                       onclick="removeInstallment(this); reorganizeInstallmentAmount(this)"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        @php $sl++ @endphp
                                    @endforeach
                                    </tbody>
                                </table>
                                <i class="fa fa-plus add_new_icon"></i>
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
                    <div class="card-body" id="loan-policy">
                        <div class="col-md-12">
                            <h4>Loan Policy</h4>
                            <hr/>
                            <ul class="policy_ul">
                                @foreach(\App\Models\Loan::LOAN_POLICIES as $loanPolicyKey => $loanPolicy)
                                    <li id="loan-policy-{{ $loanPolicyKey }}"><i class="fa fa-angle-double-right"></i>
                                        <span>{{ $loanPolicy }}</span></li>{{--fa-check--}}{{--fa-times--}}
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer" id="advance-policy">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4>Salary Advance Policy</h4>
                                <hr/>
                                <ul class="policy_ul">
                                    @foreach(\App\Models\Loan::ADVANCE_POLICIES as $advancePolicyKey => $advancePolicy)
                                        <li id="loan-policy-{{ $advancePolicyKey }}"><i
                                                class="fa fa-angle-double-right"></i> <span>{{ $advancePolicy }}</span>
                                        </li>
                                    @endforeach
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
        /*$("select").select2({
            theme: "classic",
        });*/

        let isApproved = '{!! $isApproved !!}';

        $("#user_id").select2({
            theme: "classic",
            placeholder: "Select an option",
        });

        let isInputOnLoanAmount = false;
        let isInputOnInstallmentStartMonth = false;
        let isInputOnLoanTenure = false;

        $('#loan_amount').on('change', function () {
            isInputOnLoanAmount = true;
        });

        $('.installment_start_month').on('change', function () {
            isInputOnInstallmentStartMonth = true;
        });

        $('#loan_tenure').on('change', function () {
            isInputOnLoanTenure = true;
        });

        $('.type').on('change', function () {
            checkPolicyViolation(true);
        });

        $(document).ready(function () {
            let edit = '{!! !empty($loan)? true: false !!}';
            let loanType = '{!! !empty($loan->type)? $loan->type: false !!}';

            checkPolicyViolation(true);

            if (!edit) {
                generateInstallmentTable();
            }

            if (edit) {
                if (loanType == 'Loan') {
                    $('#loan-policy').show();
                    $('#advance-policy').hide();
                } else {
                    $('#loan-policy').hide();
                    $('#advance-policy').show();
                }
            }
        });

        $(".datepicker").datepicker({
            format: "mm-yyyy",
            startView: "months",
            minViewMode: "months",
            startDate: "+0d"
        });

        $("#loan_amount, #loan_tenure").on("keyup", function (e) {
            var _loan_amount = $("#loan_amount").val();
            var _loan_tenure = $("#loan_tenure").val();

            if (_loan_amount != "" && _loan_tenure != "") {
                var _installment_amount = _loan_amount / _loan_tenure;
                $("#installment_amount").val(_installment_amount.toFixed(2));
                $("#installment_amount").prop("readonly", true);
            }
        });

        function typeWisePolicy(em) {
            if ($(em).val() == 'Loan') {
                $('#loan-policy').show();
                $('#advance-policy').hide();
            } else {
                $('#advance-policy').show();
                $('#loan-policy').hide();
            }
        }

        $('#accept_policy').on('click', function (e) {
            if ($('#accept_policy').prop('checked')) {
                checkPolicyViolation();
            }
        });

        $('#policy-form').on('submit', function (e) {
            let totalAmount = $('#loan_amount').val();
            let sumOfTotalAmount = 0;
            let sumOfTotalPaidAmount = 0;

            $('.amount_paid_class').each(function () {
                sumOfTotalAmount += parseInt(this.value);
            });

            $('.amount_paid_class_paid').each(function () {
                sumOfTotalPaidAmount += parseInt(this.value);
            });

            if (totalAmount != (sumOfTotalAmount + sumOfTotalPaidAmount)) {
                swal.fire({
                    title: 'Amount is not valid!!',
                    text: "Total loan amount and sum of Installment amount should be same!! " + "Found: " + (sumOfTotalAmount + sumOfTotalPaidAmount) + ", Require: " + totalAmount,
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success"
                    }
                })
                return false;
            }

            // Calculate duplicate month
            let all_months = [];
            let duplicate_month_rows = [];
            $(".installment_table tbody tr").each(function (fKey, fEl) {
                let ins_month = $(fEl).find(".datepicker_ins").val();

                if ($.inArray(ins_month, all_months) != -1) {
                    duplicate_month_rows.push(ins_month);
                    $(fEl).find(".datepicker_ins").addClass('month_warning');
                } else {
                    $(fEl).find(".datepicker_ins").removeClass('month_warning');
                }

                all_months.push(ins_month);
            });

            if (duplicate_month_rows.length > 0) {
                swal.fire({
                    title: 'Duplicate Installment Month!!',
                    text: "Installment Month should be Unique!!",
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success"
                    }
                })
                return false;
            } else {
                $(".installment_table tbody tr").find('.datepicker_ins').removeClass('month_warning');
            }
        });

        /** Re-organize serial number **/
        function reorganizeSerial(className) {
            $('.' + className).each(function (index, val) {
                $(this).html(index + 1);
                $(this).attr('data-serial', index + 1);
            });
        }

        /** Remaining installment amount calculation **/
        function calculateRemainInstallmentAmount() {
            let installmentTrSelector = $(".installment_table tbody tr");
            let total_amount = parseInt($('#loan_amount').val());
            let total_installment_amount = 0;
            let total_paid_amount = 0;

            installmentTrSelector.each(function (fKey, fElm) {

                if ($(fElm).data("editable") == 0) {
                    total_paid_amount += parseInt($(fElm).find(".amount_paid_class_paid").val());
                } else {
                    let final_amount = parseInt($(fElm).find(".amount_paid_class").val());
                    total_installment_amount += final_amount;
                }
            });

            let remaining_amount = total_amount - total_installment_amount - total_paid_amount;
            ///console.log(remaining_amount, total_amount, total_installment_amount, total_paid_amount);

            if (remaining_amount > 0) {
                let prev_last_ins_amount = $('.installment_table tbody tr:last').find(".amount_paid_class").val();
                $('.installment_table tbody tr:last').find(".amount_paid_class").val(parseInt(prev_last_ins_amount) + remaining_amount);
            }
        }

        /** Re-organize Installment Amount **/
        function reorganizeInstallmentAmount(el, newRow = false) {
            let installmentTrSelector = $(".installment_table tbody tr");
            let total_row = installmentTrSelector.length;
            let total_remaining_row = installmentTrSelector.find('.amount_paid_class').length;
            let total_amount = $("#loan_amount").val();
            let total_paid_amount = 0;
            let total_paid_row = 0;

            installmentTrSelector.each(function (pKey, pElm) {
                if ($(pElm).data("editable") == 0) {
                    total_paid_amount += parseInt($(pElm).find(".amount_paid_class_paid").val());
                    total_paid_row++;
                }
            });

            if (newRow) {
                let instalmentAmount = ((total_amount - total_paid_amount) / (total_row - total_paid_row));
                $(".amount_paid_class").val(parseInt(instalmentAmount));

            } else if (total_row == 1 || total_remaining_row == 1) {
                $(".amount_paid_class").val(total_amount - total_paid_amount);

            } else {

                let deleted_row_id = $(el).parent().parent().data("id");
                let deleted_row_amount = $(el).parent().parent().find(".amount_paid_class").val();

                let preRows = [];
                let postRows = [];

                installmentTrSelector.each(function (key, elm) {
                    if ($(elm).data("editable") == 0) {
                        return true;
                    }

                    if ($(elm).data("id") > deleted_row_id) {
                        postRows.push(elm);
                    } else {
                        preRows.push(elm);
                    }
                });//console.log(postRows, preRows);

                //calculate post pre/post count
                let preRowsCount = preRows.length;
                let postRowsCount = postRows.length;

                // update values
                if (postRowsCount > 0) {
                    let additionalPostRowAmount = deleted_row_amount / postRowsCount;
                    $.each(postRows, function (key, postEl) {
                        let postAmount = $(postEl).find(".amount_paid_class").val();
                        $(postEl).find(".amount_paid_class").val(parseInt(postAmount) + parseInt(additionalPostRowAmount));
                    });
                } else {
                    let additionalPreRowAmount = deleted_row_amount / preRowsCount;
                    $.each(preRows, function (pKey, preEl) {
                        let preAmount = $(preEl).find(".amount_paid_class").val();
                        $(preEl).find(".amount_paid_class").val(parseInt(preAmount) + parseInt(additionalPreRowAmount));
                    });
                }
            }

            //final calculation for remaining installment amount
            calculateRemainInstallmentAmount();
        }

        /** Re-organize installment amount for only manual amount change **/
        function reorganizeInstalmentAmountForManualChange(elm) {
            let installmentTrSelector = $(".installment_table tbody tr");
            let total_amount = $("#loan_amount").val();

            let changed_row_id = $(elm).parent().parent().data("id");
            let changed_row_amount = $(elm).parent().parent().find(".amount_paid_class").val();
            let total_post_row_amount = 0;
            let total_pre_row_amount = 0;
            let preRows = [];
            let postRows = [];

            let total_paid_amount = 0;
            let total_paid_row = 0;
            installmentTrSelector.each(function (pKey, pElm) {
                if ($(pElm).data("editable") == 0) {
                    total_paid_amount += parseInt($(pElm).find(".amount_paid_class_paid").val());
                    total_paid_row++;
                }
            });

            installmentTrSelector.each(function (key, el) {
                if ($(el).data("editable") == 0) {
                    return true;
                }

                let installment_amount = $(el).find(".amount_paid_class").val();

                if ($(el).data("id") > changed_row_id) {
                    postRows.push(el);
                    total_post_row_amount += parseInt(installment_amount);
                } else if ($(el).data("id") < changed_row_id) {
                    preRows.push(el);
                    total_pre_row_amount += parseInt(installment_amount);
                }
            });

            //calculate post pre/post count
            let preRowsCount = preRows.length;
            let postRowsCount = postRows.length;

            // update values
            if (postRowsCount > 0) {
                let postRowInstallmentAmount = ((total_amount - (parseInt(changed_row_amount) + total_pre_row_amount + parseInt(total_paid_amount))) / (postRowsCount));

                if (postRowInstallmentAmount < 0) {
                    swal.fire({
                        title: 'Amount is not valid!!',
                        text: "Sum of Installment Amount should be equal to Total Loan Amount!!",
                        icon: 'warning',
                        buttonsStyling: false,
                        showCancelButton: false,
                        allowOutsideClick: false,
                        customClass: {
                            confirmButton: "btn btn-success"
                        }
                    })
                    return false;
                }

                $.each(postRows, function (key, postEl) {
                    $(postEl).find(".amount_paid_class").val(parseInt(postRowInstallmentAmount));
                });
                console.log(total_amount, changed_row_amount, total_pre_row_amount, total_paid_amount, postRowsCount, total_paid_row, total_paid_row);
            } else {
                let preRowInstallmentAmount = ((total_amount - parseInt(changed_row_amount) - parseInt(total_paid_amount)) / (preRowsCount - total_paid_row));

                if (preRowInstallmentAmount < 0) {
                    swal.fire({
                        title: 'Amount is not valid!!',
                        text: "Sum of Installment Amount should be equal to Total Loan Amount!!",
                        icon: 'warning',
                        buttonsStyling: false,
                        showCancelButton: false,
                        allowOutsideClick: false,
                        customClass: {
                            confirmButton: "btn btn-success"
                        }
                    })
                    return false;
                }

                $.each(preRows, function (pKey, preEl) {
                    $(preEl).find(".amount_paid_class").val(parseInt(preRowInstallmentAmount));
                });
            }

            //final calculation for remaining installment amount
            calculateRemainInstallmentAmount();
        }

        /** Re-organize installment month **/
        function reorganizeInstalmentMonths(elm) {
            let installmentTrSelector = $(".installment_table tbody tr");

            let changed_row_id = $(elm).parent().parent().data("id");
            let changed_row_date = $(elm).parent().parent().find(".datepicker_ins").val();
            let preRows = [];
            let postRows = [];

            installmentTrSelector.each(function (key, el) {
                if ($(el).data("id") > changed_row_id) {
                    postRows.push(el);
                } else if ($(el).data("id") < changed_row_id) {
                    preRows.push(el);
                }
            });

            //calculate post pre/post count
            let preRowsCount = preRows.length;
            let postRowsCount = postRows.length;

            // update values
            let last_date = changed_row_date;
            if (postRowsCount > 0) {
                $.each(postRows, function (key, postEl) {
                    let newDate = moment('01-' + last_date, "DD-MM-YYYY").add(1, 'months').format('MM-YYYY');
                    $(postEl).find(".datepicker_ins").val(newDate);
                    last_date = newDate;
                });
            }

            installmentTrSelector.find('.datepicker_ins').removeClass('month_warning');
        }

        function generateInstallmentTable() {
            let loanAmount = null;
            let installmentStartMonth = null;
            let loanTenure = null;

            loanAmount = $("#loan_amount").val();
            installmentStartMonth = $(".installment_start_month").val();
            loanTenure = $("#loan_tenure").val();

            if (loanAmount != '' && loanAmount != 0) {
                isInputOnLoanAmount = true;
            }

            if (installmentStartMonth != '' && installmentStartMonth != 0) {
                isInputOnInstallmentStartMonth = true;
            }

            if (loanTenure != '' && loanTenure != 0) {
                isInputOnLoanTenure = true;
            }

            if (isInputOnLoanAmount == false || isInputOnInstallmentStartMonth == false || isInputOnLoanTenure == false) {
                return false;
            }

            if (loanAmount == '' || loanAmount == 0 || installmentStartMonth == '' || installmentStartMonth == 0 || loanTenure == '' || loanTenure == 0) {
                swal.fire({
                    title: 'Invalid Inputs Found!!',
                    text: "Amount, Installment Start Month, Total Installment(In Month) data is not correct",
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success"
                    }
                })
                return false;
            } else {

                checkPolicyViolation(true);

                $.ajax({
                    type: "POST",
                    url: '{!! route('loan.generate_installment_table') !!}',
                    data: {
                        'installment_start_month': installmentStartMonth,
                        'loan_tenure': loanTenure,
                        'loan_amount': loanAmount
                    },
                    dataType: "HTML",
                    success: function (result) {
                        $('#installment_table_main').html(result);
                    }
                });
            }
        }

        function checkPolicyViolation(fromType = false) {
            let policyViolated = false;

            let formData = $('#policy-form').serialize();
            let url = "{!! route('loan.check_loan_policy', $loan->user_id) !!}";
            $.ajax({
                type: "POST",
                url: url,
                data: {'data': formData},
                dataType: "json",
                success: function (result) {
                    $.map(result, function (val, key) {
                        if (val) {
                            $('#loan-policy-' + key).addClass('color-green').removeClass('color-red');
                            $('#loan-policy-' + key + ' i').addClass('color-green').removeClass('color-red');
                        } else {
                            $('#loan-policy-' + key).addClass('color-red').removeClass('color-green');
                            $('#loan-policy-' + key + ' i').addClass('color-red').removeClass('color-green');
                            policyViolated = true;
                        }
                    });
                    if (policyViolated) {
                        $('.policy_violation_p').show();
                        if (!fromType) {
                            swal.fire({
                                title: 'Policy violation!!',
                                text: "Some of Policy could not meet with your application. Do you really want to continue with above input?",
                                icon: 'warning',
                                buttonsStyling: false,
                                showCancelButton: true,
                                allowOutsideClick: false,
                                customClass: {
                                    confirmButton: "btn btn-success",
                                    cancelButton: "btn btn-danger"
                                },
                                cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                                confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
                            }).then(function (result) {
                                if (!result.isConfirmed) {
                                    $('#accept_policy').prop('checked', false);
                                    return false;
                                }
                            })
                        }
                    } else {
                        $('.policy_violation_p').hide();
                    }
                }
            });
        }

        function removeInstallment(el) {
            let totalRow = $('.installment_tr').find(".amount_paid_class").length;
            if (totalRow == 1) {
                swal.fire({
                    title: 'Invalid Action!!',
                    text: "Last installment should not be deleted!!",
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success"
                    }
                })
                return false;
            }
            $(el).parents().eq(1).remove();
            $('#loan_tenure').val(totalRow - 1);

            reorganizeSerial('installment_serial');
        }

        $('#loan_amount, #loan_tenure, .installment_start_month, .type').on('change', function () {
            uncheckedAcceptPolicy();
            generateInstallmentTable();
        });

        function uncheckedAcceptPolicy() {
            $('#accept_policy').prop('checked', false);
        }

        function addNewInstallment() {
            let lastDataId = $('.installment_table tr:last').data('id');
            let nextDataId = (lastDataId + 1);
            let totalRow = $('.installment_tr').length;
            let loanAmount = $('#loan_amount').val();
            let lastDate = $('.installment_table tr:last').find('.datepicker_ins').val();
            let newDate = moment('01-' + lastDate, "DD-MM-YYYY").add(1, 'months').format('MM-YYYY');

            let installmentRow = '<tr data-id="' + nextDataId + '" class="installment_tr">' +
                '<td class="installment_serial" data-serial="' + nextDataId + '">' + nextDataId + '</td>' +
                '<td>' +
                '<input type="text" class="form-control datepicker datepicker_ins" value="' + newDate + '" onchange="reorganizeInstalmentMonths(this)" name="month[' + nextDataId + ']" autocomplete="off" required/>' +
                '</td>' +
                '<td><input type="number" onchange="return reorganizeInstalmentAmountForManualChange(this)" data-serial="' + nextDataId + '" min="0" max="' + loanAmount + '" name="amount_paid[' + nextDataId + ']" value="" step="any" required ' +
                'class="amount_paid_class"></td>' +
                '<td>' +
                '<input type="text" name="remark[' + nextDataId + ']">' +
                '<i class="fa fa-minus remove_icon" onclick="removeInstallment(this); reorganizeInstallmentAmount(this)"></i>' +
                '</td></tr>';
            $('.installment_table tbody').append(installmentRow);
            $('#loan_tenure').val(totalRow + 1);

            $('.datepicker:last').datepicker(
                {
                    format: "mm-yyyy",
                    startView: "months",
                    minViewMode: "months",
                    startDate: "+0d"
                }
            );
        }

        $('.add_new_icon').on('click', function () {
            addNewInstallment();
            reorganizeSerial('installment_serial');
            reorganizeInstallmentAmount(this, true);
        });

        // Get department by division
        $('#office_division_id').change(function () {
            var _officeDivisionID = $(this).val();

            if (isApproved) {
                return false;
            }

            $("#department_id").val('').trigger('change');

            if (_officeDivisionID != '') {
                let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                url = url.replace(":officeDivision", _officeDivisionID);

                let departmentIds = '{!! $departmentIds !!}';
                departmentIds = JSON.parse(departmentIds);
                $.get(url, {}, function (response, status) {
                    $("#department_id").empty();
                    $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                    $.each(response.data.departments, function (key, value) {
                        if ($.inArray(value.id, departmentIds) != -1) {
                            $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                        }
                    });
                })
            } else {
                $('#department_id').empty('');
            }
        });

        // Get user by department
        $('#department_id').change(function () {
            var _department_id = $(this).val();

            if (isApproved) {
                return false;
            }

            if (_department_id != '') {
                let url = "{{ route('salary.getEmployeeByDepartment', ':department') }}";
                url = url.replace(":department", _department_id);

                $.get(url, {}, function (response, status) {
                    $("#user_id").empty();
                    $("#user_id").append('<option value="" selected>Select an option</option>');
                    $.each(response.data, function (key, value) {
                        $("#user_id").append('<option value="' + value.id + '">' + value.fingerprint_no + ' - ' + value.name + '</option>');
                    });
                })
            } else {
                $("#user_id").empty('');
            }
        });

        let oldDepartmentId = '{!! old('department_id') !!}';

        if (oldDepartmentId) {
            $(document).ready(function () {
                $('#office_division_id').trigger('change');
                setTimeout(function () {
                    $('#department_id').val('');
                }, 1000);
            });
        }
    </script>
@endsection
