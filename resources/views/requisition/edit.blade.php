@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Requisition</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('requisition.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('requisition.update', ['requisition' => $data['requisition']->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            {{-- Department --}}
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control" id="department_id" name="department_id" required>
                                    <option value="" disabled selected>Select an option</option>
                                    @foreach($data["departments"] as $department)
                                        <option value="{{ $department->id }}" {{ $department->id == $data['requisition']->department_id ? "selected" : "" }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("department_id")
                                <p class="text-danger"> {{ $errors->first("department_id") }} </p>
                                @enderror
                            </div>

                            {{-- Priority --}}
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="" disabled selected>Select an option</option>
                                    <option value="0" {{ \App\Models\Requisition::PRIORITY_TODAY == $data['requisition']->priority ? "selected" : "" }}>Today</option>
                                    <option value="1" {{ \App\Models\Requisition::PRIORITY_WITHIN_3_DAYS == $data['requisition']->priority ? "selected" : "" }}>Within 3 days</option>
                                    <option value="2" {{ \App\Models\Requisition::PRIORITY_WITHIN_7_DAYS == $data['requisition']->priority ? "selected" : "" }}>Within 7 days</option>
                                    <option value="3" {{ \App\Models\Requisition::PRIORITY_WITHIN_10_DAYS == $data['requisition']->priority ? "selected" : "" }}>Within 10 days</option>
                                </select>
                                @error("priority")
                                <p class="text-danger"> {{ $errors->first("priority") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
                                @for($i = 0; $i < $data['requisition']->details->count(); $i++)
                                    @php $item_details = []; @endphp
                                    <div id="item_div_{{$data['requisition']->details[$i]->id}}">
                                        <div class="form-group row">
                                            <div class="col-lg-4 requisitionItemsDiv">
                                                <label>Item<span class="text-danger">*</span></label>
                                                <select class="form-control item" name="requisition_item_id[]">
                                                    <option value="" disabled selected>Select an option</option>
                                                    @foreach($data["requisitionItems"] as $requisitionItem)
                                                        @if($requisitionItem->id == $data['requisition']->details[$i]->requisition_item_id)
                                                            @php
                                                                foreach($requisitionItem->itemMeasurements as $measure){
                                                                    $item_details[$measure->id]=$measure->measure_name;
                                                                }
                                                            @endphp
                                                        @endif
                                                        <option value="{{ $requisitionItem->id }}" {{ $requisitionItem->id == $data['requisition']->details[$i]->requisition_item_id ? "selected" : "" }}>
                                                            {{ $requisitionItem->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('requisition_item_id')
                                                <p class="text-danger"> {{ $errors->first("requisition_item_id") }} </p>
                                                @enderror
                                            </div>
                                            <div class="col-lg-2 requisitionItemsMeasureDiv">
                                                <label>Variant</label>
                                                <select required class="form-control requisition_item_measure" name="requisition_item_measure_id[]">
                                                    <option value="0" @if($data['requisition']->details[$i]->measure_id==0) selected="selected" @endif>---</option>
                                                    @isset($item_details)
                                                        @foreach($item_details as $key=>$item_detail)
                                                            <option value="{{$key}}" @if($key == $data['requisition']->details[$i]->measure_id) selected="selected" @endif>{{$item_detail}}</option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Ordered Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control qty_class" name="quantity[]" value="{{ $data['requisition']->details[$i]->quantity }}"
                                                       placeholder="Enter quantity here" required>
                                                @error('quantity')
                                                <p class="text-danger"> {{ $errors->first("quantity") }} </p>
                                                @enderror
                                            </div>
                                            <div class="col-lg-2 section-repeater-delete-btn m-auto text-center">
                                                <a href="javascript:;" data-item-id="{{$data['requisition']->details[$i]->id}}" class="btn btn-sm font-weight-bolder btn-light-danger delete_item">X</a>
                                            </div>
                                        </div>
                                    </div>
                                @endfor

                                {{-- Repeater --}}
                                <div id="kt_repeater_1">
                                    <div data-repeater-list="">
                                        <div data-repeater-item="" class="section-repeater">
                                            <div class="form-group row">
                                                <div class="col-lg-4 requisitionItemsDiv">
                                                    <label>Item<span class="text-danger">*</span></label>
                                                    <select class="form-control item" name="requisition_item_id[]">
                                                        <option value="" disabled selected>Select an option</option>
                                                        @foreach($data["requisitionItems"] as $requisitionItem)
                                                            <option value="{{ $requisitionItem->id }}" {{ $requisitionItem->id == old("requisition_item_id") ? "selected" : "" }}>
                                                                {{ $requisitionItem->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('requisition_item_id')
                                                    <p class="text-danger"> {{ $errors->first("requisition_item_id") }} </p>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-2 requisitionItemsMeasureDiv">
                                                    <label>Variant</label>
                                                    <select required class="form-control requisition_item_measure" name="requisition_item_measure_id[]">
                                                        <option value="0" selected="selected">---</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3">
                                                    <label for="tax_rate_male">Ordered Quantity <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control qty_class" name="quantity[]" required placeholder="Enter quantity here">
                                                    @error('quantity')
                                                    <p class="text-danger"> {{ $errors->first("quantity") }} </p>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-2 section-repeater-delete-btn m-auto text-center">
                                                    <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">X</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-5">
                                        <div class="col-lg-4">
                                            <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                                <i class="la la-plus"></i>Add
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Remarks --}}
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="6" id="remarks" placeholder="Remarks">{{ $data['requisition']->remarks }}</textarea>
                                @error("remarks")
                                <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                @enderror
                            </div>

                            @can("Approve Requisition")
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="" disabled selected>Select an option</option>
                                        <option value="1" {{ \App\Models\Requisition::STATUS_IN_PROGRESS == $data['requisition']->status ? "selected" : "" }}>In Progress</option>
                                        <option value="2" {{ \App\Models\Requisition::STATUS_DELIVERED == $data['requisition']->status ? "selected" : "" }}>Delivered</option>
                                        <option value="3" {{ \App\Models\Requisition::STATUS_REJECTED == $data['requisition']->status ? "selected" : "" }}>Rejected</option>
                                    </select>
                                    @error("status")
                                    <p class="text-danger"> {{ $errors->first("status") }} </p>
                                    @enderror
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-10 text-lg-right">
                                <button type="reset" class="btn btn-default mr-2">Reset</button>
                                <button type="submit" class="btn btn-primary mr-2 save_button">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ asset('assets/js/pages/form-repeater.js') }}"></script>
    <script>
        $(document).ready( function () {
            $('.item').select2();
            $(document).on('change', '.item', function(e) {
                var item_id = $(this).val();
                var ToolTipDiv = $(this).closest('.requisitionItemsDiv').next('.requisitionItemsMeasureDiv').find('.requisition_item_measure');
                var url = '{{route('requisition.findMeasurement')}}';
                $.ajax({
                    type: "GET",
                    url: url,
                    data:{'_token':'{{csrf_token()}}','item_id':item_id},
                    dataType: "json",
                    success: function(result){
                        var html = '<option value="0">---</option>';
                        $(result).each(function(inx,value) {
                            html += '<option value="'+value.id+'">'+value.measure_name+'</option>';
                        });
                        ToolTipDiv.html(html);
                    }
                });
            });

            $(document).on('click', '.delete_item', function(e) {
                var item_id = $(this).attr('data-item-id');
                $('#item_div_'+item_id).fadeOut(300);
                setTimeout(function (){
                    $('#item_div_'+item_id).remove();
                }, 300);
            });


            $('.save_button').on('click', function (e) {
                var allError = [];
                $('.qty_class').each(function(){
                    if($(this).val()!='' && Number($(this).val())<1){
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
        });
    </script>
@endsection
