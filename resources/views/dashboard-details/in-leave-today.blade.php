@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection
@section('content')
    <div class="row">
        <div class="col-xxl-12">

            <div class="card card-custom card-stretch gutter-b">

                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">In Leave Today</span>
                        <span class="text-muted mt-3 font-weight-bold font-size-sm">Lists</span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-4">
                    <div class="card-body">
                        <table class="table table-responsive-lg" id="dashboardEmployeeTable">
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
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/widget.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#dashboardEmployeeTable').DataTable({
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
                    "url": '{{ route($routeUrl) }}',
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
                            if (type === 'display' && row.current_promotion.office_division) {
                                return row.current_promotion.office_division.name;
                            }
                            return '';
                        },
                        "name": "currentPromotion.officeDivision.name",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.current_promotion.department) {
                                return row.current_promotion.department.name;
                            }
                            return '';
                        },
                        "name": "currentPromotion.department.name",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.current_promotion.designation) {
                                return row.current_promotion.designation.title;
                            }
                            return '';
                        },
                        "name": "currentPromotion.designation.title",
                        orderable: false,
                        sortable: false
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
                    }
                ]
            });

        });
    </script>
@endsection
