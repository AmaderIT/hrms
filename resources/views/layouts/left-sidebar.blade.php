<div class="aside aside-left aside-fixed" id="kt_aside">
    <!--begin::Aside Brand-->
    <div class="aside-brand h-80px px-7 flex-shrink-0">
        <!--begin::Logo-->
        <a href="{{route('home')}}" class="aside-logo">
            <h2 class="text-white">BYSL HRMS</h2>
        </a>
        <!--end::Logo-->
        <!--begin::Toggle-->
        <button class="aside-toggle btn btn-sm btn-icon-white px-0" id="kt_aside_toggle">
            <span class="svg-icon svg-icon svg-icon-xl">
                <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Toggle-Right.svg-->
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                     height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <rect x="0" y="0" width="24" height="24"/>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M22 11.5C22 12.3284 21.3284 13 20.5 13H3.5C2.6716 13 2 12.3284 2 11.5C2 10.6716 2.6716 10 3.5 10H20.5C21.3284 10 22 10.6716 22 11.5Z"
                              fill="black"/>
                        <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                              d="M14.5 20C15.3284 20 16 19.3284 16 18.5C16 17.6716 15.3284 17 14.5 17H3.5C2.6716 17 2 17.6716 2 18.5C2 19.3284 2.6716 20 3.5 20H14.5ZM8.5 6C9.3284 6 10 5.32843 10 4.5C10 3.67157 9.3284 3 8.5 3H3.5C2.6716 3 2 3.67157 2 4.5C2 5.32843 2.6716 6 3.5 6H8.5Z"
                              fill="black"/>
                    </g>
                </svg>
                <!--end::Svg Icon-->
            </span>
        </button>
        <!--end::Toolbar-->
    </div>
    <!--end::Aside Brand-->
    <!--begin::Aside Menu-->
    <div id="kt_aside_menu" class="aside-menu my-5" data-menu-vertical="1" data-menu-scroll="1"
         data-menu-dropdown-timeout="500">
        <ul class="menu-nav">
            <li class="menu-item" aria-haspopup="true">
                <a href="{{route('home')}}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li class="menu-item menu-item-submenu {{ request()->is('division') || request()->is('division/*') || request()->is('district') || request()->is('district/*') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if((auth()->user()->can('View Division List')) || auth()->user()->can('View District List'))
                    <a href="javascript:;" class="menu-link menu-toggle">
                <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <path
                                d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                fill="#000000"></path>
                            <path
                                d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                </span>
                        <span class="menu-text">Address Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">

                        @can('View Division List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('division.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Division</span>
                                </a>
                            </li>
                        @endcan

                        @can('View District List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('district.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">District</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu {{ request()->is('degree') || request()->is('degree/*') || request()->is('institute') || request()->is('institute/*') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if(auth()->user()->can('View Degree List') || auth()->user()->can('View Institute List'))
                    <a href="javascript:;" class="menu-link menu-toggle">
               <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <path
                                d="M13.6855025,18.7082217 C15.9113859,17.8189707 18.682885,17.2495635 22,17 C22,16.9325178 22,13.1012863 22,5.50630526 L21.9999762,5.50630526 C21.9999762,5.23017604 21.7761292,5.00632908 21.5,5.00632908 C21.4957817,5.00632908 21.4915635,5.00638247 21.4873465,5.00648922 C18.658231,5.07811173 15.8291155,5.74261533 13,7 C13,7.04449645 13,10.79246 13,18.2438906 L12.9999854,18.2438906 C12.9999854,18.520041 13.2238496,18.7439052 13.5,18.7439052 C13.5635398,18.7439052 13.6264972,18.7317946 13.6855025,18.7082217 Z"
                                fill="#000000"></path>
                            <path
                                d="M10.3144829,18.7082217 C8.08859955,17.8189707 5.31710038,17.2495635 1.99998542,17 C1.99998542,16.9325178 1.99998542,13.1012863 1.99998542,5.50630526 L2.00000925,5.50630526 C2.00000925,5.23017604 2.22385621,5.00632908 2.49998542,5.00632908 C2.50420375,5.00632908 2.5084219,5.00638247 2.51263888,5.00648922 C5.34175439,5.07811173 8.17086991,5.74261533 10.9999854,7 C10.9999854,7.04449645 10.9999854,10.79246 10.9999854,18.2438906 L11,18.2438906 C11,18.520041 10.7761358,18.7439052 10.4999854,18.7439052 C10.4364457,18.7439052 10.3734882,18.7317946 10.3144829,18.7082217 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>

                    </span>
                        <span class="menu-text">Education Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can('View Degree List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('degree.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Degree</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Institute List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('institute.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Institute Name</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu {{ request()->is('bank') || request()->is('bank/*') || request()->is('branch') || request()->is('branch/*') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if((auth()->user()->can('View Bank List')) || auth()->user()->can('View Bank Branch List'))
                    <a href="javascript:;" class="menu-link menu-toggle">
               <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" opacity="0.3" x="13" y="4" width="3" height="16" rx="1.5"></rect>
                            <rect fill="#000000" x="8" y="9" width="3" height="11" rx="1.5"></rect>
                            <rect fill="#000000" x="18" y="11" width="3" height="9" rx="1.5"></rect>
                            <rect fill="#000000" x="3" y="13" width="3" height="7" rx="1.5"></rect>
                        </g>
                    </svg>

                    </span>
                        <span class="menu-text">Bank Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can('View Bank List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('bank.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Bank Name</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Bank Branch List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('branch.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Branch Name</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu
                    {{ request()->is('office-division') || request()->is('office-division/*')
                    || request()->is('department') || request()->is('department/*') || request()->is('designation')
                    || request()->is('designation/*')
                     ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if((auth()->user()->can("View Office Division List")) || (auth()->user()->can('View Department List'))
                    || auth()->user()->can('View Designation List'))
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path
                                        d="M5.5,4 L9.5,4 C10.3284271,4 11,4.67157288 11,5.5 L11,6.5 C11,7.32842712 10.3284271,8 9.5,8 L5.5,8 C4.67157288,8 4,7.32842712 4,6.5 L4,5.5 C4,4.67157288 4.67157288,4 5.5,4 Z M14.5,16 L18.5,16 C19.3284271,16 20,16.6715729 20,17.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,17.5 C13,16.6715729 13.6715729,16 14.5,16 Z"
                                        fill="#000000"></path>
                                    <path
                                        d="M5.5,10 L9.5,10 C10.3284271,10 11,10.6715729 11,11.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,11.5 C4,10.6715729 4.67157288,10 5.5,10 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,12.5 C20,13.3284271 19.3284271,14 18.5,14 L14.5,14 C13.6715729,14 13,13.3284271 13,12.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Divisional Info</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can("View Office Division List")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('office-division.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Division</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Department List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('department.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Department</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Designation List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('designation.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Designation</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu
                    {{ request()->is('employee-by-zgrade') || request()->is('employee-by-paygrade/*') || request()->is('employee-by-paygrade')
                    || request()->is('supervisor') || request()->is('supervisor/*')
                    || request()->is('employee/export-profile') || request()->is('division-supervisor') || request()->is('division-supervisor/*')
                     ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if(auth()->user()->can('View Employee List') || auth()->user()->can('View Promotion List') ||
                    auth()->user()->can('Termination List') || auth()->user()->can('Export Employee Profile') ||
                    auth()->user()->can('View Division Supervisor List')
                )
                    <a href="javascript:;" class="menu-link menu-toggle">
                <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <path
                                d="M5.5,4 L9.5,4 C10.3284271,4 11,4.67157288 11,5.5 L11,6.5 C11,7.32842712 10.3284271,8 9.5,8 L5.5,8 C4.67157288,8 4,7.32842712 4,6.5 L4,5.5 C4,4.67157288 4.67157288,4 5.5,4 Z M14.5,16 L18.5,16 C19.3284271,16 20,16.6715729 20,17.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,17.5 C13,16.6715729 13.6715729,16 14.5,16 Z"
                                fill="#000000"></path>
                            <path
                                d="M5.5,10 L9.5,10 C10.3284271,10 11,10.6715729 11,11.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,11.5 C4,10.6715729 4.67157288,10 5.5,10 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,12.5 C20,13.3284271 19.3284271,14 18.5,14 L14.5,14 C13.6715729,14 13,13.3284271 13,12.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                </span>
                        <span class="menu-text">Employee Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        {{-- Moved to Topbar menu
                        @can('View Employee List')
                        <li class="menu-item {{ request()->is('employee') || request()->is('employee/*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                            <a href="{{ route('employee.index') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Employee</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        @can("Employee by Pay Grade")
                            <li class="menu-item {{ request()->is('employee-by-paygrade') || request()->is('employee-by-paygrade/*') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('employee-by-paygrade.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Employee by Pay Grade</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Supervisor List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('supervisor.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Supervisor</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Division Supervisor List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('division-supervisor.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Division Supervisor</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Moved to Topbar menu@can('View Promotion List')
                        <li class="menu-item" aria-haspopup="true">
                            <a href="{{ route('promotion.index') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Promotion</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        {{-- Moved to Topbar menu
                        @can('Termination List')
                        <li class="menu-item" aria-haspopup="true">
                            <a href="{{ route('termination.index') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Termination</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        @can('Export Employee Profile')
                            <li class="menu-item {{ request()->is('employee/export-profile') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('employee.exportProfile') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Export Profile</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu
                    {{ request()->is('holiday')
                    || request()->is('holiday/*')
                    || request()->is('public-holiday')
                    || request()->is('public-holiday/*')
                    || request()->is('weekly-holiday')
                    || request()->is('weekly-holiday/*')
                    || request()->is('leave-type')
                    || request()->is('leave-type/*')
                    || request()->is('leave-allocation')
                    || request()->is('leave-allocation/*')
                    || request()->is('employee-leave-application')
                    || request()->is('employee-leave-application/*')
                    || request()->is('late-management')
                    || request()->is('late-management/*')
                    || request()->is('late-allow')
                    || request()->is('late-allow/*')
                    || request()->is('relax-day')
                    || request()->is('relax-day/*')
                    || request()->is('assign-relax-day')
                    || request()->is('assign-relax-day/*')
                    || request()->is('requested-application')
                    || request()->is('requested-application/*')
                     ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if((auth()->user()->can("View Leave Status"))
                || (auth()->user()->can('View Holiday List'))
                || auth()->user()->can('View Public Holidays')
                || auth()->user()->can('View Weekly Holiday')
                || auth()->user()->can('View Leave Type')
                || auth()->user()->can('Leave Application List')
                || auth()->user()->can('View Leave Application')
                || auth()->user()->can('View Late Management')
                || auth()->user()->can('Late Allow')
                || auth()->user()->can('View Late Management')
                || auth()->user()->can('View Relax Days')
                || auth()->user()->can('Assign Relax Day List'))

                    <a href="javascript:;" class="menu-link menu-toggle">
                    <span class="svg-icon menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                             height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M5.5,4 L9.5,4 C10.3284271,4 11,4.67157288 11,5.5 L11,6.5 C11,7.32842712 10.3284271,8 9.5,8 L5.5,8 C4.67157288,8 4,7.32842712 4,6.5 L4,5.5 C4,4.67157288 4.67157288,4 5.5,4 Z M14.5,16 L18.5,16 C19.3284271,16 20,16.6715729 20,17.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,17.5 C13,16.6715729 13.6715729,16 14.5,16 Z"
                                    fill="#000000"></path>
                                <path
                                    d="M5.5,10 L9.5,10 C10.3284271,10 11,10.6715729 11,11.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,11.5 C4,10.6715729 4.67157288,10 5.5,10 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,12.5 C20,13.3284271 19.3284271,14 18.5,14 L14.5,14 C13.6715729,14 13,13.3284271 13,12.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z"
                                    fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                        <span class="menu-text">Leave Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif

                {{-- Leave Status --}}
                {{-- Moved to Topbar menu
                @can("View Leave Status")
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        <li class="menu-item" aria-haspopup="true">
                            <a href="{{ route('leave-status.index') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Leave Status</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endcan
                --}}

                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        <li class="menu-item menu-item-submenu
                            {{ request()->is('holiday') || request()->is('holiday/*')
                                || request()->is('public-holiday')
                                || request()->is('public-holiday/*')
                                || request()->is('weekly-holiday')
                                || request()->is('weekly-holiday/*')
                                || request()->is('leave-type')
                                || request()->is('leave-type/*')
                                || request()->is('report/leave')
                                || request()->is('late-management')
                                || request()->is('late-management/*')
                                || request()->is('relax-day')
                                || request()->is('relax-day/*')
                                || request()->is('assign-relax-day')
                                || request()->is('assign-relax-day/*')

                                ? 'menu-item-open' : '' }}" aria-haspopup="true" data-menu-toggle="hover">
                            <ul class="menu-subnav">
                                @can('View Holiday List')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('holiday.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Manage Holiday</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('View Public Holidays')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('public-holiday.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Public Holiday</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('View Weekly Holiday')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('weekly-holiday.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Weekly Holiday</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('View Relax Days')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('relax-day.create') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Relax Day</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('Assign Relax Day List')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('assign-relax-day.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Assign Relax Day</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('View Leave Type')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('leave-type.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Leave Type</span>
                                        </a>
                                    </li>
                                @endcan
                                {{-- Moved to Topbar menu
                                @can('Generate Leave Report')
                                    <li class="menu-item {{ request()->is('report/leave') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                        <a href="{{ route('report.leaveReport') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Leave Report</span>
                                        </a>
                                    </li>
                                @endcan
                                --}}
                                @can('View Late Management')
                                    <li class="menu-item {{ request()->is('late-management') ? 'menu-item-active' : '' }}"
                                        aria-haspopup="true">
                                        <a href="{{ route('late-management.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Late Management</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('Late Allow')
                                    <li class="menu-item {{ request()->is('late-allow') ? 'menu-item-active' : '' }}"
                                        aria-haspopup="true">
                                        <a href="{{ route('late-allow.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Late Allow Setting</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>

                        <li class="menu-item menu-item-submenu {{
                            request()->is('apply-for-leave') || request()->is('apply-for-leave/*') ||
                            request()->is('leave-allocation') || request()->is('leave-allocation/*') ||
                            request()->is('employee-leave-application') || request()->is('employee-leave-application/*') ||
                            request()->is('requested-application') || request()->is('requested-application/*')

                             ? 'menu-item-open' : '' }}" aria-haspopup="true" data-menu-toggle="hover">
                            @if(auth()->user()->can("Leave Application List") || auth()->user()->can("View Leave Allocation List") || auth()->user()->can('View Leave Application'))
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <i class="menu-bullet menu-bullet-line">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Leave Application</span>
                                    <i class="menu-arrow"></i>
                                </a>
                            @endif
                            <div class="menu-submenu">
                                <i class="menu-arrow"></i>
                                <ul class="menu-subnav">
                                    {{-- Moved to Topbar menu
                                    @can('Leave Application List')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('apply-for-leave.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">My Leave Application</span>
                                        </a>
                                    </li>
                                    @endcan
                                    --}}

                                    @can('Show Employee Leave Applications')
                                        <li class="menu-item" aria-haspopup="true">
                                            <a href="{{ route('requested-application.index') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">Employee Leave Apply</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can("View Leave Allocation List")
                                        <li class="menu-item" aria-haspopup="true">
                                            <a href="{{ route('leave-allocation.index') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot">
                                                    <span></span>
                                                </i>
                                                <span class="menu-text">Leave Allocation</span>
                                            </a>
                                        </li>
                                    @endcan

                                    {{-- Moved to Topbar menu
                                    @can('View Leave Application')
                                    <li class="menu-item" aria-haspopup="true">
                                        <a href="{{ route('requested-application.index') }}" class="menu-link">
                                            <i class="menu-bullet menu-bullet-dot">
                                                <span></span>
                                            </i>
                                            <span class="menu-text">Requested Application</span>
                                        </a>
                                    </li>
                                    @endcan
                                    --}}
                                </ul>
                            </div>
                        </li>

                    </ul>
                </div>
            </li>


            <li class="menu-item menu-item-submenu {{ request()->is('leave-encashment') || request()->is('leave-encashment/*') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if(auth()->user()->can("Generate Leave Encashment") OR auth()->user()->can("View Leave Encashment List"))
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path
                                        d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                        fill="#000000"></path>
                                    <path
                                        d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Leave Encashment</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif

                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can("Generate Leave Encashment")
                            <li class="menu-item {{ request()->is('leave-encashment') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('leave-encashment.leaveEncashment') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Generate Encashment</span>
                                </a>
                            </li>
                        @endcan
                        @can("View Leave Encashment List")
                            <li class="menu-item {{ request()->is('leave-encashment') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('leave-encashment.leaveEncashmentList') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Leave Encashment List</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>


            <li class="menu-item menu-item-submenu
                    {{ request()->is('earning') || request()->is('earning/*')
                    || request()->is('deduction') || request()->is('deduction/*')
                    || request()->is('paygrade') || request()->is('paygrade/*')
                     ? 'menu-item-open' : '' }}" aria-haspopup="true">
                @if(auth()->user()->can("View Earnings List") OR auth()->user()->can("View Deductions List") OR auth()->user()->can("View Pay Grade List"))
                    <a href="javascript:;" class="menu-link menu-toggle">
                    <span class="svg-icon menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                             height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                    fill="#000000"></path>
                                <path
                                    d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                    fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                        <span class="menu-text">Pay Grade Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">

                        {{-- Pay Grade --}}
                        @can("View Pay Grade List")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('paygrade.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Grades</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Earning --}}
                        @can("View Earnings List")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('earning.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Earning</span>
                                </a>
                            </li>
                        @endcan

                        @can("View Deductions List")
                            {{-- Deduction --}}
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('deduction.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Deduction</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu
                    {{ request()->is('salary/prepare-salary') || request()->is('salary/view-salary') || request()->is('salary/details/*')
                    || request()->is('salary/report/*')
                    || request()->is('bonus') || request()->is('bonus/*')
                    || request()->is('user-bonus') || request()->is('user-bonus/*')
                    || request()->is('salary/pay-slip')
                     ? 'menu-item-open' : '' }}" aria-haspopup="true">

                @if(auth()->user()->can("View Bonus List") OR auth()->user()->can("View Generated Bonus List")
                    OR auth()->user()->can("View Loan List") OR auth()->user()->can("Pay Installment Amount") OR auth()->user()->can("Salary List") OR
                    auth()->user()->can("Prepare Salary") OR auth()->user()->can("Generate Salary Report") OR
                    auth()->user()->can("View Salary Yearly History"))
                    <a href="javascript:;" class="menu-link menu-toggle">
                    <span class="svg-icon menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                             height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                    fill="#000000"></path>
                                <path
                                    d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                    fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                        <span class="menu-text">Salary</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        {{-- Prepare Salary --}}
                        @can("Prepare Salary")
                            <li class="menu-item {{ request()->is('salary/prepare-salary') || request()->is('salary/prepare-salary/*') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('salary.prepareSalary') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Prepare Salary</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Show Salary --}}
                        @can("Salary List")
                            <li class="menu-item {{ request()->is('salary/view-salary') || request()->is('salary/view-salary/*') || request()->is('salary/details/*') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('salary.viewSalary') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Salary List</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Salary Report --}}
                        @can("Generate Salary Report")
                            <li class="menu-item {{ request()->is('salary/report/*') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('salary.salaryReportFilter') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Salary Report</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Moved to Topbar menu
                        @can("View Salary")
                        <li class="menu-item {{ request()->is('salary/status') || request()->is('salary/status/*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                            <a href="{{ route('salary.status') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Pay Salary by Department</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        {{-- Moved to Topbar menu
                        @can("View Salary Yearly History")
                        <li class="menu-item {{ request()->is('salary/yearly-history') || request()->is('salary/yearly-history/*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                            <a href="{{ route('salary.history') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Salary History Yearly</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        {{-- Moved to Topbar menu
                        @can("Generate Salary Report")
                        <li class="menu-item {{ request()->is('report/salary') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                            <a href="{{ route('report.salaryReport') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Salary Report</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        {{-- Generated Bonus --}}
                        @can("Salary List")
                            <li class="menu-item {{ request()->is('user-bonus/create') || request()->is('user-bonus') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ route('user-bonus.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Generate Bonus</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Bonus Report --}}
                        @can("Generate Salary Report")
                            <li class="menu-item {{ request()->is('user-bonus/report/*') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('user-bonus.bonusReportFilter') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Bonus Report</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Bonus --}}
                        @can("View Bonus List")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('bonus.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Bonus Settings</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Generate Salary --}}
                        {{--@can("Generate Pay Slip")
                        <li class="menu-item {{ request()->is('paygrade/generate-salary-sheet') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                            <a href="{{ route('paygrade.generate-salary-sheet') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Generate Salary Sheet</span>
                            </a>
                        </li>
                        @endcan--}}

                        {{-- Generate Salary --}}
                        {{--@can("Generate Pay Slip")
                            <li class="menu-item {{ request()->is('salary/pay-slip') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ route('salary.paySlip') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Generate Pay Slip</span>
                                </a>
                            </li>
                        @endcan--}}

                        {{-- Prepare Salary --}}
                        {{--<li class="menu-item
                            {{ request()->is('salary.prepare/*') ? 'menu-item-active' : '' }}
                            " aria-haspopup="true">
                            <a href="{{ route('salary.prepare') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Prepare Salary</span>
                            </a>
                        </li>--}}

                        {{-- Loan --}}
                        {{-- Moved to Topbar menu
                        @can("View Loan List")
                        <li class="menu-item" aria-haspopup="true">
                            <a href="{{ route('loan.index') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Loan</span>
                            </a>
                        </li>
                        @endcan
                        --}}

                        {{-- Employee Loan --}}
                        {{-- Moved to Topbar menu
                        @can("Pay Installment Amount")
                        <li class="menu-item" aria-haspopup="true">
                            <a href="{{ route('user-loan.index') }}" class="menu-link">
                                <i class="menu-arrow"></i>
                                <span class="menu-text ml-1">Employee Loan</span>
                            </a>
                        </li>
                        @endcan
                        --}}
                    </ul>
                </div>
            </li>

            <li class="menu-item menu-item-submenu {{ request()->is('meal') || request()->is('meal/*') || request()->is('report/meal-view') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if(auth()->user()->can("View Active Meal Consumers") OR auth()->user()->can("View Meal Reports") OR auth()->user()->can("View Pay Grade List"))
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path
                                        d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                        fill="#000000"></path>
                                    <path
                                        d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Meal</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif

                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can("View Active Meal Consumers")
                            {{-- Prepare Meal --}}
                            <li class="menu-item {{ request()->is('meal') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('meal.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Employee Meal Status</span>
                                </a>
                            </li>
                        @endcan

                        @can("View Meal Reports")
                            <li class="menu-item {{ request()->is('report/meal-view') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('report.mealReportView') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Meal Report</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>


            {{-- Tax --}}
            {{-- Moved to Topbar menu
            @if(auth()->user()->can("View Tax List") OR auth()->user()->can("Edit Tax Rules"))
                <li class="menu-item {{ ((request()->is('tax/*') || request()->is('tax-rule/*')) AND !(request()->is('tax/history*')) ? 'menu-item-active' : '') }}" aria-haspopup="true">
                    <a href="{{ route('tax.index') }}" class="menu-link">
                        <span class="svg-icon menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                                <path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                        </span>
                        <span class="menu-text">Tax</span>
                    </a>
                </li>
            @endif
            --}}

            {{-- Moved to Topbar menu
            @can("VIEW TAX HISTORY")
            <li class="menu-item {{ (request()->is('tax/history*') ? 'menu-item-active' : '') }}" aria-haspopup="true">
                <a href="{{ route('tax.history') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                                <path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">Tax History</span>
                </a>
            </li>
            @endcan
            --}}

            {{-- Tax Customization --}}
            {{-- Moved to Topbar menu
            <li class="menu-item {{ (request()->is('tax-customization/*') ? 'menu-item-active' : '') }}" aria-haspopup="true">
                @can("View Tax Customization List")
                    <a href="{{ route('tax-customization.index') }}" class="menu-link">
                            <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                                    <path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                            </span>
                        <span class="menu-text">Tax Customization</span>
                    </a>
                @endcan
            </li>
            --}}

            <li class="menu-item menu-item-submenu {{ request()->is('roles') || request()->is('roles/*') || request()->is('permission') || request()->is('permission/*') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if(auth()->user()->can('View Role List') || auth()->user()->can('Permission List') || auth()->user()->can('View Role User List'))
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path
                                        d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                        fill="#000000"></path>
                                    <path
                                        d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Roles & Permissions</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can('View Role List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('roles.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Roles</span>
                                </a>
                            </li>
                        @endcan

                        @can('Permission List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('permission.index')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Permission</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Role User List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('roles.roleUsers')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Employees by Roles</span>
                                </a>
                            </li>
                        @endcan

                        @can('Update Employees Role')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{route('roles.editEmployeeRole')}}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Update Employees Role</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            @can('View Activity Log')
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('activity') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">Activity Log</span>
                    </a>
                </li>
            @endcan

            @canany(['Roster Create', 'Roster Update', 'Roster View'])
                <li class="menu-item menu-item-submenu {{ request()->is('rosters') || request()->is('rosters/*') ? 'menu-item-open' : '' }}" aria-haspopup="true">
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                                    <path
                                        d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Rosters</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ request()->is("rosters") ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ route('rosters.index', ['type' => 'employee']) }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Employee</span>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->is("rosters") ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ route('rosters.index', ['type' => 'department']) }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Department</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcanany


            {{-- @can("View Roaster List")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('roaster.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">Roaster</span>
                    </a>
                </li>
            @endcan --}}

            {{--@can("Generate Attendance Report")
            <li class="menu-item" aria-haspopup="true">
                <a href="{{ route('report.attendanceReport') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                    <span class="menu-text">Attendance Report</span>
                </a>
            </li>
            @endcan--}}

            <li class="menu-item menu-item-submenu {{ request()->is("attendance/daily-attendance") || request()->is('zkteco-device') || request()->is('zkteco-device/*') || request()->is('report/attendance/generate/view') || request()->is("report/attendance-view/supervisor") || request()->is('report/attendance/generate/view/supervisor') || request()->is('attendance/requested-online-attendances') || request()->is('attendance/requested-online-attendances/{uuid}') ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if(auth()->user()->can("Generate Attendance Report") || auth()->user()->can("Incomplete Biometric Data") || auth()->user()->can("Sync Employee to Attendance Device") || auth()->user()->can("View Devices List") || auth()->user()->can("Generate Attendance Report to Supervisor") || auth()->user()->can("Online Attendance List") || auth()->user()->can("Online Attendance Authorized") || auth()->user()->can("Online Attendance Approved") )
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path
                                        d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                        fill="#000000"></path>
                                    <path
                                        d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Attendance</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can("Online Attendance List")
                            <li class="menu-item {{ request()->is("attendance/requested-online-attendances") ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('attendance.requested_online_attendances.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Online Attendance</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Daily Attendance --}}
                        @can("Create Daily Attendance")
                            <li class="menu-item {{ request()->is("attendance/daily-attendance") ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('attendance.dailyAttendance') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Daily Attendance</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Attendance Report --}}
                        {{-- Moved to Topbar menu
                        @can("Generate Attendance Report")
                            <li class="menu-item {{ request()->is("report/attendance-view") || request()->is('report/attendance/generate/view') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ route('report.attendanceReportView') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Attendance Report</span>
                                </a>
                            </li>
                        @endcan
                        --}}

                        {{-- Attendance Report to Supervisor --}}
                        @if(((auth()->user()->hasRole([\App\Models\User::ROLE_SUPERVISOR])) || auth()->user()->hasRole([\App\Models\User::ROLE_DIVISION_SUPERVISOR])) && auth()->user()->can("Generate Attendance Report to Supervisor"))
                            <li class="menu-item {{ request()->is("report/attendance-view/supervisor") || request()->is('report/attendance/generate/view/supervisor') ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('report.attendanceReportViewToSupervisor') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Attendance Report</span>
                                </a>
                            </li>
                        @endif
                        {{-- Incomplete Biometric Data --}}
                        @can("Incomplete Biometric Data")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('report.incompleteBiometric') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Incomplete Biometric Data</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Sync to Attendance Device --}}
                        @can("Sync Employee to Attendance Device")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('employee.syncWithBioTime') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Sync to Attendance Device</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Devices --}}
                        @can("View Devices List")
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('zkteco-device.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Devices</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            {{-- Moved to Topbar menu
            <li class="menu-item menu-item-submenu {{ request()->is('requisition') || request()->is('requisition/*') || request()->is('apply-for-requisition') || request()->is('apply-for-requisition/*') ? 'menu-item-open' : '' }}" aria-haspopup="true">
                @if((auth()->user()->can('View Requisition')) || auth()->user()->can('View My Requisition'))
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z" fill="#000000"></path>
                                    <path d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z" fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Requisition</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        @can('View My Requisition')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('apply-for-requisition.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">My Requisition Application</span>
                                </a>
                            </li>
                        @endcan

                        @can('View Requisition')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('requisition.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Employee Requisition</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>
            --}}

            <li class="menu-item menu-item-submenu
                    {{ request()->is('setting') || request()->is('setting/edit')
                    || request()->is('designation/*') || request()->is('work-slot') || request()->is('work-slot/*')
                    || request()->is('work-slot') || request()->is('work-slot/*')
                    || request()->is('action-type') || request()->is('action-type/*')
                    || request()->is('action-reason') || request()->is('action-reason/*')
                     ? 'menu-item-open' : '' }}"
                aria-haspopup="true">
                @if((auth()->user()->can("Settings")) || auth()->user()->can('View Workslot List')
                    || auth()->user()->can('View Action type') || auth()->user()->can('View Action Reason'))
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <path
                                        d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                        fill="#000000"></path>
                                    <path
                                        d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                        fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </span>
                        <span class="menu-text">Settings</span>
                        <i class="menu-arrow"></i>
                    </a>
                @endif
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">

                        {{-- App Configuration --}}
                        @can('Settings')
                            <li class="menu-item {{ request()->is("setting/edit") ? 'menu-item-active' : '' }}"
                                aria-haspopup="true">
                                <a href="{{ route('setting.edit') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">App Configuration</span>
                                </a>
                            </li>
                        @endcan

                        {{-- WorkSlot --}}
                        @can('View Workslot List')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('work-slot.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">WorkSlot</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Change Action / Status --}}
                        @can('View Action type')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('action-type.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Change Action / Status</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Change Reason --}}
                        @can('View Action Reason')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('action-reason.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Action Reason</span>
                                </a>
                            </li>
                        @endcan

                        {{-- Copy Data for another year --}}
                        @can('Copy data to Another Year')
                            <li class="menu-item" aria-haspopup="true">
                                <a href="{{ route('copy-data.index') }}" class="menu-link">
                                    <i class="menu-arrow"></i>
                                    <span class="menu-text ml-1">Copy data to Another Year</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>

            @can("View Warehouse List")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('warehouse.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">Warehouse</span>
                    </a>
                </li>
            @endcan

            @can("View Unit List")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('unit.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">Unit</span>
                    </a>
                </li>
            @endcan

            {{-- Moved to Topbar menu
            @can("List Internal Transfer")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('internal-transfer.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">Challan</span>
                    </a>
                </li>
            @endcan
            --}}


            @can("View Requisition Item List")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('requisition-item.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">WHMS Item</span>
                    </a>
                </li>
            @endcan

            @can('Sync Requisition Item')
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('requisition-item.syncItem') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                         height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"></rect>
                            <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                            <path
                                d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                fill="#000000" opacity="0.3"></path>
                        </g>
                    </svg>
                    </span>
                        <span class="menu-text">Sync WHMS Items</span>
                    </a>
                </li>
            @endcan

            @can("View Blood Bank")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('blood-bank.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                   <i style="color: #fa5661;" class="fa fa-tint"></i>
                    </span>
                        <span class="menu-text">Blood Bank</span>
                    </a>
                </li>
            @endcan

            @can("View Policy List")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('policies.index') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                   <i style="color:#fff;" class="fa fa-shield-alt"></i>
                    </span>
                        <span class="menu-text">HR Policy</span>
                    </a>
                </li>
            @endcan

            @can("View Dashboard Policy Card")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('policies.viewDashboardPolicyCard') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                   <i style="color:#fff;" class="fa fa-shield-alt"></i>
                    </span>
                        <span class="menu-text">View HR Policy</span>
                    </a>
                </li>
            @endcan

           @can("View Leave Calendar")
            {{--@can("View Leave Calendar")--}}
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('viewLeaveCalendar') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                   <i class="fa fa-calendar-alt" style="color:#014891d6;"></i>
                    </span>
                        <span class="menu-text">Leave Calendar</span>
                    </a>
                </li>
            {{--@endcan--}}
            @endcan

            @can("View Leave History Report")
                <li class="menu-item" aria-haspopup="true">
                    <a href="{{ route('report.viewLeaveHistory') }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                   <i class="fa fa-list" style="color:#014891d6;"></i>
                    </span>
                        <span class="menu-text">Leave History Report</span>
                    </a>
                </li>
                {{--@endcan--}}
            @endcan

        </ul>
    </div>
    <!--end::Aside Menu-->
</div>
