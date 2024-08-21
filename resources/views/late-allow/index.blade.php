@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/late-allow.css') }}" rel="stylesheet"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Employee Late Allow Setting</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">

                        </div>
                    </div>
                </div>

                <form action="{{ route('late-allow.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="col-md-12">
                            <div class="row">
                                @include('filter.division-department-employee-filter')

                                <div class="col-md-2">
                                    {{-- late-allow Date --}}
                                    <div class="form-group">
                                        <label for="promoted_date">No Of Allowed Late </label>
                                        <input class="form-control" type="number" name="allow"
                                               id="allow" required>
                                        @error("allow")
                                        <p class="text-danger"> {{ $errors->first("allow") }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-12 text-lg-right">
                                <button type="submit" class="btn btn-primary mr-2">Save</button>
                            </div>
                        </div>
                    </div>

                </form>

                @include('late-allow.current-late-allow-employee')

            </div>
        </div>
    </div>


    {{--Modal --}}

    <div class="modal fade" id="modalDiv" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Allowed Late History
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">

                    <label>Name:</label>
                    <span id="name-t"></span>
                    <br>
                    <label id="name-t">Designation:</label>
                    <span id="designation-t"></span>


                    <table class="table table-responsive dataTable no-footer" role="grid"
                           aria-describedby="employeeUnpaidLeave_info">
                        <thead class="custom-thead">
                        <tr role="row">
                            <th>#</th>
                            <th>Status</th>
                            <th>Allowed Late</th>
                            <th>Created Date</th>
                            <th>Allowed By</th>
                            <th>Replaced By</th>
                            <th>Replaced Date</th>
                        </tr>
                        </thead>

                        <tbody id="tbl-data-transaction">
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer-js')

    @stack('custom-scripts')

    <script type="text/javascript">
        // CSRF Token
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $('#office_division_id').on('change', function () {
            $('#tbl-data').empty()
        })

        $('#department_id').on('change', function () {
            loadHistory('department_id', this.value)
        })

        $('#user_id').on('change', function () {
            if (this.value > 0 && edit_flag == 0) {
                loadHistory('user_id', this.value)
            } else {
                var dptId = $('#department_id').val()
                if (dptId > 0) {
                    loadHistory('department_id', dptId)
                }
            }
        })


        $(document).ready(function () {
            var dptId = $('#department_id').val()
            if (dptId > 0) {
                loadHistory('department_id', dptId)
            }
        })


    </script>
@endsection
