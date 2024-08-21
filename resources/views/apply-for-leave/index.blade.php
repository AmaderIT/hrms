@extends('layouts.app')

@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Application Requesting Listing</h3>
            </div>
            <div class="card-toolbar">
                <div class="dropdown dropdown-inline mr-2">
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2"></ul>
                    </div>
                </div>
                @can('Create Leave Application')
                <a href="{{ route('apply-for-leave.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\keen\theme\demo7\dist/../src/media/svg/icons\Code\Plus.svg-->
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                            </g>
                        </svg>
                    </span>
                    Apply for Leave
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table" id="divisionTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Employee Name</th>
                    <th scope="col">Leave Type</th>
                    <th scope="col">Request Duration</th>
                    <th scope="col">No. of day</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->employee->name }}</td>
                        <td>{{ $item->leaveType->name }}</td>
                        <td>{{ $item->from_date->format('M d, Y') }} to {{ $item->to_date->format('M d, Y') }}</td>
                        <td>{{ $item->number_of_days }}</td>
                        <td>
                            @if($item->status === 1)
                            <a href="#" class="btn btn-success btn-sm font-weight-bold btn-pill">Approved</a>
                            @elseif($item->status === 0)
                            <a href="#" class="btn btn-warning btn-sm font-weight-bold btn-pill">Applied</a>
                            @elseif($item->status === 2)
                                <a href="#" class="btn btn-danger btn-sm font-weight-bold btn-pill">Canceled</a>
                            @elseif($item->status === 3)
                                <a href="#" class="btn btn-danger btn-sm font-weight-bold btn-pill">Authorized</a>
                            @endif
                        </td>
                        <td>
                            @can('Edit Leave Application')
                                @if(($item->status == 0 || $item->status == 2) && Auth::id() == $item->user_id)
                                    <a href="{{ route('apply-for-leave.edit', ['applyForLeave' => $item->uuid]) }}"><i class="fa fa-edit" style="color: green"></i></a>
                                @endif
                            @endcan

                            @can('Edit Leave Application' && 'Delete Leave Application')
                                @if(($item->status == 0 || $item->status == 2) && Auth::id() == $item->user_id)
                                ||
                                @endif
                            @endcan

                            @can('Delete Leave Application')
                                @if(($item->status == 0 || $item->status == 2) && Auth::id() == $item->user_id)
                                    <a href="#" onclick="deleteAlert('{{route('apply-for-leave.delete', ['applyForLeave' => $item->uuid])}}')" ><i class="fa fa-trash" style="color: red"></i></a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('footer-js')
    <script>
        $(document).ready( function () {
            $('#divisionTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        } );
    </script>
@endsection
