<div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Challan Details</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-12" >
                                <div class="form-group mb-1">
                                    <label for="issue_at" style="font-weight: 600 !important;">Challan No:</label>
                                    <span style="font-weight: 400">{{ str_pad($challan->challan, 7, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12" >
                                <div class="form-group mb-1">
                                    <label for="issue_at" style="font-weight: 600 !important;">Ref. Challan No:</label>
                                    <span style="font-weight: 400">@if(!empty($challan->parents->challan)) {{ str_pad($challan->parents->challan, 7, '0', STR_PAD_LEFT) }} @endif</span>
                                </div>
                            </div>
                            <div class="col-md-12" >
                                <div class="form-group mb-1">
                                    <label for="issue_at" style="font-weight: 600 !important;">Reference:</label>
                                    <span style="font-weight: 400">{{ $challan->reference }}</span>
                                </div>
                            </div>
                            <div class="col-md-12" >
                                <div class="form-group mb-1">
                                    <label for="issue_at" style="font-weight: 600 !important;">Issue Date:</label>
                                    <span style="font-weight: 400">{{ date('d-m-Y h:i A', strtotime($challan->issue_at)) }}</span>
                                </div>
                            </div>
                            @if($challan->source_warehouse_id>0)
                                <div class="col-md-12" id="">
                                    <div class="form-group mb-1">
                                        <label for="" style="font-weight: 600 !important;">Transferred From:</label>
                                        <span style="font-weight: 400">{{ $challan->sourceWarehouse->name }}</span>
                                    </div>
                                </div>
                            @elseif($challan->from_supplier_id>0)
                                <div class="col-md-12" id="">
                                    <div class="form-group mb-1">
                                        <label for="" style="font-weight: 600 !important;">Transferred From:</label>
                                        <span style="font-weight: 400">{{ $challan->sourceSupplier->name }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12">
                                    <div class="form-group mb-1">
                                        <label for="" style="font-weight: 600 !important;">Transferred From:</label>
                                        <span style="font-weight: 400">{{ $challan->sourceDepartment->name }}</span>
                                    </div>
                                </div>
                            @endif
                            @if($challan->destination_warehouse_id>0)
                                <div class="col-md-12" id="">
                                    <div class="form-group mb-1">
                                        <label for="" style="font-weight: 600 !important;">Transferred To:</label>
                                        <span style="font-weight: 400">{{ $challan->destinationWarehouse->name }}</span>
                                    </div>
                                </div>
                            @elseif($challan->destination_department_id>0)
                                <div class="col-md-12" id="">
                                    <div class="form-group mb-1">
                                        <label for="" style="font-weight: 600 !important;">Transferred To:</label>
                                        <span style="font-weight: 400">{{ $challan->destinationDepartment->name }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12" id="">
                                    <div class="form-group mb-1">
                                        <label for="" style="font-weight: 600 !important;">Transferred To:</label>
                                        <span style="font-weight: 400">{{ $challan->destinationSupplier->name }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="challan" style="font-weight: 600 !important;">Returnable:</label>
                                    @if($challan->is_returnable == 1)
                                        <span style="font-weight: 400">Yes</span>
                                    @else
                                        <span style="font-weight: 400">No</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="challan" style="font-weight: 600 !important;">Challan Type:</label>
                                    @if($challan->is_return_challan)
                                        <span style="font-weight: 400">Return</span>
                                    @else
                                        <span style="font-weight: 400">Regular</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="challan" style="font-weight: 600 !important;">Return Status:</label>
                                    {!! getReturnStatus($challan->return_status); !!}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="challan" style="font-weight: 600 !important;">Approve Status:</label>
                                    @if($challan->status == 1)
                                        <span style="font-weight: 400">Prepared</span>
                                    @elseif($challan->status == 2)
                                        <span style="font-weight: 400">Authorized</span>
                                    @elseif($challan->status == 3)
                                        <span style="font-weight: 400">Security Checked Out</span>
                                    @elseif($challan->status == 4)
                                        <span style="font-weight: 400">Security Checked In</span>
                                    @elseif($challan->status == 5)
                                        <span style="font-weight: 400">Received</span>
                                    @else
                                        <span style="font-weight: 400">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            @if($challan->status == 6)
                                <div class="col-md-12">
                                    <div class="form-group mb-1">
                                        <label for="challan" style="font-weight: 600 !important;">Reason:</label>
                                        {{$challan->comment}}
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="challan" style="font-weight: 600 !important;">Remarks:</label>
                                    {{$challan->note}}
                                </div>
                            </div>
                            @if(!empty($challan->file_attachment_path))
                                <div class="col-md-12">
                                    <div class="form-group mb-1">
                                        <label for="challan" style="font-weight: 600 !important;">Attachment: <a target="_blank" href="{{check_storage_image_exists($challan->file_attachment_path)}}">{{$challan->file_name}}</a></label>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-12" >
                                <div class="form-group mb-1">
                                    <label for="issue_at" style="font-weight: 600 !important;">Prepared By:</label>
                                    <span style="">@if(!empty($challan->preparedBy)) {{$challan->preparedBy->fingerprint_no.' - '.$challan->preparedBy->name}} @endif</span>
                                </div>
                            </div>
                            @if(!empty($challan->updatedBy))
                                <div class="col-md-12" >
                                    <div class="form-group mb-1">
                                        <label for="issue_at" style="font-weight: 600 !important;">Edited By:</label>
                                        <span style=""> {{$challan->updatedBy->fingerprint_no.' - '.$challan->updatedBy->name}}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-12">
                                <div class="form-group mb-1">
                                    <label for="challan" style="font-weight: 600 !important;">Authorized By:</label>
                                    <span style="">
                                        @if(!empty($challan->authorizedBy))
                                            {{$challan->authorizedBy->fingerprint_no.' - '.$challan->authorizedBy->name}}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-12" id="source_warehouse">
                                <div class="form-group mb-1">
                                    <label for="source_warehouse_id" style="font-weight: 600 !important;">Security Checked Out:</label>
                                    <span style="">@if(!empty($challan->securityCheckedOutBy)) {{$challan->securityCheckedOutBy->fingerprint_no.' - '.$challan->securityCheckedOutBy->name}} @endif</span>
                                </div>
                            </div>
                            @if(!empty($challan->securityCheckedOutBy))

                                <div class="col-md-12" id="source_warehouse">
                                    <div class="form-group mb-1">
                                        <label for="source_warehouse_id" style="font-weight: 600 !important;">Gate Pass No.:</label>
                                        <span style="">{{$challan->gate_pass_checkout}}</span>
                                    </div>
                                </div>
                                <div class="col-md-12" id="source_warehouse">
                                    <div class="form-group mb-1">
                                        <label for="source_warehouse_id" style="font-weight: 600 !important;">Checkout Time:</label>
                                        <span style="">{{Date('d-m-Y h:i:s a',strtotime($challan->checkout_at))}}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12" id="source_warehouse">
                                <div class="form-group mb-1">
                                    <label for="source_warehouse_id" style="font-weight: 600 !important;">Security Checked In:</label>
                                    <span style="">@if(!empty($challan->securityCheckedInBy)) {{$challan->securityCheckedInBy->fingerprint_no.' - '.$challan->securityCheckedInBy->name}} @endif</span>
                                </div>
                            </div>
                            @if(!empty($challan->securityCheckedInBy))
                                <div class="col-md-12" id="source_warehouse">
                                    <div class="form-group mb-1">
                                        <label for="source_warehouse_id" style="font-weight: 600 !important;">Gate Pass No.:</label>
                                        <span style="">{{$challan->gate_pass_checkin}}</span>
                                    </div>
                                </div>
                                <div class="col-md-12" id="source_warehouse">
                                    <div class="form-group mb-1">
                                        <label for="source_warehouse_id" style="font-weight: 600 !important;">Checkin Time:</label>
                                        <span style="">{{Date('d-m-Y h:i:s a',strtotime($challan->checkin_at))}}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12" id="destination_warehouse">
                                <div class="form-group mb-1">
                                    <label for="destination_warehouse_id" style="font-weight: 600 !important;">Received By:</label>
                                    <span style="">@if(!empty($challan->receivedBy)) {{$challan->receivedBy->fingerprint_no.' - '.$challan->receivedBy->name}} @endif</span>
                                </div>
                            </div>
                            <div class="col-md-12" id="delivered_by">
                                <div class="form-group mb-1">
                                    <label for="delivered_by" style="font-weight: 600 !important;">Delivered By:</label>
                                    <span style="">@if(!empty($challan->deliveredBy)) {{$challan->deliveredBy->fingerprint_no.' - '.$challan->deliveredBy->name}} @endif</span>
                                </div>
                            </div>
                            @if($challan->status == 6)
                                <div class="col-md-12" id="destination_warehouse">
                                    <div class="form-group mb-1">
                                        <label for="destination_warehouse_id" style="font-weight: 600 !important;">Rejected By:</label>
                                        <span style="">@if(!empty($challan->rejectedBy)) {{$challan->rejectedBy->fingerprint_no.' - '.$challan->rejectedBy->name}} @endif</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <table class="table table-bordered">
                            <thead>
                            <tr style="background-color: #d8d6d6">
                                <th>Item Name</th>
                                <th>Variant</th>
                                <th>UOM</th>
                                <th>Quantity</th>
                                <th>Remarks</th>
                            </tr>
                            </thead>
                            <tbody id="item">
                            @foreach($challan_items as $challan_item)
                                <tr>
                                    <td>{{$challan_item->item_name_code}}</td>
                                    <td>{{$challan_item->measure_name ?? '---'}}</td>
                                    <td>{{$challan_item->unit_name ?? '---'}}</td>
                                    <td>{{$challan_item->qty}}</td>
                                    <td>{{$challan_item->remarks}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

