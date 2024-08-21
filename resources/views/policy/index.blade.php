@extends('layouts.app')

@section('top-css')
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
    <style>
        .view_icon {
            color: #fff;
            background: green;
            padding: 6px 6px;
            border-radius: 4px;
            font-size: 12px;
            vertical-align: middle;
            margin-bottom: 3px;
            margin-right: 3px;
        }
    </style>
@endsection

@section('content')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Policy List</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                @can('Create New Policy')
                    <a href="{{ route('policies.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                            </g>
                        </svg>
                    </span>Add Policy</a>
                @endcan
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <div class="card-body">
            <table class="table" id="applicationTable">
                <thead class="custom-thead">
                <tr>
                    <th scope="col" style="display: none">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Attachment</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
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
        var f = 0;
        var dataTable;

        $(document).ready(function () {
            dataTable = $('#applicationTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    ur: '{{ route('policies.index') }}',
                    data: function (d) {

                    }
                },
                order: [1, 'desc'],
                autoWidth: false,
                language: {
                    paginate: {
                        next: '&#8250;',
                        previous: '&#8249;'
                    }
                },
                stateSave: true,
                "stateDuration": 7200,
                columns: [
                    {data: 'id', name: 'id', visible: false},
                    {data: 'title', name: 'Title', orderable: true, searchable: true},
                    {data: 'attachment', name: 'Attachment', orderable: false, searchable: false},
                    {"data": "action", orderable: false, searchable: false}
                ]
            });

            $(document).on('click', '.delete_link', function (e) {
                e.preventDefault();
                var url = $(this).data('href');
                let clickedElement = event.currentTarget;
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    buttonsStyling: false,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger"
                    },
                    cancelButtonText: "<i class='las la-times'></i> No, thanks.",
                    confirmButtonText: "<i class='las la-thumbs-up'></i> Yeah, sure!",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.post(url, {}, function (data, status) {
                            let alertHeader, alertStatus, alertMessage;
                            if (data.status == true) {
                                alertHeader = 'Success';
                                alertStatus = 'success';
                                alertMessage = data.message || 'Deleted Successfully';
                            } else {
                                alertHeader = 'Cancelled';
                                alertStatus = 'error';
                                alertMessage = data.message || 'Something Went Wrong';
                            }
                            successAlert(alertHeader, alertMessage, alertStatus);
                        })
                    }
                })
            });

        });

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
