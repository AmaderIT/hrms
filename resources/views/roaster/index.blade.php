@extends('layouts.app')
@section('top-css')
    <link href="{{ asset('assets/css/toastr.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/custom-datatable.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/fontawesome.min.css') }}" rel="stylesheet"/>

    <style>
        .tri-state-toggle {
            background: rgba(165,170,174,0.25);
            box-shadow: inset 0 2px 8px 0 rgba(165,170,174,0.25);
            border-radius: 24px;
            display: inline-block;
            overflow: hidden;
            display: inline-flex;
            flex-direction: row;
        transition: all 500ms ease; 
        }

        .tri-state-toggle-button {
            border-radius: 22px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            background-color: transparent;
            border: 0px solid transparent;
            margin: 2px;
            color: #727C8F;
            cursor: pointer;
        
        /*    -webkit-transition: all 0.5s ease-in-out;
        -moz-transition:    all 0.5s ease-in-out;
        -ms-transition:     all 0.5s ease-in-out;
        -o-transition:      all 0.5s ease-in-out; */
        transition:         all 0.5s ease;
        }

        .tri-state-toggle-button.active {
            background-image: linear-gradient(-180deg, #fff 0%, #FAFAFA 81%, #F2F2F2 100%);
            border: 1px solid rgba(207,207,207,0.6);
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.1);
            color: #6744B1;
            font-weight: 500;
            transition: all .5s ease-in;
        }

        .tri-state-toggle-button:focus {
        outline: none;
        }
    </style>
@endsection
@section('content')
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Roaster Listing</h3>
            </div>
            <div class="card-toolbar">
                @can('Create New Roasters')
                <a href="{{ route('roaster.create') }}" class="btn btn-primary font-weight-bolder">
                    <span class="svg-icon svg-icon-default svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                                <path d="M11,11 L11,7 C11,6.44771525 11.4477153,6 12,6 C12.5522847,6 13,6.44771525 13,7 L13,11 L17,11 C17.5522847,11 18,11.4477153 18,12 C18,12.5522847 17.5522847,13 17,13 L13,13 L13,17 C13,17.5522847 12.5522847,18 12,18 C11.4477153,18 11,17.5522847 11,17 L11,13 L7,13 C6.44771525,13 6,12.5522847 6,12 C6,11.4477153 6.44771525,11 7,11 L11,11 Z" fill="#000000"/>
                            </g>
                        </svg>
                    </span>Add Roaster
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="d-flex">
                <div class="col-md-9 pl-0">
            
                        <div class="row">
                            @include('filter.division-department-filter')
                            <div class="form-group" style="padding-top:25px;">
                                <button class="btn btn-outline-secondary search_button" type="button">Filter</button>    
                                <button  class="btn btn-primary reset-btn" type="button">Reset</button>                                
                            </div>
                        </div>
                
                </div>
            </div>
            <table class="table" id="dataTable">
                <thead class="custom-thead">
                <tr>
                    <th width="9%" scope="col">Employee</th>
                    <th width="10%" scope="col">WorkSlot</th>
                    <th width="6%" scope="col">Start Time</th>
                    <th width="5%" scope="col">End Time</th>
                    <th width="6%" scope="col">Late Count</th>
                    <th width="5%" scope="col">Overtime</th>
                    <th width="8%" scope="col">Office Division</th>
                    <th width="6%" scope="col">Department</th>
                    <th width="6%" scope="col">Active from</th>
                    <th width="6%" scope="col">End Date</th>
                    <th width="6%" scope="col">Weekly Holiday</th>
                    <th width="12%" scope="col">Approval Status</th>
                    @can('Roaster Unlock Button')
                    <th width="12%" scope="col">Lock Status</th>
                    @endcan
                    
                    <th width="6%" scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                 
                </tbody>
            </table>
        </div>       
    </div>

    <div class="modal fade roaster_approval_status_modal" tabindex="-1" role="dialog" aria-labelledby="deptModalTitle"
             aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form name="roaster-approval-status-update" id="roaster-approval-status-update" action="#" method="POST">
                    <div class="modal-body">
                        <!-- BODY -->
                    </div>
                    <div class="modal-footer text-center">
                        <button type="button" class="btn btn-secondary roaster-approval-status-calcel-btn" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="roaster-approval-status-update-btn">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
