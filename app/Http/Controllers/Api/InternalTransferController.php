<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalTransfer;
use App\Models\MeasurementDetails;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternalTransferController extends Controller
{
    public function getChallanFromWarehouse(Request $request){
        try {
            DB::beginTransaction();
            $res = json_decode(json_encode($request->all(),true));
            $res_data = [];
            $update_challan = [];
            foreach ($res->data as $data){
                $res_data[]=$data->hrms_challan_id;
                $single_update = [
                    "id"                       => $data->hrms_challan_id,
                    "status"                   => InternalTransfer::OPERATION_RECEIVED,
                    "challan_status"           => ($data->returnable == 0) ?  CHALLAN_CLOSE : CHALLAN_PENDING_RETURN,
                    "checkin_at"               => date('Y-m-d H:i:s',strtotime($data->updated_at)),
                    "gate_pass_checkin"        => '11'.$data->hrms_challan_id,
                    "receive_security_checked" => getUserIdByEmployeeCode($data->security_user->employee_id ?? 123),
                    "received_by" => getUserIdByEmployeeCode($data->received_user->employee_id ?? 123),
                ];
                $update_challan[] = $single_update;
                if($data->hrms_parent_id>0){
                    InternalTransfer::where('id','=',$data->hrms_parent_id)->update(['return_status'=>RETURN_COMPLETE]);
                }
            }
            if($update_challan){
                $internal_transfer = new InternalTransfer();
                $index = 'id';
                batch()->update($internal_transfer, $update_challan, $index);
            }
            DB::commit();
            return response()->json(['success'=>true,'data'=>$res_data,'message'=>'Challan updated successfully!']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }

    public function getReturnChallanFromWarehouse(Request $request){
        try {
            DB::beginTransaction();
            $res = json_decode(json_encode($request->all(),true));
            $res_data = [];
            foreach ($res->data as $data){
                InternalTransfer::where('id','=',$data->hrms_parent_id)->update(['challan_status'=>CHALLAN_CLOSE]);
                $requestChallan = [
                    "parent_id"                 => $data->hrms_parent_id,
                    "reference"                 => $data->reference,
                    "delivered_by"              => DEFAULT_DELIVERED_BY,
                    "is_return_challan"         => 1,
                    "type"                      => InternalTransfer::OPERATION_TYPE_CHALLAN,
                    "workflow_type"             => GENERAL_WORKFLOW,
                    "status"                    => InternalTransfer::OPERATION_SECURITY_CHECKED_OUT,
                    "is_returnable"             => 0,
                    "issue_at"                  => date('Y-m-d H:i:s',strtotime($data->created_at)),
                    "challan_status"            => CHALLAN_OPEN,
                    "return_status"             => RETURN_NOT_APPLICABLE,
                    "source_warehouse_id"       => 0,
                    "source_department_id"      => $data->from_hrms_dept->hrms_id,
                    "destination_warehouse_id"  => 0,
                    "destination_department_id" => $data->to_department->hrms_id,
                    "to_supplier_id"            => 0,
                    "created_by"                => getUserIdByEmployeeCode($data->user->employee_id ?? 123),
                    "authorized_by"             => getUserIdByEmployeeCode($data->authorized_user->employee_id ?? 123),
                    "dispatch_security_checked" => getUserIdByEmployeeCode($data->security_user->employee_id ?? 123),
                    "checkout_at"               => date('Y-m-d H:i:s',strtotime($data->updated_at)),
                    "note"                      => $data->remark,
                    "created_at"                => now(),
                ];
                $challan_entry = InternalTransfer::create($requestChallan);
                $res_data[$data->id]=$challan_entry->id;
                $update_challan['challan'] = $challan_entry->id+999;
                $update_challan['gate_pass_checkout'] = '10'.$challan_entry->id;
                InternalTransfer::where('id','=',$challan_entry->id)->update($update_challan);
            }
            DB::commit();
            return response()->json(['success'=>true,'data'=>$res_data,'message'=>'Return challan received successfully!']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }

    public function upsertWhmsItem(Request $request){
        try {
            Log::info(json_encode($request->all()));
            DB::beginTransaction();
            $res = json_decode(json_encode($request->all(),true));
            $item = $res->data->item;
            $whms_units = $res->data->units;
            $type = $res->data->type;
            $this->resolveUnits($whms_units);
            if($type=='create'){
                $insert = [];
                $single=array();
                $single['id'] = $item->id;
                $single['name'] = $item->name;
                $single['code'] = $item->barcode;
                $single['parent_id'] = $item->category_id;
                $single['color'] = $item->color;
                $single['unit_id'] = $item->unit_id;
                $single['created_at'] = $item->created_at;
                $single['updated_at'] = $item->updated_at;
                $single['deleted_at'] = $item->deleted_at;
                $insert[]=$single;
                DB::table('requisition_items')->insert($insert);
                if($item->all_measurements){
                    foreach ($item->all_measurements as $measurement){
                        $single_measure = [];
                        $single_measure['whms_id'] = $measurement->id;
                        $single_measure['requisition_item_id'] = $measurement->item_id;
                        $single_measure['measure_id'] = $measurement->measure_id;
                        $single_measure['measure_name'] = $measurement->measure->name;
                        $single_measure['created_at'] = $measurement->created_at;
                        $single_measure['updated_at'] = $measurement->updated_at;
                        $single_measure['deleted_at'] = $measurement->deleted_at;
                        $insert_measure[]=$single_measure;
                    }
                    DB::table('item_measurement_details')->insert($insert_measure);
                }
            }else{
                $present_item = DB::table('requisition_items')->where('id','=',$item->id)->first();
                $single_update['name'] = $item->name;
                $single_update['color'] = $item->color;
                $single_update['code'] = $item->barcode;
                $single_update['parent_id'] = $item->category_id;
                $single_update['unit_id'] = $item->unit_id;
                $single_update['updated_at'] = $item->updated_at;
                if($item->deleted_at != $present_item->deleted_at){
                    $single_update['deleted_at'] = $item->deleted_at;
                }
                DB::table('requisition_items')->where('id','=',$item->id)->update($single_update);
                $present_measure_items = DB::table('item_measurement_details')->where('requisition_item_id','=', $item->id)->get();
                $present_measure_items_array = [];
                foreach($present_measure_items as $present_measure_item){
                    $present_measure_items_array[$present_measure_item->requisition_item_id][$present_measure_item->measure_id][$present_measure_item->whms_id] = $present_measure_item->id;
                }
                $insert_measure = [];
                $update_measure = [];
                if($item->all_measurements){
                    foreach ($item->all_measurements as $measurement){
                        if(!isset($present_measure_items_array[$measurement->item_id][$measurement->measure_id][$measurement->id])){
                            $single_measure = [];
                            $single_measure['whms_id'] = $measurement->id;
                            $single_measure['requisition_item_id']=$measurement->item_id;
                            $single_measure['measure_id']=$measurement->measure_id;
                            $single_measure['measure_name']=$measurement->measure->name;
                            $single_measure['created_at'] = $measurement->created_at;
                            $single_measure['updated_at'] = $measurement->updated_at;
                            $single_measure['deleted_at'] = $measurement->deleted_at;
                            $insert_measure[]=$single_measure;
                        }else{
                            $single_measure_update=[];
                            $single_measure_update['id'] = $present_measure_items_array[$measurement->item_id][$measurement->measure_id][$measurement->id];
                            $single_measure_update['measure_name'] = $measurement->measure->name;
                            $single_measure_update['deleted_at'] = $measurement->deleted_at;
                            $update_measure[] = $single_measure_update;
                        }
                    }
                    if($insert_measure){
                        DB::table('item_measurement_details')->insert($insert_measure);
                    }
                    if($update_measure){
                        $itemMeasureInstance = new MeasurementDetails;
                        $index = 'id';
                        batch()->update($itemMeasureInstance, $update_measure, $index);
                    }
                }
            }
            DB::commit();
            return response()->json(['success'=>true,'data'=>[],'message'=>'Item changed successfully!']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }

    public function resolveUnits($whms_units){
        $units = Unit::all();
        $unit_arr = [];
        foreach ($units as $u){
            $unit_arr[$u->id] = $u->name;
        }
        $insert_unit = [];
        $update_unit = [];
        foreach($whms_units as $unit){
            if(!isset($unit_arr[$unit->id])){
                $single_insert = [];
                $single_insert['id'] = $unit->id;
                $single_insert['name'] = $unit->name;
                $single_insert['description'] = $unit->description;
                $single_insert['created_at'] = $unit->created_at;
                $single_insert['updated_at'] = $unit->updated_at;
                $single_insert['deleted_at'] = $unit->deleted_at;
                $insert_unit[] = $single_insert;
            }else{
                $single_update = [];
                $single_update['id'] = $unit->id;
                $single_update['name'] = $unit->name;
                $single_update['description'] = $unit->description;
                $single_update['deleted_at'] = $unit->deleted_at;
                $update_unit[] = $single_update;
            }
        }
        if($insert_unit){
            DB::table('units')->insert($insert_unit);
        }
        if($update_unit){
            $unitInstance = new Unit();
            $index = 'id';
            batch()->update($unitInstance, $update_unit, $index);
        }
        return true;
    }

    public function insertBulkWhmsItem(Request $request){
        Log::info(json_encode($request->all()));
        try{
            DB::beginTransaction();
            $res = json_decode(json_encode($request->all(),true));
            $items = $res->data->items;
            $last_item_id = DB::table('requisition_items')->orderBy('id','desc')->first()->id;
            $insert = [];
            $insert_measure = [];
            foreach($items as $item){
                if($last_item_id<$item->id){
                    $single=[];
                    $single['id'] = $item->id;
                    $single['name'] = $item->name;
                    $single['code'] = $item->barcode;
                    $single['parent_id'] = $item->category_id;
                    $single['color'] = $item->color;
                    $single['unit_id'] = $item->unit_id;
                    $single['created_at'] = $item->created_at;
                    $single['updated_at'] = $item->updated_at;
                    $single['deleted_at'] = $item->deleted_at;
                    $insert[]=$single;
                    if($item->all_measurements){
                        foreach ($item->all_measurements as $measurement){
                            $single_measure = [];
                            $single_measure['whms_id']=$measurement->id;
                            $single_measure['requisition_item_id']=$measurement->item_id;
                            $single_measure['measure_id']=$measurement->measure_id;
                            $single_measure['measure_name']=$measurement->measure->name;
                            $single_measure['created_at'] = $measurement->created_at;
                            $single_measure['updated_at'] = $measurement->updated_at;
                            $single_measure['deleted_at'] = $measurement->deleted_at;
                            $insert_measure[]=$single_measure;
                        }
                    }
                }
            }
            if($insert){
                DB::table('requisition_items')->insert($insert);
            }
            if($insert_measure){
                DB::table('item_measurement_details')->insert($insert_measure);
            }
            $units = Unit::all();
            $unit_arr = [];
            foreach ($units as $u){
                $unit_arr[$u->id] = $u->name;
            }
            $insert_unit = [];
            $update_unit = [];
            foreach($res->data->units as $unit){
                if(!isset($unit_arr[$unit->id])){
                    $single_insert = [];
                    $single_insert['id'] = $unit->id;
                    $single_insert['name'] = $unit->name;
                    $single_insert['description'] = $unit->description;
                    $single_insert['created_at'] = $unit->created_at;
                    $single_insert['updated_at'] = $unit->updated_at;
                    $single_insert['deleted_at'] = $unit->deleted_at;
                    $insert_unit[] = $single_insert;
                }else{
                    $single_update = [];
                    $single_update['id'] = $unit->id;
                    $single_update['name'] = $unit->name;
                    $single_update['description'] = $unit->description;
                    $single_update['deleted_at'] = $unit->deleted_at;
                    $update_unit[] = $single_update;
                }
            }
            if($insert_unit){
                DB::table('units')->insert($insert_unit);
            }
            if($update_unit){
                $unitInstance = new Unit();
                $index = 'id';
                batch()->update($unitInstance, $update_unit, $index);
            }
            DB::commit();
            return response()->json(['success'=>true,'data'=>[],'message'=>'Items added successfully!']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }
}
