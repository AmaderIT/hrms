@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/toastr.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/fontawesome.min.css') }}" rel="stylesheet"/>

    <style>
        .roster-list-actions-wrap .roster-list-info i {
            font-size: 1.5rem;
            font-weight: 100;
        }
        .roster-list-actions-wrap {
            display: flex;
            column-gap: 15px;
            align-items: center;
        }
        .btn-bysl-toggle .btn {
            padding: 2px 10px 2px 10px;
        }
        .btn-bysl-toggle .bysl-active.active {
            background-color: #28a745 !important;
        }
        .btn-bysl-toggle .bysl-reject.active {
            background-color: #dc3545 !important;
        }
        .btn-bysl-toggle .bysl-pending.active {
            background-color: rgb(255, 204, 0) !important;
        }
        .btn-bysl-toggle .btn i {
            font-size: 1em;
            padding-right: 0;
        }
        .btn-bysl-toggle .bysl-active.active i, .bysl-reject.active i {
            color: #ffffff !important;
        }
        #roster-list-approve-status-modal #roster-list-approve-status-title, #roster-list-lock-status-modal #roster-list-lock-status-title {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        #roster-list-approve-status-title i, #roster-list-lock-status-title i {
            font-size: 3.5rem;
        }
        #roster-list-approve-status-modal .btn-wrap, #roster-list-lock-status-modal .btn-wrap {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0 0 0;
        }
        #roster-list-approve-status-modal .modal-body, #roster-list-lock-status-modal .modal-body {
            padding: 2.5rem;
        }
        .roster-monthly-details .modal-title {
            font-size: 1.1rem;
            line-height: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            text-transform: capitalize;
        }
        .status-notify {
            position: absolute;
            -webkit-font-smoothing: auto;
            font-family: "Google Sans",Roboto,RobotoDraft,Helvetica,Arial,sans-serif;
            font-size: .75rem;
            font-weight: bold;
            line-height: 1;
            letter-spacing: normal;
            background-color: #fbe9e7;
            background-clip: padding-box;
            color: #c53929;
            -webkit-border-radius: 10px;
            border-radius: 10px;
            border: 1px solid #eaf1fb;
            padding: 0 2px;
            z-index: 1;
            text-shadow: none;
            top: -8px;
            right: 2px;
        }
        .btn-bysl-toggle > .btn-bysl:last-child {
            border-top-right-radius: 0.42rem !important;
            border-bottom-right-radius: 0.42rem !important;
        }
        label.bysl-reject {
            border-top-right-radius: 0.42rem !important;
            border-bottom-right-radius: 0.42rem !important;
        }
        .roster-monthly-details .table th, .roster-monthly-details .table td {
            padding: 0.5rem 0.2rem;
        }
        .roster-monthly-details .table {
            margin-bottom: 0;
        }
        .roster-calendar-info > i{
            font-size: 1.1rem;
            border-radius: 7px;
            padding: 4px 6px;
            border: 1px solid;
            color: #014891d6;
        }
    </style>
@endsection
@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                @if ($data['type'] == 'employee')
                    <h3 class="card-label">Employee Rosters</h3>
                @else
                    <h3 class="card-label">Department Rosters</h3>
                @endif
            </div>
            <div class="card-toolbar">
                @can('Roster Create')
                <a href="{{ route('rosters.create', ['type' => $data['type']]) }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                            </g>
                        </svg>
                    </span>Add Roster
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="d-flex">
                <div class="col-md-9 pl-0">
                    <div class="row">
                        @if ($data['type'] == 'employee')
                            @include('filter.division-department-employee-filter')
                        @else
                            @include('filter.division-department-filter')
                        @endif
                        <div class="form-group">
                            <label for="datepicker">Month</label>
                            <input type="text" class="form-control" placeholder="YYYY-MM" name="datepicker" id="datepicker" autocomplete="off" required/>
                        </div>
                        <div class="col-md">
                            <div class="form-group" style="padding-top:25px;">
                                <button class="btn btn-outline-secondary search_button" type="button">Filter</button>
                                <button  class="btn btn-primary reset-btn" type="button">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table" id="dataTable">
                <thead class="custom-thead">
                <tr>
                    @if ( isset($data['type']) && $data['type'] == 'employee')
                        <th width="16%" scope="col">Employee Name</th>
                        <th width="16%" scope="col">Designation</th>
                        <th width="6%" scope="col">Department</th>
                        <th width="6%" scope="col">Office Division</th>
                        <th width="6%" scope="col">Month Of Year</th>
                        <th width="3%" scope="col">Actions</th>
                    @else
                        <th width="16%" scope="col">Department</th>
                        <th width="6%" scope="col">Office Division</th>
                        <th width="6%" scope="col">Month Of Year</th>
                        <th width="3%" scope="col">Actions</th>

                    @endif
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade roaster_approval_status_modal" tabindex="-1" role="dialog" aria-labelledby="deptModalTitle"
             aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form name="roaster-approval-status-update" id="roaster-approval-status-update" action="#" method="POST">
                    <div class="modal-body">
                        <!-- BODY -->
                    </div>
                    <div class="modal-footer text-center">
                        <button type="button" class="btn btn-secondary roaster-approval-status-calcel-btn" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="roaster-approval-status-update-btn">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade roster-monthly-details" tabindex="-1" role="dialog" aria-labelledby="deptModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content p-6">
                <div class="modal-header">
                    <div class="modal-title"></div>
                </div>
                <div class="modal-body pt-4">
                    <!-- BODY -->
                </div>
                <div class="modal-footer text-center">
                    <button type="button" class="btn btn-secondary roaster-approval-status-calcel-btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