@stack('custom-scripts')
    <script type="text/javascript" src="{{ asset('assets/js/toastr.min.js') }}"></script>

    <script>
       
        var dataTable;
        var loadCount = 0;
        var f = 0;

        $(document).ready( function () {

            dataTable = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                retrieve: true,
                bLengthChange: true,
                responsive: true,
                ajax: {
                    ur: '{{route('roaster.index')}}',
                    data: function (d) {
                            if(f>0) {
                            d.division_id = $('#office_division_id').val()
                            d.department_id = $('#department_id').val()
                            }       
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
                stateSaveParams: function (settings, data) {

                
                    data.office_division_id = $('#office_division_id').val()
                    data.department_id = $('#department_id').val()
                    data.f = f
                    
                
                  
                },
                stateLoadParams: function (settings, data) {


                    f = data.f

                    
                    
                    if (data.office_division_id > 0) {

                        $('#office_division_id').val(data.office_division_id).change()

                        $(document).ajaxComplete(function(event,xhr,settings){
                           
                            var givenUrl = settings.url.substr(0,settings.url.indexOf('?'));

                            var targetUrl = '{{route("filter.get-department")}}'

                            var targetUrlDataTbl = '{{route("roaster.index")}}'

                            
                            if(givenUrl == targetUrl){
                                if (data.department_id > 0) {
                                $('#department_id').val(data.department_id).change()
                                }       
                            }
                            else if(givenUrl == targetUrlDataTbl && loadCount == 0){
                                dataTable.page(dataTable.page.info().page).draw('page')
                               loadCount = 1;
                               
                           }
                            else if(givenUrl == targetUrlDataTbl && loadCount == 1){
                               
                                dataTable.page(dataTable.page.info().page).draw('page')
                                loadCount = 2
                            }
                        
                        });
                    }
                
             },
                columns: [
                    {data: 'user.name', name: 'user.name', orderable: false, searchable: false},
                    {data: 'work_slot.title', name: 'work_slot.title', orderable: false, searchable: false},
                    {data: 'work_slot.start_time', name: 'work_slot.start_time', orderable: false, searchable: false},
                    {data: 'work_slot.end_time', name: 'work_slot.end_time', orderable: false, searchable: false},
                    {data: 'work_slot.late_count_time', name: 'work_slot.late_count_time', orderable: false, searchable: false},
                    {data: 'work_slot.over_time', name: 'work_slot.over_time', orderable: false, searchable: false},
                    {data: 'office_division.name', name: 'office_division.name', orderable: false, searchable: false},
                    {data: 'department.name', name: 'department.name', orderable: false, searchable: false},
                    {data: 'active_from', name: 'active_from', orderable: false, searchable: false},
                    {data: 'end_date', name: 'end_date', orderable: false, searchable: false},
                    {data: "weekly_holidays", orderable: false, searchable: false},
                    {data: "approval_status", orderable: false, searchable: false},
                    @if (auth()->user()->can("Roaster Unlock Button"))
                    {"data": "roaster_unlock_btn", orderable: false, searchable: false},
                    @endif
                    {"data": "action", orderable: false, searchable: false}
                ]
            });


            $(document).on('click', '.search_button', function (e) {
                f=1;
                dataTable.draw();

        
            });
           
            $('.reset-btn').on('click', function(){
                f = 0;
                $('#office_division_id').val('').change()
                dataTable.draw();
               
            })


             //FOR DELETE
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

            
            $(document).on('change', '.roaster_unlock_status', function () {
                
                var roaster_unlock_status   = ($(this).val()==1? 0:1);
                var roaster_id              = $(this).attr('data');

                console.log($(this).val(),roaster_unlock_status)

                $.ajax({
                    url: '{{route("roaster.roasterLockStatusUpdate")}}',
                    type: 'GET',
                    data: {roaster_unlock_status: roaster_unlock_status, roaster_id:roaster_id},
                    success: function (res) {
                        toastr.success(res.message);
                        $('.roaster_unlock_status'+roaster_id).val(res.roStatus);
                        
                        if (res.roStatus==1) {
                            $('.icon_roaster_unlock_status'+roaster_id).removeClass('fas fa-lock-open');
                            $('.icon_roaster_unlock_status'+roaster_id).addClass('fas fa-lock');                         
                        } else {
                            $('.icon_roaster_unlock_status'+roaster_id).removeClass('fas fa-lock');
                            $('.icon_roaster_unlock_status'+roaster_id).addClass('fas fa-lock-open');
                        }                        
                    },
                    error: function (res) {
                        toastr.success('Something went wrong!!')
                    }
                });
            });     
    } );
    </script>

    <script>
        $('#dataTable').on('change', '.approval_status', function(){
            var roasterId           = $(this).attr('data');
            var approvalStatus      = $('.approval_status'+roasterId).attr('approvalStatusVal');

            $.ajax({
                type: "get",
                url: '{{route('roaster.roasterApprovalStatus')}}',
                data:{roasterId:roasterId, approvalStatus:approvalStatus},
                dataType: "html",
                success: function (response) {
                    $('.modal-body').html(response);
                    $('.roaster_approval_status_modal').modal('show');
                }
            });
        });

        //UPDATE ROASTER APPROVAL STATUS
        $('#roaster-approval-status-update-btn').on('click', function (e) {
            e.preventDefault();
            var roaster_id          = $('.roaster_approval_status_modal').find('.roaster_id').val();
            var approval_status     = $('.roaster_approval_status_modal').find('.approval_status').val();
            var id                  = $('.toggle-button'+approval_status+roaster_id).attr('id');
            var remSelector         = 'toggle-button'+roaster_id;

            var url = '{{route('roaster.roasterApprovalStatusUpdate')}}';
            
            $.ajax({
                type: "POST",
                url: url,
                data: $('#roaster-approval-status-update').serialize(),
                dataType: "json",
                success: function (response) {
                    toastr.success(response.message)
                    $('.roaster_approval_status_modal').modal('hide');

                    if (approval_status==0) {
                        $("."+remSelector).removeAttr("style");
                        $("."+remSelector).removeClass("active");
                        $("#" + id).css("background","rgb(255, 204, 0)");
                        $("#" + id).find('.fa-dot-circle').css("color", "white");
                        $("."+remSelector).find('.fa-check').removeAttr("style");  
                    } else if(approval_status==1){
                        $("."+remSelector).removeAttr("style");
                        $("."+remSelector).removeClass("active");
                        $("#" + id).css("background","#4ba774");
                        $("#" + id).find('.fa-check').css("color", "white");
                        $("."+remSelector).find('.fa-dot-circle').removeAttr("style");
                    } else {
                        $("."+remSelector).removeAttr("style");
                        $("."+remSelector).removeClass("active");
                        $("#" + id).css("background","#f64e60");
                        $("#" + id).css("color","white");  
                        $("."+remSelector).find('.fa-check').removeAttr("style");    
                        $("."+remSelector).find('.fa-dot-circle').removeAttr("style");         
                    }
                },

                error: function (response) {
                    toastr.success(response.message)
                }
                
            });
        });


        $('.roaster-approval-status-calcel-btn').on('click', function () {
            
            var roaster_id          = $('.roaster_approval_status_modal').find('.previous_roaster_id').val();
            var approval_status     = $('.roaster_approval_status_modal').find('.previous_approval_status').val();
            var id                  = $('.toggle-button'+approval_status+roaster_id).attr('id');
            var remSelector         = 'toggle-button'+roaster_id;

            if (approval_status==0) {
                $("."+remSelector).removeAttr("style");
                $("."+remSelector).removeClass("active");
                $("#" + id).css("background","rgb(255, 204, 0)");
                $("#" + id).find('.fa-dot-circle').css("color", "white");
                $("."+remSelector).find('.fa-check').removeAttr("style");  
            } else if(approval_status==1){
                $("."+remSelector).removeAttr("style");
                $("."+remSelector).removeClass("active");
                $("#" + id).css("background","#4ba774");
                $("#" + id).find('.fa-check').css("color", "white");
                $("."+remSelector).find('.fa-dot-circle').removeAttr("style");
            } else {
                $("."+remSelector).removeAttr("style");
                $("."+remSelector).removeClass("active");
                $("#" + id).css("background","#f64e60");
                $("#" + id).css("color","white");  
                $("."+remSelector).find('.fa-check').removeAttr("style");    
                $("."+remSelector).find('.fa-dot-circle').removeAttr("style");         
            }
        });


        $(document).ready(function(){
            $("#dataTable").on('click', ".tri-state-toggle-button", function(){                
                var id                  = $(this).attr('id');
                var data                = $(this).attr('data');
                var status              = $(this).attr('status');                

                $.ajax({
                    type: "get",
                    url: '{{route('roaster.roasterApprovalStatus')}}',
                    data:{roasterId:data, approvalStatus:status},
                    dataType: "html",
                    success: function (response) {
                        $('.modal-body').html(response);
                        $('.roaster_approval_status_modal').modal('show');

                        $(".toggle-button"+data).removeClass("active");
                        $("#" + id).addClass("active");

                        var btnName = $('.btn_name').val();
                        $('.roaster-approval-status-update-btn').text(btnName);

                        if (status==0) {
                            $(".toggle-button"+data).removeAttr("style");
                            $("#" + id).css("background","rgb(255, 204, 0)");
                            $("#" + id).find('.fa-dot-circle').css("color", "white");
                        } else if(status==1){
                            $(".toggle-button"+data).removeAttr("style");
                            $("#" + id).css("background","#4ba774");
                            $("#" + id).find('.fa-check').css("color", "white");
                        } else {
                            $(".toggle-button"+data).removeAttr("style");
                            $("#" + id).css("background","#f64e60");
                            $("#" + id).css("color","white");
                            
                        } 
                    }
                });                
                
            });
        });
    </script>
@endsection
