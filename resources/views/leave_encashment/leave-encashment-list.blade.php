@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Leave Encashment List</h3>
            </div>
        </div>
        <div class="card-header col-12 mt-6 mb-3">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="text" class="form-control" name="datepicker" id="datepicker" @if(old('datepicker') != '') value="{{old('datepicker')}}" @else value="{{date("Y",strtotime("-1 year"))}}" @endif  autocomplete="off" required="required">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="office_division_id">Office Division</label>
                    <select class="form-control" id="office_division_id" name="office_division_id"
                            required>
                        <option value="">Select an option</option>
                        @foreach($data["officeDivisions"] as $officeDivision)
                            <option
                                @if(old('office_division_id') != '' && $officeDivision->id == old('office_division_id') )
                                selected
                                @endif
                                value="{{ $officeDivision->id }}">
                                {{ $officeDivision->name }}
                            </option>
                        @endforeach
                    </select>
                    @error("office_division_id")
                    <p class="text-danger"> {{ $errors->first("office_division_id") }} </p>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select class="form-control department_id_select w-100" id="department_id" name="department_id[]" multiple required>
                        @if(old('department_id') != '' )
                            @php
                                $department = \App\Models\Department::find(old('department_id'));
                            @endphp
                            <option selected value="{{optional($department)->id}}">{{optional($department)->name}}</option>
                        @endif
                    </select>
                    @error("department_id")
                    <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="">Payment Status:</label>
                    <select class="form-control ml-2 w-75" id="payment_status" name="payment_status">
                        <option value="all">Both</option>
                        <option value="{{\App\Models\DepartmentLeaveEncashment::APPROVAL_PENDING}}">Unpaid</option>
                        <option value="{{\App\Models\DepartmentLeaveEncashment::APPROVAL_CONFIRMED}}">Paid</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
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
        <div class="card-body" style="width: 100%">
            <div class="card-title" style="display: flex;
    justify-content: space-between;
    align-items: center;">
                <h3 class="card-label">Leave Encashment List</h3>
                <div>
                    @can('Export Leave Encashment Bank Statement EXCEL')
                        <button style="background: white;border-color:#3699FF;color: #000"
                                class="btn btn-sm btn-success px-6 mt-5"
                                onclick="downloadExportFile('Excel','bank-statement')"
                                title="Bank Statement Excel">
                            <i style="color:#53b56f " class="fa fa-file-excel"></i> Bank Statement
                        </button>
                    @endcan
                    @can('Export Leave Encashment Bank Statement PDF')
                        <button style="background: white;border-color:#3699FF;color: #000"
                                class="btn btn-sm btn-success px-6 mt-5"
                                onclick="downloadExportFile('PDF','bank-statement')"
                                title="Bank Statement PDF">
                            <i style="color:red " class="fa fa-file-pdf"></i> Bank Statement
                        </button>
                    @endcan
                </div>
            </div>

        </div>
        <div class="card-body">
            <table class="table" id="leaveEncashmentTable">
                <thead class="custom-thead">
                    <tr>
                        <th>
                            <input id="all-checker" onchange="setAllChecked(this)" type="checkbox">
                        </th>
                        <th scope="col">Sl.</th>
                        <th scope="col">Office Division</th>
                        <th scope="col">Department</th>
                        <th scope="col">Year</th>
                        <th scope="col">Eligible Month</th>
                        <th scope="col">Payable Amount</th>
                        <th scope="col">Prepared By</th>
                        <th scope="col">Divisional Approval</th>
                        <th scope="col">Departmental Approval</th>
                        <th scope="col">HR Approval</th>
                        <th scope="col">Accounts Approval</th>
                        <th scope="col">Management Approval</th>
                        <th scope="col">Payment Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    @if(auth()->user()->can("Pay Leave Encashment"))
        <div class="modal fade" id="modal-pay" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Are you sure to Pay Leave Encashment?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <form action="{{ route('leave-encashment.payLeaveEncashmentToDepartment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="department_uuid" id="department_uuid" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary mr-2">Pay Leave Encashment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('footer-js')
    @stack('custom-scripts')
    <script src="{{asset('js/list.js')}}"></script>
    <script>
        var f = 0;
        var dataTable;
        $(document).ready(function () {
            if($('#department_uuid').length != 0) {
                $('#department_uuid').val('');
            }
            dataTable = $('#leaveEncashmentTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    ur: '{{ route('leave-encashment.leaveEncashmentList') }}',
                    data: function (d) {
                        d.datepicker = $('#datepicker').val();
                        d.office_division_id = $('#office_division_id').val();
                        d.department_id = $('#department_id').val();
                        d.payment_status = $('#payment_status').val();
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
                    data.datepicker = $('#datepicker').val();
                    data.office_division_id = $('#office_division_id').val();
                    data.department_id = $('#department_id').val();
                    data.payment_status = $('#payment_status').val();
                    data.f = f;
                    data.auth_user_id = '{{auth()->user()->uuid }}';
                    data.pageX = pageX;
                    data.pageY = pageY;
                },
                stateLoadParams: function (settings, data) {
                    if (data.auth_user_id == '{{ auth()->user()->uuid }}') {
                        f = data.f;
                        $('#datepicker').val(data.datepicker);
                        $('#payment_status').val(data.payment_status);
                        if (data.office_division_id != '') {
                            $('#office_division_id').val(data.office_division_id).change();
                        }
                        if (data.department_id != '') {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change();
                            }, 1000)
                        }
                        setTimeout(function () {
                            dataTable.page(dataTable.page.info().page).draw('page');
                        }, 2000)
                    }
                },
                columns: [
                    {data: 'checkbox', name: '', visible: true, orderable: false, searchable: false},
                    {data: 'id', name: 'id', visible: false},
                    {data: 'division_name', name: 'division_name', orderable: false, searchable: false},
                    {data: 'department_name', name: 'department_name', orderable: false, searchable: false},
                    {data: 'year', name: 'year', orderable: false, searchable: false},
                    {data: 'eligible_month', name: 'eligible_month', orderable: false, searchable: false},
                    {data: 'payable_amount', name: 'payable_amount', orderable: false, searchable: false},
                    {data: 'prepared_by', name: 'prepared_by', orderable: false, searchable: false},
                    {data: 'divisional_approved_by', name: 'divisional_approved_by', orderable: false, searchable: false},
                    {data: 'departmental_approved_by', name: 'departmental_approved_by', orderable: false, searchable: false},
                    {data: 'hr_approved_by', name: 'hr_approved_by', orderable: false, searchable: false},
                    {data: 'accounts_approved_by', name: 'accounts_approved_by', orderable: false, searchable: false},
                    {data: 'management_approved_by', name: 'management_approved_by', orderable: false, searchable: false},
                    {data: 'payment_status', name: 'payment_status', orderable: false, searchable: false},
                    {"data": "action", name: 'action', orderable: false, searchable: false}
                ]
            });
            $(document).on('click', '.search_button', function (e) {
                if($('#datepicker').val() != ''){
                    f = 1
                    dataTable.draw();
                }
                else{
                    Swal.fire({
                        title: 'Alert',
                        text: "Please select a year!",
                        icon: 'warning',
                        buttonsStyling: false,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        timer: 1000
                    })
                }
            });
            $(document).on('click', '.delete_link', function (event) {
                e.preventDefault();
                var url = $(this).data('href');
                let clickedElement = event.currentTarget;
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger"
                    },
                    cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                    confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.post(url, {}, function (data, status) {
                            let alertHeader, alertStatus, alertMessage;
                            if (data.status == true) {
                                alertHeader = 'Success';
                                alertStatus = 'success';
                                alertMessage = data.message || 'Deleted Successfully';
                            } else {
                                alertHeader = 'Cancelled';
                                alertStatus = 'error';
                                alertMessage = data.message || 'Something Went Wrong';
                            }
                            successAlert(alertHeader, alertMessage, alertStatus);
                        })
                    }
                })
            });
            $('#office_division_id').change(function(){
                var _officeDivisionID = $(this).val();
                if(_officeDivisionID != ''){
                    let url = "{{ route('leave-encashment.getDepartmentAndEmployeeByOfficeDivision') }}";
                    $.get(url, {office_division_id:_officeDivisionID}, function (response, status) {
                        $("#department_id").empty();
                        $.each(response.departments, function(key, value) {
                            $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                        });
                        $(".department_id_select").select2({
                            theme: "classic",
                        });
                    })
                }
                else{
                    $("#department_id").empty();
                    $('#department_id').val('').change();
                }
            });
            $(document).on('click', '.regenerate_leave_encashment', function (event) {
                event.preventDefault();
                var url = $(this).data('href');
                const department_id = [];
                department_id.push($(this).data('department-id'));
                let obj = {
                    'office_division_id':$(this).data('office-division-id'),
                    'department_id':department_id,
                    'eligible_month':$(this).data('eligible-month'),
                    'datepicker':$(this).data('year'),
                };
                let clickedElement = event.currentTarget;
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'success',
                    buttonsStyling: false,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger"
                    },
                    cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                    confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.get(url, obj, function (data, status) {
                            let alertHeader, alertStatus, alertMessage;
                            if (data.status == true) {
                                alertHeader = 'Success';
                                alertStatus = 'success';
                                alertMessage = data.message || 'Deleted Successfully';
                            } else {
                                alertHeader = 'Cancelled';
                                alertStatus = 'error';
                                alertMessage = data.message || 'Something Went Wrong';
                            }
                            successAlert(alertHeader, alertMessage, alertStatus);
                        });
                    }
                })
            });
            $(document).on('click', '.pay-button', function (event) {
                event.preventDefault();
                $('#department_uuid').val($(this).attr('data-uuid'));
                $('#modal-pay').modal('toggle');
            });
        });
        $('.search_reset').on('click', function () {
            f = 0
            dataTable.draw();
            $('#datepicker').val({{date("Y",strtotime("-1 year"))}});
            $('#office_division_id').val('').change();
            $('#department_id').val('').change();
            $('#payment_status').val('').change();
        });
        $("#datepicker").datepicker( {
            format: "yyyy",
            startView: "years",
            minViewMode: "years"
        });
        $(".department_id_select").select2({
            theme: "classic",
        });




        function setAllChecked(t) {
            if($(t).prop('checked')){
                $('.encashment-checkbox').prop('checked', true);
            }else{
                $('.encashment-checkbox').prop('checked', false);
            }
        }

        function downloadExportFile(file_type, report_type) {
            let encashIDs = [];
            $('.encashment-checkbox').each(function (key, el) {
                if ($(el).prop('checked')) {
                    encashIDs.push($(el).data('encashment-id'))
                }
            })
            if (encashIDs.length > 0) {
                let downlaod_url = '{{url("/")}}/leave-encashment/download-' + report_type + '?export_file_type=' + file_type + '&ids=' + encashIDs;
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
