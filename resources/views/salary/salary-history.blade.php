@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class="card mb-2">
        <div class="card-header p-4">
            Filters
        </div>
        <div class="card-body">
            <form class="d-block" action="" method="get">
                <div class="row m-auto">
                    <div class="row col-12 justify-content-start mb-2">
                        <div class="col-4">
                            <span>Type Year</span>
                            <input class="mb-2 w-100" type="text" name="year_alone" placeholder="Type in Year" style="height: 30px;" required/>
                        </div>

                        <div class="col-4">
                            <span>Choose Division</span>
                            <select class="select w-100" id="office_division_id" name="office_division_id" style="height: 30px;">
                                <option selected disabled>Choose an option</option>
                                @foreach($data["officeDivisions"] as $officeDivision)
                                    <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                        {{ $officeDivision->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-4">
                            <span>Choose Department</span>
                            <select class="form-control select w-100" name="department_id[]" id="department_id" multiple style="height: 30px;">
                            </select>
                        </div>

                        <div class="col-4">
                            <span>Choose Payment Status</span>
                            <select class="select w-100" id="payment_status" name="payment_status" style="height: 30px;">
                                <option value="0">Unpaid</option>
                                <option value="1">Paid</option>
                            </select>
                        </div>

                        <div class="col-4">
                            <button class="btn btn-sm btn-primary px-6 mt-5" type="submit">Filter</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

{{--    <div class="row">--}}
{{--        <div class="col-xl-5 dashboard-card" style="height: 140px">--}}
{{--            <div class="card card-custom card-stretch gutter-b">--}}
{{--                <div class="card-header border-0 pt-6 font-size-h3 font-weight-bolder">--}}
{{--                    Total Salary Amount--}}
{{--                    <p class="text-primary" id="totalAmount">{{ $result["amount"]["total"]  }}</p>--}}
{{--                </div>--}}
{{--                <div class="card-body d-flex align-items-center justify-content-between">--}}
{{--                    <h5 class="py-4 mb-10">--}}
{{--                        <span> Paid: </span> <span class="text-primary ml-10" id="paidAmount">{{ $result["amount"]["paid"] }}</span><br>--}}
{{--                        <span> Unpaid: </span> <span class="text-primary ml-3" id="unpaidAmount">{{ $result["amount"]["unpaid"] }}</span> <br/>--}}
{{--                    </h5>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    @foreach($result["salaryByDepartment"]->toArray() as $key1 => $months)

        <div class="row">
            <div class="col-xxl-12">
                    <div class="card card-custom card-stretch gutter-b">
                        <div class="card-header border-0 pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label font-weight-bold font-size-h4 text-dark-75" id="salaryTitle">SALARY OF {{ getMonthNameFromMonthNumber($key1) }}</span>
                                <span class="text-muted mt-3 font-weight-bold font-size-sm">SALARY BY DEPARTMENT</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0 pb-4">
                            <div class="card-body">
                                <table class="table table-responsive-lg" id="leaveRequestToAdmin{{ $key1 }}">
                                    <thead class="custom-thead">
                                    <tr>
                                        <th scope="col">Department</th>
                                        <th scope="col">Office Division</th>
                                        <th scope="col">Total Amount</th>
                                        <th scope="col">Year</th>
                                        <th scope="col">Month</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody id="resultData">
                                    @foreach($months as $key2 => $month)
                                        <tr>
                                            <td>{{ $month["department"]["name"] }}</td>
                                            <td>{{ $month["office_division"]["name"] }}</td>
                                            <td>{{ $month["total_payable_amount"] }}</td>
                                            <td>{{ $month["year"] }}</td>
                                            <td>{{ getMonthNameFromMonthNumber($month["month"]) }}</td>
                                            <td>{{ $month["status"] === "1" ? "Paid" : "Unpaid" }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    @endforeach
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        var _ids = [];
        let monthAndYear = null;
        let paymentStatus = 0;

        /*$("#datepicker").datepicker( {
            format: "mm",
            startView: "months",
            minViewMode: "months"
        });*/

        /**
         * Filter Salary by Department
         **/

        $("#leaveRequestToAdmin").DataTable({
            "ordering": false,
            "bInfo" : false,
            "bPaginate" : false,
            "searching" : true
        });




        /**
         * Toggle Check Employee IDS
         **/
        function salaryDepartmentID() {
            $(".salaryDepartmentID").change(function () {
                var id = this.value;
                if(this.checked) {
                    if(_ids.indexOf(id) === -1) _ids.push(id);
                } else if(this.checked === false) {
                    if(_ids.indexOf(id) !== - 1) _ids.splice(_ids.indexOf(id), 1);
                }
            });
        }

        /**
         * Toggle Select All
         **/
        $(".selectAll").on("change", function () {
            if(this.checked) {
                $(this).parent().parent().parent().siblings("tbody").first().find("input:checkbox").prop('checked', this.checked);

                $(".salaryDepartmentID:checked").each(function() {
                    _ids.push($(this).val());
                });
            } else if(!this.checked) {
                $(this).parent().parent().parent().siblings("tbody").first().find("input:checkbox").prop('checked', false);
                _ids.splice(0, _ids.length)
            }
            $.unique(_ids)
        });

        /**
         * Pay Salary by Department
         **/
        $(".paySalaryByDepartment").click(function () {
            let url = "{{ route('salary.paySalaryByDepartment') }}";
            let data = {
                department_id: _ids,
                monthAndYear: monthAndYear
            };

            $.post(url, data, function (response, status) {
                if (status === "success") {
                    notify().success("Salary has been paid successfully!!");
                    filterSalaryByDepartment()
                }
            });
        });

        /**
         *
         * @param monthNumber
         * @returns {*}
         * @constructor
         */
        function GetMonthName(monthNumber) {
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return months[monthNumber - 1];
        }

        // Get department by division
        $('#office_division_id').change(function(){
            var _officeDivisionID = $(this).val();

            let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
            url = url.replace(":officeDivision", _officeDivisionID);

            $.get(url, {}, function (response, status) {
                $("#department_id").empty();
                $("#department_id").append('<option value="" disabled>Select an option</option>');
                $.each(response.data.departments, function(key, value) {
                    $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
            })
        });

        // Get employees by their corresponding department
        $('#department_id').change(function() {

            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let data = {
                "_token": CSRF_TOKEN,
                "department_id": $(this).val()
            }

            if($(this).val().length > 1) $("#div_employee_field").css("display", "none");
            else if($(this).val().length == 1) $("#div_employee_field").css("display", "");

            let url = "{{ route('employee.getEmployeeByDepartment') }}";
            $.post(url, data, function (response, status) {
                $("#user_id").empty();
                $("#user_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data, function(key, value) {
                    $("#user_id").append('<option value="' + value.id + '">'+ value.fingerprint_no + ' - ' + value.name + '</option>');
                });
            })
        });

        $("select").select2({
            theme: "classic",
        });

        $("#datepicker").datepicker( {
            format: "mm-yyyy",
            startView: "months",
            changeMonth: true,
            changeYear: false,
            minViewMode: "months"
        });
    </script>
@endsection
