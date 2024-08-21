<?php

namespace App\Http\Controllers;

use App\Exports\Report\RequisitionExport;
use App\Exports\Report\RequisitionReportExport;
use App\Http\Requests\requisition\RequestRequisition;
use App\Models\Department;
use App\Models\MeasurementDetails;
use App\Models\OfficeDivision;
use App\Models\Promotion;
use App\Models\Requisition;
use App\Models\RequisitionDetails;
use App\Models\RequisitionItem;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;

class RequisitionController extends Controller
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
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "items" => RequisitionItem::orderBy("name")->get(),
        );

        $items = Requisition::with("department", "appliedBy", "details.item")->orderByDesc("id")->paginate(\Functions::getPaginate());

        return view('requisition.index', compact('items', 'data'));
    }


    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();

        $data = Requisition::
        select([
            "requisitions.id",
            "requisitions.applied_by",
            "requisitions.approved_by",
            "requisitions.department_id",
            "requisitions.applied_date",
            "requisitions.status",
            "requisitions.serve_status",
        ])
            ->with([
                "department",
                "appliedBy",
            ])
            ->whereIn('requisitions.department_id', FilterController::getDepartmentIds())
            ->orderByDesc("requisitions.id");

        if (!is_null($request->daterangepicker)) {
            $dateRangePicker = \Functions::dateRangePicker($request->daterangepicker, " / ");
            $fromDate = Carbon::parse($dateRangePicker["start_date"]);
            $toDate = Carbon::parse($dateRangePicker["end_date"]);
            $data->whereDateBetween($fromDate, $toDate);
        }

        if (!is_null($request->input("status_id"))) {
            $data->where("status", $request->input("status_id"));
        }

        if (!is_null($request->input("serve_status"))) {
            $data->where("serve_status", $request->input("serve_status"));
        }


        return DataTables::eloquent($data)
            ->addColumn('action', function (Requisition $obj) use ($authUser) {

                $str = "";


                if (!$obj->serve_status) {
                    if ($authUser->can("Edit Requisition")) {
                        $str .= '<a href="' . route('requisition.edit', ['requisition' => $obj->id]) . '"><i class="fa fa-edit" style="color: green"></i></a> ||';
                    }

                } else {
                    if ($authUser->can("Edit Requisition") && $obj->status != \App\Models\Requisition::STATUS_DELIVERED) {
                        $str .= '<a title="Deliver this requisition" href="' . route('requisition.changeStatus', ['requisition' => $obj->id]) . '">
                                        <i class="fas fa-clipboard-check" style="color: green"></i>
                                    </a> ||';
                    }

                }

                if ($authUser->can("Download Requisition")) {
                    $str .= '<a href = "' . route('requisition.download', ['requisition' => $obj->id]) . '" ><i class="fa fa-download" style = "color: green" ></i ></a >';
                }

                if (!$obj->serve_status && $authUser->can("Delete Requisition")) {
                    $delteUrl = "'" . route('requisition.delete', ['requisition' => $obj->id]) . "'";
                    $str .= '|| <a href="#" onclick="deleteAlert(' . $delteUrl . ')" ><i class="fa fa-trash" style="color: red"></i></a>';
                }


                return $str;
            })
            ->editColumn('status', function (Requisition $obj) use ($authUser) {
                $priority = ["Today", "Within 3 days", "Within 7 days", "Within 10 days"];
                $status = ["New", "In Progress", "Delieverd", "Rejected", "Received"];
                return isset($status[$obj->status]) ? $status[$obj->status] : 'N/A';
            })
            ->editColumn('serve_status', function (Requisition $obj) use ($authUser) {
                $status = ["Not Submitted", "Submitted"];
                return isset($status[$obj->serve_status]) ? $status[$obj->serve_status] : 'N/A';
            })
            ->addColumn('order_id', function (Requisition $obj) use ($authUser) {
                return '<a href="#" data-toggle="modal" data-target="#requisitionModal" data-id="' . $obj->id . '" onclick="showDetails(this)">' . $obj->id . '</a>';
            })
            ->addColumn('is_checked', function (Requisition $obj) use ($authUser) {
                if ($obj->status == 1 && $obj->serve_status == 0) {
                    return '<input type="checkbox" class="checkbox" data-id="' . $obj->id . '" onchange="setItemChecked(this)">';
                }
            })
            ->editColumn('applied_date', function (Requisition $obj) use ($authUser) {
                return $obj->applied_date;
            })
            ->rawColumns(['action', 'order_id', 'is_checked'])
            ->toJson();
    }


    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $data = [
            "departments" => Department::orderByDesc("name")->whereIn('id', FilterController::getDepartmentIds())->get(),
            "requisitionItems" => RequisitionItem::whereIn("parent_id", [182, 227])->orderBy("name")->get(),
        ];
        return view("requisition.create", compact("data"));
    }

    /**
     * @param Requisition $requisition
     * @return Application|Factory|View
     */
    public function edit(Requisition $requisition)
    {
        $data = [
            "departments" => Department::orderByDesc("name")->whereIn('id', FilterController::getDepartmentIds())->get(),
            "requisitionItems" => RequisitionItem::with('itemMeasurements')->whereIn("parent_id", [182, 227])->orderBy("name")->get(),
            "requisition" => $requisition->load("details.item")
        ];

        return view("requisition.edit", compact("data"));
    }

    /**
     * @param RequestRequisition $request
     * @return RedirectResponse
     */
    public function store(RequestRequisition $request)
    {
        try {
            DB::transaction(function () use ($request) {
                # Requisition
                $requisition = Requisition::create([
                    "department_id" => $request->input("department_id"),
                    "applied_by" => auth()->user()->id,
                    "applied_date" => today(),
                    "priority" => $request->input("priority"),
                    "status" => Requisition::STATUS_NEW,
                    "remarks" => $request->input("remarks")
                ]);

                # Requisition Details
                $requisitionDetails = [];
                foreach ($request->input("requisition_item_id") as $key => $item) {
                    if (!is_null($request->input("requisition_item_id")[$key]) && !is_null($request->input("quantity")[$key])) {
                        $detail['requisition_id'] = $requisition->id;
                        $detail['requisition_item_id'] = $item;
                        $detail['quantity'] = $request->input("quantity")[$key];
                        $detail['unit_id'] = RequisitionItem::where('id', $item)->first()->unit_id;
                        $detail['measure_id'] = $request->input("requisition_item_measure_id")[$key];
                        $requisitionDetails[] = $detail;
                    }
                }
                $requisition->details()->createMany($requisitionDetails);
            });

            session()->flash('message', 'Requisition Created Successfully');
            $redirect = redirect()->route("requisition.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestRequisition $request
     * @param Requisition $requisition
     * @return RedirectResponse
     */
    public function update(RequestRequisition $request, Requisition $requisition)
    {
        $requisition->load("details");
        try {
            DB::transaction(function () use ($requisition, $request) {
                if ($request->input("status") != $requisition->status) {
                    activity('requisition-updated')->by(auth()->user())->log('Requisition status has been changed');
                }

                # Requisition
                $requisition->update([
                    "department_id" => $request->input("department_id"),
                    "priority" => $request->input("priority"),
                    "status" => $request->input("status"),
                    "remarks" => $request->input("remarks")
                ]);

                # Requisition Details
                $requisitionDetails = [];
                foreach ($request->input("requisition_item_id") as $key => $item) {
                    if (!is_null($request->input("requisition_item_id")[$key]) && !is_null($request->input("quantity")[$key])) {
                        $detail['requisition_id'] = $requisition->id;
                        $detail['requisition_item_id'] = $item;
                        $detail['quantity'] = $request->input("quantity")[$key];
                        $detail['unit_id'] = RequisitionItem::where('id', $item)->first()->unit_id;
                        $detail['measure_id'] = $request->input("requisition_item_measure_id")[$key];
                        $requisitionDetails[] = $detail;
                    }
                }
                $requisition->details()->delete();
                $requisition->details()->createMany($requisitionDetails);
            });

            session()->flash('message', 'Requisition Updated Successfully');
            $redirect = redirect()->route("requisition.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Requisition $requisition
     * @return RedirectResponse
     */
    public function delete(Requisition $requisition)
    {
        try {
            $feedback['status'] = $requisition->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param Requisition $requisition
     * @return mixed
     */
    public function download(Requisition $requisition)
    {
        activity('requisition-download')->by(auth()->user())->log('Pay Slip has been exported');

        $data["requisition"] = $requisition->load("department", "appliedBy", "details.item");

        $fileName = $requisition->department->name . "_" . date("M d, Y") . ".pdf";

        $pdf = PDF::loadView('requisition.download', compact("data"));
        return $pdf->download($fileName, 'requisition.download', compact("data"));
    }

    /**
     * @return RedirectResponse|BinaryFileResponse
     */
    public function exportCSV()
    {
        try {
            $request = \request();
            $dateRangePicker = $request->input("daterangepicker");
            $status = $request->input("status_id");
            if ($request->operation == 'send') {
                $items = Requisition::with('details', 'approvedBy', 'appliedBy', 'department')
                    ->join('requisition_details','requisition_details.requisition_id','=','requisitions.id')
                    ->whereIn('requisitions.id', $request->ids)
                    ->where('requisitions.status', '=', '1')
                    ->where('requisitions.serve_status', '=', 0);
                $items->select('requisitions.*', 'requisitions.created_at as created_date',DB::raw('COUNT(requisition_details.id) as total_items'));
                if ($status == 0) {
                    $items->addSelect(DB::raw("'New' as status_in_word"));
                } elseif ($status == 1) {
                    $items->addSelect(DB::raw("'In Progress' as status_in_word"));
                } elseif ($status == 2) {
                    $items->addSelect(DB::raw("'Delivered' as status_in_word"));
                } elseif ($status == 3) {
                    $items->addSelect(DB::raw("'Received' as status_in_word"));
                } else {
                    $items->addSelect(DB::raw("'Rejected' as status_in_word"));
                }
                $items->groupBy('requisitions.id');
                $post_items = $items->get();
                $response['success'] = true;
                $response['message'] = 'Requisition Items sent Successfully';
                if ($post_items->count()) {
                    if (Cache::has('item_token')) {
                        $token = Cache::get('item_token');
                        $token_type = 'Bearer';
                        $response = $this->postData($token_type, $token, $post_items);
                    } else {
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
                        $url = env('WHMS_URL') . '/api/login';
                        $method = 'POST';
                        $res = curlrequest($url, $headers, $method, $params);
                        Log::info(json_encode($res));
                        $result = $res['result'];
                        $error_status = $res['error_status'];
                        if (!$error_status) {
                            $arr = json_decode($result);
                            if ($arr->status) {
                                $token = $arr->data->access_token;
                                $token_type = $arr->data->token_type;
                                $pos = strpos($token, "|");
                                $token = substr($token, $pos + 1);
                                Cache::put('item_token', $token);
                                $response = $this->postData($token_type, $token, $post_items);
                            } else {
                                $response['success'] = false;
                                $response['message'] = 'Something went wrong! Please try again.';
                            }
                        } else {
                            $response['success'] = false;
                            $response['message'] = 'Something went wrong! Please try again.';
                        }
                    }
                }
                if ($response['success']) {
                    return response()->json(['status' => 'success', 'message' => $response['message']]);
                } else {
                    return response()->json(['status' => 'error', 'message' => $response['message']]);
                }

            } else {

                if (!is_null($dateRangePicker)) {
                    $dateRangePicker = \Functions::dateRangePicker($dateRangePicker, " / ");
                    $fromDate = Carbon::parse($dateRangePicker["start_date"]);
                    $toDate = Carbon::parse($dateRangePicker["end_date"]);
                    $query = Requisition::whereDateBetween($fromDate, $toDate)
                        ->where("status", $request->input("status_id"));
                } else {
                    $query = Requisition::where("status", $request->input("status_id"));
                }

                if (!is_null($request->input("serve_status"))) {
                    $query->where("serve_status", $request->input("serve_status"));
                }
                $requisitionIds = $query->pluck("id");
                $items = RequisitionDetails::with("item")
                    ->groupBy("requisition_item_id")
                    ->whereIn("requisition_id", $requisitionIds)
                    ->select([DB::raw("requisition_item_id, SUM(quantity) as total_quantity")])
                    ->get();
                $response = Excel::download(new RequisitionReportExport($items), "requisition-report.csv");
            }
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
            $response = redirect()->back();
        }
        return $response;
    }

    public function postData($token_type, $token, $post_items)
    {
        try {
            Log::info(json_encode($post_items));
            $headers_next = array
            (
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: ' . $token_type . ' ' . $token,
            );
            $url = env('WHMS_URL') . '/api/requisition/post-data';
            $method = 'POST';
            $res = curlrequest($url, $headers_next, $method, $post_items);
            Log::info(json_encode($res));
            $response = [];
            $result = $res['result'];
            $error_status = $res['error_status'];
            if (!$error_status) {
                $arr = json_decode($result);
                if (isset($arr->status) && $arr->status) {
                    if ($arr->data) {
                        DB::table('requisitions')->whereIn('id', $arr->data)->update(['serve_status' => 1]);
                    }
                    $response['success'] = true;
                    $response['message'] = 'Requisition Items sent Successfully';
                } else {
                    Cache::forget('item_token');
                    $response['success'] = false;
                    $response['message'] = $arr->message;
                }
            } else {
                Cache::forget('item_token');
                $response['success'] = false;
                $response['message'] = 'Something went wrong! Please try again.';
            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            $response['success'] = false;
            $response['message'] = $exception->getMessage();
        }
        return $response;
    }

    /**
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function searchByChallan()
    {
        $data = [
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
        ];

        $request = \request();
        $items = Requisition::with("department", "appliedBy", "details.item")->where("id", $request->get("challan"))->paginate(\Functions::getPaginate());

        return view('requisition.index', compact('items', 'data'));
    }

    public function findMeasurement(Request $request)
    {
        return response()->json(MeasurementDetails::where('requisition_item_id', '=', $request->item_id)->where('deleted_at', NULL)->get());
    }

    public function changeStatus(Request $request)
    {
        try {
            DB::table('requisitions')->where('id', $request->requisition)->update(['status' => Requisition::STATUS_DELIVERED]);
            session()->flash('message', 'Requisition delivered successfully!');
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
        }
        return redirect()->back();
    }

    public function getDetails(Request $request)
    {
        $sts = "error";
        $data = Requisition::with(['details', 'details.item', 'department'])->find($request->id);
        if ($data) {

            $priority = ["Today", "Within 3 days", "Within 7 days", "Within 10 days"];
            $status = ["New", "In Progress", "Delieverd", "Rejected", "Received"];

            $sts = "success";

            $data->appliedDate = $data->applied_date;

            $data->status = isset($status[$data->status]) ? $status[$data->status] : 'N/A';

            $data->priority = isset($priority[$data->priority]) ? $priority[$data->priority] : 'N/A';

        }
        return response()->json([
            'status' => $sts,
            'data' => $data,
        ]);
    }
}
