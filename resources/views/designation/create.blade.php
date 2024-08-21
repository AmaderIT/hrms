@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <h3 class="card-title">Add Designation</h3>
                        <div class="card-toolbar">
                            <div class="example-tools justify-content-center">
                                <a href="{{ route('designation.index') }}" class="btn btn-primary mr-2">Back</a>
                            </div>
                        </div>
                    </div>
                <form action="{{ route('designation.store') }}" method="POST">
                    @csrf
                    @include('designation.common-view.common_create')
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
