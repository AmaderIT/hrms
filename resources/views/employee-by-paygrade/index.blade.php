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
                <h3 class="card-label">Employee Listing</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <div class="d-flex">
                <div class="col-lg-10">

                    <div class="input-group">
                        {{-- PayGrade --}}
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="pay_grade_id">From</label>
                                <select class="form-control" name="pay_grade_id" id="pay_grade_id">
                                    <option selected disabled>Pay Grade</option>
                                    @foreach($data["payGrades"] as $payGrade)
                                        <option value="{{ $payGrade->id }}">{{ $payGrade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="pay_grade_id_to">To</label>
                                <select class="form-control" name="pay_grade_id_to" id="pay_grade_id_to">
                                    <option selected disabled>Pay Grade</option>
                                    @foreach($data["payGrades"] as $payGrade)
                                        <option value="{{ $payGrade->id }}">{{ $payGrade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <input class="btn btn-sm btn-primary mt-md-7 mt-0" type="button" value="Submit" onclick="modifyPayGrade()"/>
                        </div>
                    </div>
                </div>

                <div class="ml-auto">
                    <form action="{{ route('employee.index') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="search" role="search">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="employeeTableWrapper">
                <table class="table table-responsive-lg" id="employeeTable">
                    <thead class="custom-thead">
                    <tr>
                        <th scope="col">
                            <input type="checkbox" id="selectAll"/>
                        </th>
                        <th scope="col">Photo</th>
                        <th scope="col">Office ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Department</th>
                        <th scope="col">Designation</th>
                        <th scope="col">PayGrade</th>
                        <th scope="col">Salary</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                    </tr>
                    </thead>
                    <tbody id="employeeByPayGradeData"></tbody>
                </table>
            </div>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Card-->
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">
        $("#employeeTableWrapper").css("display", "none");

        var _ids = [];
        var _payGrade = null;
        var _payGradeTo = null;

        function modifyPayGrade() {
            let url = "{{ route('employee-by-paygrade.modifyEmployeePayGrade') }}";

            let data = {
                user_id: _ids,
                pay_grade_id: _payGrade,
                pay_grade_id_to: _payGradeTo
            };

            console.log(data);

            $.post(url, data, function (response, status) {
                if (status == "success" && response.success == true) {
                    getEmployeesByPayGrade(data.pay_grade_id);
                    notify().success("Pay Grade Updated Successfully!!");
                }
            })
        }

        /**
         * Toggle Check Employee IDS
         **/
        function employeeId() {
            $(".employeeID").change(function () {
                var id = this.value;
                if(this.checked) {
                    if(_ids.indexOf(id) === -1) {
                        _ids.push(id);
                    }
                } else if(!this.checked) {
                    _ids.splice(_ids.indexOf(id), 1)
                }
            });
        }

        /**
         * Toggle Select All
         **/
        $("#selectAll").on("change", function () {
            if(this.checked) {
                $('input:checkbox').prop('checked', this.checked);

                $(".employeeID:checked").each(function() {
                    _ids.push($(this).val());
                });
            } else if(!this.checked) {
                $('input:checkbox').prop("checked", false);
                _ids.splice(0, _ids.length)
            }
            $.unique(_ids)
        });

        $("#pay_grade_id_to").change(function (e) {
            _payGradeTo = $(this).val();
        });

        /**
         * Fetch Employee by their salary range from PayGrade
         **/
        $("#pay_grade_id").change(function (e) {
            _payGrade = $(this).val();
            getEmployeesByPayGrade(_payGrade);
        });

        function getEmployeesByPayGrade(PayGradeId)
        {
            $("#employeeTableWrapper").css("display", "");

            $("#employeeByPayGradeData").empty();

            let data = {
                pay_grade_id: PayGradeId
            }

            let url = "{{ route('employee-by-paygrade.getEmployeeByPayGrade', ':payGrade') }}";
            url = url.replace(":payGrade", data.pay_grade_id);

            let table = null;
            $.post(url, data, function (response, status) {
                if(status == "success") {
                    for (var i = 0; i < response.data.length; i++) {
                        var _id = response.data[i].id;
                        var _photo = response.data[i].fingerprint_no+".jpg";
                        var _officeId = response.data[i].fingerprint_no;
                        var _name = response.data[i].name;
                        var _department = response.data[i].current_promotion.department.name;
                        var _designation = response.data[i].current_promotion.designation.title;
                        var _payGrade = response.data[i].current_promotion.pay_grade.name;
                        var _salary = response.data[i].current_promotion.salary;
                        var _email = response.data[i].email;
                        var _phone = response.data[i].phone;

                        table = '<tr>' +
                            '<td scope="row">' + '<input type="checkbox" name="employee_id[]" value="' + _id + '" class="employeeID" onclick="employeeId()" />' + '</td>' +
                            '<td>' +
                            '   <div class="symbol flex-shrink-0" style="width: 35px; height: auto">' +
                            '       <img src="photo/' + _photo + '" alt="' + _name + '" />' +
                            '   </div>' +
                            '</td>' +
                            '<td>' + _officeId + '</td>' +
                            '<td>' + _name + '</td>' +
                            '<td>' + _department + '</td>' +
                            '<td>' + _designation + '</td>' +
                            '<td>' + _payGrade + '</td>' +
                            '<td>' + _salary + '</td>' +
                            '<td>' + _email + '</td>' +
                            '<td>' + _phone + '</td>' +
                            '</tr>'
                        ;

                        $("#employeeByPayGradeData").append(table);
                    }
                }
            });
        }

        // Enable Select2
        $("select").select2({
            theme: "classic",
        });
    </script>
@endsection
