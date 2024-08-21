@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card card-custom" xmlns="http://www.w3.org/1999/html">
        <!--begin::Header-->
        <div class="card-header flex-wrap pt-3 pb-3">
            <div class="card-title">
                <h3 class="card-label">Role User List ({{ $items->name }})</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="dropdown dropdown-inline mr-2">            
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
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
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
                <!--end::Dropdown-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">

            <table class="table table-responsive-lg" id="employeeTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Photo</th>
                    <th scope="col">Office ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Department</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    @can('Change Employee Status')
                    <th scope="col">Status</th>
                    @endcan
                    @if(auth()->user()->can("Edit Employee Info") OR auth()->user()->can("Delete Employee"))
                    <th scope="col">Action</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                        
                    @foreach($items->users as $user)
                        <tr>
                            <td scope="row">
                                <div class="symbol flex-shrink-0" style="width: 35px; height: auto">
                                <img src='{{ asset("photo/".$user->fingerprint_no.".jpg") }}' onerror="this.onerror=null; this.src='{{ asset('assets/media/svg/avatars/001-boy.svg')}}';" />
                                </div>
                            </td>
                            <td>{{ $user->fingerprint_no }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->currentPromotion->department->name ?? "---" }}</td>
                            <td>{{ $user->currentPromotion->designation->title ?? "---" }}</td>
                            <td>{{ $user->email ?? "---" }}</td>
                            <td>{{ $user->phone }}</td>
                            @can('Change Employee Status')
                                <td>
                                    <span class="switch switch-outline switch-icon switch-primary">
                                        <label>
                                            <input type="checkbox" {{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'checked' : '' }}
                                            name="status" id="{{ $user->ID }}" onclick="changeStatus({{ $user->id }})"/>
                                            <span></span>
                                        </label>
                                    </span>
                                </td>
                            @endcan
                            @if(auth()->user()->can("Edit Employee Info") OR auth()->user()->can("Delete Employee"))
                                <td>
                                    @can('Edit Employee Info')
                                        <a href="{{ route('employee.edit', ['employee' => $user->id]) }}"><i class="fa fa-edit" style="color: green"></i></a>
                                    @endcan
                                    @can("Delete Employee")
                                        <a href="#" onclick="deleteAlert('{{ route('employee.delete', ['employee' => $user->id]) }}')"><i class="fa fa-trash" style="color: red"></i></a>
                                    @endcan
                                </td>
                            @endif
                        </tr>
                    @endforeach
                        
                </tbody>
            </table>
        </div>
        <!--end::Body-->
        @if($items->users->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!--end::Card-->
@endsection

@section('footer-js')
    <script type="text/javascript" src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/widget.js') }}"></script>
    <script type="text/javascript">

        $(document).ready(function () {
            $('#employeeTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
                "searching": false,
                // "bFilter": false,
            });

        });

    </script>
@endsection
