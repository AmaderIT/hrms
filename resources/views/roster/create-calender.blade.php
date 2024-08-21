
@extends('layouts.app')

@section("top-css")
    {{-- <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet"> --}}
    <link href='{{ asset('assets/css/full_calendar_main.css') }}' rel='stylesheet'/>
    <link href="{{ asset('assets/css/fontawesome.min.css') }}" rel="stylesheet"/>
    <style>

        #roster-calendar tr.row-selected {
            background-color: #52d2ef26;
        }
        #roster-calendar .fc-day-disabled-by-lock {
            background-color: var(--fc-neutral-bg-color, rgba(208, 208, 208, 0.3));
        }
        #roster-calendar .fc-day-past {
            background-color: #f5f5f5;
        }
        #roster-calendar .fc-day-disabled {
            background-color: #e9e9e9;
        }
        .fc-bysl-event-wrap h3 {
            font-size: 1em;
            text-align: left;
            display: block;
            margin: 0.25rem 0.25rem;
        }
        .fc-bysl-event-wrap p {
            font-size: 0.9em;
            text-align: left;
            display: block;
            margin: 0 3px 3px 5px;
        }
        .fc-bysl-event-wrap > p > span:first-child {
            margin-right: 10px;
        }
        .btn-bysl-toggle .btn {
            padding: 3px 8px 3px 8px;
        }
        .btn-bysl-toggle .btn i {
            font-size: 0.8rem;
            padding-right: 0;
        }
        .bysl-active.active {
            background-color: #28a745 !important;
        }
        .bysl-reject.active {
            background-color: #dc3545 !important;
        }
        .bysl-pending.active {
            background-color: rgb(255, 204, 0) !important;
        }
        .bysl-active.active i, .bysl-reject.active i {
            color: #ffffff !important;
        }
        #roster-modal-create .modal-header {
            flex-direction: column;
            justify-content: normal;
            align-items: baseline;
        }
        #roster-modal-create .header-bottom {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top:10px
        }
        #roster-modal-create .header-bottom p {
            margin: 0;
        }

        #roster-calendar .fc-daygrid-event-harness {
            top: 0 !important;
            margin-top: 0 !important;
            overflow: hidden;
        }
        #roster-calendar .fc-daygrid-event {
            overflow: hidden;
        }
        /* #roster-calendar .fc-day-past.fc-bysl-locked .fc-daygrid-day-top::before, #roster-calendar .fc-day-today.fc-bysl-locked .fc-daygrid-day-top {
            content:none;
        } */
        #roster-calendar .fc-bysl-locked .fc-daygrid-day-top:before,
        #roster-calendar .fc-bysl-holiday .fc-daygrid-day-top:after,
        #roster-calendar .fc-bysl-reject .fc-daygrid-day-bg:after,
        #roster-calendar .fc-bysl-approve .fc-daygrid-day-bg:after {
            font-family: "Font Awesome 5 Free";
            -webkit-font-smoothing: antialiased;
            display: inline-block;
            line-height: 1;
            position: absolute;
            font-weight: 700;
            font-style: normal;
            text-rendering: auto;
        }
        #roster-calendar .fc-bysl-locked .fc-daygrid-day-top:before {
            content: "\f023";
            padding-right: 3px;
            left: 7px;
            top: 7px;
            color: #ff0b0033;
        }
        .fc-bysl-holiday .fc-daygrid-day-top:after {
            content: "Weekend";
            padding-right: 3px;
            left: 7px;
            bottom: 7px;
            color: #ff0000;
            opacity: 0.4;
            font-size: 0.9em;
        }
        .fc-bysl-approve .fc-daygrid-day-bg::after {
            content: "\f058";
            padding-right: 3px;
            position: absolute;
            right: 2px;
            bottom: 7px;
            color: #28a745;
        }
        .fc-bysl-reject .fc-daygrid-day-bg::after {
            content: "\f057";
            padding-right: 3px;
            position: absolute;
            right: 2px;
            bottom: 7px;
            color: #dc3545;
        }


        #roster-update-approve-status-modal #roster-update-approve-status-title,
        #roster-update-lock-status-modal #roster-update-lock-status-title {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        #roster-update-approve-status-title i,
        #roster-update-lock-status-title i {
            font-size: 3.5rem;
        }
        #roster-update-approve-status-modal .btn-wrap,
        #roster-update-lock-status-modal .btn-wrap {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0 0 0;
        }
        #roster-update-approve-status-modal .modal-body,
        #roster-update-lock-status-modal .modal-body {
            padding: 2.5rem;
        }

        /*  */
        .fc-approve-button, .fc-pending-button, .fc-reject-button,
        .fc-lock-button, .fc-unlock-button {
            background-color: transparent !important;
            color: #1e2b37 !important;
        }
        .fc-approve-button::after, .fc-pending-button::after, .fc-reject-button::after,
        .fc-lock-button::after, .fc-unlock-button::after, .fc-datepicker-button::after {
            font-family: "Font Awesome 5 Free";
            -webkit-font-smoothing: antialiased;
            display: inline-block;
            font-weight: 700;
            padding-left: 5px;
            font-size: 1.3rem;
            vertical-align: middle;
        }
        .fc-approve-button::after {
            content: "\f058";
            color: #28a745;
        }
        .fc-pending-button::after {
            content: "\f192";
            color: rgb(255, 204, 0);
        }
        .fc-reject-button::after {
            content: "\f057";
            color: #dc3545;
        }
        .fc-lock-button::after {
            content: "\f023";
            font-size: 1.1rem;
            color: #28a745;
            margin: 2px 0 2px 0;
            padding-left: 8px;
            vertical-align: unset;
        }
        .fc-unlock-button::after {
            content: "\f3c1";
            color: #dc3545;
            font-size: 1.1rem;
            margin: 2px 0 2px 0;
            padding-left: 8px;
            vertical-align: unset;
        }
        .fc-monthPicker-button > span.fc-icon {
            font-family: "Font Awesome 5 Free" !important;
            -webkit-font-smoothing: antialiased;
            display: inline-block;
            font-weight: 100;
            font-size: 1.5rem !important;
            vertical-align: middle !important;
        }

        /* div:has(p) {
            background: red;
        } */

        th.week-checkbox, td.week-checkbox {
            width: 4.5%;
            text-align: center;
            vertical-align: middle;
        }
        th.week-checkbox {
            padding-top: 4px;
            padding-bottom: 2px;
        }
        .select-all-weeks {
            width: 16px;
            height: 18px;
            display: inline-block;
            vertical-align: text-top;
        }

        td.week-checkbox .select-weeks {
            width: 16px;
            height: 18px;
        }
        .fc-daygrid-week-number {
            display: none;
        }

    </style>

