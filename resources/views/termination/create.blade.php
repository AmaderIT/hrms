@extends('layouts.app')

@section("top-css")
    <style>
        input[type="date"]::-webkit-datetime-edit, input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-clear-button {
            color: #fff;
            position: relative;
        }

        input[type="date"]::-webkit-datetime-edit-year-field {
            position: absolute !important;
            border-left:1px solid #8c8c8c;
            padding: 2px;
            color:#000;
            left: 56px;
        }

        input[type="date"]::-webkit-datetime-edit-month-field {
            position: absolute !important;
            border-left:1px solid #8c8c8c;
            padding: 2px;
            color:#000;
            left: 26px;
        }

        input[type="date"]::-webkit-datetime-edit-day-field {
            position: absolute !important;
            color:#000;
            padding: 2px;
            left: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add (Employment Close)</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('termination.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('termination.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee Name</label>
                                <select class="form-control" name="user_id" id="selectUser">
                                    <option value="" disabled selected>Select an option</option>
                                </select>
                                @error("user_id")
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Reason --}}
                            <div class="form-group">
                                <label for="action_reason_id">Reason</label>
                               <select class="form-control" id="action_reason_id" name="action_reason_id">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($actionReasons as $actionReason)
                                        <option value="{{ $actionReason->id }}" {{ $actionReason->id == old("action_reason_id") ? "selected" : "" }}>
                                            {{ $actionReason->reason }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-warning">*Note: If reason is not available then input new reason and hit enter.Then new reason will be automatically added and can reused.</span>

                                @error("action_reason_id")
                                <p class="text-danger"> {{ $errors->first("action_reason_id") }} </p>
                                @enderror
                            </div>

                            {{-- Action Taken By --}}
                        <!--
                            <div class="form-group">
                                <label for="action_taken_by">Action Taken By</label>
                                <select class="form-control" name="action_taken_by" id="selectActionTakenBy">
                                    <option value="" disabled selected>Select an option</option>
                                </select>

                                @error("action_taken_by")
                                <p class="text-danger"> {{ $errors->first("action_taken_by") }} </p>
                                @enderror
                            </div>

                            -->

                            <input type="hidden" name="action_taken_by" value="{{auth()->user()->id}}">

                            {{-- Promoted Date --}}
                            <div class="form-group">
                                <label for="action_date">Closing Date</label>
                                <input class="form-control" type="date" name="action_date" value="{{ old('action_date') }}" id="action_date">
                                @error("action_date")
                                <p class="text-danger"> {{ $errors->first("action_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="6">{{ old('remarks') }}</textarea>
                                @error("remarks")
                                <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/select2.min.js') }}"></script>
    <script type="text/javascript">
        // CSRF Token
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $(document).ready(function() {
            $( "#selectUser" ).select2({
                theme: "classic",
                ajax: {
                    url: "{{ route('users.getUsers') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });

            $( "#selectActionTakenBy" ).select2({
                theme: "classic",
                ajax: {
                    url: "{{route('termination.getActionTakenByUsers')}}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true,
                }
            });

        });

        $('#action_reason_id').select2({
            tags: true
        })
    </script>
@endsection
