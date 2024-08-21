@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection


@section('content')
    <!--begin::Card-->
    <div class="card card-custom" xmlns="http://www.w3.org/1999/html">
        <!--begin::Header-->
        <div class="card-header flex-wrap pt-3 pb-3">
            <div class="card-title">
                <h3 class="card-label">Permission User List({{$permission->name}})</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="example-tools justify-content-center">
                    <a href="{{ route('permission.index') }}" class="btn btn-primary mr-2">Back</a>
                </div>
                <div class="dropdown dropdown-inline mr-2">
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">


                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
                <!--end::Dropdown-->
            </div>
        </div>
        <!--end::Header-->
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
                    <th scope="col">Designation</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
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
    <script>
        $(document).ready(function () {

            $('#dataTable').DataTable({
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
                    "method": "GET",
                    "url": '{{ route("permission.user-list",$permission->id) }}',
                },
                "columns": [
                    {"data": "photo", "name": "photo", orderable: false, sortable: false, searchable: false},
                    {"data": "fingerprint_no", "name": "fingerprint_no"},
                    {"data": "name", "name": "name"},
                    {
                        "data": function (row, type) {

                            if (type == 'display' && row.current_promotion && row.current_promotion.office_division) {
                                return row.current_promotion.office_division.name;
                            }
                            return '--';
                        },
                        "name": "currentPromotion.officeDivision.name"
                    },
                    {
                        "data": function (row, type) {
                            if (type == 'display' && row.current_promotion && row.current_promotion.department) {
                                return row.current_promotion.department.name;
                            }
                            return '--';
                        },
                        "name": "currentPromotion.department.name",
                        searchable: true,
                    },
                    {"data": "current_promotion.designation.title", "name": "currentPromotion.designation.title"},
                    {"data": "email", "name": "email"},
                    {"data": "phone", "name": "phone"},

                ],
            })
            ;
        });

        function imgError(image) {
            image.onerror = "";
            image.src = "{{asset('assets/media/svg/avatars/001-boy.svg')}}";
            return true;
        }

    </script>
@endsection
