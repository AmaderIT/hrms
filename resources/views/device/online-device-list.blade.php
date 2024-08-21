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
                <h3 class="card-label">Attendance Device List</h3>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-bg btn-sm search_reset"
                        style="color: white;background: #3999ff">
                    Refresh
                </button>
            </div>
        </div>
        <!--end::Header-->


        <!--begin::Body-->
        <div class="card-body">
            <table class="table" id="dataTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Hub Name</th>
                    <th scope="col">IP</th>
                    <th scope="col">Last Activity</th>
                    <th scope="col">Active Status</th>
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
    <script src="{{asset('js/list.js')}}"></script>
    <script>
        let dataTable;
        $(document).ready(function () {

            dataTable = $('#dataTable').DataTable({
                processing: true,
                serverSide: false,
                searching: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    url: '{{route('zkteco-device.online-device-list-data')}}',
                    type: 'GET',
                    data: function (d) {
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
                    data.auth_user_id = '{{auth()->user()->uuid }}'
                },
                stateLoadParams: function (settings, data) {

                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {
                        setTimeout(function () {
                            dataTable.page(dataTable.page.info().page).draw('page')
                        }, 2000)
                    }
                },
                columns: [
                    {data: 'alias', name: 'alias', orderable: false, searchable: true},
                    {data: 'ip_address', name: 'ip_address', orderable: false, searchable: true},
                    {data: 'last_activity', name: 'last_activity', orderable: false, searchable: false},
                    {data: 'active_status', name: 'active_status', orderable: false, searchable: true},
                ]
            });


        })
        $('.search_reset').on('click', function () {
            dataTable.ajax.reload();
        });
    </script>

@endsection
