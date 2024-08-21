@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Challan</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('internal-transfer.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <form action="{{ route('internal-transfer.update', ['internalTransfer' => $internalTransfer->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="issue_at">Issue Date Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" value="{{ date('Y-m-d\TH:i', strtotime($internalTransfer->issue_at)) }}" class="form-control"
                                       name="issue_at" placeholder="Enter issue date time here" required />
                                @error('issue_at')
                                <p class="text-danger"> {{ $errors->first("issue_at") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan">Reference</label>
                                <input type="text" name="reference" value="{{$internalTransfer->reference}}" class="form-control" placeholder="Enter reference here">
                                @error('reference')
                                <p class="text-danger"> {{ $errors->first("reference") }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan">Delivered by <span class="text-danger">*</span></label>
                                <select class="form-control" id="delivered_by" name="delivered_by">
                                    <option value="" selected>Select an option</option>
                                    @foreach($data["employees"] as $employee)
                                        <option value="{{ $employee->id }}" {{ $employee->id == $internalTransfer->delivered_by ? 'selected' : '' }}>
                                            {{ $employee->fingerprint_no.'-'.$employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('delivered_by')
                                <p class="text-danger"> {{ $errors->first("delivered_by") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan">Transferred From</label>
                                <div class="radio-inline">
                                    <label class="radio radio-default d-none">
                                        <input type="radio" name="transfer_from" @if($internalTransfer->source_warehouse_id) checked="checked" @endif value="warehouse" id="transfer_from_warehouse" class="transfer_from">
                                        <span></span>Warehouse</label>
                                    <label class="radio radio-default">
                                        <input type="radio" name="transfer_from" @if($internalTransfer->source_department_id) checked="checked" @endif value="department" id="transfer_from_department" class="transfer_from">
                                        <span></span>Department</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2 @if(!$internalTransfer->source_warehouse_id)  d-none @endif" id="source_warehouse">
                            <div class="form-group">
                                <label for="source_warehouse_id">Source Warehouse <span class="text-danger">*</span></label>
                                <select class="form-control" id="source_warehouse_id" name="source_warehouse_id">
                                    <option value="" selected>Select an option</option>
                                    @foreach($data["warehouses"] as $warehouse)
                                        @if($warehouse->id!=$internalTransfer->destination_warehouse_id)
                                            <option value="{{ $warehouse->id }}" {{ $warehouse->id == $internalTransfer->source_warehouse_id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error("source_warehouse_id")
                                <p class="text-danger"> {{ $errors->first("source_warehouse_id") }} </p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-2 @if(!$internalTransfer->source_department_id)  d-none @endif" id="source_department">
                            <div class="form-group">
                                <label for="source_department_id">Source Department <span class="text-danger">*</span></label>
                                <select class="form-control" id="source_department_id" name="source_department_id" required>
                                    @if(!empty($data['department_id']) && !auth()->user()->can('Create All Departments Internal Transfer'))
                                        @if(isset($data["departments_divisions"]))
                                            <option value="" selected>Select an option</option>
                                            @foreach($data["departments_divisions"] as $department)
                                                <option value="{{ $department->id }}" {{ $department->id == $internalTransfer->source_department_id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach($data["departments"] as $department)
                                                @if($department->id==$data['department_id'])
                                                    <option value="{{ $department->id }}" selected="selected" >
                                                        {{ $department->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        @endif;
                                    @else
                                        @if(isset($data["departments_divisions"]))
                                            <option value="" selected>Select an option</option>
                                            @foreach($data["departments_divisions"] as $department)
                                                @if(!$department->is_warehouse)
                                                    <option value="{{ $department->id }}" {{ $department->id == $internalTransfer->source_department_id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        @else
                                            <option value="" selected>Select an option</option>
                                            @foreach($data["departments"] as $department)
                                                @if(!$department->is_warehouse)
                                                    <option value="{{ $department->id }}" {{ $department->id == $internalTransfer->source_department_id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        @endif;

                                    @endif
                                </select>
                                @error("source_department_id")
                                <p class="text-danger"> {{ $errors->first("source_department_id") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan">Transferred To</label>
                                <div class="radio-inline">
                                    <label class="radio radio-default d-none">
                                        <input type="radio" name="transfer_to" value="warehouse" class="transfer_to" @if($internalTransfer->destination_warehouse_id) checked="checked" @endif>
                                        <span></span>Warehouse</label>
                                    <label class="radio radio-default">
                                        <input type="radio" name="transfer_to" value="department" class="transfer_to" @if($internalTransfer->destination_department_id) checked="checked" @endif>
                                        <span></span>Department</label>
                                    <label class="radio radio-default">
                                        <input type="radio" name="transfer_to" value="supplier" class="transfer_to" @if($internalTransfer->to_supplier_id) checked="checked" @endif>
                                        <span></span>Supplier</label>
                                </div>
                                @error('transfer_to')
                                <p class="text-danger"> {{ $errors->first("transfer_to") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2 @if(!$internalTransfer->destination_warehouse_id)  d-none @endif" id="destination_warehouse">
                            <div class="form-group">
                                <label for="destination_warehouse_id">Destination Warehouse <span class="text-danger">*</span></label>
                                <select class="form-control" id="destination_warehouse_id" name="destination_warehouse_id">
                                    <option value="" selected>Select an option</option>
                                    @foreach($data["warehouses"] as $warehouse)
                                        @if($warehouse->id!=$internalTransfer->source_warehouse_id)
                                            <option value="{{ $warehouse->id }}" {{ $warehouse->id == $internalTransfer->destination_warehouse_id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error("destination_warehouse_id")
                                <p class="text-danger"> {{ $errors->first("destination_warehouse_id") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2 @if(!$internalTransfer->destination_department_id)  d-none @endif" id="destination_department">
                            <div class="form-group">
                                <label for="destination_department_id">Destination Department <span class="text-danger">*</span></label>
                                <select class="form-control" id="destination_department_id" name="destination_department_id">
                                    <option value="" selected>Select an option</option>
                                    @foreach($data["departments"] as $department)
                                        @if($department->id!=$internalTransfer->source_department_id)
                                            <option value="{{ $department->id }}" {{ $department->id == $internalTransfer->destination_department_id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error("destination_department_id")
                                <p class="text-danger"> {{ $errors->first("destination_department_id") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2 @if(!$internalTransfer->to_supplier_id)  d-none @endif" id="destination_supplier">
                            <div class="form-group">
                                <label for="destination_supplier_id">Destination Supplier
                                    (<i style="font-size: 12px;text-decoration: underline;color: lightskyblue;cursor: pointer" class="type_supplier">Click here for type</i>)
                                    <span class="text-danger">*</span></label>
                                <div class="supplier_div" data-value="select">
                                    <select class="form-control" id="destination_supplier_id" name="to_supplier_id">
                                        <option value="" selected>Select an option</option>
                                        @foreach($data["suppliers"] as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $supplier->id == $internalTransfer->to_supplier_id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error("to_supplier_id")
                                <p class="text-danger"> {{ $errors->first("to_supplier_id") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan">Returnable <span class="text-danger">*</span></label>
                                <div class="radio-inline">
                                    <label class="radio radio-primary">
                                        <input type="radio" name="is_returnable" value="1" {{ $internalTransfer->is_returnable == 1 ? 'checked' : '' }}>
                                        <span></span>Yes</label>
                                    <label class="radio radio-primary">
                                        <input type="radio" name="is_returnable" {{ $internalTransfer->is_returnable == 0 ? 'checked' : '' }} value="0">
                                        <span></span>No</label>
                                </div>
                                @error('is_returnable')
                                <p class="text-danger"> {{ $errors->first("is_returnable") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan">Remarks</label>
                                <textarea name="note" class="form-control">{{$internalTransfer->note}}</textarea>
                                @error('note')
                                <p class="text-danger"> {{ $errors->first("note") }} </p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8 offset-md-2">
                            <div class="form-group">
                                <label for="challan" style="font-weight: 600 !important;">Attachment: <a target="_blank" href="{{check_storage_image_exists($internalTransfer->file_attachment_path)}}">{{$internalTransfer->file_name}}</a></label>
                                <input type="file" name="file" value="" class="form-control">
                                @error('file')
                                <p class="text-danger"> {{ $errors->first("file") }} </p>
                                @enderror
                            </div>
                        </div>

                        <hr/>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-8 offset-md-2">
                                    <div id="kt_repeater_1">
                                        <div data-repeater-list="">
                                            @foreach($internalTransfer_items as $internalTransferItem)
                                                <div data-repeater-item="" class="section-repeater">
                                                    <div class="form-group row float-right">
                                                        <div class="col-lg-4 section-repeater-delete-btn">
                                                            <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-lg-4 challanItemsDiv">
                                                            <label>Item <span class="text-danger">*</span></label>
                                                            <input type="text" autocomplete="off" class="form-control search" name="search[]" placeholder="Item Name"
                                                                   @if($internalTransferItem->item_type=='whms') value="{{$internalTransferItem->item_code.'-'.$internalTransferItem->item_name}}" @else value="{{$internalTransferItem->other_item_code.'-'.$internalTransferItem->other_item_name}}" @endif required>
                                                            <input type="hidden" class="form-control challan_item_name" name="item[]" value="{{ $internalTransferItem->item_id }}">
                                                            <input type="hidden" class="challan_item_type" name="item_type[]" value="{{ $internalTransferItem->item_type }}">
                                                            <div class="autocomplete-items" style="display: none;">
                                                            </div>
                                                            @error("item")
                                                            <p class="text-danger"> {{ $errors->first("item") }} </p>
                                                            @enderror
                                                        </div>
                                                        <div class="col-lg-2 challanItemsMeasureDiv">
                                                            <label>Variant</label>
                                                            <select class="form-control challan_item_measure" name="measure[]">
                                                                <option value="" selected>Select</option>
                                                                @foreach($internalTransferItem->itemMeasurements as $itemMeasurements)
                                                                    <option value="{{$itemMeasurements->id}}" @if($itemMeasurements->id==$internalTransferItem->measure_id) selected @endif>{{$itemMeasurements->measure_name}}</option>
                                                                @endforeach
                                                            </select>
                                                            @error("measure")
                                                            <p class="text-danger"> {{ $errors->first("measure") }} </p>
                                                            @enderror
                                                        </div>
                                                        <div class="col-lg-2 challanItemsUnitDiv">
                                                            <label>UOM</label>
                                                            @php
                                                                if(isset($internalTransferItem->uomName->name)){
                                                                        $unit_name = $internalTransferItem->uomName->name;
                                                                        $unit_id = $internalTransferItem->uomName->id;
                                                                    }else{
                                                                        $unit_name = '---';
                                                                        $unit_id= '';
                                                                    }
                                                            @endphp
                                                            <input type="text" class="form-control unit_class" disabled value="{{$unit_name}}">
                                                            <input type="hidden" class="form-control unit_input_class" name="unit[]" value="{{$unit_id}}">
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <label>Qty <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control qty_class" name="qty[]" value="{{ $internalTransferItem->qty }}" required/>
                                                            @error("qty")
                                                            <p class="text-danger"> {{ $errors->first("qty") }} </p>
                                                            @enderror
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <label>Remarks</label>
                                                            <input name="remarks[]" type="text" class="form-control" value="{{ $internalTransferItem->remarks }}" placeholder="Result"/>
                                                            @error("remarks")
                                                            <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="form-group row mt-5">
                                            <div class="col-lg-4">
                                                <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary add_button">
                                                    <i class="la la-plus"></i>Add
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2 save_button">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>
    <script>
        $(document).ready( function () {
            $('.add_button').on('click', function () {
                let a = 0;
                let b = 0;
                let c = 0;
                let d = 0;
                $(".challan_item_measure").each(function() {
                    ++a;
                });
                $(".challan_item_measure").each(function() {
                    ++b;
                    if(a==b){
                        $(this).html('<option value="" selected="">Select</option>');
                    }
                });
                $(".unit_class").each(function() {
                    ++c;
                });
                $(".unit_class").each(function() {
                    ++d;
                    if(c==d){
                        $(this).val('---');
                    }
                });
            });



            var currentFocus;
            $(document).on('input', '.search', function(e) {

                if($(this).val().length==0){
                    var challan_item_name = $(this).closest('.challanItemsDiv').find('.challan_item_name');
                    var challan_item_type = $(this).closest('.challanItemsDiv').find('.challan_item_type');
                    challan_item_name.val('');
                    challan_item_type.val('');
                }
                if($(this).val().length>2){
                    currentFocus=0;
                    var search = $(this).val();
                    var itemSearchElement = $(this).closest('.challanItemsDiv').find('.autocomplete-items');
                    itemSearchElement.html('');
                    var url = '{{route('internal-transfer.findItemName')}}';
                    $.ajax({
                        type: "GET",
                        url: url,
                        data:{'_token':'{{csrf_token()}}','search':search},
                        dataType: "json",
                        success: function(result){
                            itemSearchElement.hide();
                            var html = '';
                            var code = '';
                            $(result.items).each(function(inx,value) {
                                if(value.code){
                                    code=value.code;
                                }else{
                                    code = '';
                                }
                                inx++;
                                html += `<div class="item_link specific_${inx}" data-serial="${inx}" data-id="${value.id}" data-item-type="${value.item_type}">${code}-${value.name}</div>`;
                            });
                            if(html!=''){
                                itemSearchElement.html(html);
                                itemSearchElement.show();
                            }
                        }
                    });
                }
            });

            $(document).on('keydown', '.search', function(e) {
                var itemSearchElement = $(this).closest('.challanItemsDiv').find('.autocomplete-items').find('.item_link');
                var numDivs = itemSearchElement.length;
                if (e.keyCode == 40) {
                    if(numDivs>currentFocus){
                        ++currentFocus;
                        itemSearchElement.each(function(i,v){
                            if(i+1==currentFocus){
                                addActive($(this));
                            }
                        })
                    }
                } else if (e.keyCode == 38) {
                    if (currentFocus > 1) {
                        --currentFocus;
                        itemSearchElement.each(function(i,v){
                            if(i+1==currentFocus){
                                addActive($(this));
                            }
                        })
                    }
                } else if (e.keyCode == 13) {
                    e.preventDefault();
                    if (currentFocus > 0) {
                        itemSearchElement.each(function(i,v){
                            if(i+1==currentFocus){
                                $(this).trigger('click');
                            }
                        })
                    }
                }
                function addActive(element){
                    removeActive();
                    element.addClass("autocomplete-active");
                    var serial = element.data('serial');
                    let el = document.querySelector('.specific_'+serial);
                    el.scrollIntoView({behavior: "auto", block: "nearest", inline: "nearest"});
                }
                function removeActive() {
                    $('.item_link').each(function() {
                        $(this).removeClass("autocomplete-active");
                    });
                }
            });

            $(document).on('click', '.item_link', function(e) {
                var item_name = $(this).html();
                var item_id = $(this).data('id');
                var item_type = $(this).data('item-type');
                var itemNameElement = $(this).closest('.challanItemsDiv').find('.challan_item_name');
                itemNameElement.val(item_id);
                var itemTypeElement = $(this).closest('.challanItemsDiv').find('.challan_item_type');
                itemTypeElement.val(item_type);
                var itemSearchElement = $(this).closest('.challanItemsDiv').find('.search');
                itemSearchElement.val(item_name);
                var itemSearchDivElement = $(this).closest('.challanItemsDiv').find('.autocomplete-items');
                itemSearchDivElement.hide();
                var unitElement = $(this).closest('.challanItemsDiv').next('.challanItemsMeasureDiv').next('.challanItemsUnitDiv').find('.unit_class');
                var unitInputElement = $(this).closest('.challanItemsDiv').next('.challanItemsMeasureDiv').next('.challanItemsUnitDiv').find('.unit_input_class');
                var variantElement = $(this).closest('.challanItemsDiv').next('.challanItemsMeasureDiv').find('.challan_item_measure');
                var url = '{{route('internal-transfer.findUnitMeasurement')}}';
                $.ajax({
                    type: "GET",
                    url: url,
                    data:{'_token':'{{csrf_token()}}','item_id':item_id,'item_type':item_type},
                    dataType: "json",
                    success: function(result){
                        var html = '<option value="" selected>Select</option>';
                        $(result.variant).each(function(inx,value) {
                            html += '<option value="'+value.id+'">'+value.measure_name+'</option>';
                        });
                        variantElement.html(html);
                        if(typeof result.unit.unit_name == 'undefined' ||  result.unit.unit_name === null){
                            unitElement.val('---');
                            unitInputElement.val('');
                        }else{
                            unitElement.val(result.unit.unit_name.name);
                            unitInputElement.val(result.unit.unit_name.id);
                        }
                    }
                });
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.save_button').on('click', function (e) {
                var allError = new Array();
                var not_valid = true;
                if($('#destination_department_id').val()!=''){
                    not_valid = false;
                }
                if($('#destination_supplier_id').val()!=''){
                    not_valid = false;
                }if(typeof $('#destination_supplier_name').val() != 'undefined' && $('#destination_supplier_name').val()!=''){
                    not_valid = false;
                }
                if(not_valid){
                    allError.push("<p>Destination field is required!</p>");
                }
                $('.challan_item_name').each(function(){
                    if(typeof $(this).val() == 'undefined' || $(this).val() == ''){
                        let search_value = $(this).closest('.challanItemsDiv').find('.search').val();
                        if(search_value==''){
                            allError.push("<p>Item not found! Please select a valid item from suggestion box</p>");
                        }else{
                            allError.push("<p>'"+search_value+"' not found! Please select a valid item from suggestion box</p>");
                        }
                    }
                })
                $('.qty_class').each(function(){
                    if(Number($(this).val())<1){
                        allError.push("<p>Quantity field must be greater than zero!</p>");
                    }
                })
                if(allError.length > 0){
                    e.preventDefault();
                    toastr.options = {
                        "closeButton": true,
                        "debug": false,
                        "newestOnTop": false,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "preventDuplicates": true,
                        "onclick": null,
                        "showDuration": "300",
                        "hideDuration": "1000",
                        "timeOut": "1000",
                        "extendedTimeOut": "1000",
                        "showEasing": "swing",
                        "hideEasing": "linear",
                        "showMethod": "fadeIn",
                        "hideMethod": "fadeOut"
                    };
                    return toastr.error(allError);
                }
            });
            $('#delivered_by').select2();
            $('#source_warehouse_id').select2();
            $('#source_department_id').select2();
            $('#destination_warehouse_id').select2();
            $('#destination_department_id').select2();
            $('#destination_supplier_id').select2();
            $('.item').select2();
            let warehouses = {!! $data["warehouses"] !!};
            let departments = {!! $data["departments"] !!};
            let suppliers = {!! $data["suppliers"] !!};
            let employee_department_id = '{!! $data['department_id'] !!}';
            let all_permission = '{!! auth()->user()->can('Create All Departments Internal Transfer') ? 1:0 !!}';
            $('.transfer_from').on('click', function () {
                let _transfer_from = $(this).attr('value');
                if(_transfer_from == "warehouse") {
                    showWarehouse();
                    $('#source_department_id').val('');
                } else if(_transfer_from == "department") {
                    showDepartment();
                    $('#source_warehouse_id').val('');
                }
            });
            $('.transfer_to').on('click', function () {
                let _transfer_to = $(this).attr('value');
                if(_transfer_to == "warehouse") {
                    showDestinationWarehouse();
                    $('#destination_department_id').val('');
                } else if(_transfer_to == "department") {
                    showDestinationDepartment();
                    $('#destination_warehouse_id').val('');
                } else if(_transfer_to == "supplier") {
                    showDestinationSupplier();
                    $('#destination_supplier_id').val('');
                }
            });
            $('.type_supplier').on('click', function () {
                var data_value = $('.supplier_div').attr('data-value');
                $('.supplier_div').html('');
                if(data_value=='select'){
                    var html = '<input type="text" id="destination_supplier_name" name="to_supplier_name" required value="" class="form-control" placeholder="Enter supplier Name">';
                    $('.supplier_div').html(html);
                    $(this).html('Click here for select');
                    $('.supplier_div').attr('data-value','input');
                }else{
                    var html = '<select class="form-control" id="destination_supplier_id" name="to_supplier_id"><option value="" selected>Select an option</option>';
                    $.each(suppliers, function (i,v) {
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    });
                    html += '</select>';
                    $('.supplier_div').html(html);
                    $('#destination_supplier_id').select2();
                    $('.supplier_div').attr('data-value','select');
                    $(this).html('Click here for new supplier');
                }
                $('#destination_department_id').val("").trigger('change');
                $('#destination_warehouse_id').val("").trigger('change');
            });
            function showDestinationSupplier() {
                var html = '<option value="" selected>Select an option</option>';
                $.each(suppliers, function (i,v) {
                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                });
                $('#destination_supplier').removeClass('d-none');
                $('#destination_supplier_id').html(html);
                $('#destination_department_id').val("").trigger('change');
                $('#destination_warehouse_id').val("").trigger('change');
                $('#destination_supplier_id').select2();
                $('#source_warehouse_id').select2();
                $('#source_department_id').select2();
                $('#destination_warehouse').addClass('d-none');
                $('#destination_department').addClass('d-none');
                $('#destination_warehouse_id').select2();
                $('#destination_department_id').select2();
            }
            function showDepartment() {
                var html = '<option value="" selected>Select an option</option>';
                var des_ware_id = $('#destination_warehouse_id').val();
                if(des_ware_id=='' || des_ware_id==null){
                    $.each(warehouses, function (i,v) {
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    });
                }else{
                    $.each(warehouses, function (i,v) {
                        if(v.id==des_ware_id){
                            html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                        }else{
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        }
                    });
                }
                $('#destination_warehouse_id').html(html);
                var html = '<option value="" selected>Select an option</option>';
                $.each(warehouses, function (i,v) {
                    if(v.id!=des_ware_id){
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    }
                });
                $('#source_department').removeClass('d-none');
                $('#source_warehouse_id').html(html);
                $('#source_warehouse_id').select2();
                $('#source_department_id').select2();
                $('#destination_warehouse_id').select2();
                $('#destination_department_id').select2();
                $('#destination_supplier_id').select2();
                $('#source_warehouse').addClass('d-none');
            }
            function showDestinationDepartment() {
                var src_ware_id = $('#source_warehouse_id').val();
                var html = '<option value="" selected>Select an option</option>';
                if(src_ware_id=='' || src_ware_id==null){
                    $.each(warehouses, function (i,v) {
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    });
                }else{
                    $.each(warehouses, function (i,v) {
                        if(v.id==src_ware_id){
                            html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                        }else{
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        }
                    });
                }
                $('#source_warehouse_id').html(html);
                var html = '<option value="" selected>Select an option</option>';
                $.each(warehouses, function (i,v) {
                    if(v.id!=src_ware_id){
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    }
                });
                $('#destination_department').removeClass('d-none');
                $('#destination_warehouse_id').html(html);
                $('#destination_supplier_id').val("").trigger('change');
                $('#destination_warehouse_id').val("").trigger('change');
                $('#destination_warehouse_id').select2();
                $('#source_warehouse_id').select2();
                $('#source_department_id').select2();
                $('#destination_warehouse').addClass('d-none');
                $('#destination_supplier').addClass('d-none');
                $('#destination_department_id').select2();
                $('#destination_supplier_id').select2();
            }
            function showWarehouse() {
                var html = '<option value="" selected>Select an option</option>';
                var des_dep_id = $('#destination_department_id').val();
                if(des_dep_id=='' || des_dep_id==null){
                    $.each(departments, function (i,v) {
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    });
                }else{
                    $.each(departments, function (i,v) {
                        if(v.id==des_dep_id){
                            html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                        }else{
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        }
                    });
                }
                $('#destination_department_id').html(html);
                var html = '<option value="" selected>Select an option</option>';
                $.each(departments, function (i,v) {
                    if(v.id!=des_dep_id){
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    }
                });
                $('#source_department_id').html(html);
                $('#source_department').addClass('d-none');
                $('#source_warehouse').removeClass('d-none');
                $('#source_warehouse_id').select2();
                $('#source_department_id').select2();
                $('#destination_warehouse_id').select2();
                $('#destination_department_id').select2();
                $('#destination_supplier_id').select2();
            }
            function showDestinationWarehouse() {
                var src_dep_id = $('#source_department_id').val();
                var html = '<option value="" selected>Select an option</option>';
                if(src_dep_id=='' || src_dep_id==null){
                    $.each(departments, function (i,v) {
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    });
                }else{
                    $.each(departments, function (i,v) {
                        if(v.id==src_dep_id){
                            html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                        }else{
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        }
                    });
                }
                $('#source_department_id').html(html);
                var html = '<option value="" selected>Select an option</option>';
                $.each(departments, function (i,v) {
                    if(v.id!=src_dep_id){
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    }
                });
                $('#destination_department_id').html(html);
                $('#destination_supplier_id').val("").trigger('change');
                $('#destination_department').addClass('d-none');
                $('#destination_warehouse').removeClass('d-none');
                $('#destination_supplier').addClass('d-none');
                $('#destination_department_id').val("").trigger('change');
                $('#destination_supplier_id').val("").trigger('change');
                $('#source_warehouse_id').select2();
                $('#source_department_id').select2();
                $('#destination_warehouse_id').select2();
                $('#destination_department_id').select2();
                $('#destination_supplier_id').select2();
            }
            $('#source_warehouse_id').on('change', function () {
                let source_warehouse_id = $(this).val();
                let destination_warehouse_id = $('#destination_warehouse_id').val();
                if(destination_warehouse_id=='' || destination_warehouse_id==null){
                    let html = '<option value="" selected>Select an option</option>';
                    if(source_warehouse_id==''){
                        $.each(warehouses, function (i,v) {
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        });
                    }else{
                        $.each(warehouses, function (i,v) {
                            if(v.id!=source_warehouse_id){
                                html += '<option value="'+v.id+'">'+v.name+'</option>';
                            }
                        });
                    }
                    $('#destination_warehouse_id').html(html);
                }else{
                    let html = '<option value="" selected>Select an option</option>';
                    if(source_warehouse_id==''){
                        $.each(warehouses, function (i,v) {
                            if(v.id==destination_warehouse_id){
                                html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                            }else{
                                html += '<option value="'+v.id+'">'+v.name+'</option>';
                            }
                        });
                    }else{
                        $.each(warehouses, function (i,v) {
                            if(v.id!=source_warehouse_id){
                                if(v.id==destination_warehouse_id){
                                    html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                                }else{
                                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                                }
                            }
                        });
                    }
                    $('#destination_warehouse_id').html(html);
                }
            });
            $('#source_department_id').on('change', function () {
                let source_department_id = $(this).val();
                let destination_department_id = $('#destination_department_id').val();
                if(destination_department_id=='' || destination_department_id==null){
                    let html = '<option value="" selected>Select an option</option>';
                    if(source_department_id==''){
                        $.each(departments, function (i,v) {
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        });
                    }else{
                        $.each(departments, function (i,v) {
                            if(v.id!=source_department_id){
                                html += '<option value="'+v.id+'">'+v.name+'</option>';
                            }
                        });
                    }
                    $('#destination_department_id').html(html);
                }else{
                    let html = '<option value="" selected>Select an option</option>';
                    if(source_department_id==''){
                        $.each(departments, function (i,v) {
                            if(v.id==destination_department_id){
                                html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                            }else{
                                html += '<option value="'+v.id+'">'+v.name+'</option>';
                            }
                        });
                    }else{
                        $.each(departments, function (i,v) {
                            if(v.id!=source_department_id){
                                if(v.id==destination_department_id){
                                    html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                                }else{
                                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                                }
                            }
                        });
                    }
                    $('#destination_department_id').html(html);
                }
            });
            $('#destination_warehouse_id').on('change', function () {
                let destination_warehouse_id = $(this).val();
                let source_warehouse_id = $('#source_warehouse_id').val();
                if(source_warehouse_id=='' || source_warehouse_id==null){
                    let html = '<option value="" selected>Select an option</option>';
                    if(destination_warehouse_id==''){
                        $.each(warehouses, function (i,v) {
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        });
                    }else{
                        $.each(warehouses, function (i,v) {
                            if(v.id!=destination_warehouse_id){
                                html += '<option value="'+v.id+'">'+v.name+'</option>';
                            }
                        });
                    }
                    $('#source_warehouse_id').html(html);
                }else{
                    let html = '<option value="" selected>Select an option</option>';
                    if(destination_warehouse_id==''){
                        $.each(warehouses, function (i,v) {
                            if(v.id==source_warehouse_id){
                                html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                            }else{
                                html += '<option value="'+v.id+'">'+v.name+'</option>';
                            }
                        });
                    }else{
                        $.each(warehouses, function (i,v) {
                            if(v.id!=destination_warehouse_id){
                                if(v.id==source_warehouse_id){
                                    html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                                }else{
                                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                                }
                            }
                        });
                    }
                    $('#source_warehouse_id').html(html);
                }
            });
            $('#destination_department_id').on('change', function () {
                let destination_department_id = $(this).val();
                let source_department_id = $('#source_department_id').val();
                let html='';
                if(source_department_id=='' || source_department_id==null){
                    if(all_permission=='1'){
                        html += '<option value="">Select an option</option>';
                        if(destination_department_id==''){
                            $.each(departments, function (i,v) {
                                if(v.is_warehouse==0){
                                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                                }
                            });
                        }else{
                            $.each(departments, function (i,v) {
                                if(v.id!=destination_department_id && v.is_warehouse==0){
                                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                                }
                            });
                        }
                    }else{
                        $.each(departments, function (i,v) {
                            if(v.id==employee_department_id){
                                if(v.is_warehouse==0){
                                    html += '<option value="'+v.id+'" selected>'+v.name+'</option>';
                                }
                            }
                        });
                        if(html==''){
                            html += '<option value="">Select an option</option>';
                        }
                    }
                    $('#source_department_id').html(html);
                }else{
                    if(all_permission=='1'){
                        html += '<option value="">Select an option</option>';
                        if(destination_department_id==''){
                            $.each(departments, function (i,v) {
                                if(v.is_warehouse==0){
                                    if(v.id==source_department_id){
                                        html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                                    }else{
                                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                                    }
                                }
                            });
                        }else{
                            $.each(departments, function (i,v) {
                                if(v.id!=destination_department_id && v.is_warehouse==0){
                                    if(v.id==source_department_id){
                                        html += '<option selected value="'+v.id+'">'+v.name+'</option>';
                                    }else{
                                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                                    }
                                }
                            });
                        }
                    }else{
                        $.each(departments, function (i,v) {
                            if(v.id==employee_department_id && v.is_warehouse==0){
                                if(v.id==source_department_id){
                                    html += '<option value="'+v.id+'" selected>'+v.name+'</option>';
                                }else{
                                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                                }
                            }
                        });
                        if(html==''){
                            html += '<option value="">Select an option</option>';
                        }
                    }
                    $('#source_department_id').html(html);
                }
            });
        });
    </script>
@endsection
