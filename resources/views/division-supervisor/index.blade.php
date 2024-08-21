@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
    <style>
        .role-btn {
            border: 1px solid black;
            margin: 1px;
        }

        .role-btn:hover {
            color: chocolate;
            background: white;
        }
    </style>
@endsection
@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Division Supervisor Listing
                </h3>
            </div>
            <div class="card-toolbar">
                @can('Create Division Supervisor')
                <a href="{{ route('division-supervisor.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\keen\theme\demo7\dist/../src/media/svg/icons\Code\Plus.svg-->
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
                    Add Division Supervisor
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive-lg" id="employeeTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Fingerprint No.</th>
                    <th scope="col">Name</th>
                    <th scope="col">Division</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Action</th>
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
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#employeeTable').DataTable({
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
                    "url": '{{ route("division-supervisor.datatable") }}',
                },
                "columns": [
                    {
                        "data": "supervised_by.fingerprint_no",
                        "name": "supervisedBy.fingerprint_no",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "supervised_by.name",
                        "name": "supervisedBy.name",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "all_divisions",
                        "name": "all_divisions",
                        orderable: false,
                        sortable: false,
                        searchable: false,
                    },
                    {
                        "data": "supervised_by.email",
                        "name": "supervisedBy.name",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "supervised_by.phone",
                        "name": "supervisedBy.name",
                        orderable: false,
                        sortable: false
                    },
                    {"data": "action", "name": "action", orderable: false, sortable: false, searchable: false},

                ],
            });

        });

        function showListsOfficeDivisionWise(id) {
            if (id == '') {
                alert("Missing ID!!!");
                return false;
            }
            let $templateModal = $('#template_modal');
            let $responseStatus = false;
            $.ajax({
                url: '{{ route("division-supervisor.listsOfficeDivisionWise") }}',
                type: 'POST',
                data: {'office_division_id': id},
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
@endsection
