@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Policy</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('policies.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('policies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="input-group">
                            <div class="col-md-10 offset-1 mb-5">
                                <label for="title">Title</label>
                                <input type="text" id="title" name="title"
                                       value="{{ old('title') }}"
                                       class="form-control" placeholder="Enter policy title here" required>
                                @error("title")
                                <p class="text-danger"> {{ $errors->first("title") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-4 offset-1">
                                <label for="title">Attachment</label>
                                <input type="file" id="attachment" name="attachment" accept=".png, .jpg, .jpeg, .pdf" value="{{ old('attachment') }}"
                                       class="form-control">
                                @error("attachment")
                                <p class="text-danger"> {{ $errors->first("attachment") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-5">
                                <label for="title">Order No.</label>
                                <input type="number" id="order_no" name="order_no" value="{{ old('order_no')?old('order_no'):$orderNumber }}" min="1"
                                       class="form-control" required autocomplete="off">
                                @error("order_no")
                                <p class="text-danger"> {{ $errors->first("order_no") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-10 offset-1">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control"
                                          rows="5">{{ old('description') }}</textarea>
                                @error("description")
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
            </div>
        </div>
    </div>
@endsection

@section('footer-js')

@endsection
