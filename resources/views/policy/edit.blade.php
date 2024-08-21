@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Policy</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('policies.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('policies.update', $policy->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="input-group">
                            <div class="col-md-10 offset-1 mb-5">
                                <label for="title">Title</label>
                                <input type="text" id="title" name="title"
                                       value="{{ !empty($policy->title)? $policy->title: old('title') }}"
                                       class="form-control" placeholder="Enter policy title here" required>
                                @error("title")
                                <p class="text-danger"> {{ $errors->first("title") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-4 offset-1">
                                <label for="title">Attachment</label>
                                <input type="file" id="attachment" name="attachment" accept=".png, .jpg, .jpeg, .pdf" value="{{ old('attachment') }}"
                                       class="form-control">
                                @if($policy->attachment)
                                    <a href="{{ !empty($policy->attachment) ? asset('storage/'.$policy->attachment) : '#' }}" data-title="{{ $policy->title }}" class="file-link bold" style="float: right; margin-right: 5px;" data-toggle="modal" data-target="#file-modal">Show</a>
                                @endif
                                @error("attachment")
                                <p class="text-danger"> {{ $errors->first("attachment") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-5">
                                <label for="title">Order No.</label>
                                <input type="number" id="order_no" name="order_no" value="{{ !empty($policy->order_no)? $policy->order_no: old('order_no') }}" min="1"
                                       class="form-control" required autocomplete="off">
                                @error("order_no")
                                <p class="text-danger"> {{ $errors->first("order_no") }} </p>
                                @enderror
                            </div>
                            <div class="col-md-10 offset-1">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control"
                                          rows="5">{{ !empty($policy->description)? $policy->description: old('description') }}</textarea>
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
    <div class="modal fade" id="file-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Policy Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="{{ asset($policy->attachment) }}" alt="{{ $policy->title }}" class="file-preview">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script>
        $(document).on('click', '.file-link', function (e) {
            e.preventDefault();

            var fileUrl = $(this).attr('href');
            var fileName = $(this).data('title');
            var modal = $('#file-modal');
            var modalTitle = modal.find('.modal-title');
            var modalBody = modal.find('.modal-body');
            var fileType = fileUrl.split('.').pop();
            var filePreview;

            modalTitle.text(fileName);

            if (fileType === 'pdf') {
                filePreview = $('<iframe src="' + fileUrl + '" class="file-preview" style="width:100%;height:500px;"></iframe>');
            } else {
                filePreview = $('<img src="' + fileUrl + '" alt="' + fileName + '" class="file-preview">');
            }

            modalBody.empty().append(filePreview);
            modal.modal('show');
        });
    </script>
@endsection
