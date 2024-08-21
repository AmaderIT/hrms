/**
 * Success alert after deletion
 *
 * @param header
 * @param message
 * @param status
 */
function successAlert(header, message, status) {
    header = header || 'Deleted!';
    message = message || 'File has been deleted.';
    status = status || 'success'
    swal.fire({
        title: header,
        text: message,
        icon: status,
        allowOutsideClick: false
    }).then((result) => {
        if(result.isConfirmed) {
            window.location.reload()
        }
    });
}

/**
 * Show sweet alert before attempt to delete
 *
 * @param url
 */
function deleteAlert(url) {
    let clickedElement = event.currentTarget;
    swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
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
    }).then(function(result) {
        if(result.isConfirmed) {
            $.post(url, {}, function (data, status) {
                let alertHeader, alertStatus, alertMessage;
                if ( data.status == true ) {
                    alertHeader = 'Success';
                    alertStatus = 'success';
                    alertMessage = data.message || 'Deleted Successfully';

                    clickedElement.parentElement.parentElement.style.display = 'none'
                } else {
                    alertHeader = 'Cancelled';
                    alertStatus = 'error';
                    alertMessage = data.message || 'Something Went Wrong';
                }

                successAlert(alertHeader, alertMessage, alertStatus);
            })
        }
    })
}


/**
 * Show sweet alert before attempt to delete from datatable
 *
 * @param url
 */
function deleteAlertAnother(url) {
    let clickedElement = event.currentTarget;
    swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
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
    }).then(function(result) {
        if(result.isConfirmed) {
            $.post(url, {}, function (data, status) {
                let alertHeader, alertStatus, alertMessage;
                if (data.status == true) {
                    alertHeader = 'Success';
                    alertStatus = 'success';
                    alertMessage = data.message || 'Deleted Successfully';
                } else {
                    alertHeader = 'Cancelled';
                    alertStatus = 'error';
                    alertMessage = data.message || 'Something Went Wrong';
                }

                successAlert(alertHeader, alertMessage, alertStatus);
            })
        }
    })
}

/**
 * Show sweet alert before attempt to reset password
 *
 * @param url
 */
function resetAlert(url) {
    let clickedElement = event.currentTarget;
    swal.fire({
        title: 'Are you sure?',
        text: "Password will be reset to 123456",
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
    }).then(function(result) {
        if(result.isConfirmed) {
            $.post(url, {}, function (data, status) {

                if ( data.status == true ) {
                    window.location.reload();
                } else {
                    alertHeader = 'Cancelled';
                    alertStatus = 'error';
                    alertMessage = data.message || 'Something Went Wrong';
                }

                successAlert(alertHeader, alertMessage, alertStatus);
            })

        }
    })
}

/**
 * Append necessary active class(s) to the corresponding nav-item on the left-sidebar
 */
function selectActiveMenu() {
    let menuNavs = document.querySelectorAll('.menu-nav .menu-link');
    for(let menu of menuNavs) {
        let buildUrl = `${location.protocol}//${location.host}/${location.pathname.split('/')[1]}`
        if (menu.href === buildUrl) {
            menu.parentElement.classList.add('menu-item-active');
        }
    }
}

selectActiveMenu();

function notify() {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "10000",
        "extendedTimeOut": "10000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    return toastr
}

// Success Alert after Completing Salary Generation
function successAlertSalaryGeneration(header, message, status) {
    header = header || 'Done!';
    message = message || 'Salary has been generated.';
    status = status || 'success';
    swal.fire({
        title: header,
        text: message,
        icon: status,
        allowOutsideClick: false
    }).then((result) => {
        if(result.isConfirmed) {
            window.location.reload()
        }
    });
}

// salary generate alert
function salaryPrepareAlert(url) {
    let clickedElement = event.currentTarget;
    swal.fire({
        title: 'Are you sure to generate salary?',
        text: "You won't be able to revert this!",
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
    }).then(function(result) {
        if(result.isConfirmed) {
            let manageLeave = 0;
            if($('#manageLeave').is(':checked')) manageLeave = 1;
            else manageLeave = 0;

            const formData = {
                datepicker: $("#datepicker").val(),
                manageLeave: manageLeave
            };

            $.post(url, formData, function (data, status) {
                let alertHeader, alertStatus, alertMessage;
                if ( data.status == true ) {
                    alertHeader = 'Success';
                    alertStatus = 'success';
                    alertMessage = data.message || 'Salary Generation has been Completed';

                    clickedElement.parentElement.parentElement.style.display = 'none'
                } else {
                    alertHeader = 'Cancelled';
                    alertStatus = 'error';
                    alertMessage = data.message || 'Sorry! Something Went Wrong';
                }

                successAlertSalaryGeneration(alertHeader, alertMessage, alertStatus);
            })
        }
    })
}

// Success Alert after Changing Supervisor
function successAlertOnChangingSupervisor(header, message, status) {
    header = header || 'Done!';
    message = message || 'Supervisor changed successfully.';
    status = status || 'success';

    swal.fire({
        title: header,
        text: message,
        icon: status,
        allowOutsideClick: false
    }).then((result) => {
        if(result.isConfirmed) {
            window.location.reload()
        }
    });
}

// Supervisor Change Alert
function supervisorChangeAlert(url) {
    let clickedElement = event.currentTarget;
    swal.fire({
        title: "Are you sure to change supervisor?",
        text: "You won't be able to revert this!",
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        allowOutsideClick: false,
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        cancelButtonText: "<i class='las la-times'></i> No, thanks.",
        confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
    }).then(function(result) {
        if(result.isConfirmed) {
            let formData = {};
            if($('#override').is(':checked')) {
                formData = {
                    office_division_id: $("#office_division_id").val(),
                    department_id: $("#department_id").val(),
                    supervised_by: $("#supervised_by").val(),
                    override: 1,
                };
            } else {
                formData = {
                    office_division_id: $("#office_division_id").val(),
                    department_id: $("#department_id").val(),
                    supervised_by: $("#supervised_by").val()
                };
            }

            $.post(url, formData, function (data) {
                let alertHeader, alertStatus, alertMessage;

                if (data.status == true) {
                    alertHeader = 'Success';
                    alertStatus = 'success';
                    alertMessage = data.message || 'Supervisor Changed Successfully';

                    clickedElement.parentElement.parentElement.style.display = 'none'
                } else {
                    alertHeader = 'Cancelled';
                    alertStatus = 'error';
                    alertMessage = data.message || 'Sorry! Something Went Wrong';
                }

                successAlertOnChangingSupervisor(alertHeader, alertMessage, alertStatus);
            }).fail(function (response, status) {
                const message   = JSON.parse(response.responseText);
                const error     = message.errors

                $.each(error, function(key, value) {
                    notify().error(value)
                });
            })
        }
    })
}