@endsection

@section('content')
    <div class="card card-custom" id="attendanceReportView">
        <div class="card-header">
            @if ($data['type'] == 'employee')
                <h5 class="card-title">Employee Name: {{$data['user']->name}}</h5>
            @else
                <h5 class="card-title">Department Name: {{$data['department']->name}}</h5>
            @endif
            <div class="card-toolbar">
                <div class="example-tools justify-content-center">
                    <a href="{{ route('rosters.index', ['type' => $data['type']]) }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id='roster-calendar'
                data-date-start="{{ $data['start'] ?? Carbon\Carbon::now()->startOfMonth()->startOfDay()->toDateString() }}"
                data-date-end="{{ $data['end'] ?? Carbon\Carbon::now()->endOfMonth()->endOfDay()->toDateString() }}"
                data-get-roster-events="{{ route('rosters.get') }}"
                data-roster-days="{{ route('rosters.days')}}"
                data-roster-url="{{ route('rosters.get')}}"
            >
            </div>
        </div>

        @can('Roster Approve')
            <div id="roster-update-approve-status-modal" class="modal fade" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-labelledby="rosterModalApproveUpdate" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <form name="frm-roster-update-approve-status" id="frm-roster-update-approve-status" action="{{ route('rosters.update') }}" method="POST">
                            <input type="hidden" name="type" value="{{ $data['type'] === 'employee' ? 1 : 2 }}"/>
                            <input type="hidden" name="status" value="" />
                            <input type="hidden" name="is_weekly" value="1" />
                            @if ($data['type'] == 'employee')
                                <input type="hidden" name="user_id" value="{{ $data['user']->id }}"/>
                            @else
                                <input type="hidden" name="department_id" value="{{ $data['department']->id }}"/>
                            @endif
                            <div class="modal-header">
                                @if ($data['type'] == 'employee')
                                    <h5 class="modal-title">Roster of employee: {{$data['user']->name}}</h5>
                                @else
                                    <h5 class="modal-title">Roster of department: {{$data['department']->name}}</h5>
                                @endif
                            </div>

                            <div class="modal-body">
                                <div id="roster-update-approve-status-title"></div>
                                <div class="btn-wrap">
                                    <button type="button" class="btn btn-secondary btn-roster-approve-close" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary btn-roster-approve-submit">Submit</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('Roster Unlock')
            <div id="roster-update-lock-status-modal" class="modal fade" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-labelledby="rosterModalLockUpdate" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <form name="frm-roster-update-lock-status" id="frm-roster-update-lock-status" action="{{ route('rosters.update') }}" method="POST">
                            <input type="hidden" name="type" value="{{ $data['type'] === 'employee' ? 1 : 2 }}"/>
                            <input type="hidden" name="is_locked" value="" />
                            <input type="hidden" name="is_weekly" value="1" />
                            @if ($data['type'] == 'employee')
                                <input type="hidden" name="user_id" value="{{ $data['user']->id }}"/>
                            @else
                                <input type="hidden" name="department_id" value="{{ $data['department']->id }}"/>
                            @endif
                            <div class="modal-header">
                                @if ($data['type'] == 'employee')
                                    <h5 class="modal-title">Roster of employee: {{$data['user']->name}}</h5>
                                @else
                                    <h5 class="modal-title">Roster of department: {{$data['department']->name}}</h5>
                                @endif
                            </div>

                            <div class="modal-body">
                                <div id="roster-update-lock-status-title"></div>
                                <div class="btn-wrap">
                                    <button type="button" class="btn btn-secondary btn-roster-lock-close" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary btn-roster-lock-submit">Submit</button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        @endcan

        @canany(['Roster Create', 'Roster Update'])
            <div id="roster-modal-create" class="modal fade" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-labelledby="rosterModalCreate" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <form name="frm-roster-create" id="frm-roster-create" action="{{ route('rosters.store') }}" method="POST">
                            <div class="modal-header">
                                @if ($data['type'] == 'employee')
                                    <h5 class="modal-title">Roster of employee: {{$data['user']->name}}</h5>
                                @else
                                    <h5 class="modal-title">Roster of department: {{$data['department']->name}}</h5>
                                @endif
                                <div class="header-bottom">
                                    <p id="bysl-roster-range">Range : <span></span></p>
                                    @canany(['Roster Approve', 'Roster Unlock'])
                                        <div>
                                            @can('Roster Approve')
                                                <div class="btn-group btn-group-toggle  btn-bysl-toggle pr-3" data-toggle="buttons">
                                                    <label class="btn btn-outline-secondary bysl-active active" title="Approve">
                                                        <input type="radio" name="status" value="1" id="status-active" autocomplete="1"><i class="fa fa-check"></i>
                                                    </label>
                                                    <label class="btn btn-outline-secondary bysl-pending" title="Pending"><input type="radio" name="status" value="0" id="status-pending" autocomplete="0"><i class="fas fa-dot-circle"></i></label>
                                                    <label class="btn btn-outline-secondary bysl-reject" title="Reject"><input type="radio" name="status" value="2" id="status-reject" autocomplete="2"><i class="fa fa-times"></i></label>
                                                </div>
                                            @endcan
                                            @can('Roster Unlock')
                                                <div class="btn-group btn-group-toggle  btn-bysl-toggle" data-toggle="buttons">
                                                    <label class="btn btn-outline-secondary bysl-active active" title="Lock">
                                                        <input type="radio" name="is_locked" id="locked" value="1" autocomplete="1"><i class="fas fa-lock"></i></i>
                                                    </label>
                                                    <label class="btn btn-outline-secondary bysl-reject" title="Unlock">
                                                        <input type="radio" name="is_locked" id="lock-free" value="0" autocomplete="0"><i class="fas fa-lock-open"></i>
                                                    </label>
                                                </div>
                                            @endcan
                                        </div>
                                    @endcanany
                                </div>
                            </div>

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input type="hidden" name="type" value="{{ $data['type'] === 'employee' ? 1 : 2 }}"/>
                                            @if ($data['type'] == 'employee')
                                                <input type="hidden" name="user_id" value="{{ $data['user']->id }}"/>
                                            @else
                                                <input type="hidden" name="department_id" value="{{ $data['department']->id }}"/>
                                            @endif
                                            <label for="workSlotId">Select Work Slots</label>
                                            <select class="form-control" name="work_slot_id" id="workSlotId" required>
                                                <option value="" selected>Select an option</option>
                                                @foreach($data['workSlots'] as $workSlot)
                                                    <option value="{{ $workSlot->id }}" >{{ $workSlot->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="weekly_holidays">Select Holidays</label>
                                            <select class="form-control" name="weekly_holidays[]" id="weekly_holidays" multiple></select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer text-center">
                                <button type="button" class="btn btn-secondary btn-roster-cancel" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary btn-roster-submit">Submit</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        @endcanany

    </div>
@endsection

@section('footer-js')
    <script src='{{ asset('assets/js/full_calendar_main.js') }}'></script>
    <script>
        const rosterCalender = document.getElementById('roster-calendar');
        let calendar = null;
        let selectArg = null;

        document.addEventListener('DOMContentLoaded', function() {
            var days = getDaysStatus();
            var calendarEl = document.getElementById('roster-calendar');

            let footerToolbar = '';
            @can('Roster Approve')
                footerToolbar += 'approve,pending,reject ';
            @endcan
            @can('Roster Unlock')
                footerToolbar += 'lock,unlock';
            @endcan

            calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'UTC',
                showNonCurrentDates: false,
                height: 700,
                aspectRatio: 1.8,
                dayMaxEvents: false,
                weekNumbers: true,
                droppable: false,
                navLinks: false,
                editable: false,
                dayMaxEvents: false,
                selectable: {{$data['calenderSettings']['selectable']}},
                selectMirror: {{$data['calenderSettings']['selectMirror']}},
                selectOverlap: {{$data['calenderSettings']['selectOverlap']}},
                initialDate: moment($('#roster-calendar').attr("data-date-start")).format('YYYY-MM-DD'),
                headerToolbar: {
                    left: '',
                    center: 'title',
                    right: 'prev,next,monthPicker today'
                },
                footerToolbar: {
                    center: footerToolbar,
                },
                customButtons: {
                    prev: {
                        text: 'Prev',
                        click: function(arg,sds) {
                            let _start = $('#roster-calendar').attr("data-date-start");
                            let _end = $('#roster-calendar').attr("data-date-end");
                            let _prevStartDate = moment(_start).subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
                            let _prevEndDate = moment(_start).subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
                            _start = $('#roster-calendar').attr("data-date-start", _prevStartDate);
                            _end = $('#roster-calendar').attr("data-date-end", _prevEndDate);

                            let _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month');
                            let _currentMonth = moment().startOf('month');

                            if(_startMonth < _currentMonth) {
                                $('.fc-footer-toolbar.fc-toolbar').css('visibility', 'hidden');
                            } else {
                                $('.fc-footer-toolbar.fc-toolbar').css('visibility', 'visible');
                            }

                            days = getDaysStatus();

                            window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'start', _prevStartDate));
                            window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'end', _prevEndDate));

                            calendar.prev();
                        }
                    },
                    next: {
                        text: 'Next',
                        click: function() {
                            let _start = $('#roster-calendar').attr("data-date-start");
                            let _end = $('#roster-calendar').attr("data-date-end");
                            let _nextStartDate = moment(_start).add(1, 'month').startOf('month').format('YYYY-MM-DD');
                            let _nextEndDate = moment(_start).add(1, 'month').endOf('month').format('YYYY-MM-DD');
                            _start = $('#roster-calendar').attr("data-date-start", _nextStartDate);
                            _end = $('#roster-calendar').attr("data-date-end", _nextEndDate);

                            let _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month');
                            let _currentMonth = moment().startOf('month');

                            if(_startMonth < _currentMonth) {
                                $('.fc-footer-toolbar.fc-toolbar').css('visibility', 'hidden');
                            } else {
                                $('.fc-footer-toolbar.fc-toolbar').css('visibility', 'visible');
                            }

                            days = getDaysStatus(_nextStartDate, _nextEndDate);

                            //You can reload the url like so
                            window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'start', _nextStartDate));
                            window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'end', _nextEndDate));

                            calendar.next();
                        }
                    },
                    today: {
                        text: 'Current Month',
                        click: function() {
                            let _start = $('#roster-calendar').attr("data-date-start"),
                                _end = $('#roster-calendar').attr("data-date-end"),
                                _todayStartDate =moment().startOf('month').format('YYYY-MM-DD'),
                                _todayEndDate = moment().endOf('month').format('YYYY-MM-DD');
                            _start = $('#roster-calendar').attr("data-date-start", _todayStartDate);
                            _end = $('#roster-calendar').attr("data-date-end", _todayEndDate);

                            days = getDaysStatus(_todayStartDate, _todayEndDate);

                            //You can reload the url like so
                            window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'start', _todayStartDate));
                            window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'end', _todayEndDate));

                            calendar.today();
                        }
                    },
                    monthPicker: {
                        icon: 'fa fa-calendar-alt',
                        hint : 'Month Picker',
                        click: function() {
                            $("#monthPicker").datepicker('show');
                        }
                    },
                    @can('Roster Approve')
                        approve: {
                            text: 'Approve',
                            click: function(e){
                                let $this = $(this),
                                    $modal = $('#roster-update-approve-status-modal'),
                                    $from = $('#frm-roster-update-approve-status'),
                                    _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month'),
                                    _currentMonth = moment().startOf('month');

                                if(_startMonth < _currentMonth) {
                                    $modal.find('#roster-update-approve-status-title').html(
                                    '<i class="fa fa-info-circle" style="color:#17a2b8;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>This action not work for previous months. it will only work for current month to future month !!!</p>');
                                    $from.find("button[type='submit']").css('display', 'none');
                                } else {
                                    $modal.find('#roster-update-approve-status-title').html(
                                    '<i class="fa fa-check-circle" style="color:#28a745;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>You want to approve selected weeks whole month of "'+ moment($('#roster-calendar').attr("data-date-start")).format("MMMM YYYY") +'" roster!</p>');
                                    $from.find("input[name='status']").val(1);
                                    $from.find("button[type='submit']").text('Approving');
                                    $from.find("button[type='submit']").css('display', 'inline-block');
                                }
                                $modal.modal('show');

                            }
                        },
                        pending: {
                            text: 'Pending',
                            click: function(e){
                                let $this = $(this),
                                    $modal = $('#roster-update-approve-status-modal'),
                                    $from = $('#frm-roster-update-approve-status'),
                                    _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month'),
                                    _currentMonth = moment().startOf('month');

                                if(_startMonth < _currentMonth) {
                                    $modal.find('#roster-update-approve-status-title').html(
                                    '<i class="fa fa-info-circle" style="color:#17a2b8;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>This action not work for previous months. it will only work for current month to future month !!!</p>');
                                    $from.find("button[type='submit']").css('display', 'none');
                                } else {
                                    $modal.find('#roster-update-approve-status-title').html(
                                    '<i class="fa fa-exclamation-circle" style="color:rgb(255, 204, 0);" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>You want to panding selected weeks whole month of "'+ moment($('#roster-calendar').attr("data-date-start")).format("MMMM YYYY") +'" roster!</p>');
                                    $from.find("input[name='status']").val(0);
                                    $from.find("button[type='submit']").text('Pending');
                                    $from.find("button[type='submit']").css('display', 'inline-block');
                                }
                                $modal.modal('show');
                            }
                        },
                        reject: {
                            text: 'Reject',
                            click: function(e){
                                let $this = $(this),
                                    $modal = $('#roster-update-approve-status-modal'),
                                    $from = $('#frm-roster-update-approve-status'),
                                    _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month'),
                                    _currentMonth = moment().startOf('month');

                                if(_startMonth < _currentMonth) {
                                    $modal.find('#roster-update-approve-status-title').html(
                                    '<i class="fa fa-info-circle" style="color:#17a2b8;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>This action not work for previous months. it will only work for current month to future month !!!</p>');
                                    $from.find("button[type='submit']").css('display', 'none');
                                } else {
                                    $modal.find('#roster-update-approve-status-title').html(
                                    '<i class="fa fa-times-circle" style="color:#dc3545;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>You want to reject selected weeks whole month of "'+ moment($('#roster-calendar').attr("data-date-start")).format("MMMM YYYY") +'" roster!</p>');
                                    $from.find("input[name='status']").val(2);
                                    $from.find("button[type='submit']").text('Rejecting');
                                    $from.find("button[type='submit']").css('display', 'inline-block');
                                }
                                $modal.modal('show');
                            }
                        },
                    @endcan
                    @can('Roster Unlock')
                        lock: {
                            text: 'Lock',
                            click: function(e){
                                let $this = $(this),
                                    $modal = $('#roster-update-lock-status-modal'),
                                    $from = $('#frm-roster-update-lock-status'),
                                    _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month'),
                                    _currentMonth = moment().startOf('month');

                                if(_startMonth < _currentMonth) {
                                    $modal.find('#roster-update-lock-status-title').html(
                                    '<i class="fa fa-info-circle" style="color:#17a2b8;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>This action not work for previous months. it will only work for current month to future month !!!</p>');
                                    $from.find("button[type='submit']").css('display', 'none');
                                } else {
                                    $modal.find('#roster-update-lock-status-title').html(
                                    '<i class="fas fa-lock" style="color:#28a745;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>You want to lock selected weeks whole month of "'+ moment($('#roster-calendar').attr("data-date-start")).format("MMMM YYYY") +'" roster!</p>');
                                    $from.find("input[name='is_locked']").val(1);
                                    $from.find("button[type='submit']").text('Locking');
                                    $from.find("button[type='submit']").css('display', 'inline-block');
                                }
                                $modal.modal('show');
                            }
                        },
                        unlock: {
                            text: 'Unlock',
                            click: function(e){
                                let $this = $(this),
                                    $modal = $('#roster-update-lock-status-modal'),
                                    $from = $('#frm-roster-update-lock-status'),
                                    _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month'),
                                    _currentMonth = moment().startOf('month');

                                if(_startMonth < _currentMonth) {
                                    $modal.find('#roster-update-lock-status-title').html(
                                    '<i class="fa fa-info-circle" style="color:#17a2b8;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>This action not work for previous months. it will only work for current month to future month !!!</p>');
                                    $from.find("button[type='submit']").css('display', 'none');
                                } else {
                                    $modal.find('#roster-update-lock-status-title').html(
                                    '<i class="fas fa-lock-open" style="color:#dc3545;" aria-hidden="true"></i><h5>Are you sure?</h5>'
                                    +'<p>You want to unlock selected weeks whole month of "'+ moment($('#roster-calendar').attr("data-date-start")).format("MMMM YYYY") +'" roster!</p>');
                                    $from.find("input[name='is_locked']").val(0);
                                    $from.find("button[type='submit']").text('Unlocking');
                                    $from.find("button[type='submit']").css('display', 'inline-block');
                                }
                                $modal.modal('show');
                            }
                        }
                    @endcan

                },
                select: function(arg) {
                    selectArg = arg;

                    // init model title date range
                    _modelDateRange = document.getElementById('bysl-roster-range').getElementsByTagName('span')[0];
                    _modelDateRange.innerHTML = moment(arg.startStr).format("DD/MM/YYYY") + ' - ' + moment(arg.endStr).add(-1, 'day').format("DD/MM/YYYY");

                    // Holiday select field generate
                    selectHolidays = $('#weekly_holidays');
                    daysName = getDatesName(arg.start, arg.end);
                    selectHolidays.empty();
                    Object.entries(daysName).forEach(([key, value]) =>{
                        selectHolidays.append(new Option(value, key)).trigger('change');
                    });

                    $("#weekly_holidays").select2({
                        placeholder: "Select an options",
                        allowClear: true,
                        width: '100%'
                    });

                    getOverlapEvents(calendar, arg);
                },
                selectAllow:function(arg) {

                    @if ($data['calenderSettings']['currentDateAccess'] == 1)
                        if(moment().add(-1, 'day') < arg.start ) return !isLocked(days, arg);
                    @else
                        if(moment().diff(arg.start) <= 0 ) return !isLocked(days, arg);
                    @endif
                    return false;
                },
                dayCellClassNames:function(args){
                    return getClasses(days, moment(args.date).format("YYYY-MM-DD"));
                },
                events: function(info, successCallback, failureCallback) {
                    @if ($data['type'] === 'employee')
                        _data = {
                            type: "{{ $data['type'] === 'employee' ? 1 : 2 }}",
                            r_type: 'events',
                            user_id: "{{ $data['user']->id }}",
                            start: $('#roster-calendar').attr("data-date-start"),
                            end: $('#roster-calendar').attr("data-date-end")
                        }
                    @else
                        _data = {
                            type: "{{ $data['type'] === 'employee' ? 1 : 2 }}",
                            r_type: 'events',
                            department_id: "{{ $data['department']->id }}",
                            start: $('#roster-calendar').attr("data-date-start"),
                            end: $('#roster-calendar').attr("data-date-end")
                        }
                    @endif
                    $.ajax({
                        url: calendarEl.getAttribute('data-get-roster-events'),
                        data: _data,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                        dataType: 'json',
                        success: function(res) {
                            // colorsGenerate(15, res.colors);
                            let events = [];
                            res.data.forEach(item => {
                                if(item.sub_roster_count > 1) {
                                    item.end = moment(item.end).add(1, 'days').format("YYYY-MM-DD HH:mm:ss");
                                }

                                events.push({
                                    id: item.id,
                                    allDay: true,
                                    allow: false,
                                    title: item.work_slot.title + ' (' + moment(item.work_slot.start_time, 'hh:mm').format('hh:mm A') + ' - ' + moment(item.work_slot.end_time, 'hh:mm').format('hh:mm A') +')',
                                    work_slot_id: item.work_slot.id,
                                    overlap: false,
                                    start: item.start,
                                    end: item.end,
                                    holiday: item.is_weekly_holiday == 1 ? 'true' : 'false',
                                    lock: item.is_locked == 1 ? 'true' : 'false',
                                    backgroundColor: colorsGenerate(item.work_slot.id, res.colors),
                                });

                            })
                            successCallback(events);
                        }
                    });
                },
                eventContent: function(arg) {
                    let wrap = document.createElement('div'),
                        title = document.createElement('h3'),
                        p = document.createElement('p'),
                        deleteLink = document.createElement('a'),
                        locl = document.createElement('a');

                    let _event = arg.event,
                        _eventEx = arg.event.extendedProps,
                        wrapClass = 'fc-bysl-event-wrap';

                    wrap.id = _event.id;
                    wrap.className = wrapClass;
                    title.innerHTML = _event.title;
                    title.title = _event.title;
                    title.className = 'work-slot-'+_eventEx.work_slot_id;
                    wrap.appendChild(title);

                    let arrayOfDomNodes = [ wrap ]
                    return { domNodes: arrayOfDomNodes }
                },

                @canany(['Roster Approve', 'Roster Unlock'])
                    weekNumberDidMount: function(arg) {
                        let thEle = document.createElement("td");
                            thEle.className = 'week-checkbox';
                        let pEle = document.createElement("p");
                            pEle.innerHTML = arg.text;
                            pEle.style.marginBottom = '0.25rem';
                            thEle.append(pEle);

                        let inputEle = document.createElement("INPUT");
                            inputEle.setAttribute("type", "checkbox");
                            inputEle.className = 'select-weeks';
                            inputEle.name = 'selected_weeks['+arg.num+']';
                            inputEle.value = '';

                        thEle.append(inputEle);
                        inputEle.addEventListener("change", function(){
                            if(this.checked == true){
                                $(this).parent('td.week-checkbox').parent('tr[role="row"]').addClass('row-selected');
                                $(this).prop('checked', true).change();
                            } else {
                                $(this).parent('td.week-checkbox').parent('tr[role="row"]').removeClass('row-selected');
                                $(this).prop('checked', false).change();
                            }
                        });

                        arg.el.closest("tr[role='row']").prepend(thEle);
                    },
                @endcanany

            });
            calendar.render();

            let _startMonth = moment($('#roster-calendar').attr("data-date-start")).startOf('month');
            let _currentMonth = moment().startOf('month');

            if(_startMonth < _currentMonth) {
                $('.fc-footer-toolbar.fc-toolbar').css('visibility', 'hidden');
            } else {
                $('.fc-footer-toolbar.fc-toolbar').css('visibility', 'visible');
            }

            @canany(['Roster Approve', 'Roster Unlock'])
                $("thead[role='presentation'] tr[role='row']").prepend('<th class="week-checkbox fc-col-header-cell"><input id="select_all_weeks" class="select-all-weeks" type="checkbox" name="select_all_weeks"></td>');
                document.getElementById('select_all_weeks').addEventListener("change", function(e){
                    if(this.checked == true){
                        $('input.select-weeks[type="checkbox"]').prop('checked', true).change();
                        $('input.select-weeks[type="checkbox"]').trigger('change');
                        $('tbody tr[role="row"]').addClass('row-selected');
                        $(this).prop('checked', true).change();
                    } else {
                        $('input.select-weeks[type="checkbox"]').prop('checked', false).change();
                        $('input.select-weeks[type="checkbox"]').trigger('change');
                        $('tbody tr[role="row"]').removeClass('row-selected');
                        $(this).prop('checked', false).change();
                    }
                });
            @endcanany


            $(".fc-monthPicker-button").append('<input type="text" name="month_picker" id="monthPicker" class="monthPicker" placeholder="YYYY-MM" style="padding: 0;width: 0;border: none;margin: 0;">');
            $("#monthPicker").datepicker( {
                format: "yyyy-mm",
                startView: "months",
                minViewMode: "months",
                autoSize: true,
                setDate: new Date(),
                autoclose: true
            });

            $('#monthPicker').on('change', function() {
                let $this = $(this),
                    $start = $('#roster-calendar').attr("data-date-start"),
                    $end = $('#roster-calendar').attr("data-date-end"),
                    _startDate = moment($this.val()).startOf('month').format('YYYY-MM-DD'),
                    _endDate = moment($this.val()).endOf('month').format('YYYY-MM-DD');

                $start = $('#roster-calendar').attr("data-date-start", _startDate);
                $end = $('#roster-calendar').attr("data-date-end", _endDate);

                days = getDaysStatus();

                window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'start', _startDate));
                window.history.pushState("", "", updateQueryStringParameter(window.location.href, 'end', _endDate));
                calendar.gotoDate($(this).val());
            });









        }); // inside DOMContentLoad


        function getOverlapEvents(calendar, arg) {
            _type = $("#frm-roster-create input[name='type']").val();
            if(_type == 1){
                _data = {
                    type: _type,
                    r_type: 'overlap_events',
                    user_id: $("#frm-roster-create input[name='user_id']").val(),
                    start: arg.startStr,
                    end: moment(arg.endStr).add(-1, 'day').format("YYYY-MM-DD"),
                }
            } else {
                _data = {
                    type: _type,
                    r_type: 'overlap_events',
                    department_id: $("#frm-roster-create input[name='department_id']").val(),
                    start: arg.startStr,
                    end: moment(arg.endStr).add(-1, 'day').format("YYYY-MM-DD"),
                }
            }
            $.ajax({
                type: "GET",
                url: rosterCalender.getAttribute('data-roster-url'),
                data: _data,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                dataType: "json",
                success: function (res) {
                    if(res.data.length > 0) {
                        item = res.data[0];
                        // status input field init
                        if(item.status == 1) {
                            $("#status-active").prop("checked", true).trigger("click");
                        } else if (item.status == 0){
                            $("#status-pending").prop("checked", true).trigger("click");
                        } else if (item.status == 2){
                            $("#status-reject").prop("checked", true).trigger("click");
                        }
                        // is_locked input field init
                        if(item.is_locked == 1) {
                            $("#locked").prop("checked", true).trigger("click");
                        } else if (item.is_locked == 0){
                            $("#lock-free").prop("checked", true).trigger("click");
                        }
                        // workslot input field init
                        $('#workSlotId').val(item.work_slot.id);
                        $('#workSlotId').trigger('change');
                        // holiday input field init
                        if(item.is_weekly_holiday == 1) {
                            $('#weekly_holidays').val(moment(item.active_date).format('ddd').toLowerCase());
                            $('#weekly_holidays').trigger('change');
                        }
                    } else {
                        $("#status-pending").prop("checked", true).trigger("click").hide();
                        $("#locked").prop("checked", true).trigger("click").hide();
                    }

                    $('#roster-modal-create').modal('show');
                },
                error: function (res) {
                    $.when( toastr.error(res.message) ).then(function( data, textStatus, jqXHR ) {
                        window.location.reload();
                    });
                }
            });
        }

        $( document ).ready(function() {


            $('#frm-roster-update-approve-status').submit(function (e) {
                e.preventDefault();
                $('#roster-update-approve-status-modal').modal('hide');

                let $this = $(this),
                    _start = $('#roster-calendar').attr("data-date-start"),
                    _end = $('#roster-calendar').attr("data-date-end"),
                    _selected_days = daysOfWeek();

                if(_selected_days.length <= 0) return toastr.info('Any week of this month was not selected !!!');
                _selected_days = JSON.stringify(_selected_days);

                _selected_days = '&selected_days=' + _selected_days;
                let _data = ($(this).serialize()) +'&start='+ _start + '&end='+ _end + _selected_days;

                updateFromAjax($this, _data);
            });

            $('#frm-roster-update-lock-status').submit(function (e) {
                e.preventDefault();
                $('#roster-update-lock-status-modal').modal('hide');
                let $this = $(this),
                    _start = $('#roster-calendar').attr("data-date-start"),
                    _end = $('#roster-calendar').attr("data-date-end"),
                    _selected_days = daysOfWeek();

                if(_selected_days.length <= 0) return toastr.info('Any week of this month was not selected !!!');
                _selected_days = JSON.stringify(_selected_days);

                _selected_days = '&selected_days=' + _selected_days;
                let _data = ($(this).serialize()) +'&start='+ _start + '&end='+ _end + _selected_days;

                updateFromAjax($this, _data);
            });

            $('#frm-roster-create').submit(function (e) {
                e.preventDefault();
                $('#roster-modal-create').modal('hide');
                let $this = $(this),
                    _data = ($(this).serialize()) +'&start='+ selectArg.startStr + '&end='+ selectArg.endStr;

                createFromAjax($this, _data);
            });

        });

        function daysOfWeek() {
            let daysOfWeek = new Array();
            $('input.select-weeks[type="checkbox"]:checked').each(function(i, el) {
                let daysOfWeekEl = el.closest('tr[role="row"]').querySelectorAll('td[role="gridcell"]');
                daysOfWeekEl.forEach(dayEl => {
                    if(dayEl.getAttribute('data-date') != null){
                        daysOfWeek.push(dayEl.getAttribute('data-date'));
                    };
                });
            });
           return daysOfWeek;
        }

        function updateFromAjax($this, _data){
            $.ajax({
                type: "POST",
                url: $this.attr('action'),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                data: _data,
                dataType: "json",
                success: function (response) {
                    $.when( toastr.success(response.message) ).then(function( data, textStatus, jqXHR ) {
                        window.location.reload();
                    });
                },
                error: function (response) {
                    toastr.error(response.message);
                }


            });
        }

        function getDaysStatus() {
            _type = {{ $data['type'] === 'employee' ? 1 : 2 }};
            let data = [];
            @if ($data['type'] == 'employee')
                _data = {
                    type: _type,
                    user_id: {{ $data['user']->id }},
                    start: $('#roster-calendar').attr("data-date-start"),
                    end: $('#roster-calendar').attr("data-date-end"),
                }
            @else
                _data = {
                    type: _type,
                    department_id: {{ $data['department']->id }},
                    start: $('#roster-calendar').attr("data-date-start"),
                    end: $('#roster-calendar').attr("data-date-end"),
                }
            @endif

            $.ajax({
                type: "GET",
                url: rosterCalender.getAttribute('data-roster-days'),
                data: _data,
                async: false,
                dataType: "json",
                success: function (res) {
                    let processDate = new Array();
                    res.data.forEach(item=>{
                        processDate.push({
                            active_date : moment(item.active_date).add(+6, 'hours').format('YYYY-MM-DD'),
                            is_locked : item.is_locked,
                            is_weekly_holiday : item.is_weekly_holiday,
                            status : item.status
                        })
                    });
                    data = {
                        dates:processDate,
                        lockContext:res.lockContext,
                    }
                },
            });
            return data;
        }

        function createFromAjax($this, _data) {
            $.ajax({
                type: "POST",
                url: $this.attr('action'),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                data: _data,
                dataType: "json",
                success: function (response) {
                    $.when( toastr.success(response.message) ).then(function( data, textStatus, jqXHR ) {
                        window.location.href = window.location.pathname+ "?_id=" + response._id
                        window.location;
                        // window.location.reload();
                    });
                },
                error: function (response) {
                    $.when( toastr.error(response.message) ).then(function( data, textStatus, jqXHR ) {
                        window.location.href = window.location.pathname+ "?_id=" + response._id
                        window.location;
                        // window.location.reload();
                    });
                }
            });
        }

        function getDates(start, end) {
            var dates = new Array();
            var startDate = moment(start);
            var endDate = moment(end);
            while (startDate <= endDate) {
                dates.push( {day : moment(startDate).format('YYYY-MM-DD'), name:moment(startDate).format('ddd')} )
                startDate = moment(startDate).add(1, 'days').format('ddd');
            }
            return dates;
        }

        function getDatesName(start, end) {
            var dates = new Array();
            var startDate = moment(start);
            var endDate = moment(end);
            while (startDate < endDate) {
                dates[(moment(startDate).format('ddd')).toLowerCase()] = moment(startDate).format('dddd')
                startDate = moment(startDate).add(1, 'days');
            }
            return dates;
        }

        function isLocked(days, arg){
            if(days.lockContext === 'dont_follow_days_records') {
                return false;
            } else {
                let dates = days.dates;
                let startDay = moment(arg.startStr).format('DD');
                let endDay = moment(arg.endStr).add(-1, 'day').format('DD');
                let flag = [0];
                if(!dates) return false;
                for( let i = startDay; i <= endDay; i++) {
                    for (let index = 0; index < dates.length; index++) {
                        let dbDay = moment(dates[index].active_date).format('DD')
                        if(i == dbDay) flag.push(dates[index].is_locked)
                    }
                }
                return Math.max.apply(null, flag);
            }
        }

        function getClasses(days, date){
            let classes = '';
            let dates = days.dates;
            if(!dates) return ''
            for (let index = 0; index < dates.length; index++) {
                if(moment(dates[index].active_date).format('YYYY-MM-DD') == date) {
                    classes += dates[index].is_weekly_holiday == 1 ? 'fc-bysl-holiday' : '';
                    if(days.lockContext === 'dont_follow_days_records') {
                        classes += dates[index].is_locked == 1 ? ' fc-bysl-locked' : '';
                    } else {
                        classes += dates[index].is_locked == 1 ? ' fc-bysl-locked fc-day-disabled-by-lock' : '';
                    }
                    if(dates[index].status == 0) {
                        classes += ' fc-bysl-pending';
                    } else if (dates[index].status == 1) {
                        classes += ' fc-bysl-approve';
                    } else if (dates[index].status == 2) {
                        classes += ' fc-bysl-reject';
                    }
                    return classes;
                }
            }
        }

        function updateQueryStringParameter(uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            }
            else {
                return uri + separator + key + "=" + value;
            }
        }

        function colorsGenerate(id, colors) {
            if(colors.hasOwnProperty(id)) {
                return colors[id];
            } else {
                let max_rgb_int = 1661324;
                id = id * max_rgb_int;
                let blue = Math.floor(id % 256);
                let green = Math.floor(id / 256 % 256);
                let red = Math.floor(id / 256 / 256 % 256);
                return 'rgba('+ red +','+ green +','+ blue +',0.8)';
            }
        }

    </script>
@endsection
