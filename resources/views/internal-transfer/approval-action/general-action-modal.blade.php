<div class="modal-header">
    <h5 class="modal-title">Challan No: <span class="font-weight-600">{{ str_pad($challan->challan, 7, '0', STR_PAD_LEFT) }}</span></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@php
    $department_id = \App\Models\Promotion::where('user_id','=',Illuminate\Support\Facades\Auth::id())->orderBy('id','desc')->first()->department_id;
@endphp
<div class="modal-body">
    <div class="col-12">
        <div class="row">
            <div class="col-md-6">
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
                <div class="col-md-12">
                    <div class="form-group mb-1">
                        <label for="challan" style="font-weight: 600 !important;">Remarks:</label>
                        {{$challan->note}}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12" >
                    <div class="form-group mb-1">
                        <label for="issue_at" style="font-weight: 600 !important;">Prepared By:</label>
                        <span style="">@if(!empty($challan->preparedBy)) {{$challan->preparedBy->name}} @endif</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group mb-1">
                        <label for="challan" style="font-weight: 600 !important;">Authorized By:</label>
                        <span style="">@if(!empty($challan->authorizedBy)) {{$challan->authorizedBy->name}} @endif</span>
                    </div>
                </div>
                <div class="col-md-12" id="source_warehouse">
                    <div class="form-group mb-1">
                        <label for="source_warehouse_id" style="font-weight: 600 !important;">Security Checked Out:</label>
                        <span style="">@if(!empty($challan->securityCheckedOutBy)) {{$challan->securityCheckedOutBy->name}} @endif</span>
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
                        <span style="">@if(!empty($challan->securityCheckedInBy)) {{$challan->securityCheckedInBy->name}} @endif</span>
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
                        <span style="">@if(!empty($challan->receivedBy)) {{$challan->receivedBy->name}} @endif</span>
                    </div>
                </div>
                <div class="col-md-12" id="delivered_by">
                    <div class="form-group mb-1">
                        <label for="delivered_by" style="font-weight: 600 !important;">Delivered By:</label>
                        <span style="">@if(!empty($challan->deliveredBy)) {{$challan->deliveredBy->name}} @endif</span>
                    </div>
                </div>
                @if($challan->status == 6)
                    <div class="col-md-12" id="destination_warehouse">
                        <div class="form-group mb-1">
                            <label for="destination_warehouse_id" style="font-weight: 600 !important;">Rejected By:</label>
                            <span style="">@if(!empty($challan->rejectedBy)) {{$challan->rejectedBy->name}} @endif</span>
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
    <hr>
    @if(($challan->status != 5) && (auth()->user()->can('Can Internal Transfer Approve')))
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($challan->status == 6)
                        <label class="pb-3">Rejected by: {{ $challan->rejectedBy ? $challan->rejectedBy->name : "Not found" }}</label> <br>
                    @else
                        @if(($challan->status == 1) && auth()->user()->can('Authorize Internal Transfer') && ($department_id==$challan->source_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($challan->source_department_id,$departmentIds)) ))
                            <label><input type="radio" class="action" name="action" value="approve"/> Authorized</label>
                        @elseif($challan->status == 2 && auth()->user()->can('Security CheckOut Internal Transfer'))
                            <label><input type="radio" class="action" name="action" value="approve"/> Checked Out</label>
                        @elseif($challan->status == 3 && auth()->user()->can('Security CheckIn Internal Transfer') && !$challan->dept_to_ware)
                            <label><input type="radio" class="action" name="action" value="approve"/> Checked In</label>
                        @elseif($challan->status == 4 && auth()->user()->can('Receive Internal Transfer') && !$challan->dept_to_ware && ($department_id==$challan->destination_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($challan->destination_department_id,$departmentIds)) ))
                            <label><input type="radio" class="action" name="action" value="approve"/> Received</label>
                        @endif
                        @if($challan->status < 2 && auth()->user()->can('Reject Internal Transfer') && !$challan->dept_to_ware && ($department_id==$challan->source_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($challan->source_department_id,$departmentIds)) ))
                            <label style="margin-left: 15px">
                                <input type="radio" name="action" class="action" value="reject"/>
                                Reject
                            </label>
                        @elseif($challan->status < 3 && auth()->user()->can('Reject Internal Transfer') && !$challan->dept_to_ware && ($department_id==$challan->source_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($challan->source_department_id,$departmentIds)) ))
                            <label style="margin-left: 15px">
                                <input type="radio" name="action" class="action" value="reject"/>
                                Reject
                            </label>
                        @endif
                        <form class="approve" action="{{route('internal-transfer.approvalAction')}}" method="POST" style="display: none">
                            @csrf
                            <input type="hidden" name="id" value="{{$challan->id}}">
                            <input type="hidden" name="current_challan_status" value="{{$challan->status}}">
                            @if(($challan->status == 1) && auth()->user()->can('Authorize Internal Transfer'))
                                <input type="hidden" value="2" name="status">
                            @elseif($challan->status == 2 && auth()->user()->can('Security CheckOut Internal Transfer'))
                                <input type="hidden" value="3" name="status">
                            @elseif($challan->status == 3 && auth()->user()->can('Security CheckIn Internal Transfer'))
                                <input type="hidden" value="4" name="status">
                            @elseif($challan->status == 4 && auth()->user()->can('Receive Internal Transfer'))
                                <input type="hidden" value="5" name="status">
                                <div class="col-sm-6 mt-3">
                                    <div class="form-group">
                                        <label for="challan">Delivered by <span class="text-danger">*</span></label>
                                        <select class="form-control" id="delivered_by" name="delivered_by" required>
                                            <option value="" selected>Select an option</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ $employee->id == $challan->delivered_by ? 'selected' : '' }}>
                                                    {{ $employee->fingerprint_no.'-'.$employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('delivered_by')
                                    <p class="text-danger"> {{ $errors->first("delivered_by") }} </p>
                                    @enderror
                                </div>
                            @endif
                            <button type="submit" id="ApproveBtn" class="btn btn-sm btn-info mt-2">Submit</button>
                        </form>
                        <form class="comment" action="{{route('internal-transfer.approvalAction')}}" method="POST" style="display: {{ $challan->status == 6 ? 'block' : 'none' }}">
                            @csrf
                            <input type="hidden" name="id" value="{{$challan->id}}">
                            <input type="hidden" name="status" value="6">
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <textarea name="comment" class="form-control" rows="3" required>{{$challan->comment}}</textarea>
                                </div>
                                @error('comment')
                                <p class="text-danger"> {{ $errors->first("comment") }} </p>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-sm btn-info ml-2">Submit</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<script type="text/javascript">

    $(document).ready(function () {
        $(document).on('click', '.action', function(e) {
            if($(this).val() == "reject"){
                $( ".approve" ).hide();
                $( ".comment" ).show();
            }else {
                $( ".comment" ).hide();
                $( ".approve" ).show();
            }
        });
    });
</script>
