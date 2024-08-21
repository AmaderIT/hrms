@extends('layouts.app')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Division Listing</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                @can('Create Office Division')
                    <a href="{{ route('office-division.create') }}" class="btn btn-primary font-weight-bolder">
                        <span class="svg-icon svg-icon-default svg-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"/>
                                    <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                    <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                                </g>
                            </svg>
                        </span>
                        Add Division
                    </a>
                @endcan
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body">
            <div class="d-flex">
                <div class="ml-auto">
                    <form action="{{ route('office-division.index') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="search" role="search">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table" id="divisionTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>

                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>
                            @can("Edit Office Division")
                            <a href="{{ route('office-division.edit', ['officeDivision' => $item->id]) }}"><i class="fa fa-edit" style="color: green"></i></a>
                            @endcan
                            @can("Delete Office Division")
                                ||
                            <a href="#" onclick="deleteAlert('{{ route('office-division.delete', ['officeDivision' => $item->id]) }}')" ><i class="fa fa-trash" style="color: red"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
        <!--end::Body-->
        <!--begin::Footer-->
        @if($items->hasPages())
            <div class="card-footer">
                <div class="d-flex">
                    <div class="ml-auto">
                        {{ $items->links() }}
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
            $('#divisionTable').DataTable({
                "order": [],
                "ordering": true,
                "paging": false,
                "bInfo": false,
                "bPaginate": false,
                "searching": false,
            });
        } );
    </script>
@endsection
