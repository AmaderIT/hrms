<?php

namespace App\Http\Controllers;

use App\Http\Requests\bank\RequestBank;
use App\Http\Requests\requisitionItem\RequestRequisitionItem;
use App\Models\Bank;
use App\Models\MeasurementDetails;
use App\Models\RequisitionItem;
use App\Models\Unit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Mavinoo\Batch\Batch;

class RequisitionItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $items = RequisitionItem::select("id", "name", "code")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('requisition-item.index', compact('items'));
    }

    /**
     * @param RequisitionItem $requisitionItem
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function edit(RequisitionItem $requisitionItem)
    {
        return view("requisition-item.edit", compact('requisitionItem'));
    }

    /**
     * @param RequestRequisitionItem $request
     * @return RedirectResponse|string
     */
    public function store(RequestRequisitionItem $request)
    {
        try {
            RequisitionItem::create($request->validated());

            session()->flash('message', 'Requisition Item Created Successfully');
            $redirect = redirect()->route("requisition-item.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestRequisitionItem $request
     * @param RequisitionItem $requisitionItem
     * @return RedirectResponse
     */
    public function update(RequestRequisitionItem $request, RequisitionItem $requisitionItem)
    {
        try {
            $requisitionItem->update($request->validated());

            session()->flash('message', 'Requisition Item Updated Successfully');
            $redirect = redirect()->route("requisition-item.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequisitionItem $requisitionItem
     * @return mixed
     */
    public function delete(RequisitionItem $requisitionItem)
    {
        try {
            $feedback['status'] = $requisitionItem->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }
        return $feedback;
    }

    public function syncItem(){
        try {
            if (Cache::has('item_token')) {
                $token = Cache::get('item_token');
                $token_type = 'Bearer';
                $response = $this->getItems($token_type,$token);
            }else{
                $params = array
                (
                    'email' => env('WHMS_USER'),
                    'password' => env('WHMS_PASSWORD')
                );
                $headers = array
                (
                    'Accept: application/json',
                    'Content-Type: application/json'
                );
                $url = env('WHMS_URL').'/api/login';
                $method='POST';
                $res = curlrequest($url,$headers,$method,$params);
                Log::info(json_encode($res));
                $result = $res['result'];
                $error_status = $res['error_status'];
                if (!$error_status) {
                    $arr = json_decode($result);
                    if($arr->status){
                        $token = $arr->data->access_token;
                        $token_type = $arr->data->token_type;
                        $pos = strpos($token,"|");
                        $token = substr($token,$pos+1);
                        Cache::put('item_token', $token);
                        $response = $this->getItems($token_type,$token);
                    }else{
                        $response = false;
                    }
                }else{
                    $response = false;
                }
            }
            if($response){
                session()->flash('message', 'Requisition Items Updated Successfully');
            }else{
                session()->flash('type', 'error');
                session()->flash('message', 'Something went wrong! Please try again.');
            }
            return redirect()->back();
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            session()->flash('type', 'error');
            session()->flash('message', 'Something went wrong! Please try again.');
            return redirect()->back();
        }
    }

    public function getItems($token_type,$token,$page_no=null){
        $headers_next = array
        (
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: '.$token_type.' '.$token,
        );
        $url = env('WHMS_URL').'/api/item/list';
        if($page_no){
            $url.='?page='.$page_no;
        }
        $method='GET';
        $res = curlrequest($url,$headers_next,$method);
        $result = $res['result'];
        $error_status = $res['error_status'];
        if (!$error_status) {
            $arr = json_decode($result);
            if(isset($arr->status) && $arr->status){
                $response = $this->getPageData($arr->data,$arr->data->items->current_page);
                if($response){
                    if($arr->data->items->current_page!=$arr->data->items->last_page){
                        $this->getItems($token_type,$token,$arr->data->items->current_page+1);
                    }
                }else{
                    Cache::forget('item_token');
                }
            }else{
                Cache::forget('item_token');
                $response = false;
            }
        }else{
            Cache::forget('item_token');
            $response = false;
        }
        return $response;
    }

    public function getPageData($data,$current_page){
        try{
            DB::beginTransaction();
            $first_item_id=0;
            $last_item_id=0;
            $present_item_array = [];
            $count = 1;
            $count1 = 1;
            DB::table('requisition_items')->orderBy('id')->chunk(1000, function($present_items) use (&$present_item_array,&$count,$current_page,&$count1,&$first_item_id,&$last_item_id) {
                if($current_page==$count){
                    foreach($present_items as $p_item){
                        if($count1==1){
                            $first_item_id=$p_item->id;
                            $count1++;
                        }
                        $present_item_array[$p_item->id]['id'] = $p_item->id;
                        $present_item_array[$p_item->id]['name'] = $p_item->name;
                        $present_item_array[$p_item->id]['code'] = $p_item->code;
                        $present_item_array[$p_item->id]['parent_id'] = $p_item->parent_id;
                        $present_item_array[$p_item->id]['color'] = $p_item->color;
                        $present_item_array[$p_item->id]['unit_id'] = $p_item->unit_id;
                        $present_item_array[$p_item->id]['created_at'] = $p_item->created_at;
                        $present_item_array[$p_item->id]['updated_at'] = $p_item->updated_at;
                        $present_item_array[$p_item->id]['deleted_at'] = $p_item->deleted_at;
                        $last_item_id=$p_item->id;
                    }
                }
                ++$count;
            });
            $present_measure_items = DB::table('item_measurement_details')->whereBetween('requisition_item_id', [$first_item_id, $last_item_id])->get();
            $present_measure_item_array = [];
            foreach($present_measure_items as $present_measure_item){
                $present_measure_item_array[$present_measure_item->requisition_item_id][$present_measure_item->measure_id][$present_measure_item->whms_id] = $present_measure_item->id;
            }
            $insert = [];
            $insert_measure = [];
            $update = [];
            $update_measure = [];
            foreach($data->items->data as $real_item){
                if($last_item_id<$real_item->id){
                    $single=[];
                    $single['id'] = $real_item->id;
                    $single['name'] = $real_item->name;
                    $single['code'] = $real_item->barcode;
                    $single['parent_id'] = $real_item->category_id;
                    $single['color'] = $real_item->color;
                    $single['unit_id'] = $real_item->unit_id;
                    $single['created_at'] = $real_item->created_at;
                    $single['updated_at'] = $real_item->updated_at;
                    $single['deleted_at'] = $real_item->deleted_at;
                    $insert[]=$single;
                    if($real_item->all_measurements){
                        foreach ($real_item->all_measurements as $measurement){
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
                }else{
                    $single_update=[];
                    $single_update['id'] = $real_item->id;
                    $single_update['name'] = $real_item->name;
                    $single_update['color'] = $real_item->color;
                    if($real_item->barcode != $present_item_array[$real_item->id]['code']) {
                        $single_update['code'] = $real_item->barcode;
                    }
                    if($real_item->category_id != $present_item_array[$real_item->id]['parent_id']){
                        $single_update['parent_id'] = $real_item->category_id;
                    }
                    if($real_item->unit_id != $present_item_array[$real_item->id]['unit_id']){
                        $single_update['unit_id'] = $real_item->unit_id;
                    }
                    $single_update['updated_at'] = $real_item->updated_at;
                    if($real_item->deleted_at != $present_item_array[$real_item->id]['deleted_at']){
                        $single_update['deleted_at'] = $real_item->deleted_at;
                    }
                    $update[]=$single_update;
                    if($real_item->all_measurements){
                        foreach ($real_item->all_measurements as $measurement){
                            if(!isset($present_measure_item_array[$measurement->item_id][$measurement->measure_id][$measurement->id])){
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
                                $single_measure_update['id'] = $present_measure_item_array[$measurement->item_id][$measurement->measure_id][$measurement->id];
                                $single_measure_update['measure_name'] = $measurement->measure->name;
                                $single_measure_update['deleted_at'] = $measurement->deleted_at;
                                $update_measure[] = $single_measure_update;
                            }
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
            if($update){
                $itemInstance = new RequisitionItem;
                $index = 'id';
                batch()->update($itemInstance, $update, $index);
            }
            if($update_measure){
                $itemMeasureInstance = new MeasurementDetails;
                $index = 'id';
                batch()->update($itemMeasureInstance, $update_measure, $index);
            }
            if($current_page==1){
                $units = Unit::all();
                $unit_arr = [];
                foreach ($units as $u){
                    $unit_arr[$u->id] = $u->name;
                }
                $insert_unit = [];
                $update_unit = [];
                foreach($data->units as $unit){
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
            }
            DB::commit();
            $response = true;
        }catch (\Exception $exception){
            DB::rollBack();
            $response = false;
            Log::info($exception->getMessage());
        }
        return $response;
    }
}
