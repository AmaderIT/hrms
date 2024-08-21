@extends('layouts.app')
@section('top-css')
    <link href='{{ asset('assets/css/full_calendar_main.css') }}' rel='stylesheet'/>
    <link href='{{ asset('assets/css/custom-leave-calendar.css') }}' rel='stylesheet'/>
    <link href='{{ asset('assets/css/style-highchart.css') }}' rel='stylesheet'/>
@endsection
@section('content')
@can("View Leave Calendar")
    @include('dashboard-notification.leave-calendar-card',
             [
                 'card_width' => 12,
                 'card_key' => 'leaveCalendar',
                 'permission_key'=> 'View Leave Calendar',
                 'card_title' => 'Leave Calendar',
                 'room' => 'sp-room'
             ])
    @include('dashboard-notification.chart',
         [
             'card_width' => 12,
             'card_key' => 'leaveCalendar',
             'permission_key'=> 'View Leave Calendar',
             'card_title' => 'Leave Calendar',
             'room' => 'sp-room'
         ])
@endcan
@endsection

@section('footer-js')
    <script src='{{ asset('assets/js/full_calendar_main.js') }}'></script>
@endsection
