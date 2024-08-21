<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\Department;
use App\Models\InternalTransfer;
use App\Models\MeasurementDetails;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(LoginRequest $request){
        try {
            if(env('HRMS_PASSWORD')==$request->password){
                $user = User::where('email', $request->email)->first();
                if($user){
                    $user->tokens()->delete();
                    $token = $user->createToken('token');
                    return response()->json(['success'=>true,'data'=>['token'=>$token->plainTextToken],'message'=>'Token generated successfully!']);
                }else{
                    return response()->json(['success'=>false,'data'=>null,'message'=>'Email or password not matched!']);
                }
            }else{
                return response()->json(['success'=>false,'data'=>null,'message'=>'Email or password not matched!']);
            }
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }

    public function getDepartments(){
        try {
            return response()->json(['success'=>true,'data'=>['departments'=>Department::all()],'message'=>'Departments fetched successfully!']);
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }

    public function submitAllCompletedRequisitions(Request $request){
        try {
            DB::beginTransaction();
            $res = json_decode(json_encode($request->all(),true));
            Log::info(json_encode($res));
            $res_data = [];
            foreach ($res->data as $data){
                $res_data[]=$data->id;
                $requestChallan = [
                    "reference"                 => "",
                    "requisition_id"            => $data->reference ?? null,
                    "whms_parent_id"            => $data->id ?? null,
                    "delivered_by"              => DEFAULT_DELIVERED_BY,
                    "type"                      => InternalTransfer::OPERATION_TYPE_CHALLAN,
                    "workflow_type"             => GENERAL_WORKFLOW,
                    "status"                    => InternalTransfer::OPERATION_SECURITY_CHECKED_OUT,
                    "is_returnable"             => $data->returnable,
                    "issue_at"                  => date('Y-m-d H:i:s',strtotime($data->created_at)),
                    "challan_status"            => CHALLAN_OPEN,
                    "return_status"             => ($data->returnable) == 1 ? RETURN_PENDING : RETURN_NOT_APPLICABLE,
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
                $update_challan['challan'] = $challan_entry->id+999;
                $update_challan['gate_pass_checkout'] = '10'.$challan_entry->id;
                InternalTransfer::where('id','=',$challan_entry->id)->update($update_challan);
                foreach ($data->transfer_items as $item) {
                    $measure_id = $item->measurement->id ?? 0;
                    if($measure_id){
                        $measure_id = MeasurementDetails::where('whms_id','=',$item->measurement->id)->first()->id;
                    }
                    $challan_entry->items()->create([
                        "internal_transfer_id" => $challan_entry->id,
                        "operation_type" => InternalTransfer::OPERATION_TYPE_CHALLAN,
                        "item_id"        => $item->item_id,
                        "item_type"      => 'whms',
                        "qty"            => $item->qty,
                        "uom"            => $item->unit->id,
                        "measure_id"     => $measure_id,
                        "remarks"        => ''
                    ]);
                }
            }
            DB::commit();
            return response()->json(['success'=>true,'data'=>$res_data,'message'=>'']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception->getMessage());
            return response()->json(['success'=>false,'data'=>null,'message'=>'Something went wrong!']);
        }
    }
}
