@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">{{$card_title?? ''}}</h3>
            </div>
        </div>
        <!--end::Header-->

        <div class="card-header col-12 mt-0 mb-3">

            @include('filter.blood-group-filter')

            <div class="col-md-9 mt-8">
                <div class="form-group">

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

            <table class="table table-responsive-lg" id="dataTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Photo</th>
                    <th scope="col">Office ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Personal Phone</th>
                    <th scope="col">Age</th>
                    <th scope="col">Blood Group</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Card-->

@endsection

@section('footer-js')
    @stack('custom-scripts')
    <script type="text/javascript" src="{{ asset('assets/js/widget.js') }}"></script>
    <script>
        var dataTable;

        $(document).ready(function () {
            dataTable = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    url: '{{route("blood-bank.get-datatable")}}',
                    method: 'POST',
                    data: function (d) {
                        d.division_id = $('#office_division_id').val()
                        d.department_id = $('#department_id').val()
                        d.blood_group = $('#blood_group').val()
                    }
                },
                autoWidth: false,
                language: {
                    paginate: {
                        next: '&#8250;',
                        previous: '&#8249;'
                    }
                },
                stateSave: true,
                stateDuration: 7200,
                stateSaveParams: function (settings, data) {

                    data.office_division_id = $('#office_division_id').val()
                    data.department_id = $('#department_id').val()
                    data.blood_group = $('#blood_group').val()
                    data.auth_user_id = '{{auth()->user()->id }}'
                },
                stateLoadParams: function (settings, data) {

                    if (data.auth_user_id == '{{auth()->user()->id }}') {

                        $('#blood_group').val(data.blood_group).change()

                        if (data.office_division_id > 0) {
                            $('#office_division_id').val(data.office_division_id).change()
                        }
                        if (data.department_id > 0) {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change()
                            }, 1000)
                        }
                        setTimeout(function () {
                            dataTable.page(dataTable.page.info().page).draw('page')
                        }, 0)
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
                        "name": "current_promotion.office_division.name",
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
                        "name": "current_promotion.department.name",
                        orderable: false,
                        sortable: false,
                        searchable: false
                    },
                    {"data": "email", "name": "email", orderable: false, sortable: false},
                    {"data": "phone", "name": "phone", orderable: false, sortable: false},
                    {"data": "personal_phone", "name": "profiles.personal_phone", orderable: false, sortable: false},
                    {"data": "age", "name": "age", orderable: false, sortable: false},
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.blood_group) {
                                return row.blood_group;
                            }
                            return '';
                        },
                        "name": "profiles.blood_group",

                    }

                ]
            });

        });

        $(document).on('click', '.search_button', function (e) {
            dataTable.draw();
        });

        $('.search_reset').on('click', function () {

            $('#blood_group').val('all').change()
            dataTable.draw();

        });
        $('#blood_group').select2()
    </script>
@endsection

