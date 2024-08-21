$('.tax-btn-group').hide();

$('.tax-amount-adjust').css('border', 'none').css('font-weight', 'bold');


function adjustTaxAmount(params) {
    $.ajax({
        url: taxAdjustmentRoute,
        type: 'POST',
        data: params,
        success: function (res) {
            if (res.status == 'success') {

                localStorage.setItem('ListScroll' + window.location.pathname, JSON.stringify({
                    uuid: params.uuid,
                    msg: res.message
                }));
                location = '';

            } else {
                toastr.error(res.message)
            }

        },
        error: function (err) {
            console.log(err)
        }
    })
}


/**
 * track previous row uuid
 * track current row uuid
 * track first click on tax amount input field
 */

let uuid = null;
let current_uuid = null;
let fc = 0;


$('.tax-amount-adjust').on('input', function () {

    const old_tax_value = $('#tax-amount-reset-btn-' + current_uuid).val();

    if (parseFloat(old_tax_value) != parseFloat(this.value)) {
        fc = 1;
        uuid = $(this).data('uuid')
    } else {
        fc = 0;
    }
})

$('.tax-amount-adjust').mousedown(function () {

    current_uuid = $(this).data('uuid');

    if (uuid != null && uuid != $(this).data('uuid') && fc == 1) {
        $('#tax-amount-adjust-btn-' + uuid).trigger('click', 1);
    } else {
        $('.tax-amount-reset-btn').click()
        $(this).css('border', '1px solid green')
        $(this).next().show();

    }


});


$('.tax-amount-adjust-btn').on('click', function (e, i = 0) {

    const inputFiled = $('#tax-amount-' + $(this).data('uuid'));
    const params = {
        'payable_tax_amount': inputFiled.val(),
        'uuid': $(this).data('uuid'),
        'uuid_dpt': $('#uuid_dpt').val()

    }

    swal.fire({
        title: 'Are you sure want to adjust tax amount?',
        text: 'If confirmed, all previous approvals will be reset, and the process will begin again from the beginning!',
        icon: 'warning',
        buttonsStyling: false,
        showCancelButton: true,
        allowOutsideClick: false,
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        cancelButtonText: "<i class='las la-times'></i> No, thanks.",
        confirmButtonText: "<i class='las la-thumbs-up'></i> Yes, sure!",
    }).then(function (result) {
        if (result.isConfirmed) {
            adjustTaxAmount(params);
        } else {
            $('.tax-amount-reset-btn').click();
            if (i == 1) {
                setTimeout(function () {
                    $('#tax-amount-' + current_uuid).mousedown()
                    $('#tax-amount-' + current_uuid).focus()
                }, 400)
            }


        }
    })


})

$('.tax-amount-reset-btn').on('click', function () {
    fc = 0;
    uuid = null;
    const inputFiled = $('#tax-amount-' + $(this).data('uuid'));
    inputFiled.val(this.value);
    inputFiled.css('border', 'none');
    inputFiled.next().hide()
    inputFiled.blur();


})

$('.tax-amount-adjust').keypress(function (event) {

    if (event.which != 13) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
            ((event.which < 48 || event.which > 57) &&
                (event.which != 0 && event.which != 8))) {
            event.preventDefault();
        }

        var text = $(this).val();

        if ((text.indexOf('.') != -1) &&
            (text.substring(text.indexOf('.')).length > 2) &&
            (event.which != 0 && event.which != 8) &&
            ($(this)[0].selectionStart >= text.length - 2)) {
            event.preventDefault();
        }
    }
});


let scrollPosition = localStorage.getItem('ListScroll' + window.location.pathname);
scrollPosition = JSON.parse(scrollPosition);

$(document).ready(function () {
    if (scrollPosition != null && scrollPosition.uuid != null) {
        toastr.success(scrollPosition.msg);
        $('#tax-amount-' + scrollPosition.uuid).focus();
        $('#tax-amount-' + scrollPosition.uuid).mousedown()
        $('.tax-amount-reset-btn').click()

    }
});
localStorage.setItem('ListScroll' + window.location.pathname, null);
