@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Employee Salary</h3>
                </div>
                <!--begin::Form-->
                <form action="{{ route('salary.generateSalary') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            {{-- Year --}}
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="monthAndYear">Month</label>
                                    <input type="text" class="form-control" name="monthAndYear" id="monthAndYear"
                                           {{--                                           value="{{ date("m") . "-" . date("Y") }}" --}}
                                           autocomplete="off" required/>
                                    @error('monthAndYear')
                                    <p class="text-danger"> {{ $errors->first("monthAndYear") }} </p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Office Divisions --}}
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="office_division_id">Division</label>
                                    <select class="form-control" id="office_division_id" name="office_division_id">
                                        <option selected disabled>Choose an option</option>
                                        <option value="all">All</option>
                                        @foreach($data["officeDivisions"] as $officeDivision)
                                            <option value="{{ $officeDivision->id }}" {{ $officeDivision->id == old("office_division_id") ? 'selected' : '' }}>
                                                {{ $officeDivision->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error("office_division_id")
                                    <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Department --}}
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="department_id">Department</label>
                                    <select class="form-control" name="department_id" id="department_id">
                                        <option selected disabled>Select an option</option>
                                    </select>

                                    @error('department_id')
                                    <p class="text-danger"> {{ $errors->first("department_id") }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" name="status" id="status">
                                        <option selected disabled>Select an option</option>
                                        <option value="{{ \App\Models\Salary::STATUS_PAID }}">Paid</option>
                                        <option value="{{ \App\Models\Salary::STATUS_UNPAID }}">Unpaid</option>
                                    </select>
                                    @error("status")
                                    <p class="text-danger"> {{ $errors->first("status") }} </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="salaryTableWrapper">
                            <table class="table" id="salaryTable">
                                <thead class="custom-thead">
                                    <tr>
                                        <th scope="col">
                                            <input type="checkbox" name="select_all[]" id="selectAll"/>
                                        </th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Division</th>
                                        <th scope="col">Department</th>
                                        <th scope="col">Month</th>
                                        <th scope="col">Day</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="salaryData"></tbody>
                            </table>
                        </div>
                    </div>
                    @can("Pay Salary")
                    <div class="card-footer" id="payNowButton">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">
                                <a href="#" class="btn btn-primary mr-2" onclick="payNow()">Pay Now</a>
                            </div>
                        </div>
                    </div>
                    @endcan
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection

@section('footer-js')
<script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
    $("#salaryTableWrapper").css("display", "none");
    $("#payNowButton").css("display", "none");

    // Get department by division
    $('#office_division_id').change(function(){
       var _officeDivisionID = $(this).val();

        let url = "{{ route('salary.getDepartmentByAllOfficeDivision', ':officeDivision') }}";
        url = url.replace(":officeDivision", _officeDivisionID);

        $.get(url, {}, function (response, status) {
            $("#department_id").empty();
            $("#department_id").append('<option value="" "selected disabled">Select an option</option>');

            if(_officeDivisionID == "all") {
                for(var i = 0; i < response.data.length; i++) {
                    $("#department_id").append('<option value="' + response.data[i].departments[0]["id"] + '">'+ response.data[i].departments[0]["name"] + '</option>');
                }
            } else {
                $.each(response.data.departments, function(key, value) {
                    $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
            }
        })
    });

    $("#office_division_id, #department_id, #status").on("change", function () {
        getSalary();
    });

    $("#monthAndYear").datepicker( {
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    }).on("changeDate", function () {
        getSalary();
    });

    $("select").select2({
        theme: "classic",
    });

    $('#salaryTable').DataTable({
        "ordering": false
    });

    var _ids = [];
    function payNow() {
        var url = "{{ route('salary.payNow') }}";
        $.post(url, {ids: _ids}, function (data, status) {
            if ( data.status !== false ) {
                swal.fire({
                    title: "Salary Paid",
                    text: "Salary Paid",
                    icon: status,
                    allowOutsideClick: false
                }).then((result) => {
                    if(result.isConfirmed) {
                        window.location.reload()
                    }
                });
            }
        })
    }

    // select all
    $("#selectAll").on("change", function () {
        var _selectALl = this.value;
        if(this.checked) {
            $('input:checkbox').prop('checked', this.checked);

            $(".salaryID:checked").each(function(){
                _ids.push($(this).val());
            });
        } else if(!this.checked) {
            $('input:checkbox').prop("checked", false);
            _ids.splice(0, _ids.length)
        }
    });

    /**
     * Toggle Check salary IDS
     **/
    function salaryId() {
        $(".salaryID").change(function () {
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
     * Get Salary info according to filter
     */
    function getSalary() {
        $("#salaryData").empty();
        $("#salaryTableWrapper").css("display", "");

        var _officeDivisionID = $("#office_division_id").val();
        var _departmentID = $("#department_id").val();
        var _monthAndYear = $("#monthAndYear").val();
        var _status = $("#status").val();
        var url = '{{ route("salary.filterSalary") }}';
        $.post(url, {officeDivisionID: _officeDivisionID, departmentID: _departmentID, monthAndYear: _monthAndYear, status: _status}, function (data, status) {
            // Status
            if($("#status").val() == 1) $("#payNowButton").css("display", "none");
            else $("#payNowButton").css("display", "");

            let alertHeader, alertStatus, alertMessage;
            if ( data.status == true && data.items.length > 0) {
                var months = ["Jan", "Feb", "Mar", "Apr", "May", "June", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                var statuses = ["Unpaid", "Paid"];

                for(var i = 0; i < data.items.length; i++) {
                    var _id = data.items[i].id;
                    var _name = data.items[i].user.name;
                    var _officeDivision = data.items[i].office_division.name;
                    var _department = data.items[i].department.name;
                    var _month = months[data.items[i].month];
                    var _year = data.items[i].year;
                    var _status = statuses[data.items[i].status];

                    var table = "";
                    if (_status === "Unpaid") {
                        table = '<tr>' +
                            '<td>' + '<input type="checkbox" name="salary_id[]" value="' + _id + '" class="salaryID" onclick="salaryId()" />' + '</td>' +
                            '<td>' + _name + '</td>' +
                            '<td>' + _officeDivision + '</td>' +
                            '<td>' + _department + '</td>' +
                            '<td>' + _month + '</td>' +
                            '<td>' + _year + '</td>' +
                            '<td>' + _status + '</td>' +
                            '</tr>';
                    } else if (_status === "Paid") {
                        table = '<tr>' +
                            '<td></td>' +
                            '<td>' + _name + '</td>' +
                            '<td>' + _officeDivision + '</td>' +
                            '<td>' + _department + '</td>' +
                            '<td>' + _month + '</td>' +
                            '<td>' + _year + '</td>' +
                            '<td>' + _status + '</td>' +
                            '</tr>';
                    }

                    $("#salaryData").append(table);
                }
            } else if(data.items.length == 0) {
                $("#salaryData").append(
                    '<tr>' +
                        '<td></td>' +
                        '<td></td>' +
                        '<td></td>' +
                        '<td>No Data Available</td>' +
                        '<td></td>' +
                        '<td></td>' +
                    '</tr>');
            }
        })
    }
</script>
@endsection
