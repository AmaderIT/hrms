@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
    <style>
        .dataTables_length {
            position: absolute;
            right: 0;
            top: -2.5rem;
        }
        table.dataTable.no-footer {
            border-top-left-radius: 0;
        }
        .dataTables_wrapper .dataTable {
            margin-top: 0 !important;
        }
        .nav-tabs .nav-link {
            padding: 0.5rem 2.5rem 0.4rem 1.5rem;
            border-top-right-radius: 2.5rem;
            border-color: #EBEDF3 #EBEDF3 transparent #EBEDF3 ;
            position: relative;
            color: #686868;
            font-weight: 600;
            letter-spacing: 1;
        }
        .nav-tabs .nav-link.nav-assigned {
            background-color: rgba(135, 247, 23, 0.5);
        }
        .nav-tabs .nav-link.nav-non-assign {
            background-color: rgba(255, 232, 124, 0.5);
        }
        .nav-tabs .nav-link.nav-archived {
            background-color: rgba(217, 217, 217, 0.5);
        }
        .nav-tabs .nav-link.nav-assigned.active {
            background-color: rgba(135, 247, 23, 1);
            color: #686868;
        }
        .nav-tabs .nav-link.nav-non-assign.active {
            background-color: rgba(255, 232, 124, 1);
            color: #686868;
        }
        .nav-tabs .nav-link.nav-archived.active {
            background-color: rgba(217, 217, 217, 1);
            color: #686868;
        }
        .nav-tabs .nav-link:hover, .nav-tabs .nav-link:focus {
            border-color: #EBEDF3 #EBEDF3 transparent #E4E6EF;
        }
        .nav-tabs .nav-link.active {
            border-color: #3999ff #3999ff transparent #3999ff;
        }
        .tab-content table {
            position: relative;
            overflow: hidden;
        }

    </style>
@endsection
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Relax Day List</h3>
            </div>
        </div>
        <!--end::Header-->

        <div class="card-header row">

            <div class="col-md-4">
                {{-- Office Divisions --}}
                <div class="form-group">
                    <label for="office_division_id">Office Divisions</label>
                    <select class="form-control" id="office_division_id" name="office_division_id" required>
                        <option value="" selected>Select an option</option>
                        @foreach($officeDivisions as $officeDivision)
                            <option value="{{ $officeDivision->id }}"> {{ $officeDivision->name }} </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Department --}}
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select class="form-control" id="department_id" name="department_id" required>
                        <option value="" selected>Select an option</option>
                        @foreach($officeDepartments as $officeDepartment)
                            <option value="{{ $officeDepartment->id }}"> {{ $officeDepartment->name }} </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="datepicker">Date</label>
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD" name="datepicker" id="datepicker" autocomplete="off" required/>
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


        <!--begin::Body-->
        <div class="card-body">
            <ul class="nav nav-tabs mb-n1" role="tablist">
                <li class="nav-item m-0" role="presentation">
                    <a class="nav-link nav-assigned active" href="#assigned-tab-table" data-toggle="tab">Assigned</a>
                </li>
                <li class="nav-item m-0" role="presentation">
                    <a class="nav-link nav-non-assign" href="#not-assigned-tab-table" data-toggle="tab">Non Assigned</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link nav-archived" href="#archived-tab-table" data-toggle="tab">Archived</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="assigned-tab-table">
                    <table class="table mt-0" id="relaxdayTable">
                        <thead class="custom-thead">
                        <tr>
                            <th scope="col" style="display: none">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Department</th>
                            <th scope="col">Type</th>
                            <th scope="col">Assigned</th>
                            <th scope="col">Approved</th>
                            <th scope="col">Note</th>
                            <th scope="col">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="not-assigned-tab-table">
                    <table class="table mt-0" id="relaxdayTableFuture">
                        <thead class="custom-thead">
                        <tr>
                            <th scope="col" style="display: none">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Department</th>
                            <th scope="col">Type</th>
                            <th scope="col">Assignable</th>
                            <th scope="col">Note</th>
                            <th scope="col">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="archived-tab-table">
                    <table class="table mt-0" id="relaxdayArchive">
                        <thead class="custom-thead">
                        <tr>
                            <th scope="col" style="display: none">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Department</th>
                            <th scope="col">Type</th>
                            <th scope="col">Assigned</th>
                            <th scope="col">Approved</th>
                            <th scope="col">Note</th>
                            <th scope="col">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Card-->


    <div class="modal fade" id="exampleModalDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Assigned Employees [Relax Day : <span
                            id="relax_date_label"></span>]</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body details_modal_body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



