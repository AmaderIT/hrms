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
            vertical-align: center;
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
            bottom: 17px;
            color: #fff;
            background: #d7041a;
            cursor: pointer;
            z-index: 9;
            border-radius: 2px;
            padding: 8px 6px;
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
                    <h3 class="card-title">Apply for Loan / Advance</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('loan.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ empty($loan)? route('loan.store'): route('loan.update', ['loan' => $loan->uuid]) }}"
                      id="policy-form" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                {{-- Type --}}
                                <div class="form-group">
                                    <label>Type</label>
                                    @if(empty($loan))
                                        <div class="radio-inline">
                                            <label class="radio">
                                                <input onclick="typeWisePolicy(this)" class="type" type="radio"
                                                       {{ ((!empty(old('type')) && old('type') == 'Loan') || empty(old('type')))? 'checked': '' }}
                                                       name="type" value="Loan">
                                                <span></span>Loan</label>
                                            <label class="radio">
                                                <input type="radio" name="type" value="Advance" class="type"
                                                       {{ ((!empty(old('type'))) && old('type') == 'Advance')? 'checked': '' }}
                                                       onclick="typeWisePolicy(this)">
                                                <span></span>Advance Salary</label>
                                        </div>
                                    @else
                                        <div class="radio-inline">
                                            <label class="radio">
                                                <input onclick="typeWisePolicy(this)" class="type" type="radio"
                                                       {{ $loan->type == 'Loan' ? 'checked': '' }}
                                                       name="type" value="Loan">
                                                <span></span>Loan</label>
                                            <label class="radio">
                                                <input type="radio" name="type" value="Advance" class="type"
                                                       {{ $loan->type == 'Advance' ? 'checked': '' }}
                                                       onclick="typeWisePolicy(this)">
                                                <span></span>Advance Salary</label>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group mt-12">
                                    <label for="loan_tenure">Installment Start Month<span
                                            class="text-danger">*</span></label></label>
                                    <input type="text" class="form-control installment_start_month datepicker"
                                           value="{{ !empty($loan)? $loan->installment_start_month: old('installment_start_month') }}"
                                           name="installment_start_month" autocomplete="off" required/>
                                    @error("installment_start_month")
                                    <p class="text-danger"> {{ $errors->first("installment_start_month") }} </p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="loan_amount">Amount <span class="text-danger">*</span></label></label>
                                    <input type="number" class="form-control" name="loan_amount" id="loan_amount"
                                           value="{{ !empty($loan)? $loan->loan_amount: old('loan_amount') }}"
                                           min="100"
                                           placeholder="Loan Amount" required>
                                    @error("loan_amount")
                                    <p class="text-danger"> {{ $errors->first("loan_amount") }} </p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="loan_tenure">Total Installment(In Month)<span
                                            class="text-danger">*</span></label></label>
                                    <input type="number" class="form-control" name="loan_tenure" id="loan_tenure"
                                           oninput="checkTotalInstalmentNumberLimit(this)"
                                           min="1" max="60"
                                           placeholder="Loan Tenure(In Month)"
                                           value="{{ !empty($loan)? $loan->loan_tenure: old('loan_tenure') }}"
                                           required/>
                                    @error("loan_tenure")
                                    <p class="text-danger"> {{ $errors->first("loan_tenure") }} </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" name="remarks" placeholder="Remarks"
                                              rows="1">{{ !empty($loan)? $loan->remarks: old('remarks') }}</textarea>
                                    @error("remarks")
                                    <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="checkbox">
                                        <input id="accept_policy" type="checkbox" name="accept_policy" value="Y"
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
                                @if(!empty($loan))
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
                                            @endphp
                                            <tr data-id="{{ $sl }}" class="installment_tr">
                                                <td class="installment_serial" data-serial="{{ $sl }}">{{ $sl }}</td>
                                                <td>
                                                    <input type="text" class="form-control datepicker datepicker_ins"
                                                           onchange="reorganizeInstalmentMonths(this)"
                                                           name="month[{{ $sl }}]"
                                                           value="{{ $month }}"
                                                           autocomplete="off" required/>
                                                    <input type="hidden" name="ids[{{ $sl }}]"
                                                           value="{{ $instalment->id }}">
                                                </td>
                                                <td><input type="number" data-serial="{{ $sl }}" min="0"
                                                           onchange="return reorganizeInstalmentAmountForManualChange(this)"
                                                           name="amount_paid[{{ $sl }}]"
                                                           value="{{ $instalment->amount_paid? round($instalment->amount_paid, 2): 0 }}"
                                                           required step="any"
                                                           class="amount_paid_class" max="{{ $loan->loan_amount }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="remark[{{ $sl }}]"
                                                           value="{{ $instalment->remark }}">
                                                    <i class="fa fa-minus remove_icon"
                                                       onclick="removeInstallment(this); reorganizeInstallmentAmount(this)"></i>
                                                </td>
                                            </tr>
                                            @php $sl++ @endphp
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <i class="fa fa-plus add_new_icon"></i>
                                @endif
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
        $("select").select2({
            theme: "classic",
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

        function checkTotalInstalmentNumberLimit(elm) {
            let inputInstallment = $(elm).val();
            if (inputInstallment != '' && inputInstallment > 60) {
                $(elm).val('');
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
            $('.amount_paid_class').each(function () {
                sumOfTotalAmount += parseFloat(this.value);
            });
            if (totalAmount != parseFloat(sumOfTotalAmount)) {
                swal.fire({
                    title: 'Amount is not valid!!',
                    text: "Total loan amount and sum of Installment amount should be same!! " + "Found: " + parseFloat(sumOfTotalAmount) + ", Require: " + totalAmount,
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
            let total_amount = $('#loan_amount').val();
            let total_installment_amount = 0;

            installmentTrSelector.each(function (fKey, fElm) {
                let final_amount = $(fElm).find(".amount_paid_class").val();
                total_installment_amount += parseInt(final_amount);
            });

            let remaining_amount = total_amount - total_installment_amount;

            if (remaining_amount > 0) {
                let prev_last_ins_amount = $('.installment_table tbody tr:last').find(".amount_paid_class").val();
                $('.installment_table tbody tr:last').find(".amount_paid_class").val(parseInt(prev_last_ins_amount) + remaining_amount);
            }
        }

        /** Re-organize Installment Amount **/
        function reorganizeInstallmentAmount(el, newRow = false) {
            let installmentTrSelector = $(".installment_table tbody tr");
            let total_row = installmentTrSelector.length;
            let total_amount = $("#loan_amount").val();

            if (newRow) {
                let instalmentAmount = total_amount / total_row;
                $(".amount_paid_class").val(parseInt(instalmentAmount));

            } else if (total_row == 1) {
                $(".amount_paid_class").val(total_amount);

            } else {

                let deleted_row_id = $(el).parent().parent().data("id");
                let deleted_row_amount = $(el).parent().parent().find(".amount_paid_class").val();

                let preRows = [];
                let postRows = [];

                installmentTrSelector.each(function (key, elm) {
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

            installmentTrSelector.each(function (key, el) {
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
                let postRowInstallmentAmount = ((total_amount - (parseInt(changed_row_amount) + total_pre_row_amount)) / postRowsCount);

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
            } else {
                let preRowInstallmentAmount = ((total_amount - parseInt(changed_row_amount)) / preRowsCount);

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
            let url = "{!! route('loan.check_loan_policy') !!}";
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
            let totalRow = $('.installment_tr').length;
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
                '<input type="text" class="form-control datepicker_ins" value="' + newDate + '"  onchange="reorganizeInstalmentMonths(this)" name="month[' + nextDataId + ']" autocomplete="off" required/>' +
                '</td>' +
                '<td><input type="number" onchange="return reorganizeInstalmentAmountForManualChange(this)" data-serial="' + nextDataId + '" min="0" max="' + loanAmount + '" name="amount_paid[' + nextDataId + ']" value="" step="any" required ' +
                'class="amount_paid_class"></td>' +
                '<td>' +
                '<input type="text" name="remark[' + nextDataId + ']">' +
                '<i class="fa fa-minus remove_icon" onclick="removeInstallment(this); reorganizeInstallmentAmount(this)"></i>' +
                '</td></tr>';
            $('.installment_table tbody').append(installmentRow);
            $('#loan_tenure').val(totalRow + 1);

            $('.datepicker_ins:last').datepicker(
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

        /*$('.remove_icon').on('click', function () {
            removeInstallment();
            reorganizeInstallmentAmount(this);
        });*/
    </script>
@endsection
