<div class="col-xxl-{{$card_width ?? ''}}">
    <div class="card card-custom card-stretch gutter-b">
        <div class="card-header" id="view-emp-name">
            <h5 class="card-title">{{$card_title}}</h5>
        </div>
        <div class="card-header border-0 pt-6">
            <div id='leave-calendar' data-leave-emp-id="" style="width:100%; padding: 30px;"></div>
        </div>
    </div>
</div>
<div id="leaveCalendarModal" class="modal fade identity-leave-calendar-modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        </div>
    </div>
</div>
<script>

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl12 = document.getElementById('leave-calendar');
        var eventsLists=null;
        var calendarEl1222 = new FullCalendar.Calendar(calendarEl12, {
            headerToolbar: {
                left: 'title',
                right: 'prev,next today'
            },
            height:500,
            showNonCurrentDates: false,
            expandRows: false,
            timeZone: 'UTC',
            navLinks: false,
            selectable: true,
            selectMirror: false,
            editable: false,
            eventOverlap: false,
            handleWindowResize: true,
            dayMaxEvents: 0,
            customButtons: {
                prev: {
                    text: 'Prev',
                    click: function (arg, sds) {
                        $("td[role='gridcell']").attr('data-events-count',0);
                        calendarEl1222.prev();
                    }
                },
                next: {
                    text: 'Next',
                    click: function () {
                        $("td[role='gridcell']").attr('data-events-count',0);
                        calendarEl1222.next();
                    }
                },
                today: {
                    text: 'Current',
                    click: function () {
                        $("td[role='gridcell']").attr('data-events-count',0);
                        calendarEl1222.today();
                    }
                }
            },
            dayCellDidMount : function(arg) {
                arg.el.setAttribute("data-events-count", 0);
            },
            dateClick: function (info) {
                setTimeout(function () {
                    $('.fc-popover.fc-more-popover').hide();
                }, 500);
                $.ajax({
                    url: '{{ route('dashboard-notification.getSpecificDateLeaveLists') }}',
                    type: 'POST',
                    data: {'start': info.dateStr},
                    success: function (data) {
                        try {
                            $.parseJSON(data);
                            $('#leaveCalendarModal').modal('hide');
                        } catch (e) {
                            $('#leaveCalendarModal').find('.modal-content').html(data);
                            $('#leaveCalendarModal').modal('show');
                        }
                        let inputEle=document.querySelectorAll('.get-employee-id');
                        inputEle.forEach(item => {
                            item.addEventListener("click", function(){
                                $("td[role='gridcell']").attr('data-events-count',0);
                                $('#leave-calendar').attr('data-leave-emp-id',$(this).attr('data-emp-id'));
                                let empName = '';
                                empName = '<h5 class="card-title"><span>Employee Name: </span> <span style="font-style: italic;font-size: 14px;color: #3699ff;">'+$(this).text()+'</span></h5>';
                                empName += '<div class="card-toolbar">';
                                empName += '<div class="example-tools justify-content-center">';
                                empName += '<a href="{{route('viewLeaveCalendar')}}" class="btn btn-primary">Back</a>';
                                empName +='</div></div>';
                                $('#view-emp-name').html(empName);
                                calendarEl1222.refetchEvents();
                                setTimeout(function () {
                                    getChartData();
                                    eventsLists = getEventsData();
                                }, 500);
                                $('#leaveCalendarModal').modal('hide');
                                $('.identity-leave-calendar-modal').removeAttr('id');
                                let dates = [];
                                $( ".leaveCalendar" ).each(function( index ) {
                                    dates.push($(this).attr('data-date'));
                                });
                                jQuery.each( dates, function( i, val ) {
                                    $("td[data-date='" +val+ "']").removeClass('leaveCalendar');
                                });
                            });
                        })
                    },
                    error: function (xhr, desc, err) {
                        console.log("error");
                    },
                    complete:function () {
                    }
                });
            },
            events: {
                url: '{{route("dashboard-notification.get-data")}}',
                type: 'GET',
                extraParams: () => {
                    return {
                        card_title: '{{$card_title ?? ''}}',
                        card_key: '{{$card_key ?? ''}}',
                        permission_key: '{{$permission_key?? ''}}',
                        room: '{{$room?? ''}}',
                        empID: $('#leave-calendar').attr('data-leave-emp-id'),
                        type: 'leave'
                    }
                },
                success: function (data) {
                    setTimeout(function () {
                        getChartData(data[0].request.start,data[0].request.end);
                    }, 500);
                },
                failure: function (er) {
                    console.log(er);
                }
            },
            moreLinkDidMount:function(args){
                setTimeout(function () {
                    if(eventsLists == null){
                        eventsLists = getEventsData();
                    }
                    //fetchingEvents(eventsLists,args);
                     eventsLists = getEventsData();
                    let totalLength = eventsLists.length;
                    let ratio = '',calculatePx = '';
                    ratio = 22/totalLength;
                    eventsLists.forEach((index, item) => {
                        calculatePx = args.num*ratio;
                        if(args.num === parseInt(index)){
                            if(calculatePx<=10){
                                args.el.style.fontSize = '13px';
                                args.el.style.fontWeight = '500';
                            }else if(calculatePx>=24){
                                args.el.style.fontSize = '24px';
                                args.el.style.fontWeight = '700';
                            }else{
                                args.el.style.fontSize = calculatePx+'px';
                                args.el.style.fontWeight = '600';
                            }
                        }
                    });
                }, 500);

            },
            moreLinkContent: function (args) {
                return args.num;
            },
            eventContent: function (arg) {
                let event = arg.event;
                let start = event.start, end = event.end, currentDate = new Date(start), eventTitle = event.title;
                let fetchEventStartDate = '';
                while (currentDate < end) {
                    fetchEventStartDate = new Date(currentDate);
                    if (eventTitle) {
                        $("td[data-date='" + moment(fetchEventStartDate).format('YYYY-MM-DD') + "']").addClass('dayWithEventtest');
                    } else {
                        $("td[data-date='" + moment(fetchEventStartDate).format('YYYY-MM-DD') + "']").addClass('leaveCalendar');
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
            },
            eventDidMount: (args) => {
                let parent = args.el.closest("td[role='gridcell']");
                //console.log(parseInt(parent.getAttribute("data-events-count")))
                parent.setAttribute("data-events-count", parseInt(parent.getAttribute("data-events-count")) + 1);
            }
        });
        calendarEl1222.render();
    });
</script>
<script>
    function getEventsData() {
        let eventsArr = [];
        $( ".leaveCalendar" ).each(function( index ) {
            let getNum = parseInt($(this).attr('data-events-count'));
            if(jQuery.inArray(getNum, eventsArr) === -1){
                eventsArr.push(getNum);
            }
        });
        return eventsArr;
    }

    function fetchingEvents(eventsLists,args){
        let totalLength = eventsLists.length;
        let ratio = '',calculatePx = '';
        ratio = 22/totalLength;
        eventsLists.forEach((index, item) => {
            calculatePx = args.num*ratio;
            if(args.num === parseInt(index)){
                if(calculatePx<=10){
                    args.el.style.fontSize = '10px';
                    args.el.style.fontWeight = '400';
                }else if(calculatePx>=24){
                    args.el.style.fontSize = '24px';
                    args.el.style.fontWeight = '700';
                }else{
                    args.el.style.fontSize = calculatePx+'px';
                    args.el.style.fontWeight = '500';
                }
            }
        });
    }
</script>



