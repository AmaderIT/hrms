<style>
    .dashboard-card {
        height: 400px;
    }

    .card-body {
        overflow-y: auto;
        max-height: 350px;
    }
</style>

<div class="row">
    @if(!empty($policies) && $policies->count()>0)
        @foreach($policies->get() as $policy)
            <div class="col-xl-4 dashboard-card" style="margin-left:-12px;">
                <div class="card card-custom card-stretch gutter-b" style="padding-bottom: 20px;">
                    <div class="card-header border-1pt-4" style="min-height: auto">
                        <div class="card-title">
                            <h3><span class="card-label text-dark-75"
                                      style="font-size: 15px">{{$policy['title']}}</span></h3>
                        </div>
                        <div class="card-title" style="margin-top: 0">
                            @if($policy->attachment)
                                <a href="{{ !empty($policy->attachment) ? asset('storage/'.$policy->attachment) : '#' }}"
                                   class="file-link bold btn btn-primary btn-sm" data-toggle="modal"
                                   data-target="#file-modal">Show Attachment</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body d-flex">
                        @if($policy->description)
                            <p style="text-align: justify">{{ $policy->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
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
                filePreview = $('<iframe id="iframe" src="' + fileUrl + '" class="file-preview" style="width:100%;height:500px;"></iframe>');
            } else {
                filePreview = $('<img src="' + fileUrl + '" alt="' + fileName + '" class="file-preview">');
            }

            modalBody.empty().append(filePreview);
            modal.modal('show');
        });
    </script>
@endsection
