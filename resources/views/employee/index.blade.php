@extends('layouts.app')

@section('top-css')
{{--    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>--}}
{{--    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">--}}
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
    <style>
        .profile-link{
            font-style: italic;
            font-size: 15px;
            margin-right: 25%;
        }
        .modal .modal-header .modal-title {
            font-weight: 500;
            font-size: 1.3rem !important;
            color: #181C32;
        }
        .last_promoted_date{
            font-style: italic;
            font-size: 12px;
        }
        span.inner-promoted-text {
            color:red;
        }
        input#reset_password_emp {
            width:150px;
        }
/*        .icon-class{
            width: 50px;
            height: 50px;
        }
        .swal2-popup {
            width: 29em !important;
        }
        .swal2-content {
            padding: 0 1.6em !important;
        }
        .swal2-popup .swal2-content {
            margin-top: -85px !important;
        }
        .swal2-header {
            flex-direction: row !important;
        }*/
        .action-btn {
            margin: -1.5rem auto 0rem auto !important;
        }
    </style>
@endsection

@section('content')
    <div class="card card-custom" xmlns="http://www.w3.org/1999/html">
        <div class="card-header flex-wrap pt-3 pb-3">
            <div class="card-title">
                <h3 class="card-label">
                    Employee Listing
                    @if (session()->has('employeeTrackDevice') && session('employeeTrackDevice')==='no')
                        <div id="mydiv" onclick="agianSyncEmployeeWithDevice('{{session('trackEmpUuid')}}')"></div>
                    @endif
                </h3>
            </div>
            <div class="card-toolbar">
                <div class="dropdown dropdown-inline mr-2">
                    @can('Export Employee Info')
                        <a href="{{ route('employee.export') }}" data-toggle="tooltip" title="Export">
                            <span class="svg-icon svg-icon-primary svg-icon-2x">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                     height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <rect x="0" y="0" width="24" height="24"/>
                                        <path
                                            d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z"
                                            fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                        <rect fill="#000000" opacity="0.3"
                                              transform="translate(12.000000, 8.000000) scale(1, -1) rotate(-180.000000) translate(-12.000000, -8.000000) "
                                              x="11" y="2" width="2" height="12" rx="1"/>
                                        <path
                                            d="M12,2.58578644 L14.2928932,0.292893219 C14.6834175,-0.0976310729 15.3165825,-0.0976310729 15.7071068,0.292893219 C16.0976311,0.683417511 16.0976311,1.31658249 15.7071068,1.70710678 L12.7071068,4.70710678 C12.3165825,5.09763107 11.6834175,5.09763107 11.2928932,4.70710678 L8.29289322,1.70710678 C7.90236893,1.31658249 7.90236893,0.683417511 8.29289322,0.292893219 C8.68341751,-0.0976310729 9.31658249,-0.0976310729 9.70710678,0.292893219 L12,2.58578644 Z"
                                            fill="#000000" fill-rule="nonzero"
                                            transform="translate(12.000000, 2.500000) scale(1, -1) translate(-12.000000, -2.500000) "/>
                                    </g>
                                </svg>
                            </span>
                        </a>
                    @endcan
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a href="#" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-pdf-o"></i>
                                    </span>
                                    <span class="navi-text">PDF</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                @can('Create New Employee')
                    <a href="{{ route('employee.create') }}" class="btn btn-primary font-weight-bolder">
                        <span class="svg-icon svg-icon-default svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"/>
                                    <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                    <path
                                        d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z"
                                        fill="#000000"/>
                                </g>
                            </svg>
                        </span>
                        Add Employee
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex">
                @can("Filter option for Employee List")
                    @if(isset($data['filterToEmployee']) && !in_array($data['filterToEmployee'],['supervisor']))
                    <div class="col-lg-10">
                        <form action="#" method="GET">
                            <div class="input-group">
                                {{-- Office Division --}}
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <select class="form-control" name="office_division_id" id="office_division_id">
                                            <option selected value="">Office Division</option>
                                            @foreach($data["officeDivisions"] as $officeDivision)
                                                <option
                                                    value="{{ $officeDivision->id }}" {{ request()->get("office_division_id") == $officeDivision->id ? 'selected' : '' }}>
                                                    {{ $officeDivision->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Department --}}
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <select class="form-control" name="department_id[]" id="department_id" multiple>

                                        </select>
                                    </div>
                                </div>
                                @if(!empty($data["designations"]) && count($data["designations"])>0)
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <select class="form-control" name="designation_id" id="select_designation_id">
                                            <option selected value="">-Designations-</option>
                                            @foreach($data["designations"] as $designation)
                                                <option
                                                    value="{{ $designation->id }}" {{ request()->get("designation_id") == $designation->id ? 'selected' : '' }}>
                                                    {{ $designation->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif

                                @if(!empty($data['status']) && $data['status'] == 1)
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <select id="status_filter" name="status" class="form-control">
                                                <option value="1" {{ $data['status'] == 1 ? 'selected' : '' }}>
                                                    Active
                                                </option>
                                                <option value="0" {{ $data['status'] == 0 ? 'selected' : '' }}>
                                                    Inactive
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <select id="status_filter" name="status" class="form-control">
                                                <option value="">-Status-</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-2">
                                    <input class="btn btn-primary btn-sm" type="submit" value="Search" id="srcBtn">
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                @endcan
            </div>

            <table class="table table-responsive-lg" id="employeeTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Photo</th>
                    <th scope="col">Office ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    @can('Change Employee Status')
                        <th scope="col">Status</th>
                    @endcan
                    @if(auth()->user()->can("Edit Employee Info") OR auth()->user()->can("Delete Employee"))
                        <th scope="col">Action</th>
                    @endif
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
    <div id="template_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            </div>
        </div>
    </div>
    <div class="modal fade" id="employee-rejoin-modal" tabindex="-1" role="dialog"
         aria-labelledby="rejoinModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Re-join
                        <span id="last-promoted-date" class="last_promoted_date"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <form name="rejoin-modal" id="creation-rejoin" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="card-body">
                                @include('employee.rejoin-form')
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="update-profile-link" class="profile-link text-left"></span>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="creation-rejoin-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        function changeStatus(uuid,checkedVal) {
            let msg = 'Would you like to activate this employee?';
            if(checkedVal == ''){
                msg = 'Would you like to deactivate this employee?';
            }
            $("#"+uuid).prop('checked',true);
            swal.fire({
                title: 'Are you sure?',
                text: msg,
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
                    let url = "{{ route('employee.changeStatus', ':employee') }}";
                    url = url.replace(":employee", uuid);
                    $.post(url, {}, function (response, status) {
                        let alertHeader, alertStatus, alertMessage;
                        if (status) {
                            alertHeader = 'Success';
                            alertStatus = 'success';
                            alertMessage = 'Status updated successfully!!!';
                        } else {
                            alertHeader = 'Cancelled';
                            alertStatus = 'error';
                            alertMessage = 'Something Went Wrong';
                        }
                        swal.fire({
                            title: alertHeader,
                            text: alertMessage,
                            icon: alertStatus
                        }).then((result) => {
                            if(result.isConfirmed) {
                                $('#employeeTable').DataTable().ajax.reload();
                            }
                        });
                    })
                }else{
                    $('#employeeTable').DataTable().ajax.reload();
                }
            })
        }

        $(document).ready(function () {
            // Get department by office division
            $('#office_division_id').change(function () {
                var _officeDivisionID = $(this).val();

                let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                url = url.replace(":officeDivision", _officeDivisionID);

                $.get(url, {}, function (response, status) {
                    $("#department_id").empty();
                    $("#department_id").append('<option value=" " disabled>Select an option</option>');
                    $.each(response.data.departments, function (key, value) {
                        $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                })
            });
        });
        // Enable Select2
        $("select").select2({
            theme: "classic",
            width: '100%'
        });
    </script>
    <script>
        $(document).ready(function () {
            var oTable = $('#employeeTable').DataTable({
                language: {
                    paginate: {
                        next: '&#8250;',
                        previous: '&#8249;'
                    }
                },
                "processing": true,
                "serverSide": true,
                "ordering": true,
                "searching": true,
                "stateSave": true,
                "ajax": {
                    "method": "POST",
                    "url": '{{ route("employee.datatable") }}',
                    data: function (d) {
                        d.office_division_id = $('#office_division_id').val();
                        d.department_id = $('#department_id').val();
                        d.status_filter = $('#status_filter').val();
                        d.department_ids = '{{isset($supervisorDepartmentIds)?json_encode($supervisorDepartmentIds):json_encode([])}}';
                        d.designation_id =  $('#select_designation_id').val();
                    }
                },
                "columns": [
                    {
                        "data": "photo",
                        "name": "photo",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                    {
                        "data": "fingerprint_no",
                        "name": "fingerprint_no",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "name",
                        "name": "name",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.current_promotion && row.current_promotion.office_division) {
                                return row.current_promotion.office_division.name;
                            }
                            return '';
                        },
                        "name": "currentPromotion.officeDivision.name",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.current_promotion && row.current_promotion.department) {
                                return row.current_promotion.department.name;
                            }
                            return '';
                        },
                        "name": "currentPromotion.department.name",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.current_promotion && row.current_promotion.designation) {
                                return row.current_promotion.designation.title;
                            }
                            return '';
                        },
                        "name": "currentPromotion.designation.title",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                    {
                        "data": "email",
                        "name": "email",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "phone",
                        "name": "phone",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                        @can('Change Employee Status')
                    {
                        "data": "status",
                        "name": "status",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                        @endcan
                        @if(auth()->user()->can("Edit Employee Info") OR auth()->user()->can("Delete Employee"))
                    {
                        "data": "action", "name": "action", orderable: false, sortable: false, searchable: false
                    },
                    @endif
                ]
            });
            $("#srcBtn").on("click", function (e) {
                oTable.draw();
                e.preventDefault();
            });
        });

        function showProfile(uuid) {
            if (uuid == '') {
                alert("Missing Employee ID!!!");
                return false;
            }
            let $templateModal = $('#template_modal');
            let $responseStatus = false;
            $.ajax({
                url: '{{ route("employee.showProfile") }}',
                type: 'POST',
                data: {'uuid': uuid},
                success: function (data) {
                    $templateModal.modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $templateModal.find('.modal-content').html(data);
                    $templateModal.modal();
                },
                error: function (xhr, desc, err) {
                    console.log("error");
                }
            });
        }
    </script>
    <script>
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        function changeInactiveStatus(id,empID) {
            $("#rejoin_employee_"+id).prop('checked',false);
            if(id == ''){
                toastr.error("Missing Employee ID!!!");
                return false;
            }
            let promotedDate = $("#rejoin_employee_"+id).data('promoted-date');
            $('#last-promoted-date').html(" (Last Action Date : <span class='inner-promoted-text'>"+promotedDate+"</span>)");
            $('#trackID').val(btoa(empID));
            let updateLinkUrl = '{{ route("employee.edit",":employee") }}';
            updateLinkUrl = updateLinkUrl.replace(":employee",id);
            let updateLink = 'Would you like to update profile information? <a href="'+updateLinkUrl+'" target="_blank"> Click Here...</a>';
            $('#update-profile-link').html(updateLink);
            $("#employment_type").val('').trigger('change');
            $('#creation-rejoin')[0].reset();
        }
        $(document).ready(function () {
            $('#creation-rejoin-btn').on('click', function (e) {
                e.preventDefault();
                if($('#re-joining-date').val()== ''){
                  toastr.error("Missing Re-join Date!!!");
                  return false;
                }
                let data = {
                    '_token':'{{csrf_token()}}',
                    'values':$('#creation-rejoin').serialize()
                };
                var url = '{{route('employee.rejoinEmployee')}}';
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: "json",
                    success: function(result){
                        //console.log(result);
                        if(result.status){
                            let alertHeader = 'Success';
                            let alertStatus = 'success';
                            let alertMessage = result.message;
                            //successAlert(alertHeader, alertMessage, alertStatus);
                            swal.fire({
                                title: alertHeader,
                                text: alertMessage,
                                icon: alertStatus
                            }).then((result) => {
                                    $('#creation-rejoin')[0].reset();
                                    $('#employee-rejoin-modal').modal('hide');
                                    $('#employeeTable').DataTable().ajax.reload();
                            });
                        }else{
                            if(result.message){
                                toastr.error(result.message);
                            }else{
                                toastr.error("Something went wrong!!!");
                            }
                        }
                    },
                    error:function () {
                       toastr.error("Something went wrong!!!");
                    }
                });
          });
            $('#employee-rejoin-modal').on('shown.bs.modal', function (e) {
                $('#re-joining-date').datepicker({
                    minDate: 0,
                    startDate: '-40y',
                    format: 'yyyy-mm-dd',
                    todayHighlight: true,
                    autoclose: true
                });
            });

            $("#select_designation_id").select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('getDesignations') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });

        });
        function resetPassword(id,dob) {
            if(id == ''){
                toastr.error("Missing Employee ID!!!");
                return false;
            }
            if(dob == ''){
                toastr.error("Missing Employee DOB!!!");
                return false;
            }
            let msg = 'Password will be reset to';
            swal.fire({
                //title: 'Are you sure?',
                //text: msg,
                html:'<form name="reset-password-modal" id="creation-reset-password-emp" action="#" method="POST"><p>'+msg+'<input type="hidden" name="hidden_emp_id" id="hidden_emp_id" value="'+id+'" class="swal2-input"><input type="text" name="reset_password_emp" id="reset_password_emp"  value="'+atob(dob)+'" class="swal2-input"></p></form>',
                //icon: 'warning',
                buttonsStyling: false,
                showCancelButton: true,
                showCloseButton: true,
                allowOutsideClick: false,
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger",
                    actions:"action-btn",
                    //icon: 'icon-class'
                },
                cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
                reverseButtons: true
            }).then(function(result) {
                if(result.isConfirmed) {
                    if($('#reset_password_emp').val()== ''){
                        toastr.error("Missing Password!!!");
                        return false;
                    }
                    let data = {
                        '_token':'{{csrf_token()}}',
                        'values':$('#creation-reset-password-emp').serialize()
                    };
                    var url = '{{route('employee.resetEmployeePassword')}}';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: "json",
                        success: function(result){
                            //console.log(result);
                            if(result.status){
                                let alertHeader = 'Success';
                                let alertStatus = 'success';
                                let alertMessage = result.message;
                                //successAlert(alertHeader, alertMessage, alertStatus);
                                swal.fire({
                                    title: alertHeader,
                                    text: alertMessage,
                                    icon: alertStatus
                                }).then((result) => {
                                    $('#creation-reset-password-emp')[0].reset();
                                });
                            }else{
                                if(result.message){
                                    toastr.error(result.message);
                                }else{
                                    toastr.error("Something went wrong!!!");
                                }
                            }
                        },
                        error:function () {
                            toastr.error("Something went wrong!!!");
                        }
                    });
                }
            })
        }

        function syncEmployeeWithDevice(id) {
            if(id == ''){
                toastr.error("Missing Employee ID!!!");
                return false;
            }
            let msg = 'Employee sync attendance device';
            swal.fire({
                title: 'Are you sure?',
                text: msg,
                //icon: 'warning',
                buttonsStyling: false,
                showCancelButton: true,
                showCloseButton: true,
                allowOutsideClick: false,
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger",
                },
                cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
                reverseButtons: true
            }).then(function(result) {
                if(result.isConfirmed) {
                    var url = '{{route('employee.syncEmployeeDevice')}}';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {'uuid':id},
                        dataType: "json",
                        success: function(result){
                            if(result.status){
                               toastr.success(result.message);
                            }else{
                                agianSyncEmployeeWithDevice(id);
                            }
                            $('#employeeTable').DataTable().ajax.reload();
                        },
                        error:function () {
                            toastr.error("Something went wrong!!!");
                        }
                    });
                }
            })
        }
        function agianSyncEmployeeWithDevice(id){
            swal.fire({
                title: "Failed!",
                text: 'to sync attendance device!!!',
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger",
                },
                showCancelButton: true,
                showCloseButton: true,
                allowOutsideClick: false,
                cancelButtonText: "<i class='las la-times'></i> Cancel",
                confirmButtonText: "<i class='las la-thumbs-up'></i> Try Again",
                reverseButtons: true
            }).then(function(resultss){
                if(resultss.isConfirmed){
                    var url = '{{route('employee.syncEmployeeDevice')}}';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {'uuid':id},
                        dataType: "json",
                        success: function(result){
                            if(result.status){
                                toastr.success(result.message);
                            }else{
                                agianSyncEmployeeWithDevice(id);
                            }
                            $('#employeeTable').DataTable().ajax.reload();
                        },
                        error:function () {
                            toastr.error("Something went wrong!!!");
                        }
                    });
                }
            });
        }
    </script>
    <script type="text/javascript">
        @if (session()->has('employeeTrackDevice') && session('employeeTrackDevice')==='no')
        $(window).on('load', function() {
            $( "#mydiv" ).trigger('click');
        });
        @endif
    </script>
@endsection
