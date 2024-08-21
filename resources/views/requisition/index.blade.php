@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <style>
        .table-borderless td {
            padding: 2px;
            margin: 2px;
        }
    </style>
    @php
        $priority = ["Today", "Within 3 days", "Within 7 days", "Within 10 days"];
        $status = ["New", "In Progress", "Delieverd", "Rejected", "Received"];
    @endphp

    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Requisition Listing</h3>
            </div>
            <div class="card-toolbar">
                @can("Send Data to WHMS")
                    <div class="mr-2">
                        <span class="btn btn-primary font-weight-bolder send_whms" style="cursor: pointer">Submit to WHMS</span>
                    </div>
                @endcan

                @can("Create Requisition")
                    <a href="{{ route('requisition.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                             height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path
                                    d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z"
                                    fill="#000000"/>
                            </g>
                        </svg>
                    </span>Add Requisition
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex">
                @can('Export Requisition')
                    <div class="col-lg-8">
                        <form id="exportForm" action="{{ route('requisition.exportCSV') }}" method="GET">
                            <input type="hidden" id="operation_id" name="operation" value="">
                            <div class="input-group">
                                {{-- Date range picker --}}
                                <div class="col-lg-4" id="kt_daterangepicker_2">
                                    <input type="text" class="form-control" readonly="readonly" name="daterangepicker"
                                           id="daterangepicker"
                                           placeholder="Select date range"/>
                                    @error('daterangepicker')
                                    <p class="text-danger"> {{ $errors->first("daterangepicker") }} </p>
                                    @enderror
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <select class="form-control" name="status_id" id="status_id" required>
                                            <option selected disabled>Status</option>
                                            <option value="0" {{ request()->get("status_id") == 0 ? "selected" : "" }}>
                                                New
                                            </option>
                                            <option value="1" {{ request()->get("status_id") == 1 ? "selected" : "" }}>
                                                In Progress
                                            </option>
                                            <option value="2" {{ request()->get("status_id") == 2 ? "selected" : "" }}>
                                                Delivered
                                            </option>
                                            <option value="4" {{ request()->get("status_id") == 4 ? "selected" : "" }}>
                                                Received
                                            </option>
                                            <option value="3" {{ request()->get("status_id") == 3 ? "selected" : "" }}>
                                                Rejected
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <select class="form-control" name="serve_status" id="serve_status">
                                            <option value="" selected>Select</option>
                                            <option value="0">Not Submitted</option>
                                            <option value="1">Submitted</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button class="btn btn-outline-primary filter_button" type="button">Filter
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-primary filter_button" type="submit">Export
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endcan

            </div>
            <table class="table" id="dataTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col"><input type="checkbox" class="checkbox" data-id="0" onchange="setItemChecked(this)">
                    </th>
                    <th style="display: none"></th>
                    <th scope="col">Order No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Department</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">WHMS Status</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>

    {{-- Requisition Modal --}}

    <div class="modal fade" id="requisitionModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Departmental Requisition Form for Office Supplies
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table table-borderless text-left">
                                <tbody>
                                <tr>
                                    <td width="50%"><strong>Order No: <span id="m-orderId"></span> </strong></td>
                                    <td width="50%"><strong>Date: <span id="m-date"></span> </strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Department: <span id="m-dpt"></span></strong></td>
                                    <td><strong>Status: <span id="m-sts"></span></strong></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-0">
                        <div class="col-lg-12">
                            <table class="table table-borderless text-left">
                                <tbody>
                                <tr>
                                    <td width="55%">
                                        <strong>Priority: <span id="m-prty"></span></strong></td>
                                    <td width="45%"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-0">
                        <div class="col-lg-12">
                            <h4 style="text-align: center; vertical-align: middle;"><strong>Details of
                                    Requisition</strong></h4>
                            <table class="table table-bordered text-center">
                                <thead>
                                <tr>
                                    <td width="10%">SL No.</td>
                                    <td width="40%">Item</td>
                                    <td width="20%">Requested Quantity (Pcs)</td>
                                    <td width="30%">Received Quantity (Pcs)</td>
                                </tr>
                                </thead>
                                <tbody id="m-items">

                                <tr>
                                    <td width="5%"></td>
                                    <td width="40%"></td>
                                    <td width="20%"></td>
                                    <td width="30%"></td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-0">
                        <div class="col-lg-12">
                            <table class="table table-borderless text-left">
                                <tbody>
                                <tr>
                                    <td width="55%"><strong>Remarks: <span id="m-remarks"></span></strong></td>
                                    <td width="45%"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-daterangepicker.js') }}"></script>

    <script type="text/javascript">
        var tbl;
        $(document).ready(function () {
            tbl = $('#dataTable').DataTable({
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
                    "url": '{{ route("requisition.datatable") }}',
                    "data": function (d) {
                        d.extra_search = $('#extra').val();
                        d.daterangepicker = $('#daterangepicker').val();
                        d.status_id = $('#status_id').val();
                        d.serve_status = $('#serve_status').val();
                    }
                },

                "columns": [
                    {"data": "is_checked", "name": "is_checked", orderable: false, sortable: false, searchable: false},
                    {"data": "id", "name": "id", searchable: true, visible: false},
                    {"data": "order_id", "name": "order_id", orderable: false, sortable: false, searchable: false},

                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.applied_by) {
                                return row.applied_by.name;
                            }
                            return '';
                        },
                        "name": "appliedBy.name"
                    },


                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.department) {
                                return row.department.name;
                            }
                            return '';
                        },
                        "name": "department.name",
                    },
                    {
                        "data": "applied_date",
                        "name": "applied_date",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                    {"data": "status", "name": "status"},
                    {"data": "serve_status", "name": "serve_status"},
                    {"data": "action", "name": "action", orderable: false, sortable: false, searchable: false},

                ],
            });


        });


        $(document).on('click', '.export_button', function (e) {
            $('#operation_id').val('export');
            $('#exportForm').submit();
        });


        $(document).on('click', '.filter_button', function (e) {
            tbl.draw()
        });


        $(document).on('click', '.send_whms', function (e) {
            e.preventDefault();

            if (checkedItems.length < 1) {
                notify().error("Please check at least one item to send WHMS!")
            } else {
                $.ajax({
                    url: '{{route("requisition.exportCSV")}}',
                    type: 'GET',
                    data: {
                        ids: checkedItems,
                        'operation': 'send',
                        'status_id': $('#status_id').val()
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            notify().success(res.message)
                            tbl.draw()
                            checkedItems = [];
                        } else {
                            notify().error(res.message)
                        }
                    },
                    error: function (res) {
                        console.log(res)
                    }
                })
            }
        });


        var checkedItems = [];

        function setItems() {
            checkedItems = [];
            $('#dataTable tr').each(function () {
                var keval = $(this).find(".checkbox");
                if (keval.prop('checked') && keval.data('id') > 0) {
                    checkedItems.push(keval.data('id'))
                }
            });
        }

        function setItemChecked(t) {
            var id = $(t).data('id');
            if (id == 0) {
                if ($(t).prop('checked')) {
                    $('.checkbox').prop('checked', true)
                } else {
                    $('.checkbox').prop('checked', false)
                }
            }
            setItems();
            console.log(checkedItems)
        }

        function showDetails(t) {

            var id = $(t).data('id');

            $.ajax({
                url: '{{route("requisition.get-details")}}',
                type: 'POST',
                data: {
                    id: id
                },
                success: function (res) {
                    $('#m-orderId').html(res.data.id)
                    $('#m-date').html(res.data.appliedDate)
                    $('#m-dpt').html(res.data.department.name)
                    $('#m-sts').html(res.data.status)
                    $('#m-prty').html(res.data.priority)
                    $('#m-remarks').html(res.data.remarks)
                    var items = '';
                    $.each(res.data.details, function (x, y) {
                        items += '<tr>';
                        items += '<td>' + (x + 1) + '</td>';
                        items += '<td>' + y.item.name + '</td>';
                        items += '<td>' + y.quantity + '</td>';
                        items += '<td>' + y.received_quantity + '</td>';
                        items += '</tr>';
                    })
                    $('#m-items').html(items)
                },
                error: function (res) {
                    console.log(res)
                }
            })
        }


    </script>


@endsection
