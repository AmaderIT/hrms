@extends('layouts.app')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Public Holiday List</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="svg-icon svg-icon-md">
                            <!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px"
                                 viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"/>
                                    <path
                                        d="M3,16 L5,16 C5.55228475,16 6,15.5522847 6,15 C6,14.4477153 5.55228475,14 5,14 L3,14 L3,12 L5,12 C5.55228475,12 6,11.5522847 6,11 C6,10.4477153 5.55228475,10 5,10 L3,10 L3,8 L5,8 C5.55228475,8 6,7.55228475 6,7 C6,6.44771525 5.55228475,6 5,6 L3,6 L3,4 C3,3.44771525 3.44771525,3 4,3 L10,3 C10.5522847,3 11,3.44771525 11,4 L11,19 C11,19.5522847 10.5522847,20 10,20 L4,20 C3.44771525,20 3,19.5522847 3,19 L3,16 Z"
                                        fill="#000000" opacity="0.3"/>
                                    <path
                                        d="M16,3 L19,3 C20.1045695,3 21,3.8954305 21,5 L21,15.2485298 C21,15.7329761 20.8241635,16.200956 20.5051534,16.565539 L17.8762883,19.5699562 C17.6944473,19.7777745 17.378566,19.7988332 17.1707477,19.6169922 C17.1540423,19.602375 17.1383289,19.5866616 17.1237117,19.5699562 L14.4948466,16.565539 C14.1758365,16.200956 14,15.7329761 14,15.2485298 L14,5 C14,3.8954305 14.8954305,3 16,3 Z"
                                        fill="#000000"/>
                                </g>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>Export
                    </button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <form id="public_holiday_download_form" method="POST" action="">
                                <input type="hidden" name="_token" value="'.csrf_token().'">
                            </form>

                            <li class="navi-item">
                                <a href="{{url('public-holiday/public-holiday-download')}}" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-pdf-o"></i>
                                    </span>
                                    <span class="navi-text download_link">PDF</span>
                                </a>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
                <!--end::Dropdown-->
                <!--begin::Button-->
                @can('Add Public Holidays')
                    <a href="{{ route('public-holiday.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\keen\theme\demo7\dist/../src/media/svg/icons\Code\Plus.svg-->
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
                    </span>Add Public Holiday</a>
            @endcan
            <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-header col-12 mt-6 mb-3">
                <div class="col-md-12">
                    <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="">Year</label>
                            <input class="form-control ml-2 w-75" id="year" name="year"/>
                        </div>

                    </div>
                    <div class="col-md-4">
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
            </div>


        </div>

        <div class="card-body">
            <table class="table" id="publicHolidayTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">From date</th>
                    <th scope="col">To date</th>
                    <th scope="col">Day Count</th>
                    <th scope="col">Remarks</th>
                    <th scope="col">Actions</th>
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
    <script src="{{asset('js/list.js')}}"></script>
    <script>
        var dataTable;

        $(document).ready(function () {
            dataTable = $('#publicHolidayTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    ur: '{{ route('public-holiday.index') }}',
                    data: function (d) {
                        d.year = $('#year').val()
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
                    data.pageX = pageX
                    data.pageY = pageY
                },
                stateLoadParams: function (settings, data) {
                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    {data: 'holiday.name', name: 'holiday.name'},
                    {data: 'from_date', name: 'to_date', orderable: true, searchable: true},
                    {data: 'to_date', name: 'to_date', orderable: true, searchable: true},
                    {data: 'day_count', name: 'day_count', orderable: true, searchable: true},
                    {data: 'remarks', name: 'remarks', orderable: true, searchable: false},
                    {data: "action", orderable: false, searchable: false}
                ]
            });
        });

        $(document).on('click', '.search_button', function (e) {
            dataTable.draw();
        });
        $('.search_reset').on('click', function () {
            dataTable.draw();
            $('#year').val('')
        });
        $("#year").datepicker({
            format: "yyyy",
            startView: "years",
            changeMonth: false,
            changeYear: true,
            minViewMode: "years",
        });
        setTimeout(function () {
            $("#year").val('{{date("Y")}}')
        }, 1000)
    </script>
@endsection
