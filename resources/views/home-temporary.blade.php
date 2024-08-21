@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    {{-- Overview --}}
    @can('View Dashboard Employee List')
    <div class="row">
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75" style="font-size: 15px">NUMBER OF EMPLOYEES</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                            <span class="navi-icon">
                                                <span class="svg-icon svg-icon-md svg-icon-primary">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         width="24px" height="24px"
                                                         viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                            <polygon points="0 0 24 0 24 24 0 24"/>
                                                            <path
                                                                d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                            <path
                                                                d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                fill="#000000" fill-rule="nonzero"/>
                                                        </g>
                                                    </svg>
                                                    <!--end::Svg Icon-->
                                                </span>
                                            </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/employee.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 mr-0 text-primary">{{ $data["totalEmployee"] }}</div>
                    </div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Stats Widget 1-->
        </div>
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75" style="font-size: 15px">NUMBER OF DEPARTMENTS</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24"
                                         version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <span class="svg-icon svg-icon-md svg-icon-primary">
                                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                                <path
                                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                    fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                                <path
                                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                    fill="#000000" fill-rule="nonzero"/>
                                                            </g>
                                                        </svg>
                                                        <!--end::Svg Icon-->
                                                    </span>
                                                </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/department.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 pl-5 pr-5 text-primary">{{ $data["departments"] }}</div>
                    </div>
                </div>
            </div>
            <!--end::Stats Widget 1-->
        </div>
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75">TODAYS PRESENT</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <span class="svg-icon svg-icon-md svg-icon-primary">
                                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                <polygon
                                                                    points="0 0 24 0 24 24 0 24"/>
                                                                <path
                                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                    fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                                <path
                                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                    fill="#000000" fill-rule="nonzero"/>
                                                            </g>
                                                        </svg>
                                                        <!--end::Svg Icon-->
                                                    </span>
                                                </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/present.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 pl-5 pr-5 text-primary">{{ $data["presentToday"] }}</div>
                    </div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Stats Widget 1-->
        </div>
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75">TODAYS ABSENT</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <span class="svg-icon svg-icon-md svg-icon-primary">
                                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1"
                                                               fill="none" fill-rule="evenodd">
                                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                                <path
                                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                    fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                                <path
                                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                    fill="#000000" fill-rule="nonzero"/>
                                                            </g>
                                                        </svg>
                                                        <!--end::Svg Icon-->
                                                    </span>
                                                </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/absent.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 pl-5 pr-5 text-primary">{{ $data["absentToday"] }}</div>
                    </div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Stats Widget 1-->
        </div>

        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75" style="font-size: 15px">UNPAID LEAVE</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                            <span class="navi-icon">
                                                <span class="svg-icon svg-icon-md svg-icon-primary">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         width="24px" height="24px"
                                                         viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                            <polygon points="0 0 24 0 24 24 0 24"/>
                                                            <path
                                                                d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                            <path
                                                                d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                fill="#000000" fill-rule="nonzero"/>
                                                        </g>
                                                    </svg>
                                                    <!--end::Svg Icon-->
                                                </span>
                                            </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{ asset('assets/media/users/employee.png') }}" width="60px" height="60px">
                        <div class="display5 py-4 mr-0 text-primary">
                            @if($data["unpaidLeaves"]->count() > 1)
                                {{ $data["unpaidLeaves"]->count() }}
                            @elseif($data["unpaidLeaves"]->count() == 1)
                                <a href="{{ route('apply-for-leave.create', ['date' => $data["unpaidLeaves"]->first()->leave_date]) }}">
                                    {{ $data["unpaidLeaves"]->count() }}
                                </a>
                            @else
                                {{ $data["unpaidLeaves"]->count() }}
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Stats Widget 1-->
        </div>
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75" style="font-size: 15px">NUMBER OF DEPARTMENTS</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24"
                                         version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <span class="svg-icon svg-icon-md svg-icon-primary">
                                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                                <path
                                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                    fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                                <path
                                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                    fill="#000000" fill-rule="nonzero"/>
                                                            </g>
                                                        </svg>
                                                        <!--end::Svg Icon-->
                                                    </span>
                                                </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/department.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 pl-5 pr-5 text-primary">{{ $data["departments"] }}</div>
                    </div>
                </div>
            </div>
            <!--end::Stats Widget 1-->
        </div>
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75">TODAYS PRESENT</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <span class="svg-icon svg-icon-md svg-icon-primary">
                                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                <polygon
                                                                    points="0 0 24 0 24 24 0 24"/>
                                                                <path
                                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                    fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                                <path
                                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                    fill="#000000" fill-rule="nonzero"/>
                                                            </g>
                                                        </svg>
                                                        <!--end::Svg Icon-->
                                                    </span>
                                                </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/present.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 pl-5 pr-5 text-primary">{{ $data["presentToday"] }}</div>
                    </div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Stats Widget 1-->
        </div>
        <div class="col-xl-3 dashboard-card">
            <!--begin::Stats Widget 1-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label text-dark-75">TODAYS ABSENT</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="dropdown dropdown-inline" data-toggle="tooltip" data-placement="left">
                            <a href="#" class="btn btn-icon-primary btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Dots.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1">
                                            <rect x="14" y="9" width="6" height="6" rx="3" fill="black"/>
                                            <rect x="3" y="9" width="6" height="6" rx="3" fill="black" fill-opacity="0.7"/>
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi navi-hover py-5">
                                    <li class="navi-item">
                                        <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <span class="svg-icon svg-icon-md svg-icon-primary">
                                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1"
                                                               fill="none" fill-rule="evenodd">
                                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                                <path
                                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                    fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                                <path
                                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                    fill="#000000" fill-rule="nonzero"/>
                                                            </g>
                                                        </svg>
                                                        <!--end::Svg Icon-->
                                                    </span>
                                                </span>
                                            <span class="navi-text">Member</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex align-items-center justify-content-between pt-7 flex-wrap">
                    <div class="progress-vertical w-250px">
                        <img src="{{asset('assets/media/users/absent.png')}}" width="60px" height="60px">
                        <div class="display5 py-4 pl-5 pr-5 text-primary">{{ $data["absentToday"] }}</div>
                    </div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Stats Widget 1-->
        </div>
    </div>
    @endcan

    {{-- Employee Attendance --}}
    <div class="row">
        <div class="col-xxl-12">
            <!--begin::List Widget 7-->
            <div class="card card-custom card-stretch gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bold font-size-h4 text-dark-75">Attendance</span>
                        <span class="text-muted mt-3 font-weight-bold font-size-sm">Employee Attendance</span>
                    </h3>
                </div>

                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-0 pb-4">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ml-10 mr-10 mb-0">
                                <form action="{{ route('filterAttendance') }}" method="POST">
                                    @csrf
                                    <div class="form-row">
                                        <div class="col">
                                            <label for="status" class="mr-3">Office Division</label>
                                            <select class="form-control select" id="office_division_id" name="office_division_id">
                                                <option value="" disabled selected>Choose an option</option>
                                                @foreach($data["officeDivisions"] as $officeDivision)
                                                    <option value="{{ $officeDivision->id }}">
                                                        {{ $officeDivision->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="department_id" class="mr-3">Department</label>
                                            <select class="form-control select" id="department_id" name="department_id">
                                                <option value="" disabled selected>Choose an option</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="date" class="mr-3">Date</label>
                                            <input type="date" class="form-control" name="date">
                                        </div>
                                        <div class="col">
                                            <button type="submit" class="btn btn-primary mr-2 mt-7">Filter</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-responsive-lg">
                            <thead class="custom-thead">
                            <tr>
                                <th scope="col">Photo</th>
                                <th scope="col">Office ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Office Division</th>
                                <th scope="col">Department</th>
                                <th scope="col">Time In</th>
                                <th scope="col">Time Out</th>
                                <th scope="col">Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data["attendances"] as $attendance)
                                @if(!is_null($attendance["data"]))
                                    <tr>
                                        <td></td>
                                        <td>{{ $attendance["data"]->emp_code }}</td>
                                        <td>{{ $attendance["promotion"]->user->name ??  $attendance["data"]->first_name . " " . $attendance["data"]->last_name }}</td>
                                        <td>{{ $attendance["promotion"]->officeDivision->name ?? "" }}</td>
                                        <td>{{ $attendance["promotion"]->department->name ?? "" }}</td>
                                        <td>{{ date('h:i:s a', strtotime($attendance["data"]->timeIn->punch_time))  }}</td>
                                        <td>{{ date('h:i:s a', strtotime($attendance["data"]->timeOut->punch_time))  }}</td>
                                        <td>{{ date("M d, Y", strtotime($attendance["data"]->timeIn->punch_time)) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::List Widget 7-->
        </div>
    </div>

    {{-- Employee Unpaid Leave --}}
    @if($data["unpaidLeaves"]->count() > 0)
        <div class="row">
            <div class="col-xxl-12">
                <!--begin::List Widget 7-->
                <div class="card card-custom card-stretch gutter-b">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label font-weight-bold font-size-h4 text-dark-75">Unpaid Leave</span>
                            <span class="text-muted mt-3 font-weight-bold font-size-sm">Employee Unpaid Leave</span>
                        </h3>
                    </div>

                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body pt-0 pb-4">
                        <div class="card-body">
                            <table class="table table-responsive-lg" id="employeeUnpaidLeave">
                                <thead class="custom-thead">
                                <tr>
                                    <th scope="col">Leave Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($data["unpaidLeaves"] as $unpaidLeave)
                                    <tr>
                                        <td>{{ date("M d, Y", strtotime($unpaidLeave->leave_date)) }}</td>
                                        <td>
                                            <a href="{{ route('apply-for-leave.create', ['date' => $unpaidLeave->leave_date]) }}" class="btn btn-primary btn-sm">Apply for Leave</a>
                                        </td>
                                    </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                <!--end::Body-->
            </div>
            <!--end::List Widget 7-->
        </div>
    </div>
    @endif
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#employeeUnpaidLeave").DataTable();
        });

        // Get department by division
        // Get department by division
        $('#office_division_id').change(function(){
            var _officeDivisionID = $(this).val();

            let url = "{{ route('salary.getDepartmentByOfficeDivision', ':officeDivision') }}";
            url = url.replace(":officeDivision", _officeDivisionID);

            $.get(url, {}, function (response, status) {
                $("#department_id").empty();
                $("#department_id").append('<option value="" "selected disabled">Select an option</option>');
                $.each(response.data.departments, function(key, value) {
                    $("#department_id").append('<option value="' + value.id + '">'+ value.name + '</option>');
                });
            })
        });

        // Enable Select2
        $("select").select2({
            theme: "classic",
        });

        // Year Picker
        $("#datepicker").datepicker( {
            format: "yyyy",
            startView: "years",
            minViewMode: "years"
        });
    </script>
@endsection
