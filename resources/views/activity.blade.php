@extends('layouts.app')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Activity Log</h3>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <table class="table" id="activityTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Activities</th>
                    <th scope="col">Activity By</th>
                    <th scope="col">Activity Time</th>
                </tr>
                </thead>
                <tbody>

                @foreach($activities as $activity)
                    <tr>
                        <td>{{ $activity->description }}</td>
                        <td>{{ optional($activity->causer)->name }}</td>
                        <td>{{ date_format($activity->created_at, 'jS F Y, g:ia') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!--end::Body-->
        <!--begin::Footer-->
        @if($activities->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        @endif
        <!--end::Footer-->
    </div>
    <!--end::Card-->
@endsection

@section('footer-js')
    <script>
        $(document).ready( function () {
            $('#activityTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        } );
    </script>
@endsection
