@php
    $options = '<option value="">Select</option>';
    $installmentTable = '';
    $installmentAmount = floor($loanAmount/$loanTenure);
    $lastInsAmount = (($loanAmount - ($loanTenure * $installmentAmount)) + $installmentAmount);
@endphp

@foreach ($months as $monthKey => $month)
    @php $options .= '<option value="' . $monthKey . '">' . $month . '</option>'; @endphp
@endforeach

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
    @for ($i = 0; $i < $loanTenure; $i++)
        @php
            $selectedInstallmentMonth = date('m-Y', strtotime("+$i months", strtotime($installmentStartMonth)));
            $sl = ($i + 1);
        @endphp
        @if ($loanTenure == $sl)
            @php $installmentAmount = $lastInsAmount @endphp
        @endif
        <tr data-id="{{ $sl }}" class="installment_tr">
            <td class="installment_serial" data-serial="{{ $sl }}">{{ $sl }}</td>
            <td>
                <input type="text" class="form-control datepicker_ins" onchange="reorganizeInstalmentMonths(this)"
                       name="month[{{ $sl }}]"
                       value="{{ $selectedInstallmentMonth }}" autocomplete="off" required/>
            </td>
            <td><input type="number" min="0" name="amount_paid[{{ $sl }}]"
                       value="{{ $installmentAmount }}" required step="any" class="amount_paid_class"
                       max="{{ $loanAmount }}" onchange="return reorganizeInstalmentAmountForManualChange(this)"></td>
            <td>
                <input type="text" name="remark[{{ $sl }}]">
                <i class="fa fa-minus remove_icon"
                   onclick="removeInstallment(this); reorganizeInstallmentAmount(this);"></i>
            </td>
        </tr>
    @endfor
    </tbody>
</table>
<i class="fa fa-plus add_new_icon" onclick="addNewInstallment(); reorganizeInstallmentAmount(this, true);"></i>

<script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".datepicker_ins").datepicker({
            format: "mm-yyyy",
            startView: "months",
            minViewMode: "months",
            startDate: "+0d"
        });
    });

    function addNewInstallment() {
        let lastDataId = $('.installment_table tr:last').data('id');
        let nextDataId = (lastDataId + 1);
        let totalRow = $('.installment_tr').length;
        let loanAmount = '{{ $loanAmount }}';
        let lastDate = $('.installment_table tr:last').find('.datepicker_ins').val();
        let newDate = moment('01-' + lastDate, "DD-MM-YYYY").add(1, 'months').format('MM-YYYY');

        let installmentRow = '<tr data-id="' + nextDataId + '" class="installment_tr">' +
            '<td class="installment_serial" data-serial="' + nextDataId + '">' + nextDataId + '</td>' +
            '<td>' +
            '<input type="text" class="form-control datepicker_ins" value="' + newDate + '" onchange="reorganizeInstalmentMonths(this)" name="month[' + nextDataId + ']" autocomplete="off" required/>' +
            '</td>' +
            '<td><input type="number" min="0" max="' + loanAmount + '" name="amount_paid[' + nextDataId + ']" value="" step="any" required ' +
            'class="amount_paid_class" onchange="return reorganizeInstalmentAmountForManualChange(this)"></td>' +
            '<td>' +
            '<input type="text" name="remark[' + nextDataId + ']">' +
            '<i class="fa fa-minus remove_icon" onclick="removeInstallment(this); reorganizeInstallmentAmount(this);"></i>' +
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

        reorganizeSerial('installment_serial');
    }

    /*$('.add_new_icon').on('click', function () {
        addNewInstallment();
        reorganizeInstallmentAmount(this);
    });

    $('.remove_icon').on('click', function () {
        removeInstallment(this);
        reorganizeInstallmentAmount(this, false);
    })*/

    /** Re-organize serial number **/
    /*function reorganizeSerial(className){
        $('.' + className).each(function (index, val) {
            $(this).html(index + 1);
        });
    }*/
</script>
