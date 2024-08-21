@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Warning</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('warning.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('warning.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="user">User Name</label>
                                <select class="form-control" id="user" name="user_id">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{$item->id == old('user_id') ? 'selected' : ''}}>{{ $item->name }}</option>
                                    @endforeach
                                </select>

                                @error('user_id')
                                <p class="text-danger"> {{ $errors->first("user_id") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Memo No.</label>
                                <input type="text" value="{{ old('memo_no') }}" class="form-control" id="kt_maxlength_1" minlength="3" maxlength="30" name="memo_no" placeholder="Enter memo no here" required>
                                @error('memo_no')
                                    <p class="text-danger"> {{ $errors->first("memo_no") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="level">Level</label>
                                <select class="form-control" id="division" name="level">
                                    <option value="" disabled selected>Select an option</option>
                                    @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('level') == $i ? 'selected' : ''}}>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('level')
                                    <p class="text-danger"> {{ $errors->first("level") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Subject</label>
                                <input type="text" value="{{ old('subject') }}" class="form-control" id="kt_maxlength_3" minlength="3" maxlength="100" name="subject" placeholder="Enter subject here" required>
                                @error('subject')
                                    <p class="text-danger"> {{ $errors->first("subject") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="warning_date">Warning Date</label>
                                <input class="form-control" type="date" name="warning_date" value="{{ old('warning_date') }}">
                                @error('warning_date')
                                <p class="text-danger"> {{ $errors->first("warning_date") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="warned_by">Warned By</label>
                                <select class="form-control" id="warned_by" name="warned_by">
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ $item->id == old('warned_by') ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>

                                @error('warned_by')
                                <p class="text-danger"> {{ $errors->first("warned_by") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="kt_maxlength_4" minlength="3" maxlength="500" rows="3" name="description">{{ old('description') }}</textarea>
                                @error('description')
                                <p class="text-danger"> {{ $errors->first("description") }} </p>
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
