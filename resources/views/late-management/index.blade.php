@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection
@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Late Management</h3>
            </div>
            <div class="card-toolbar">

            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive-lg" id="lateManagementDataTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Department Name</th>
                    <th scope="col">Days Late</th>
                    <th scope="col">Equivalent Working Day</th>
                    <th scope="col">Deduction Method</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('footer-js')
    <script>
        $(document).ready(function () {
            var oTable = $('#lateManagementDataTable').DataTable({
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
                "ajax": {
                    "method": "POST",
                    "url": '{{ route("late-management.get-data-table") }}',
                    data: function (d) {

                    }
                },
                "columns": [
                    {
                        "data": function (row, type, set) {
                            if (type === 'display' && row.department) {
                                return row.department.name;
                            }
                            return '';
                        },
                        "name": "department.name",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "total_days",
                        "name": "total_days",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "deduction_day",
                        "name": "deduction_day",
                        orderable: false,
                        sortable: false
                    },
                    {
                        "data": "type",
                        "name": "type",
                        orderable: false,
                        sortable: false
                    },
                        @if(auth()->user()->can("Edit Late Management"))
                    {
                        "data": "action", "name": "action", orderable: false, sortable: false, searchable: false
                    },
                    @endif
                ]
            });
        });
    </script>
@endsection