@endsection

@section('footer-js')
    <script src="{{asset('js/list.js')}}"></script>
    <script>
        let dataTable;
        let dataTableFutureData;
        let dataTableArchiveData;
        $(document).ready(function () {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                if(this.getAttribute("href") == '#not-assigned-tab-table') dataTableFutureData.draw();
                if(this.getAttribute("href") == '#assigned-tab-table') dataTable.draw();
                if(this.getAttribute("href") == '#assigned-tab-table') dataTableArchiveData.draw();
            });

            let all_departments = {!! $officeDepartments !!};
            $('#office_division_id').select2();
            $('#department_id').select2();
            $("#datepicker").datepicker({
                format: "yyyy-mm-dd",
                autoclose: true
            });


            dataTable = $('#relaxdayTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    url: '{{route('assign-relax-day.index')}}',
                    type: 'GET',
                    data: function (d) {
                        d.division_id = $('#office_division_id').val();
                        d.department_id = $('#department_id').val();
                        d.datepicker = $('#datepicker').val();
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
                    data.office_division_id = $('#office_division_id').val();
                    data.department_id = $('#department_id').val();
                    data.auth_user_id = '{{auth()->user()->uuid }}';
                    data.datepicker = $('#datepicker').val();
                },
                stateLoadParams: function (settings, data) {
                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {

                        if (data.office_division_id > 0) {
                            $('#office_division_id').val(data.office_division_id).change()
                        }
                        if (data.datepicker != '') {
                            setTimeout(function () {
                                $('#datepicker').val(data.datepicker).change();
                            }, 1000)
                        }
                        if (data.department_id > 0) {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change()
                            }, 1000)
                        }
                        setTimeout(function () {

                            dataTable.page(dataTable.page.info().page).draw('page')
                        }, 2000)
                    }
                },
                columns: [
                    {data: 'id', name: 'id', visible: false},
                    {data: 'date', name: 'date', orderable: false, searchable: false},
                    {data: 'department_name', name: 'department_name', orderable: false, searchable: false},
                    {data: 'type', name: 'type', orderable: false, searchable: false},
                    {data: 'assignee', name: 'assignee', orderable: false, searchable: false},
                    {data: 'assignee_approved', name: 'assignee_approved', orderable: false, searchable: false},
                    {data: 'note', name: 'note', orderable: false, searchable: false},
                    {data: "action", orderable: false, searchable: false}
                ]
            });

            $(document).on('click', '.detail_link', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                var id = $(this).data('id');
                let data = {
                    '_token': '{{csrf_token()}}',
                    'relax_day_id': id,
                };
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType: "json",
                    success: function (result) {
                        $('.details_modal_body').html(result.html);
                        $('#exampleModalDetails').modal('toggle');
                    }
                });
            });

            // approve
            $(document).on('click', '.approve_link', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                var id = $(this).data('id');
                let data = {
                    '_token': '{{csrf_token()}}',
                    'relax_day_id': id,
                };
                swal.fire({
                    title: 'Are you sure to approve this?',
                    text: "To revert you need to edit the assignee list again!",
                    icon: 'success',
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
                        $.post(url, data, function (data, status) {
                            let alertHeader, alertStatus, alertMessage;
                            if (data.status == true) {
                                alertHeader = 'Success';
                                alertStatus = 'success';
                                alertMessage = data.message || 'Approved Successfully';
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

            // delete
            $(document).on('click', '.delete_link', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                var id = $(this).data('id');
                let data = {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                };
                swal.fire({
                    title: 'Are you sure to delete this?',
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
                        $.post(url, data, function (data, status) {
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

            // data table future data
            dataTableFutureData = $('#relaxdayTableFuture').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    url: '{{route('assign-relax-day.not-assign')}}',
                    type: 'GET',
                    data: function (d) {
                        d.division_id = $('#office_division_id').val();
                        d.department_id = $('#department_id').val();
                        d.datepicker = $('#datepicker').val();
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
                    data.office_division_id = $('#office_division_id').val();
                    data.department_id = $('#department_id').val();
                    data.auth_user_id = '{{auth()->user()->uuid }}';
                    data.datepicker = $('#datepicker').val();
                },
                stateLoadParams: function (settings, data) {

                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {

                        if (data.office_division_id > 0) {
                            $('#office_division_id').val(data.office_division_id).change()
                        }
                        if (data.datepicker != '') {
                            setTimeout(function () {
                                $('#datepicker').val(data.datepicker).change();
                            }, 1000)
                        }
                        if (data.department_id > 0) {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change()
                            }, 1000)
                        }
                        setTimeout(function () {
                            dataTableFutureData.page(dataTableFutureData.page.info().page).draw('page')
                        }, 2000)
                    }
                },
                columns: [
                    {data: 'id', name: 'id', visible: false},
                    {data: 'date', name: 'date', orderable: false, searchable: false},
                    {data: 'department_name', name: 'department_name', orderable: false, searchable: false},
                    {data: 'type', name: 'type', orderable: false, searchable: false},
                    {data: 'assignable', name: 'assignable', orderable: false, searchable: false},
                    {data: 'note', name: 'note', orderable: false, searchable: false},
                    {data: "action", orderable: false, searchable: false}
                ]
            });

            // archive datatable
            dataTableArchiveData = $('#relaxdayArchive').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    url: '{{route('assign-relax-day.archived')}}',
                    type: 'GET',
                    data: function (d) {
                        d.division_id = $('#office_division_id').val();
                        d.department_id = $('#department_id').val();
                        d.datepicker = $('#datepicker').val();
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
                    data.office_division_id = $('#office_division_id').val();
                    data.department_id = $('#department_id').val();
                    data.auth_user_id = '{{auth()->user()->uuid }}';
                    data.datepicker = $('#datepicker').val();
                },
                stateLoadParams: function (settings, data) {

                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {

                        if (data.office_division_id > 0) {
                            $('#office_division_id').val(data.office_division_id).change()
                        }
                        if (data.datepicker != '') {
                            setTimeout(function () {
                                $('#datepicker').val(data.datepicker).change();
                            }, 1000)
                        }
                        if (data.department_id > 0) {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change()
                            }, 1000)
                        }
                        setTimeout(function () {
                            dataTableArchiveData.page(dataTableArchiveData.page.info().page).draw('page')
                        }, 2000)
                    }
                },
                columns: [
                    {data: 'id', name: 'id', visible: false},
                    {data: 'date', name: 'date', orderable: false, searchable: false},
                    {data: 'department_name', name: 'department_name', orderable: false, searchable: false},
                    {data: 'type', name: 'type', orderable: false, searchable: false},
                    {data: 'assignee', name: 'assignee', orderable: false, searchable: false},
                    {data: 'assignee_approved', name: 'assignee_approved', orderable: false, searchable: false},
                    {data: 'note', name: 'note', orderable: false, searchable: false},
                    {data: "action", orderable: false, searchable: false}
                ]
            });

            $(document).on('click', '.search_button', function (e) {
                dataTable.draw();
                dataTableFutureData.draw();
                dataTableArchiveData.draw();
            });

            $('#office_division_id').change(function () {
                let division_id = $(this).val();
                if (division_id == '') {
                    var items = '<option value="">Select an option</option>';
                    $.each(all_departments, function (x, y) {
                        items += '<option value="' + y.id + '">' + y.name + '</option>';
                    })
                    $('#department_id').html(items);
                } else {
                    $.ajax({
                        url: '{{route("filter.get-department")}}',
                        data: {
                            office_division_id: division_id
                        },
                        success: function (res) {
                            $('#department_id').empty();
                            var items = '<option value="">Select an option</option>';
                            $.each(res.data, function (x, y) {
                                items += '<option value="' + y.id + '">' + y.name + '</option>';
                            })
                            $('#department_id').append(items)
                        },
                        error: function (err) {
                            console.log(err)
                        }
                    })
                }
            });

            $('.search_reset').on('click', function () {
                $('#office_division_id').val('').change();
                $('#department_id').val('').change();
                $('#datepicker').val('').change();
                dataTable.draw();
                dataTableFutureData.draw();
                dataTableArchiveData.draw();
            });


        });











    </script>

@endsection
