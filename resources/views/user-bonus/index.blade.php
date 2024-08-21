@extends('layouts.app')
@section('top-css')
    <style>
        table.dataTable thead th, table.dataTable thead td {
            padding: 10px 6px !important;
        }
    </style>
@endsection
@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form class="d-block" action="" method="get">
                <div class="row m-auto">
                    <div class="row col-12 justify-content-start mb-2">

                        <div class="col-3">
                            <span>Choose Bonus</span>
                            <select class="select w-100" id="bonus_id" name="bonus_id"
                                    style="height: 30px;">
                                <option selected disabled>Choose an option</option>
                                @foreach($data["bonuses"] as $bonusId => $bonusName)
                                    <option
                                        value="{{ $bonusId }}" {{ $bonusId == request()->get("bonus_id") ? 'selected' : '' }}>
                                        {{ $bonusName }}
                                    </option>
                                @endforeach
                            </select>
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
                <h3 class="card-label">List of Bonus</h3>
            </div>

            <div>
                @can('Export Salary Bank Statement CSV')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-3"
                            onclick="downloadExportFile('xlsx','bank-statement')"
                            title="Bank Statement Excel">
                        <i style="color:#53b56f " class="fa fa-file-excel"></i> Bank Statement
                    </button>
                @endcan

                @can('Export Salary Bank Statement PDF')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-3"
                            onclick="downloadExportFile('pdf','bank-statement')"
                            title="Bank Statement PDF">
                        <i style="color:red " class="fa fa-file-pdf"></i> Bank Statement
                    </button>
                @endcan

                @can('Export Tax Deduction')
                    <button style="background: white;border-color:#3699FF;color: #000"
                            class="btn btn-sm btn-success px-6 mt-3"
                            onclick="downloadExportFile('xlsx','tax-deduction')"
                            title="Tax Deduction ">
                        <i style="color:#53b56f " class="fa fa-file-excel"></i> Tax Deduction
                    </button>
                @endcan
            </div>

            <div class="card-toolbar">
                <!--begin::Button-->
                @can("Create Bonus")
                    <a href="{{ route('user-bonus.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
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
                    </span>Generate New Bonus
                    </a>
                @endcan
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <table class="table" id="salaryTable">
                <thead class="custom-thead">
                <tr>
                    <th width="3%">
                        <input id="all-checker" onchange="setAllChecked(this)" type="checkbox">
                    </th>
                    <th width="9%" scope="col">Bonus Name</th>
                    <th width="9%" scope="col">Office Division</th>
                    <th width="9%" scope="col">Department</th>
                    <th width="3%" scope="col">Month</th>
                    <th width="3%" scope="col">Year</th>
                    <th width="8%" scope="col">Prepared By</th>
                    <th width="8%" scope="col">Divisional Approval</th>
                    <th width="8%" scope="col">Departmental Approval</th>
                    <th width="8%" scope="col">HR Approval</th>
                    <th width="9%" scope="col">Accounts Approval</th>
                    <th width="9%" scope="col">Management Approval</th>
                    <th width="8%" scope="col">Payment Status</th>
                    <th width="3%" scope="col">Regenerate</th>
                    <th width="3%" scope="col">Actions</th>
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
                        <td>{{ $item->bonus->festival_name }}</td>
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
                                <a href="{{ route('user-bonus.details', ['bonusDepartment' => $item->uuid]) }}"><i
                                        class="fa fa-eye mt-2" style="color: green"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pay Bonus --}}
    @foreach($items as $item)
        @if(($item->divisional_approval_status === 1 OR $item->departmental_approval_status === 1) AND auth()->user()->can("Pay Salary by Department") AND $item->paid_at != 1)
            <div class="modal fade" id="modal-pay-{{ $item->uuid }}" tabindex="-1" role="dialog"
                 aria-labelledby="exampleModalSizeXl" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Are you sure to Pay this Bonus?</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <form action="{{ route('user-bonus.payBonusToDepartment') }}" method="POST">
                            @csrf
                            <input type="hidden" name="bonus_department_id" value="{{ $item->uuid }}">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary mr-2">Pay Bonus</button>
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
                            <h5 class="modal-title" id="exampleModalLabel">Bonus Regeneration</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <form action="{{ route('user-bonus.regenerate', ['bonusDepartment' => $item->uuid]) }}"
                              method="POST">
                            @csrf
                            <input type="hidden" name="bonus_department_id" value="{{ $item->uuid }}">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        {{-- Include TAX --}}
                                        <div class="form-group">
                                            <label class="checkbox checkbox-success">
                                                <input type="checkbox" name="tax" id="tax"/>
                                                <span></span> &nbsp; Bonus Generate with TAX
                                            </label>
                                            @error('tax')
                                            <p class="text-danger"> {{ $errors->first("tax") }} </p>
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
            if (dptIds.length > 0) {console.log(dptIds);
                let download_url = '{{url("/")}}/user-bonus/download-' + report_type + '?type=' + report_type + '&export_file_type=.' + file_type + '&month=' + month + '&year=' + year + '&department_ids=[' + dptIds + ']';
                window.open(download_url, '_blank');

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
