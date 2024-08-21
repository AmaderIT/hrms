@extends('layouts.app')

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form class="d-block" action="" method="get">
                <div class="row m-auto">
                    <div class="row col-12 justify-content-start mb-2">
                        {{-- Month and Year --}}
                        <div class="col-3">
                            <span>Choose Month</span>
                            <input class="mb-2 w-100" type="text" name="month_and_year" id="datepicker"
                                   value="{{ request()->get("month_and_year") ?? $latest['month'] . '-' . $latest['year'] }}"
                                   readonly placeholder="Select Month" autocomplete="off" style="height: 30px;"
                                   required/>
                        </div>

                        {{-- Office Division --}}
                        <div class="col-3">
                            <span>Choose Division</span>
                            <select class="select w-100" id="office_division_id" name="office_division_id"
                                    style="height: 30px;">
                                <option selected disabled>Choose an option</option>
                                @foreach($data["officeDivisions"] as $officeDivision)
                                    <option
                                        value="{{ $officeDivision->id }}" {{ $officeDivision->id == request()->get("office_division_id") ? 'selected' : '' }}>
                                        {{ $officeDivision->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Department --}}
                        <div class="col-3">
                            <span>Choose Department</span>
                            <select class="form-control select w-100" name="department_id[]" id="department_id" multiple
                                    style="height: 30px;">
                                @foreach($data["departments"] as $department)
                                    <option value="{{ $department->id }}"
                                    @if(!is_null(request()->get("department_id")))
                                        {{ in_array($department->id, \request()->get("department_id")) ? 'selected' : '' }}
                                        @endif
                                    >
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status (Paid/Unapid) --}}
                        <div class="col-2">
                            <span>Choose Payment Status</span>
                            <select class="select w-100" id="payment_status" name="payment_status"
                                    style="height: 30px;">
                                <option value="0" {{ \request()->get("payment_status") == 0 ? 'selected' : '' }}>
                                    Unpaid
                                </option>
                                <option value="1" {{ \request()->get("payment_status") == 1 ? 'selected' : '' }}>Paid
                                </option>
                            </select>
                        </div>

                        <div class="col-1">
                            <button class="btn btn-sm btn-primary px-6 mt-5" type="submit">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Salary Listing</h3>
            </div>

            <div>
                @can('Export Salary Bank Statement CSV')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-5"
                            onclick="downloadExportFile('xlsx','bank-statement')"
                            title="Bank Statement Excel">
                        <i style="color:#53b56f " class="fa fa-file-excel"></i> Bank Statement
                    </button>
                @endcan

                @can('Export Salary Bank Statement PDF')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-5"
                            onclick="downloadExportFile('pdf','bank-statement')"
                            title="Bank Statement PDF">
                        <i style="color:red " class="fa fa-file-pdf"></i> Bank Statement
                    </button>
                @endcan

                @can('Export Tax Deduction')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-5"
                            onclick="downloadExportFile('xlsx','tax-deduction')"
                            title="Tax Deduction ">
                        <i style="color:#53b56f " class="fa fa-file-excel"></i> Tax Deduction
                    </button>
                @endcan

                @can('Export Loan Deduction')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-5"
                            onclick="downloadExportFile('xlsx','loan-deduction')"
                            title="Loan & Advance Adjustment ">
                        <i style="color:#53b56f " class="fa fa-file-excel"></i> Loan & Advance Adjustment
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table" id="salaryTable">
                <thead class="custom-thead">
                <tr>
                    <th>
                        <input id="all-checker" onchange="setAllChecked(this)" type="checkbox">
                    </th>
                    <th scope="col">Office Division</th>
                    <th scope="col">Department</th>
                    <th scope="col">Month</th>
                    <th scope="col">Year</th>
                    <th scope="col">Prepared By</th>
                    <th scope="col">Divisional Approval</th>
                    <th scope="col">Departmental Approval</th>
                    <th scope="col">HR Approval</th>
                    <th scope="col">Accounts Approval</th>
                    <th scope="col">Management Approval</th>
                    <th scope="col">Payment Status</th>
                    <th scope="col">Regenerate</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    @if($isAccountant && !in_array($item->department_id, $accountsDepartmentsIds))
                        @if($item->hr_approval_status != 1) @continue @endif
                    @endif
                    <tr>
                        <td>
                            @if($item->managerial_approval_status == 1)

                                <input onchange="setDepartmentRow()" class="row-checkbox" type="checkbox"
                                       data-value="{{$item->id ?? 0}}"
                                       data-month="{{ $item->month}}" data-year="{{ $item->year}}">
                            @endif
                        </td>
                        <td>{{ $item->officeDivision->name }}</td>
                        <td>{{ $item->department->name }}</td>
                        <td>{{ date('F', mktime(0, 0, 0, $item->month, 10)) }}</td>
                        <td>{{ $item->year }}</td>
                        <td>{{ $item->preparedBy->fingerprint_no . ' - ' . $item->preparedBy->name }}  <small>{{$item->prepared_date ? "@".date("d M h:i A",strtotime($item->prepared_date)) : ""}}</small></td>
                        <td>{{ optional($item->divisionalApprovalBy)->fingerprint_no . ' - ' . optional($item->divisionalApprovalBy)->name }} <small>{{$item->divisional_approved_date ? "@".date("d M h:i A",strtotime($item->divisional_approved_date)) : ""}}</small></td>
                        <td>{{ optional($item->departmentalApprovalBy)->fingerprint_no . ' - ' . optional($item->departmentalApprovalBy)->name }}<small>{{$item->departmental_approved_date ? "@".date("d M h:i A",strtotime($item->departmental_approved_date)) : ""}}</small></td>
                        <td>{{ optional($item->hrApprovalBy)->fingerprint_no . ' - ' . optional($item->hrApprovalBy)->name }} <small>{{$item->hr_approved_date ? "@".date("d M h:i A",strtotime($item->hr_approved_date)) : ""}}</small></td>
                        <td>{{ optional($item->accountsApprovalBy)->fingerprint_no . ' - ' . optional($item->accountsApprovalBy)->name }} <small>{{$item->accounts_approved_date ? "@".date("d M h:i A",strtotime($item->accounts_approved_date)) : ""}}</small></td>
                        <td>{{ optional($item->managerialApprovalBy)->fingerprint_no . ' - ' . optional($item->managerialApprovalBy)->name }} <small>{{$item->managerial_approved_date ? "@".date("d M h:i A",strtotime($item->managerial_approved_date)) : ""}}</small></td>
                        <td>
                            @if($item->status == 1)
                                {{ 'Paid @ ' . date('M d, Y', strtotime($item->paid_at)) }}
                            @elseif(
                                ($item->divisional_approval_status === 1 || $item->departmental_approval_status === 1) &&
                                ($item->divisional_approval_status !== 2 && $item->departmental_approval_status !== 2) &&
                                $item->hr_approval_status === 1 && $item->accounts_approval_status === 1 && $item->managerial_approval_status === 1 &&
                                auth()->user()->can("Pay Salary by Department")
                            )
                                @if($item->status == 1)
                                    {{ 'Paid @ ' . date('M d, Y', strtotime($item->paid_at)) }}
                                @else
                                    <a href="#" class="btn btn-sm btn-primary" data-toggle="modal"
                                       data-target="#modal-pay-{{ $item->uuid }}">Pay</a>
                                @endif
                            @else
                                <span>Unpaid</span>
                            @endif
                        </td>
                        <td>
                            @if(is_null($item->paid_at) AND auth()->user()->can("Regenerate Salary"))
                                <a href="#" class="btn btn-sm btn-primary" data-toggle="modal"
                                   data-target="#modal-regenerate-{{ $item->uuid }}">Regenerate</a>
                            @endif
                        </td>
                        <td>
                            @can('Salary Details')
                                <a href="{{ route('salary.details', ['salaryDepartment' => $item->uuid]) }}"><i
                                        class="fa fa-eye mt-2" style="color: green"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pay Salary --}}
    @foreach($items as $item)
        @if(($item->divisional_approval_status === 1 OR $item->departmental_approval_status === 1) AND auth()->user()->can("Pay Salary by Department") AND $item->paid_at != 1)
            <div class="modal fade" id="modal-pay-{{ $item->uuid }}" tabindex="-1" role="dialog"
                 aria-labelledby="exampleModalSizeXl" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Are you sure to Pay Salary?</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <form action="{{ route('salary.paySalaryToDepartment') }}" method="POST">
                            @csrf
                            <input type="hidden" name="salary_department_id" value="{{ $item->uuid }}">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary mr-2">Pay Salary</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Regenerate Salary --}}
    @foreach($items as $item)
        @if(is_null($item->paid_at) AND auth()->user()->can("Regenerate Salary"))
            <div class="modal fade" id="modal-regenerate-{{ $item->uuid }}" tabindex="-1" role="dialog"
                 aria-labelledby="exampleModalSizeXl" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Salary Regeneration</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <form action="{{ route('salary.regenerate', ['salaryDepartment' => $item->uuid]) }}"
                              method="POST">
                            @csrf
                            <input type="hidden" name="salary_department_id" value="{{ $item->uuid }}">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        {{-- Include Overtime --}}
                                        <div class="form-group">
                                            <label class="checkbox checkbox-success">
                                                <input type="checkbox" name="overtime" id="overtime"/>
                                                <span></span> &nbsp; Salary Generate with Overtime
                                            </label>
                                            @error('overtime')
                                            <p class="text-danger"> {{ $errors->first("overtime") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Include Late Deduction --}}
                                        <div class="form-group">
                                            <label class="checkbox checkbox-success">
                                                <input type="checkbox" name="late_deduction" id="late_deduction"/>
                                                <span></span> &nbsp; Salary Generate with Late Deduction
                                            </label>
                                            @error('late_deduction')
                                            <p class="text-danger"> {{ $errors->first("late_deduction") }} </p>
                                            @enderror
                                        </div>

                                        {{-- Include Absent Deduction --}}
                                        <div class="form-group">
                                            <label class="checkbox checkbox-success">
                                                <input type="checkbox" name="absent_deduction" id="absent_deduction"/>
                                                <span></span> &nbsp; Salary Generate with Absent Deduction
                                            </label>
                                            @error('absent_deduction')
                                            <p class="text-danger"> {{ $errors->first("absent_deduction") }} </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary mr-2">Regenerate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        /*var _ids = [];
        let monthAndYear = null;
        let paymentStatus = 0;*/

        /**
         * Toggle Select All
         **/
        /*$(".selectAll").on("change", function () {
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
        });*/

        // Get department by division
        $('#office_division_id').change(function () {
            var _officeDivisionID = $(this).val();
            let url = "{{ route('report.getDepartmentAndEmployeeByOfficeDivision', true) }}";
            $.get(url, {office_division_id: _officeDivisionID}, function (response, status) {
                $("#department_id").empty();
                // $("#department_id").append('<option value="all" selected="selected">All Departments</option>');
                $.each(response.departments, function (key, value) {
                    $("#department_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                });
                $("#user_id").empty();
                $("#user_id").append('<option value="all" selected="selected">All Employees</option>');
                $.each(response.employees, function (key, value) {
                    $("#user_id").append('<option value="' + value.id + '">' + value.fingerprint_no + ' - ' + value.name + '</option>');
                });
                $("select").select2({
                    theme: "classic",
                });
            })
        });

        $("select").select2({
            theme: "classic",
        });

        $("#datepicker").datepicker({
            format: "mm-yyyy",
            startView: "months",
            changeMonth: true,
            changeYear: false,
            minViewMode: "months"
        });

        $('#salaryTable').DataTable({
            "order": [],
            "ordering": false,
            "paging": false,
            "bInfo": false,
            "bPaginate": false,
        });

        function setAllChecked(t) {
            let state = $(t).prop('checked');
            $('.row-checkbox').prop('checked', state)
        }

        function downloadExportFile(file_type, report_type) {
            let dptIds = [];
            let month = 0;
            let year = 0;

            $('.row-checkbox').each(function (key, el) {
                if ($(el).prop('checked')) {
                    dptIds.push($(el).data('value'))
                    month = $(el).data('month')
                    year = $(el).data('year')
                }
            })
            if (dptIds.length > 0) {
                let downlaod_url = '{{url("/")}}/salary/download-' + report_type + '?type=' + report_type + '&export_file_type=.' + file_type + '&month=' + month + '&year=' + year + '&department_ids=[' + dptIds + ']';
                window.open(downlaod_url, '_blank');

            } else {
                swal.fire({
                    title: "Export File Download",
                    text: 'please select at least one department to download!',
                    icon: 'warning',
                    allowOutsideClick: false
                })
            }

        }

        function setDepartmentRow() {
            let rowCount = $('.row-checkbox').length

            let checkedRowCount = 0;
            $('.row-checkbox').each(function (key, el) {
                if ($(el).prop('checked')) {
                    checkedRowCount++;
                }
            })
            console.log(rowCount, checkedRowCount)
            if (checkedRowCount == rowCount) {
                $('#all-checker').prop('checked', true)
            } else {
                $('#all-checker').prop('checked', false)
            }
        }

    </script>
@endsection
