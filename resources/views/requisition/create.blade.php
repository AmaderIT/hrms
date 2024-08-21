@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Requisition</h3>
                    <div class="card-toolbar">
                        <div class="example-tools justify-content-center">
                            <a href="{{ route('requisition.index') }}" class="btn btn-primary mr-2">Back</a>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form action="{{ route('requisition.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="col-md-8 offset-md-2">
                            {{-- Department --}}
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select class="form-control" id="department_id" name="department_id" required>
                                    <!-- <option value="" disabled selected>Select an option</option> -->
                                    @foreach($data["departments"] as $department)
                                        <option value="{{ $department->id }}" {{ $department->id == old("department_id") ? "selected" : "" }}>
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
                                    <option value="0" {{ \App\Models\Requisition::PRIORITY_TODAY == old("priority") ? "selected" : "" }}>Today</option>
                                    <option value="1" {{ \App\Models\Requisition::PRIORITY_WITHIN_3_DAYS == old("priority") ? "selected" : "" }}>Within 3 days</option>
                                    <option value="2" {{ \App\Models\Requisition::PRIORITY_WITHIN_7_DAYS == old("priority") ? "selected" : "" }}>Within 7 days</option>
                                    <option value="3" {{ \App\Models\Requisition::PRIORITY_WITHIN_10_DAYS == old("priority") ? "selected" : "" }}>Within 10 days</option>
                                </select>
                                @error("priority")
                                <p class="text-danger"> {{ $errors->first("priority") }} </p>
                                @enderror
                            </div>

                            <div class="form-group">
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
                                                    <label>Quantity <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control qty_class" name="quantity[]" placeholder="Enter quantity here" required>
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

                                {{-- Remarks --}}
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="6" id="remarks" placeholder="Remarks"></textarea>
                                    @error("remarks")
                                    <p class="text-danger"> {{ $errors->first("remarks") }} </p>
                                    @enderror
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
