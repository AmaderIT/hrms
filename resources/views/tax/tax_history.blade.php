@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card card-custom" xmlns="http://www.w3.org/1999/html">
        <!--begin::Header-->
        <div class="card-header flex-wrap pt-3 pb-3">
            <div class="card-title">
                <h3 class="card-label">Tax history</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="dropdown dropdown-inline mr-2">

                </div>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <div class="row justify-content-end">
                <div class="col-lg-3">
                    <form action="" method="GET">
                        @can("Search For Employee Tax")
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="search" role="search" placeholder="Search By ID">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                        @endcan
                    </form>
                </div>
            </div>
            <table class="table table-responsive-lg" id="employeeTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Year</th>
                    <th scope="col">Month</th>
                    <th scope="col">Dept</th>
                    <th scope="col">Tax Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($salaries as $salary)
                    <tr>
                        <td>{{ $salary->user->fingerprint_no }}</td>
                        <td>{{ $salary->user->name }}</td>
                        <td>{{ $salary->year }}</td>
                        <td>{{ getMonthNameFromMonthNumber($salary->month) }}</td>
                        <td>{{ $salary->department->name }}</td>
                        <td>{{ $salary->taxable_amount }}</td>
                    </tr>
                @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right">Total :</td>
                        <td>{{ $total }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Card-->
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        // Change Status
        function changeStatus(id) {
            let url = "{{ route('employee.changeStatus', ':employee') }}";
            url = url.replace(":employee", id);

            $.post(url, {}, function (response, status) {
                if(status === "success") {
                    swal.fire({
                        title: "Status updated successfully!!"
                    })
                }
            })
        }

        $(document).ready(function () {
            $('#employeeTable').DataTable({
                "order": [],
                "ordering": false,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
                "searching": false,
                // "bFilter": false,
            });

            // Get department by office division
            $('#office_division_id').change(function(){
                var _officeDivisionID = $(this).val();

                let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
                url = url.replace(":officeDivision", _officeDivisionID);

                $.get(url, {}, function (response, status) {
                    $("#department_id").empty();
                    $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                    $.each(response.data.departments, function(key, value) {
                        $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                    });
                })
            });
        });

        // Enable Select2
        $("select").select2({
            theme: "classic",
        });
    </script>
@endsection
