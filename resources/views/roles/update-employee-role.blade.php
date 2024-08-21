@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Update Employee Roles</h3>
                </div>
                <form action="{{ route('roles.updateEmployeeRole') }}" method="POST" onsubmit="submitCheck(event)">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">

                            {{-- Employee --}}
                            <div class="form-group">
                                <label for="user_id">Employee <span class="text-danger">*</span></label>
                                <select class="form-control" id="user_id" name="user_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($employees as $employee)
                                        <option
                                            value="{{ $employee->id }}" {{ $employee->id == old("user_id") ? 'selected' : '' }}>
                                            {{ $employee->fingerprint_no . " - " . $employee->name }}
                                            (
                                            @foreach( $employee->roles as $k => $role)
                                                {{ $role->name }}
                                                @if ( ($k+1) < count($employee->roles))
                                                    ,
                                                @endif
                                            @endforeach
                                            )
                                        </option>
                                    @endforeach
                                </select>

                                @error("user_id")
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            {{-- Roles --}}
                            <div class="form-group">
                                <label for="user_id">Change Role To <span class="text-danger">*</span></label>
                                <select class="form-control" id="role_id" name="role_id[]" required multiple>
                                    <option value="" disabled>Select an option</option>
                                    @foreach($roles as $role)
                                        <option
                                            value="{{ $role->id }}" {{ $role->id == old("role_id") ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error("role_id")
                                <p class="text-danger"> {{ $errors->first("role_id") }} </p>
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
            </div>
        </div>
    </div>
@endsection
@section('footer-js')
    <script>
        $(document).ready(function () {
            $('#user_id').select2();
            $('#role_id').select2();
        });
    </script>
@endsection
