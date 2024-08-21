@extends('layouts.app')

@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Employee Late Status</h3>
            </div>
        </div>
        <div class="card-body">
            <table class="table" id="bankTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Month</th>
                    <th scope="col">Total Days</th>
                    <th scope="col">Total Deduction</th>
                    <th scope="col">Deduction Type</th>
                    <th scope="col">Lates</th>
                </tr>
                </thead>
                <tbody>

                @foreach($items as $item)
                    <tr>
                        <td>{{ date("F", strtotime($item->month)) }}</td>
                        <td>{{ $item->total_late }} day(s)</td>
                        <td>{{ $item->total_deduction }}</td>
                        <td>{{ ucfirst($item->type) }}</td>
                        <td>
                            <a href="#" class="btn btn-light-primary font-weight-bold mr-2">Details</a>
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
            $('#bankTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
            });
        } );
    </script>
@endsection