@stack('custom-scripts')
    <script type="text/javascript" src="{{ asset('assets/js/toastr.min.js') }}"></script>

    <script>

        var dataTable;
        var loadCount = 0;
        var f = 0;

        $(document).ready( function () {

            dataTable = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    ur: '{{route('roaster.index')}}',
                    data: function (d) {
                        if(f>0) {
                            d.division_id = $('#office_division_id').val(),
                            d.department_id = $('#department_id').val(),
                            d.user_id = $('#user_id').val(),
                            d.datepicker = $('#datepicker').val()
                        }
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
                stateSaveParams: function (settings, data) {
                    data.office_division_id = $('#office_division_id').val();
                    data.department_id = $('#department_id').val();
                    data.user_id = $('#user_id').val();
                    data.datepicker = $('#datepicker').val();
                    data.auth_user_id = '{{auth()->user()->id }}';
                    data.f = f
                },
                stateLoadParams: function (settings, data) {
                    f = data.f
                    if (data.office_division_id > 0) {

                        $('#office_division_id').val(data.office_division_id).change();

                        $(document).ajaxComplete(function(event,xhr,settings){

                            var givenUrl = settings.url.substr(0,settings.url.indexOf('?'));

                            var targetUrl = '{{route("filter.get-department")}}'

                            var targetUrlDataTbl = '{{route("roaster.index")}}'

                            var targetUrlEmployee = '{{route("filter.get-employee")}}'


                            if(givenUrl == targetUrl){
                                if (data.department_id > 0) $('#department_id').val(data.department_id).change();
                            }
                            else if(givenUrl == targetUrlDataTbl && loadCount == 0){
                                dataTable.page(dataTable.page.info().page).draw('page');
                                loadCount = 1;
                            }
                            else if(givenUrl == targetUrlDataTbl && loadCount == 1){
                                dataTable.page(dataTable.page.info().page).draw('page');
                                loadCount = 2
                            }
                            else if(givenUrl == targetUrlEmployee) {
                                $('#user_id').val(data.user_id ).change()
                                dataTable.page(dataTable.page.info().page).draw('page')

                            }

                        });
                    }

                    $('#datepicker').val(data.datepicker ).change();
                    if (data.auth_user_id == '{{auth()->user()->id }}') {
                        if (data.office_division_id > 0) {
                            $('#office_division_id').val(data.office_division_id).change();
                        }
                        if (data.department_id > 0) {
                            setTimeout(function () {
                                $('#department_id').val(data.department_id).change();
                            }, 1000)
                        }
                        if (data.user_id > 0) {
                            setTimeout(function () {
                                $('#user_id').val(data.user_id).change();
                            }, 1000)
                        }
                        if (data.datepicker != '') {
                            setTimeout(function () {
                                $('#datepicker').val(data.datepicker).change();
                            }, 1000)
                        }
                        setTimeout(function () {
                            dataTable.page(dataTable.page.info().page).draw('page')
                        }, 0)
                    }


                },
                columns: [
                    @if (isset($data['type']) && $data['type'] == 'employee')
                        {data: 'user.name', name: 'user.name', orderable: false, searchable: false},
                        {data: 'user.designation', name: 'user.designation', orderable: false, searchable: false},
                        {data: 'department', name: 'department', orderable: false, searchable: false},
                        {data: 'office_division', name: 'office_division', orderable: false, searchable: false},
                        {data: "month", orderable: false, searchable: false},
                        {data: "action", orderable: false, searchable: false}
                    @else
                        {data: 'department', name: 'department', orderable: false, searchable: false},
                        {data: 'office_division', name: 'office_division', orderable: false, searchable: false},
                        {data: "month", orderable: false, searchable: false},
                        {data: "action", orderable: false, searchable: false}
                    @endif
                ]

            });


            $(document).on('click', '.search_button', function (e) {
                f=1;
                dataTable.draw();
            });

            $('.reset-btn').on('click', function () {
                f = 0
                dataTable.draw();

                $('#office_division_id').val('').change();
                $('#department_id').val('').change();
                $('#user_id').val('').change();
                $('#datepicker').val('').change();
            });

            // approve status event listner
            $(document).on('click', '.btn-bysl-approve-group label', function (e) {
                e.preventDefault();
                let $this = $(this),
                    $clickedInput = $this.children(':input'),
                    $wrap = $this.parent('.btn-bysl-approve-group'),
                    $modal = $wrap.find('#roster-list-approve-status-modal'),
                    $modelTitle = $modal.find('#roster-list-approve-status-title'),
                    $from = $modal.find('#frm-roster-list-approve-status'),
                    $submit = $from.find("button[type='submit']"),
                    $status = $from.find("input[name='status']"),
                    $prevStatus = $from.find("input[name='prev_status']"),
                    $start = $from.find("input[name='start']"),
                    $end = $from.find("input[name='end']");

                    // if(moment($start.val()).startOf('month') < moment().startOf('month')){
                    //     console.log(moment($start.val()).startOf('month'), moment().startOf('month'));
                    //     return;
                    // };

                $status.val($clickedInput.val());
                switch ($clickedInput.val()) {
                    case '0': // panding
                        $modelTitle.html(
                            '<i class="fa fa-exclamation-circle" style="color:rgb(255, 204, 0);" aria-hidden="true"></i><h5>Are you sure?</h5>'
                            +'<p>You want to pending whole month of "'+ moment($start).format("MMMM YYYY") +'" roster!</p>');
                            $submit.text('Pending');
                            break;
                    case '1':
                        $modelTitle.html(
                            '<i class="fa fa-check-circle" style="color:#28a745;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                            +'<p>You want to approve whole month of "'+ moment($start).format("MMMM YYYY") +'" roster!</p>');
                        $submit.text('Approve');
                        break;
                    case '2':
                        $modelTitle.html(
                            '<i class="fa fa-times-circle" style="color:#dc3545;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                            +'<p>You want to reject whole month of "'+ moment($start).format("MMMM YYYY") +'" roster!</p>');
                        $submit.text('Reject');
                        break;
                }
                $modal.modal('show');

            });

            // lock status event listner
            $(document).on('click', '.btn-bysl-lock-group label', function (e) {
                e.preventDefault();
                let $this = $(this),
                    $clickedInput = $this.children(':input'),
                    $wrap = $this.parent('.btn-bysl-lock-group'),
                    $modal = $wrap.find('#roster-list-lock-status-modal'),
                    $modelTitle = $modal.find('#roster-list-lock-status-title'),
                    $from = $modal.find('#frm-roster-list-lock-status'),
                    $submit = $from.find("button[type='submit']"),
                    $status = $from.find("input[name='is_locked']"),
                    $prevStatus = $from.find("input[name='prev_is_locked']"),
                    $start = $from.find("input[name='start']"),
                    $end = $from.find("input[name='end']");

                // if($clickedInput.val() === $prevStatus.val()) return;
                $status.val($clickedInput.val());
                switch ($clickedInput.val()) {
                    case '0': // unlock
                        $modelTitle.html(
                            '<i class="fa fa-exclamation-circle" style="color:rgb(255, 204, 0);" aria-hidden="true"></i><h5>Are you sure?</h5>'
                            +'<p>You want to unlocking whole month of "'+ moment($start).format("MMMM YYYY") +'" roster!</p>');
                            $submit.text('Unlocking');
                            break;
                    case '1': // lock
                        $modelTitle.html(
                            '<i class="fa fa-check-circle" style="color:#28a745;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                            +'<p>You want to locking whole month of "'+ moment($start).format("MMMM YYYY") +'" roster!</p>');
                        $submit.text('Locking');
                        break;
                }
                $modal.modal('show');
            });


            $(document).on('submit','form', function(e){
                e.preventDefault();

                $('.roster-list-approve-status-modal').modal('hide');
                $('.roster-list-lock-status-modal').modal('hide');

                let $this = $(this),
                    $wrap = $this.parents('.btn-group-toggle');
                    _data = $this.serialize();

                $.ajax({
                    type: "POST",
                    url: $this.attr('action'),
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                    data: _data,
                    dataType: "json",
                    success: function (res) {
                        if($wrap.hasClass('btn-bysl-approve-group')) {
                            $this.find("input[name='prev_status']").val($this.find("input[name='status']").val());
                        } else if ($wrap.hasClass('btn-bysl-lock-group')) {
                            $this.find("input[name='prev_is_locked']").val($this.find("input[name='is_locked']").val());
                        }
                        toastr.success(res.message);
                        dataTable.page(dataTable.page.info().page).draw('page')
                    },
                    error: function (res) {
                        toastr.error(res.message);
                        dataTable.page(dataTable.page.info().page).draw('page')
                        //dataTable.draw();
                    }
                });

            });

            $(document).on('click','.btn-roster-approve-close, .btn-roster-lock-close', function(e){
                e.preventDefault();
                $('.roster-list-approve-status-modal').modal('hide');
                $('.roster-list-lock-status-modal').modal('hide');
                let $this = $(this),
                    $wrap = $this.parents('.btn-group-toggle');

                $wrap.find('label.btn-bysl').removeClass('active');
                if($wrap.hasClass('btn-bysl-approve-group')) {
                    let $prevStatus = $wrap.find("input[name='prev_status']");
                    $wrap.find("input[name='status']").val($prevStatus.val());
                    if($prevStatus.val() == 1){
                        $wrap.find('label.bysl-active').addClass('active')
                    } else if ($prevStatus.val() == 0) {
                        $wrap.find('label.bysl-pending').addClass('active')
                    } else if ($prevStatus.val() == 2) {
                        $wrap.find('label.bysl-reject').addClass('active')
                    }
                } else if ($wrap.hasClass('btn-bysl-lock-group')) {
                    let $prevStatus = $wrap.find("input[name='prev_is_locked']");
                    $wrap.find("input[name='is_locked']").val($prevStatus.val());

                    if($prevStatus.val() == 1){
                        $wrap.find('label.bysl-active').addClass('active')
                    } else if ($prevStatus.val() == 0) {
                        $wrap.find('label.bysl-reject').addClass('active')
                    }
                }
            });

            $("#datepicker").datepicker( {
                format: "yyyy-mm",
                startView: "months",
                minViewMode: "months"
            });

            $(document).on('click','.roster-calendar-info', function(e){
                e.preventDefault();
                $.ajax({
                    type: "GET",
                    url: this.href,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                    success: (res) => {
                        if(res.length > 0) {
                            let table = document.createElement('table');
                                table.className = 'table table-responsiv';
                            let thead = document.createElement('thead');
                            let tr = document.createElement('tr');
                            let tbody = document.createElement('tbody');

                            let columns = [
                                {name: 'Start Date',width: '12%'},
                                {name: 'End Date',width: '12%'},
                                {name: 'Status',width: '12%'},
                                {name: 'Lock Status',width: '12%'},
                                {name: 'Created By',width: '26%'},
                                {name: 'Approved By',width: '26%'},
                            ];
                            columns.forEach(item => {
                                let col = document.createElement('th');
                                    col.scope = 'col';
                                    col.style.width = item.width;
                                    col.innerHTML = item.name;
                                    tr.append(col);
                            });
                            thead.append(tr);
                            table.append(thead);

                            res.forEach(item => {
                                let row = document.createElement('tr');
                                Object.entries(item).forEach(([index, colitem]) => {
                                    if(index !== 'NumInGroup'){
                                        let col = document.createElement('td');
                                            if(colitem == 'Approved' || colitem == 'Locked') {
                                                col.style.color = '#28a745'
                                            } else if (colitem == 'Rejected' || colitem == 'Unlocked') {
                                                col.style.color = '#dc3545'
                                            } else if(colitem == 'Pending') {
                                                col.style.color = '#ffcc00'
                                            }
                                            col.innerHTML = colitem;
                                        row.append(col);
                                    }
                                });
                                tbody.append(row);
                            });
                            table.append(tbody);

                            let html = '';
                            let dataSets = this.closest('tr[role="row"]').dataset;
                            Object.entries(dataSets).forEach(([index, dataSet]) => {
                                if(dataSet != ''){
                                    html += '<div style="font-size:1.2rem; margin-right:1rem">'+index+': <small style="font-size:1.1rem; color:#64647c;">'+ dataSet +'</small></div>';
                                }
                            });
                            $('.roster-monthly-details .modal-title').html(html);
                            $('.roster-monthly-details .modal-body').html(table);
                            $('.roster-monthly-details').modal('show');

                        };
                    },
                    error: (res) => {
                        $('.roster-monthly-details').modal('hide');
                    }
                });

            });






    } );
    </script>

@endsection
