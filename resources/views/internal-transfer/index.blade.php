@extends('layouts.app')

@section('content')

    {{--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">--}}
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">

    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Delivery Challan</h3>
            </div>
            <div class="card-toolbar">
                <div class="dropdown dropdown-inline mr-2">
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a href="#" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-pdf-o"></i>
                                    </span>
                                    <span class="navi-text">PDF</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                @can('Create Internal Transfer')
                    <a href="{{ route('internal-transfer.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                            </g>
                        </svg>
                    </span>Create Challan</a>
                @endcan
            </div>
        </div>




        <div class="card-header col-12 mt-6 mb-3">
            <div class="col-md-3" id="kt_daterangepicker_2" >
                <div class="form-group d-flex">
                    <label class="mt-3 mr-2" style=" width:166px;">Date of Issue:</label>
                    <input type="text" class="form-control issue_daterangepicker" readonly="readonly" name="date_range" placeholder="Select date range"/>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group d-flex">
                    <label for="office_division_id" class="mt-3 mr-2">Office Divisions</label>
                    <select class="form-control ml-2 w-75" id="office_division_id" name="office_division_id"
                            required>
                        <option value="0" selected>Select an option</option>
                        @foreach($data["officeDivisions"] as $officeDivision)
                            <option
                                @if(old('office_division_id') > 0 && $officeDivision->id == old('office_division_id') )
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
                <div class="form-group d-flex">
                    <label for="department_id" class="mt-3 mr-2">Department</label>
                    <select class="form-control ml-2 w-75" id="department_id" name="department_id" required>
                        <option value="0" selected>Select an option</option>
                        @if(old('department_id') > 0 )
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

            <div class="col-md-3" >
                <div class="form-group d-flex">
                    <label class="mt-3 mr-2">Status:</label>
                    <select class="form-control ml-2 w-75" id="challan_status" name="challan_status">
                        <option value="" selected>Select an option</option>
                        <option value="all" selected>All</option>
                        <option value="{{CHALLAN_OPEN}}">Open</option>
                        <option value="{{CHALLAN_PENDING_RETURN}}">Return Pending</option>
                        <option value="{{CHALLAN_CLOSE}}">Close</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3" >
                <div class="form-group d-flex">
                    <label class="mt-3 mr-2">Return:</label>
                    <select class="form-control ml-2 w-75" id="return_status" name="return_status">
                        <option value="" selected>Select an option</option>
                        <option value="{{RETURN_PENDING}}">Returnable</option>
                        <option value="{{RETURN_COMPLETE}}">Returned</option>
                        <option value="{{RETURN_NOT_APPLICABLE}}">Regular</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4" >
                <div class="form-group d-flex">
                    <label class="mt-3 mr-2" style="width:166px;">Approval Status:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="" selected>Select an option</option>
                        <option value="{{\App\Models\InternalTransfer::OPERATION_CREATED}}">Prepared</option>
                        <option value="{{\App\Models\InternalTransfer::OPERATION_AUTHORIZED}}">Authorized</option>
                        <option value="{{\App\Models\InternalTransfer::OPERATION_SECURITY_CHECKED_OUT}}">Security Checked Out</option>
                        <option value="{{\App\Models\InternalTransfer::OPERATION_SECURITY_CHECKED_IN}}">Security Checked In</option>
                        <option value="{{\App\Models\InternalTransfer::OPERATION_RECEIVED}}">Received</option>
                        <option value="{{\App\Models\InternalTransfer::OPERATION_REJECT}}">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2" >
                <div class="form-group d-flex">
                    <button type="button" class="btn btn-bg-dark btn-sm search_button" style="color: white">Search</button>
                </div>
            </div>
            <div class="col-md-3 text-right" >
                @if(auth()->user()->can('Download Internal Transfer Report'))
                    <div class="dropdown dropdown-inline mr-5">
                        <form id="exportForm" method="POST" action="{{route('internal-transfer.exportExcel')}}">
                            @csrf
                            <input type="hidden" id="hidden_challan_status" name="challan_status" value="">
                            <input type="hidden" id="hidden_return_status" name="return_status" value="">
                            <input type="hidden" id="hidden_status" name="status" value="">
                            <input type="hidden" id="from_date" name="from_date" value="">
                            <input type="hidden" id="to_date" name="to_date" value="">
                            <input type="hidden" id="hidden_office_division_id" name="office_division_id" value="">
                            <input type="hidden" id="hidden_department_id" name="department_id" value="">
                            <a href="#" data-toggle="tooltip" title="" data-original-title="Export">
                                <span class="svg-icon svg-icon-primary svg-icon-2x">
                                    <img  style="margin-top: -3px;" class="export_button" src="{{asset("assets/media/misc/excel.png")}}">
                                </span>
                            </a>
                        </form>
                    </div>
                @endif
            </div>
        </div>




        <div class="card-body">
            <table class="table" id="internalTransferTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col" style="display: none">#</th>
                    <th scope="col">Challan No.</th>
                    <th scope="col">Reference</th>
                    <th scope="col">Issued On</th>
                    {{--                    <th scope="col">Type</th>--}}
                    <th scope="col">Source Division</th>
                    <th scope="col">Source</th>

                    <th scope="col">Destination Division</th>

                    <th scope="col">Destination</th>
                    <th scope="col">Return Status</th>
                    <th scope="col">Approval Status</th>
                    <th scope="col">Status</th>
                    <th scope="col" style="width: 15%;">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade approvalModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal_content_div" style="width: 900px !important;">
            </div>
        </div>
    </div>
@endsection
@section('footer-js')
    {{--    <script src="https://preview.keenthemes.com/metronic/theme/html/demo2/dist/assets/js/pages/crud/forms/widgets/bootstrap-daterangepicker.js?v=7.2.9"></script>--}}
    <script>
        $(document).ready( function () {
            $('.issue_daterangepicker').daterangepicker({
                locale: {
                    format: 'DD-MM-YYYY',
                    separator:' / ',
                    value:''
                }
            });
            $('.issue_daterangepicker').val('');
            callDatatable('?challan_status=all');
            function callDatatable(options){
                $('#internalTransferTable').DataTable({
                    processing: false,
                    serverSide: true,
                    pageLength: '{{\Functions::getPaginate()}}',
                    retrieve: true,
                    bLengthChange: true,
                    responsive: true,
                    ajax: '{{route('internal-transfer.index')}}' + options,
                    order: [1, 'desc'],
                    autoWidth: false,
                    language: {
                        paginate: {
                            next: 'Next &#8250;',
                            previous: '&#8249; Previous'
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id', visible:false },
                        { data: 'challan', name: 'challan', orderable: true, searchable: true },
                        { data: 'parent_challan', name: 'parent_challan', orderable: true, searchable: true },
                        { data: 'issued_on', name: 'issued_on', orderable: true, searchable: true },
                        // { data: 'challan_type', name: 'challan_type', orderable: false, searchable: true },
                        {data: 'source_division',name: 'source_division',sortable: false},
                        { data: 'source', name: 'source',sortable: false },
                        { data: 'destination_division', name: 'destination_division', orderable: false},
                        { data: 'destination', name: 'destination', orderable: false, searchable: true },
                        { data: 'return_status', name: 'return_status', orderable: false, searchable: true },
                        { data: 'status', name: 'status', sortable: false,orderable: false},
                        { data: 'challan_status', name: 'challan_status', orderable: false, searchable: true },
                        {"data": "action", orderable: false, searchable: false}
                    ]
                });
            }

            $(document).on('click', '.search_button', function(e) {
                $('.table').DataTable().clear();
                $('.table').DataTable().destroy();
                var params = '';
                if($(".issue_daterangepicker").val() != ''){
                    var dates = $(".issue_daterangepicker").val().split(" / ");
                    $('.from_date').val(dates[0]);
                    $('.to_date').val(dates[1]);
                    params='?from_date='+dates[0]+'&to_date='+dates[1];
                }
                if($('#challan_status').val()!=null){
                    if(params==''){
                        params='?challan_status='+$('#challan_status').val();
                    }else{
                        params+='&challan_status='+$('#challan_status').val();
                    }
                }
                if($('#return_status').val()!=null){
                    if(params==''){
                        params='?return_status='+$('#return_status').val();
                    }else{
                        params+='&return_status='+$('#return_status').val();
                    }
                }
                if($('#status').val()!=null){
                    if(params==''){
                        params='?status='+$('#status').val();
                    }else{
                        params+='&status='+$('#status').val();
                    }
                }
                if($('#office_division_id').val()!=null){
                    if(params==''){
                        params='?office_division_id='+$('#office_division_id').val();
                    }else{
                        params+='&office_division_id='+$('#office_division_id').val();
                    }
                }
                if($('#department_id').val()!=null){
                    if(params==''){
                        params='?department_id='+$('#department_id').val();
                    }else{
                        params+='&department_id='+$('#department_id').val();
                    }
                }
                callDatatable(params);
            });

            $(document).on('click', '.export_button', function(e) {
                if($(".issue_daterangepicker").val() != ''){
                    var dates = $(".issue_daterangepicker").val().split(" / ");
                    $('#from_date').val(dates[0]);
                    $('#to_date').val(dates[1]);
                }
                if($('#challan_status').val()!=null){
                    $('#hidden_challan_status').val($('#challan_status').val());
                }
                if($('#return_status').val()!=null){;
                    $('#hidden_return_status').val($('#return_status').val());
                }
                if($('#status').val()!=null){
                    $('#hidden_status').val($('#status').val());
                }
                if($('#office_division_id').val()!=null){
                    $('#hidden_office_division_id').val($('#office_division_id').val());
                }
                if($('#department_id').val()!=null){
                    $('#hidden_department_id').val($('#department_id').val());
                }
                $('#exportForm').submit();
            });


            $(document).on('click', '.viewModal_link', function(e) {
                e.preventDefault();
                var challan_id = $(this).data('id');
                var url = $(this).data('href');
                $.ajax({
                    type: "GET",
                    url: url,
                    data:{'_token':'{{csrf_token()}}','challan_id':challan_id},
                    dataType: "json",
                    success: function(result){
                        $('.modal_content_div').html(result.html);
                        $('.approvalModal').modal('show');
                    }
                });
            });

            $(document).on('click', '.approvalModal_link', function(e) {
                e.preventDefault();
                var challan_id = $(this).data('id');
                var url = $(this).data('href');
                $.ajax({
                    type: "POST",
                    url: url,
                    data:{'_token':'{{csrf_token()}}','challan_id':challan_id},
                    dataType: "json",
                    success: function(result){
                        $('.modal_content_div').html(result.html);
                        $('.approvalModal').modal('show');
                    }
                });
            });

            $(document).on('click', '.delete_challan', function(e) {
                e.preventDefault();
                var url = $(this).data('href');
                deleteAlertAnother(url);
            });

            $(document).on('click', '.download_link', function(e) {
                e.preventDefault();
                var challan_id = $(this).data('id');
                $('#download_form_'+challan_id).submit();
            });

            $(document).on('click', '.returnModal_link', function(e) {
                e.preventDefault();
                var challan_id = $(this).data('id');
                var url = $(this).data('href');
                $.ajax({
                    type: "POST",
                    url: url,
                    data:{'_token':'{{csrf_token()}}','challan_id':challan_id},
                    dataType: "json",
                    success: function(result){
                        $('.modal_content_div').html(result.html);
                        $('.approvalModal').modal('show');
                    }
                });
            });

            $('#office_division_id').change(function () {
                $.ajax({
                    url: '{{route("filter.get-department")}}',
                    data: {
                        office_division_id: this.value
                    },
                    success: function (res) {
                        $('#department_id').empty();
                        var items = '<option value="0">Select an option</option>';
                        $.each(res.data, function (x, y) {
                            items += '<option value="' + y.id + '">' + y.name + '</option>';
                        })
                        $('#department_id').append(items)
                    },
                    error: function (err) {
                        console.log(err)
                    }
                })
            });

        });
    </script>

@endsection
