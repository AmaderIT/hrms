@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Requested Application Listing</h3>
            </div>
        </div>

        <div class="card-header col-12 mt-6 mb-3">
            @include('filter.division-department-employee-filter')
            <div class="col-md">
                <div class="form-group">
                    <label class="">Status:</label>
                    <select class="form-control ml-2 w-75" id="status" name="status">
                        <option value="all">All</option>
                        <option value="{{\App\Models\LeaveRequest::STATUS_PENDING}}">Pending</option>
                        <option value="{{\App\Models\LeaveRequest::STATUS_AUTHORIZED}}">Authorized</option>
                        <option value="{{\App\Models\LeaveRequest::STATUS_APPROVED}}">Approved</option>
                        <option value="{{\App\Models\LeaveRequest::STATUS_REJECTED}}">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mt-8">
                    <button type="button" class="btn btn-bg-dark btn-sm search_button" style="color: white">Search
                    </button>
                    <button type="button" class="btn btn-bg btn-sm search_reset"
                            style="color: white;background: #3999ff">
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-header col-12 mt-6 mb-3">
            <div class="col-md-4">
                <div class="form-group d-flex">
                    <input class="form-control" id="employee_id_name" name="employee_id_name" value=""
                           placeholder="Employee ID or Name">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group d-flex">
                    <button type="button" class="btn btn-bg-dark btn-sm search_input" style="color: white">Search
                    </button>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>
        <div class="card-body">
            <table class="table" id="applicationTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col" style="display: none">#</th>
                    <th scope="col">Office ID</th>
                    <th scope="col">Employee Name</th>
                    <th scope="col">Office Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Leave Type</th>
                    <th scope="col">Request Duration</th>
                    <th scope="col">Applied Date</th>
                    <th scope="col">No. of day</th>
                    <th scope="col">Paid days</th>
                    <th scope="col">Unpaid days</th>
                    <th scope="col">Authorized By</th>
                    <th scope="col">Approved By</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('footer-js')
    @stack('custom-scripts')
    <script src="{{asset('js/list.js')}}"></script>
    <script>
        var f = 0;
        var dataTable;

        $(document).ready(function () {
            dataTable = $('#applicationTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    ur: '{{ route('requested-application.index') }}',
                    data: function (d) {
                        if (f == 1) {
                            d.division_id = $('#office_division_id').val()
                            d.department_id = $('#department_id').val()
                            d.employee_id = $('#user_id').val()
                            d.status = $('#status').val()
                        } else if (f == 2) {
                            d.employee_id_name = $('#employee_id_name').val()
                        }
                    }
                },
                order: [1, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: '&#8250;',
                        previous: '&#8249;'
                    }
                },
                stateSave: true,
                "stateDuration": 7200,
                stateSaveParams: function (settings, data) {

                    data.office_division_id = $('#office_division_id').val()
                    data.department_id = $('#department_id').val()
                    data.user_id = $('#user_id').val()
                    data.status = $('#status').val()
                    data.employee_id_name = $('#employee_id_name').val()
                    data.f = f
                    data.auth_user_id = '{{auth()->user()->uuid }}'
                    data.pageX = pageX
                    data.pageY = pageY
                },
                stateLoadParams: function (settings, data) {
                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {
                        f = data.f

                        $('#status').val(data.status)
                        $('#employee_id_name').val(data.employee_id_name)

                        if (data.office_division_id > 0) {
                            $('#office_division_id').val(data.office_division_id).change()
                        }
                        if (data.department_id > 0) {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change()
                            }, 1000)
                        }
                        if (data.uuid > 0) {
                            setTimeout(function () {
                                $('#user_id').val(data.user_id).change()
                            }, 2000)
                        }
                        setTimeout(function () {
                            dataTable.page(dataTable.page.info().page).draw('page')

                        }, 2000)
                    }
                },
                columns: [
                    {data: 'id', name: 'id', visible: false},
                    {data: 'fingerprint_no', name: 'fingerprint_no', orderable: true, searchable: false},
                    {data: 'employee_name', name: 'employee_name', orderable: false, searchable: false},
                    {data: 'division_name', name: 'division_name', orderable: false, searchable: false},
                    {data: 'department_name', name: 'department_name', orderable: false, searchable: false},
                    {data: 'leave_type_name', name: 'leave_type_name', orderable: false, searchable: false},
                    {data: 'from_to_date', name: 'from_to_date', orderable: false, searchable: false},
                    {data: 'applied_date', name: 'applied_date', orderable: false, searchable: false},
                    {data: 'number_of_days', name: 'number_of_days', orderable: false, searchable: false},
                    {data: 'number_of_paid_days', name: 'number_of_paid_days', orderable: false, searchable: false},
                    {data: 'number_of_unpaid_days', name: 'number_of_unpaid_days', orderable: false, searchable: false},
                    {data: 'authorized_by', name: 'authorized_by', orderable: false, searchable: false},
                    {data: 'approved_by', name: 'approved_by', orderable: false, searchable: false},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {"data": "action", orderable: false, searchable: false}
                ]
            });

            $(document).on('click', '.search_button', function (e) {

                f = 1
                dataTable.draw();
            });

            $(document).on('click', '.search_input', function (e) {
                f = 2
                dataTable.draw();
            });

            $(document).on('click', '.delete_link', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
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
                }).then(function (result) {
                    if (result.isConfirmed) {
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
            });

            $(document).on('click', '.rollback_link', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                let clickedElement = event.currentTarget;
                swal.fire({
                    title: 'Are you sure to roll back this?',
                    text: "",
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
                    if (result.isConfirmed) {
                        $.get(url, {}, function (data, status) {
                            let alertHeader, alertStatus, alertMessage;
                            if (data.status == true) {
                                alertHeader = 'Success';
                                alertStatus = 'success';
                                alertMessage = data.message || 'Rolled back successfully';
                            } else {
                                alertHeader = 'Cancelled';
                                alertStatus = 'error';
                                alertMessage = data.message || 'Something Went Wrong';
                            }
                            successAlert(alertHeader, alertMessage, alertStatus);
                        })
                    }
                })
            });

        });

        $('.search_reset').on('click', function () {
            f = 0
            dataTable.draw();

            $('#office_division_id').val('').change()
            $('#status').val('all').change()
            $('#employee_id_name').val('')
        });

    </script>
@endsection
