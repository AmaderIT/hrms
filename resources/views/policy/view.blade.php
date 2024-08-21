@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="mt-n0">
                <div class="card card-custom card-stretch gutter-b">
                    <div class="card-header flex-wrap">
                        <div class="card-title">
                            <h3 class="card-label">View Policy</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group row">
                                    <table class="table table-responsive-lg table-bordered table-hover">
                                        <tr>
                                            <td width="30%"><strong>Title</strong></td>
                                            <td>{{ !empty($policy->title)?$policy->title:"" }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Description</strong></td>
                                            <td>{{ !empty($policy->description)?$policy->description:"" }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Order No</strong></td>
                                            <td>{{ !empty($policy->order_no)?$policy->order_no:"" }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Attachment</strong></td>
                                            <td>
                                                @if($policy->attachment)
                                                    <a href="{{ !empty($policy->attachment) ? asset('storage/'.$policy->attachment) : '#' }}"
                                                       class="file-link bold btn btn-primary btn-sm" data-toggle="modal"
                                                       data-target="#file-modal">Show Attachment</a>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="file-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalSizeXl"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Policy Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="" alt="" class="file-preview">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer-js')
    @stack('custom-scripts')
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

