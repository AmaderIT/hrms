<?php

namespace App\Http\Controllers;

use App\Http\Requests\bank\RequestBank;
use App\Http\Requests\internalTransfer\RequestInternalTransfer;
use App\Jobs\ChallanPassToWarehouse;
use App\Models\Bank;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\InternalTransfer;
use App\Models\InternalTransferDeptToWare;
use App\Models\InternalTransferDeptToWareReturn;
use App\Models\InternalTransferItems;
use App\Models\InternalTransferSourceWarehouseReject;
use App\Models\MeasurementDetails;
use App\Models\OfficeDivision;
use App\Models\OtherRequisitionItem;
use App\Models\Promotion;
use App\Models\RequisitionItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

//use Illuminate\View\View;
use Exception;
use Intervention\Image\Facades\Image;
use PDF;
use View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;


class InternalTransferController extends Controller
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
    public function index(Request $request)
    {
        if((auth()->user()->isAdminUser() === true) || (auth()->user()->can('View All Departments Internal Transfer'))){
            $data = array(
                "officeDivisions" => OfficeDivision::select("id", "name")
                    ->whereIn('id', FilterController::getDivisionIds(true))
                    ->get()
            );
        }else{
            $data = array(
                "officeDivisions" => OfficeDivision::select("id", "name")
                    ->whereIn('id', FilterController::getDivisionIds())
                    ->get()
            );
        }
        if (request()->ajax()) {

            $today=date('Y-m-d');
            $challan_status = $request->challan_status ?? null;
            $return_status = $request->return_status ?? null;
            $office_division_id = $request->office_division_id ?? null;
            $department_id = $request->department_id ?? null;
            $status = $request->status ?? null;
            $items = InternalTransfer::with([
                    "sourceDepartment",
                    "sourceWarehouse",
                    "sourceSupplier",
                    "destinationDepartment",
                    "destinationWarehouse",
                    "destinationSupplier",
                    "preparedBy",
                    "authorizedBy",
                    "securityCheckedInBy",
                    "securityCheckedOutBy",
                    "rejectedBy",
                    "receivedBy",
                    "parents",
                    "sourceDepartment.officeDivision",
                    "destinationDepartment.officeDivision"
                ]
            );
            if($department_id){
                $items->where(function ($query) use ($department_id) {
                    $query->where('internal_transfers.source_department_id', '=', $department_id);
                    $query->orWhere('internal_transfers.destination_department_id', '=', $department_id);
                });
                $departmentIds = $this->getDepartmentSupervisorIds();
            }else{
                if($office_division_id){
                    if((auth()->user()->isAdminUser() === true) || (auth()->user()->can('View All Departments Internal Transfer')) || (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department'))){
                        $departmentIds = Department::where("office_division_id", '=', $office_division_id)->pluck('id')->toArray();
                        $items->where(function ($query) use ($departmentIds) {
                            $query->whereIn('internal_transfers.source_department_id', $departmentIds);
                            $query->orWhere(function ($query) use ($departmentIds) {
                                $query->whereIn('internal_transfers.destination_department_id', $departmentIds);
                            });
                        });
                    }else{
                        $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $office_division_id)
                            ->pluck('department_id')->toArray();
                        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $office_division_id)
                            ->pluck('office_division_id')->toArray();
                        $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                        $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                        $items->where(function ($query) use ($departmentIds) {
                            $query->whereIn('internal_transfers.source_department_id', $departmentIds);
                            $query->orWhere(function ($query) use ($departmentIds) {
                                $query->whereIn('internal_transfers.destination_department_id', $departmentIds);
                            });
                        });
                    }
                }else{
                    $user_id = Auth::id();
                    $current_department = DB::select("SELECT promotions.department_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = $user_id AND pm.promoted_date <= '$today' ) WHERE users.`status` = 1");
                    $department_id = $current_department[0]->department_id ?? '';
                    $departmentIds = $this->getDepartmentSupervisorIds();
                    if ((Auth::user()->email != 'admin@byslglobal.com') && !(auth()->user()->can('View All Departments Internal Transfer')) && !(auth()->user()->can('Show All Office Division')) && !(auth()->user()->can('Show All Department'))) {
                        if ($departmentIds) {
                            $items->where(function ($query) use ($departmentIds) {
                                $query->whereIn('internal_transfers.source_department_id', $departmentIds);
                                $query->orWhere(function ($query) use ($departmentIds) {
                                    $query->whereIn('internal_transfers.destination_department_id', $departmentIds);
                                });
                            });
                        } else {
                            $items->where(function ($query) use ($department_id) {
                                $query->where('internal_transfers.source_department_id', '=', $department_id);
                                $query->orWhere('internal_transfers.destination_department_id', '=', $department_id);
                            });
                        }
                    }
                }
            }
            if (!is_null($challan_status)) {
                if ($challan_status != 'all') {
                    $items->where('internal_transfers.challan_status', '=', $challan_status);
                }
            }
            if (!is_null($return_status)) {
                $items->where('internal_transfers.return_status', '=', $return_status);
            }
            if (!is_null($status)) {
                if ($status == InternalTransfer::OPERATION_CREATED || $status == InternalTransfer::OPERATION_REJECT || $status == InternalTransfer::OPERATION_AUTHORIZED) {
                    $items->where('internal_transfers.status', '=', $status);
                } else {
                    $items->where('internal_transfers.status', '>=', $status);
                    $items->where('internal_transfers.status', '<>', InternalTransfer::OPERATION_REJECT);
                }
            }
            if (is_null($challan_status) && is_null($return_status)) {
                $items->where(function ($query) {
                    $query->where('internal_transfers.challan_status', '=', CHALLAN_OPEN);
                    $query->orWhere('internal_transfers.return_status', '=', RETURN_PENDING);
                });
            }
            if (isset($request->from_date) && isset($request->to_date)) {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));
                $items->whereDate('internal_transfers.issue_at', '>=', $from_date)
                    ->whereDate('internal_transfers.issue_at', '<=', $to_date);
            }
            $items->where("internal_transfers.type", InternalTransfer::OPERATION_TYPE_CHALLAN)->where("internal_transfers.deleted_at", '=', null)
                ->select("internal_transfers.*")
                ->orderBy(DB::raw("DATE(internal_transfers.issue_at)"), "desc")->orderBy("internal_transfers.challan_status");
            return datatables($items)
                ->editColumn('challan', function ($item) {
                    return '<span style="color:green;cursor:pointer;" class="viewModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getDetails') . '">
                                           ' . str_pad($item->challan, 7, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('parent_challan', function ($item) {
                    $parent_challan = '';
                    if (!empty($item->parent_id)) {
                        $parent_challan = '<span style="color:green;cursor:pointer;" class="viewModal_link" data-id="' . $item->parent_id . '" data-href="' . route('internal-transfer.getDetails') . '">
                                           ' . str_pad($item->parents->challan, 7, '0', STR_PAD_LEFT) . '</span>';
                    }
                    return $parent_challan;
                })
                ->addColumn('issued_on', function ($item) {
                    return date('d-m-Y', strtotime($item->issue_at));
                })
                ->addColumn('source_division', function ($item) {
                    if (isset($item->sourceDepartment->name)) {
                        return optional($item->sourceDepartment->officeDivision)->name;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('source', function ($item) {
                    if (isset($item->sourceWarehouse->name)) {
                        return $item->sourceWarehouse->name;
                    } elseif (isset($item->sourceDepartment->name)) {
                        return $item->sourceDepartment->name;
                    } elseif (isset($item->sourceSupplier->name)) {
                        return $item->sourceSupplier->name;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('destination_division', function ($item) {
                    if (isset($item->destinationDepartment->name)) {
                        return optional($item->destinationDepartment->officeDivision)->name;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('destination', function ($item) {
                    if (isset($item->destinationWarehouse->name)) {
                        return $item->destinationWarehouse->name;
                    } elseif (isset($item->destinationDepartment->name)) {
                        return $item->destinationDepartment->name;
                    } elseif (isset($item->destinationSupplier->name)) {
                        return $item->destinationSupplier->name;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('return_status', function ($item) {
                    return getReturnStatus($item->return_status);
                })
                ->editColumn('status', function ($item) {
                    if ($item->status == 1) {
                        return '<span class="badge badge-info">Prepared</span>';
                    } elseif ($item->status == 2) {
                        return '<span class="badge badge-primary">Authorized</span>';
                    } elseif ($item->status == 3) {
                        return '<span class="badge badge-secondary">Security Checked Out</span>';
                    } elseif ($item->status == 4) {
                        return '<span class="badge badge-dark">Security Checked In</span>';
                    } elseif ($item->status == 5) {
                        return '<span class="badge badge-success">Received</span>';
                    } elseif ($item->status == 6) {
                        return '<span class="badge badge-danger">Rejected</span>';
                    }
                })
                ->editColumn('challan_status', function ($item) {
                    return getChallanStatus($item->challan_status);
                })
                ->addColumn('action', function ($item) use ($department_id,$departmentIds) {
                    $html = '<div class="row">';
                    if (($item->workflow_type == GENERAL_WORKFLOW) && !($item->dept_to_ware) && ($item->status == 5) && (auth()->user()->can('Return Internal Transfer'))
                        && $item->is_returnable && $item->challan_status == CHALLAN_PENDING_RETURN && (($department_id == $item->destination_department_id) || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($item->destination_department_id,$departmentIds) ))) {
                        $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Return">
                                           <a href="#" class="returnModal_link" data-toggle="modal"
                                              data-target="#returnModal" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanReturnView') . '">
                                               <i class="fa fa-undo" aria-hidden="true" style="color: orange;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                    } elseif (($item->workflow_type == VENDOR_WORKFLOW) && ($item->status == 3) && (auth()->user()->can('Return Internal Transfer')) && $item->is_returnable && $item->challan_status == CHALLAN_PENDING_RETURN) {
                        $html .= '<div class="ml-2">
                                   <span data-toggle="tooltip" data-placement="bottom" title="Return">
                                       <a href="#" class="returnModal_link" data-toggle="modal"
                                          data-target="#returnModal" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanReturnView') . '">
                                           <i class="fa fa-undo" aria-hidden="true" style="color: orange;font-size: 1.4em !important;padding: 1px;"></i>
                                       </a>
                                   </span>
                                </div>';
                    }
                    if (($item->workflow_type == GENERAL_WORKFLOW) && ($item->status != 5) && ($item->status != 6) &&
                        (auth()->user()->can('Can Internal Transfer Approve'))) {
                        if (($item->status == 1) && auth()->user()->can('Authorize Internal Transfer') && ($department_id == $item->source_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($item->source_department_id,$departmentIds) ))) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        } elseif ($item->status == 2 && auth()->user()->can('Security CheckOut Internal Transfer')) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        } elseif ($item->status == 3 && auth()->user()->can('Security CheckIn Internal Transfer') && !$item->dept_to_ware) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        } elseif ($item->status == 4 && auth()->user()->can('Receive Internal Transfer') && !$item->dept_to_ware && ($department_id == $item->destination_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($item->destination_department_id,$departmentIds) ))) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        }
                    } elseif (($item->workflow_type == VENDOR_WORKFLOW) && ($item->status != 3) &&
                        ($item->status != 6) && $item->is_return_challan == 0 && (auth()->user()->can('Can Internal Transfer Approve'))) {
                        if (($item->status == 1) && auth()->user()->can('Authorize Internal Transfer')) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        } elseif ($item->status == 2 && auth()->user()->can('Security CheckOut Internal Transfer')) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        }
                    } elseif (($item->workflow_type == VENDOR_WORKFLOW) && ($item->status != 5) && ($item->status != 6) &&
                        $item->is_return_challan == 1 && (auth()->user()->can('Can Internal Transfer Approve'))) {
                        if (($item->status == 1) && auth()->user()->can('Security CheckIn Internal Transfer')) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        } elseif ($item->status == 4 && auth()->user()->can('Receive Internal Transfer')) {
                            $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Approval">
                                           <a href="#" class="approvalModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getChallanApprovalView') . '">
                                               <i class="fa fa-check-circle" style="color: #528505;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                        }
                    }
                    if (auth()->user()->can('Detail Internal Transfer')) {
                        $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Detail view">
                                           <a href="#" class="viewModal_link" data-id="' . $item->id . '" data-href="' . route('internal-transfer.getDetails') . '">
                                               <i class="fa fa-info-circle" style="color: grey;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                    }

                    if (($item->workflow_type == GENERAL_WORKFLOW) && $item->status < 2 && (!$item->is_return_challan) && ($department_id == $item->source_department_id || (Auth::user()->email == 'admin@byslglobal.com') || (in_array($item->source_department_id,$departmentIds))) && (auth()->user()->can("Edit Internal Transfer"))) {
                        $html .= '<div class="ml-2">
                                        <span data-toggle="tooltip" data-placement="bottom" title="Edit">
                                            <a href="' . route('internal-transfer.edit', ['internalTransfer' => $item->id]) . '"
                                                data-toggle="tooltip" data-placement="bottom" title="Edit">
                                                <i class="fa fa-edit" style="color: lightskyblue;font-size: 1.4em !important;padding: 1px;"></i>
                                            </a>
                                        </span>
                                    </div>';
                    }elseif(($item->workflow_type == VENDOR_WORKFLOW) && ($item->status < 2) && (!$item->is_return_challan) && (auth()->user()->can("Edit Internal Transfer"))){
                        $html .= '<div class="ml-2">
                                        <span data-toggle="tooltip" data-placement="bottom" title="Edit">
                                            <a href="' . route('internal-transfer.edit', ['internalTransfer' => $item->id]) . '"
                                                data-toggle="tooltip" data-placement="bottom" title="Edit">
                                                <i class="fa fa-edit" style="color: lightskyblue;font-size: 1.4em !important;padding: 1px;"></i>
                                            </a>
                                        </span>
                                    </div>';
                    }
                    if (auth()->user()->can('Print Internal Transfer')) {
                        $html .= '<div class="ml-2">
                                       <span data-toggle="tooltip" data-placement="bottom" title="Print">
                                           <a target="_blank" href="' . route('internal-transfer.generate-pdf') . '/delivery-challan/' . $item->id . '">
                                               <i class="fa fa-print" style="color: green;font-size: 1.4em !important;padding: 1px;"></i>
                                           </a>
                                       </span>
                                    </div>';
                    }
                    if (($item->workflow_type == GENERAL_WORKFLOW) && ($item->status < 2) && (!$item->is_return_challan) && (in_array($item->source_department_id,$departmentIds)) && (auth()->user()->can("Delete Internal Transfer"))) {
                        $html .= '<div class="ml-2">
                                        <span data-toggle="tooltip" data-placement="bottom" title="Delete">
                                            <a class="delete_challan" href="#" data-href="' . route('internal-transfer.delete', ['internalTransfer' => $item->id]) . '">
                                                <i class="fa fa-trash" style="color: red;font-size: 1.4em !important;padding: 1px;"></i></a>
                                        </span>
                                    </div>';
                    }elseif(($item->workflow_type == VENDOR_WORKFLOW) && ($item->status < 2) && (!$item->is_return_challan) && (in_array($item->source_department_id,$departmentIds)) && (auth()->user()->can("Delete Internal Transfer"))){
                        if ((!$item->is_return_challan) && auth()->user()->can('Delete Internal Transfer')) {
                            $html .= '<div class="ml-2">
                                        <span data-toggle="tooltip" data-placement="bottom" title="Delete">
                                            <a class="delete_challan" href="#" data-href="' . route('internal-transfer.delete', ['internalTransfer' => $item->id]) . '">
                                                <i class="fa fa-trash" style="color: red;font-size: 1.4em !important;padding: 1px;"></i></a>
                                        </span>
                                    </div>';
                        }

                    }
                    if (auth()->user()->can('Download Internal Transfer Attachment')) {
                        $html .= '<form id="download_form_' . $item->id . '" method="POST" action="' . route('internal-transfer.download', ['internalTransfer' => $item->id]) . '">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                </form>';
                        $html .= '<div class="ml-2">
                                        <span data-toggle="tooltip" data-placement="bottom" title="Attachment Download">
                                            <a href="#">
                                                <i class="fa fa-download download_link" data-id="' . $item->id . '" style="color: blue;font-size: 1.4em !important;padding: 1px;"></i></a>
                                        </span>
                                    </div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['challan', 'parent_challan', 'return_status', 'status', 'challan_status', 'action'])
                ->make(true);
        }
        return view('internal-transfer.index',compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse|StreamedResponse
     */
    public function exportExcel(Request $request)
    {
        try {
            $challan_status = $request->challan_status ?? null;
            $return_status = $request->return_status ?? null;
            $status = $request->status ?? null;
            $office_division_id = $request->office_division_id ?? null;
            $department_id = $request->department_id ?? null;
            $conditional_sql1 = "`internal_transfers`.`type` = '" . InternalTransfer::OPERATION_TYPE_CHALLAN . "' AND `internal_transfers`.`deleted_at` IS NULL AND `internal_transfer_items`.`deleted_at` IS NULL";
            $conditional_sql2 = "`internal`.`type` = '" . InternalTransfer::OPERATION_TYPE_CHALLAN . "' AND `internal`.`deleted_at` IS NULL AND `internal_items`.`deleted_at` IS NULL";
            if (!is_null($challan_status)) {
                if ($challan_status != 'all') {
                    $conditional_sql1 .= " AND internal_transfers.challan_status = $challan_status";
                    $conditional_sql2 .= " AND internal.challan_status = $challan_status";
                }
            }
            if (!is_null($return_status)) {
                $conditional_sql1 .= " AND internal_transfers.return_status = $return_status";
                $conditional_sql2 .= " AND internal.return_status = $return_status";
            }
            if (!is_null($status)) {
                if ($status == InternalTransfer::OPERATION_CREATED || $status == InternalTransfer::OPERATION_REJECT || $status == InternalTransfer::OPERATION_AUTHORIZED) {
                    $conditional_sql1 .= " AND internal_transfers.status = $status";
                    $conditional_sql2 .= " AND internal.status = $status";
                } else {
                    $conditional_sql1 .= " AND internal_transfers.status >= $status AND internal_transfers.status <> " . InternalTransfer::OPERATION_REJECT;
                    $conditional_sql2 .= " AND internal.status >= $status AND internal.status <> " . InternalTransfer::OPERATION_REJECT;
                }
            }
            if (is_null($challan_status) && is_null($return_status)) {
                $conditional_sql1 .= " AND ( internal_transfers.challan_status = " . CHALLAN_OPEN . " OR internal_transfers.return_status = " . RETURN_PENDING . " )";
                $conditional_sql2 .= " AND ( internal.challan_status = " . CHALLAN_OPEN . " OR internal.return_status = " . RETURN_PENDING . " )";
            }
            if (isset($request->from_date) && isset($request->to_date)) {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));
                $conditional_sql1 .= " AND DATE(internal_transfers.issue_at) >= '$from_date' AND DATE(internal_transfers.issue_at) <= '$to_date'";
                $conditional_sql2 .= " AND DATE(internal.issue_at) >= '$from_date' AND DATE(internal.issue_at) <= '$to_date'";
            }
            if($department_id){
                $conditional_sql1 .= " AND (
                      ( internal_transfers.source_department_id <> 0 AND internal_transfers.source_department_id = $department_id )
                        OR
                      ( internal_transfers.destination_department_id <> 0 AND internal_transfers.destination_department_id = $department_id )
                    )";
                $conditional_sql2 .= " AND (
                      ( internal.source_department_id <> 0 AND internal.source_department_id = $department_id )
                        OR
                      ( internal.destination_department_id <> 0 AND internal.destination_department_id = $department_id )
                    )";
            }else{
                if($office_division_id){
                    if((auth()->user()->isAdminUser() === true) || (auth()->user()->can('View All Departments Internal Transfer')) || (auth()->user()->can('Show All Office Division') && auth()->user()->can('Show All Department'))){
                        $departmentIds = Department::where("office_division_id", '=', $office_division_id)->pluck('id')->toArray();
                        $departmentIds_in_string = implode(',',$departmentIds);
                        $conditional_sql1 .= " AND (
                      ( internal_transfers.source_department_id <> 0 AND internal_transfers.source_department_id IN ($departmentIds_in_string) )
                        OR
                      ( internal_transfers.destination_department_id <> 0 AND internal_transfers.destination_department_id IN ($departmentIds_in_string) )
                    )";
                        $conditional_sql2 .= " AND (
                      ( internal.source_department_id <> 0 AND internal.source_department_id IN ($departmentIds_in_string) )
                        OR
                      ( internal.destination_department_id <> 0 AND internal.destination_department_id IN ($departmentIds_in_string) )
                    )";
                    }else{
                        $departmentalSupervisorDepartments = DepartmentSupervisor::where('status', DepartmentSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $office_division_id)
                            ->pluck('department_id')->toArray();
                        $divisionalSupervisorDivisions = DivisionSupervisor::where('status', DivisionSupervisor::STATUS_ACTIVE)
                            ->where('supervised_by', auth()->user()->id)
                            ->where("office_division_id", '=', $office_division_id)
                            ->pluck('office_division_id')->toArray();
                        $divisionalSupervisorDepartments = Department::whereIn('office_division_id', $divisionalSupervisorDivisions)->pluck('id')->toArray();
                        $departmentIds = array_unique(array_merge($divisionalSupervisorDepartments, $departmentalSupervisorDepartments));
                        $departmentIds_in_string = implode(',',$departmentIds);
                        $conditional_sql1 .= " AND (
                      ( internal_transfers.source_department_id <> 0 AND internal_transfers.source_department_id IN ($departmentIds_in_string) )
                        OR
                      ( internal_transfers.destination_department_id <> 0 AND internal_transfers.destination_department_id IN ($departmentIds_in_string) )
                    )";
                        $conditional_sql2 .= " AND (
                      ( internal.source_department_id <> 0 AND internal.source_department_id IN ($departmentIds_in_string) )
                        OR
                      ( internal.destination_department_id <> 0 AND internal.destination_department_id IN ($departmentIds_in_string) )
                    )";
                    }
                }else{
                    if ((auth()->user()->isAdminUser() === false) && !(auth()->user()->can('View All Departments Internal Transfer')) && !(auth()->user()->can('Show All Office Division')) && !(auth()->user()->can('Show All Department'))) {
                        $department_id = Promotion::where('user_id', '=', Auth::id())->orderBy('id', 'desc')->first()->department_id;
                        $conditional_sql1 .= " AND (
                      ( internal_transfers.source_department_id <> 0 AND internal_transfers.source_department_id = $department_id )
                        OR
                      ( internal_transfers.destination_department_id <> 0 AND internal_transfers.destination_department_id = $department_id )
                    )";
                        $conditional_sql2 .= " AND (
                      ( internal.source_department_id <> 0 AND internal.source_department_id = $department_id )
                        OR
                      ( internal.destination_department_id <> 0 AND internal.destination_department_id = $department_id )
                    )";
                    }
                }
            }
            $order_sql1 = "DATE(internal_transfers.issue_at) DESC, `internal_transfers`.`challan_status` ASC";
            $order_sql2 = "DATE(internal.issue_at) DESC, `internal`.`challan_status` ASC";
            $sql1 = "( SELECT
                        `internal_transfers`.*,
                        `internal_transfer_items`.`operation_type`,
                        `internal_transfer_items`.`item_id`,
                        `internal_transfer_items`.`qty`,
                        `internal_transfer_items`.`uom`,
                        `internal_transfer_items`.`remarks`,
                         '' AS ref_challan,
                         departments.`name` AS source_department_name,
                         des_departments.`name` AS destination_department_name,
                         suppliers.`name` AS source_supplier_name,
                         des_suppliers.`name` AS destination_supplier_name,
                         users.`name` as prepared_name, users.`fingerprint_no` as prepared_fingerprint_no,
                         deliver.`name` as delivered_name, deliver.`fingerprint_no` as delivered_fingerprint_no,
                         authorize.`name` as authorized_name, authorize.`fingerprint_no` as authorized_fingerprint_no,
                         checkout.`name` as checkout_name, checkout.`fingerprint_no` as checkout_fingerprint_no,
                         checkin.`name` as checkin_name, checkin.`fingerprint_no` as checkin_fingerprint_no,
                         receive.`name` as receive_name, receive.`fingerprint_no` as receive_fingerprint_no,
                         reject.`name` as rejected_name, reject.`fingerprint_no` as rejected_fingerprint_no,
                         IF
                            (`internal_transfer_items`.`item_type` = 'whms',
                            (
                            SELECT
                                CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                            FROM
                                requisition_items
                            WHERE
                                id = `internal_transfer_items`.`item_id`
                            ),
                            ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_transfer_items`.`item_id` )
                            ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                    FROM
                        `internal_transfers`
                        INNER JOIN `internal_transfer_items` ON `internal_transfer_items`.`internal_transfer_id` = `internal_transfers`.`id`
                        LEFT JOIN `departments` ON `departments`.`id` = `internal_transfers`.`source_department_id`
                        LEFT JOIN `departments` AS `des_departments` ON `des_departments`.`id` = `internal_transfers`.`destination_department_id`
                        LEFT JOIN `suppliers` ON `suppliers`.`id` = `internal_transfers`.`from_supplier_id`
                        LEFT JOIN `suppliers` AS `des_suppliers` ON `des_suppliers`.`id` = `internal_transfers`.`to_supplier_id`
                        LEFT JOIN `users` ON `users`.`id` = `internal_transfers`.`created_by`
                        LEFT JOIN `users` AS `deliver` ON `deliver`.`id` = `internal_transfers`.`delivered_by`
                        LEFT JOIN `users` AS `authorize` ON `authorize`.`id` = `internal_transfers`.`authorized_by`
                        LEFT JOIN `users` AS `checkout` ON `checkout`.`id` = `internal_transfers`.`dispatch_security_checked`
                        LEFT JOIN `users` AS `checkin` ON `checkin`.`id` = `internal_transfers`.`receive_security_checked`
                        LEFT JOIN `users` AS `receive` ON `receive`.`id` = `internal_transfers`.`received_by`
                        LEFT JOIN `users` AS `reject` ON `reject`.`id` = `internal_transfers`.`rejected_by`
                        LEFT JOIN `units` ON `units`.`id` = `internal_transfer_items`.`uom`
                        LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_transfer_items.measure_id
                    WHERE $conditional_sql1
                    ORDER BY $order_sql1 )";

            $sql2 = "( SELECT
                        `internal`.*,
                        `internal_items`.`operation_type`,
                        `internal_items`.`item_id`,
                        `internal_items`.`qty`,
                        `internal_items`.`uom`,
                        `internal_items`.`remarks`,
                        (SELECT challan FROM internal_transfers AS it where it.id=internal.parent_id) AS ref_challan,
                         source_departments.`name` AS source_department_name,
                         destination_departments.`name` AS destination_department_name,
                         source_suppliers.`name` AS source_supplier_name,
                         destination_suppliers.`name` AS destination_supplier_name,
                         `prepare`.`name` as prepared_name, `prepare`.`fingerprint_no` as prepared_fingerprint_no,
                         deliver1.`name` as delivered_name, deliver1.`fingerprint_no` as delivered_fingerprint_no,
                         authorize1.`name` as authorized_name, authorize1.`fingerprint_no` as authorized_fingerprint_no,
                         checkout1.`name` as checkout_name, checkout1.`fingerprint_no` as checkout_fingerprint_no,
                         checkin1.`name` as checkin_name, checkin1.`fingerprint_no` as checkin_fingerprint_no,
                         receive1.`name` as receive_name, receive1.`fingerprint_no` as receive_fingerprint_no,
                         reject1.`name` as rejected_name, reject1.`fingerprint_no` as rejected_fingerprint_no,
                         IF
                            (`internal_items`.`item_type` = 'whms',
                            (
                            SELECT
                                CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                            FROM
                                requisition_items
                            WHERE
                                id = `internal_items`.`item_id`
                            ),
                            ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_items`.`item_id` )
                            ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                    FROM
                        `internal_transfers` as internal
                        INNER JOIN `internal_transfer_items` AS internal_items ON `internal_items`.`internal_transfer_id` = `internal`.`parent_id`
                        LEFT JOIN `departments` AS `source_departments` ON `source_departments`.`id` = `internal`.`source_department_id`
                        LEFT JOIN `departments` AS `destination_departments` ON `destination_departments`.`id` = `internal`.`destination_department_id`
                        LEFT JOIN `suppliers` AS `source_suppliers` ON `source_suppliers`.`id` = `internal`.`from_supplier_id`
                        LEFT JOIN `suppliers` AS `destination_suppliers` ON `destination_suppliers`.`id` = `internal`.`to_supplier_id`
                        LEFT JOIN `users` AS `prepare` ON `prepare`.`id` = `internal`.`created_by`
                        LEFT JOIN `users` AS `deliver1` ON `deliver1`.`id` = `internal`.`delivered_by`
                        LEFT JOIN `users` AS `authorize1` ON `authorize1`.`id` = `internal`.`authorized_by`
                        LEFT JOIN `users` AS `checkout1` ON `checkout1`.`id` = `internal`.`dispatch_security_checked`
                        LEFT JOIN `users` AS `checkin1` ON `checkin1`.`id` = `internal`.`receive_security_checked`
                        LEFT JOIN `users` AS `receive1` ON `receive1`.`id` = `internal`.`received_by`
                        LEFT JOIN `users` AS `reject1` ON `reject1`.`id` = `internal`.`rejected_by`
                        LEFT JOIN `units` ON `units`.`id` = `internal_items`.`uom`
                        LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_items.measure_id
                    WHERE $conditional_sql2
                    ORDER BY $order_sql2 )";
            $sql = $sql1 . ' UNION ALL ' . $sql2;
            $items = DB::select($sql);
            $spreadsheet = new Spreadsheet();
            $col = 1;
            $header = array(
                'Challan No.',
                'Reference',
                'Ref. Challan No.',
                'Issue Date',
                'Transferred From',
                'Transferred To',
                'Item Name',
                'Variant',
                'UOM',
                'Quantity',
                'Item Remarks',
                'Returnable',
                'Type',
                'Return Status',
                'Approve Status',
                'Status',
                'Remarks',
                'Prepared By',
                'Authorized By',
                'Security Checked Out',
                'Gate Pass No.',
                'Checkout Time',
                'Security Checked In',
                'Gate Pass No.',
                'Checkin Time',
                'Received By',
                'Delivered By',
                'Rejected By'
            );
            foreach ($header as $val) {
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, 1, $val);
                $spreadsheet->getActiveSheet()
                    ->getStyleByColumnAndRow($col, 1)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('A6A6A6');
                $col++;
            }
            $data = array();
            $row = 2;
            $temp = '';
            foreach ($items as $key=>$challan) {
                $col = 1;
                if($key == 0){
                    $temp = $challan->challan;
                }
                if ($challan->challan != $temp) {
                    foreach ($header as $val) {
                        $spreadsheet->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $row, '');
                        $col++;
                    }
                    $row++;
                    $temp = $challan->challan;
                }
                $col = 1;
                if ($challan->from_supplier_id > 0) {
                    $transfer_from = $challan->source_supplier_name;
                } else {
                    $transfer_from = $challan->source_department_name;
                }
                if ($challan->destination_department_id > 0) {
                    $transfer_to = $challan->destination_department_name;
                } else {
                    $transfer_to = $challan->destination_supplier_name;
                }
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, str_pad($challan->challan, 7, '0', STR_PAD_LEFT));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->reference);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, !empty($challan->ref_challan) ? str_pad($challan->ref_challan, 7, '0', STR_PAD_LEFT) : '');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, date('d-m-Y h:i A', strtotime($challan->issue_at)));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $transfer_from);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $transfer_to);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->item_name_code);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->measure_name ?? '---');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->unit_name ?? '---');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->qty);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->remarks);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->is_returnable == 1 ? 'Yes' : 'No');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->is_return_challan == 1 ? 'Return' : 'Regular');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, getReturnStatusLabel($challan->return_status));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, getApproveStatusLabel($challan->status));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, getChallanStatusLabel($challan->challan_status));
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $challan->note);
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, !empty($challan->prepared_name) ? $challan->prepared_fingerprint_no . ' - ' . $challan->prepared_name : '');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, !empty($challan->authorized_name) ? $challan->authorized_fingerprint_no . ' - ' . $challan->authorized_name : '');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, !empty($challan->checkout_name) ? $challan->checkout_fingerprint_no . ' - ' . $challan->checkout_name : '');
                $col++;
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, !empty($challan->gate_pass_checkout) ? $challan->gate_pass_checkout : '');
                $col++;
                $dd = (isset($challan->checkout_at) && !empty($challan->checkout_at)) ? date('d-m-Y h:i:s a', strtotime($challan->checkout_at)) : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $col++;
                $dd = (!empty($challan->checkin_name)) ? $challan->checkin_fingerprint_no . ' - ' . $challan->checkin_name : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $col++;
                $dd = (!empty($challan->gate_pass_checkin)) ? $challan->gate_pass_checkin : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $col++;
                $dd = (isset($challan->checkin_at) && !empty($challan->checkin_at)) ? date('d-m-Y h:i:s a', strtotime($challan->checkin_at)) : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $col++;
                $dd = (!empty($challan->receive_name)) ? $challan->receive_fingerprint_no . ' - ' . $challan->receive_name : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $col++;
                $dd = (!empty($challan->delivered_name)) ? $challan->delivered_fingerprint_no . ' - ' . $challan->delivered_name : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $col++;
                $dd = (!empty($challan->rejected_name)) ? $challan->rejected_fingerprint_no . ' - ' . $challan->rejected_name : '';
                $spreadsheet->getActiveSheet()
                    ->setCellValueByColumnAndRow($col, $row, $dd);
                $row++;

            }
            $writer = new Xlsx($spreadsheet);
            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            $file_name = 'Delivery-Challan-' . date('d-m-Y');
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $file_name . '.xlsx"');
            $response->headers->set('Cache-Control', 'max-age=0');
            return $response;
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            return redirect()->back();
        }
    }

    /**
     * @param InternalTransfer $internalTransfer
     * @return \Illuminate\Http\JsonResponse
     */
    public function download(InternalTransfer $internalTransfer)
    {
        try {
            $id = $internalTransfer->id;
            $data['item'] = InternalTransfer::with("sourceDepartment", "sourceWarehouse", "sourceSupplier", "destinationDepartment", "destinationWarehouse", "destinationSupplier", "preparedBy",
                "authorizedBy", "securityCheckedInBy", "securityCheckedOutBy", "rejectedBy", "deliveredBy", "receivedBy")
                ->select("id", "parent_id", "reference", "type", "workflow_type", "challan_status", "is_return_challan", "issue_at", "challan", "source_warehouse_id",
                    "source_department_id", "from_supplier_id", "destination_warehouse_id", "destination_department_id", "is_returnable", "to_supplier_id", "authorized_by",
                    "dispatch_security_checked", "receive_security_checked", "delivered_by", "gate_pass_checkin", "gate_pass_checkout", "checkout_at", "checkin_at", "received_by", "comment", "rejected_by", "status", "return_status", "created_by", "note", "file_attachment_path", "file_name")
                ->where("id", '=', $id)
                ->where('type', '=', InternalTransfer::OPERATION_TYPE_CHALLAN)
                ->where('deleted_at', '=', null)
                ->first();
            if (!empty($data['item']->parent_id) && $data['item']->is_return_challan == 1) {
                $id = $data['item']->parent_id;
            }
            $sql = "SELECT
                internal_transfer_items.*,
                IF
                (`internal_transfer_items`.`item_type` = 'whms',
                (
                SELECT
                    CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                FROM
                    requisition_items
                WHERE
                    id = `internal_transfer_items`.`item_id`
                ),
                ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_transfer_items`.`item_id` )
                ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                FROM
                internal_transfer_items
                LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_transfer_items.measure_id
                LEFT JOIN units ON units.id=internal_transfer_items.uom
                WHERE
                internal_transfer_items.internal_transfer_id = $id AND internal_transfer_items.deleted_at IS NULL";
            $data['challan_items'] = DB::select($sql);
            $page_name = "internal-transfer.print.delivery-challan-page";
            $pdf = PDF::loadView($page_name, $data);
            $file_name = date('d-m-Y', time()) . '-internal-transfer-invoice[' . $data['item']->challan . '].pdf';
            return $pdf->download($file_name);

        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    /**
     * @return Category|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $data = $this->getFormData();
        $departmentIds = $this->getDepartmentSupervisorIds();
        if ($departmentIds) {
            $data["departments_divisions"] = Department::orderBy("name")->select("*")->get()->whereIn('id', $departmentIds);
        }
        return view('internal-transfer.create', compact('data'));
    }

    /**
     * @return array
     */
    protected function getFormData()
    {
        $user_id = Auth::id();
        $today = date('Y-m-d');
        $current_department = DB::select("SELECT promotions.department_id FROM users INNER JOIN promotions ON promotions.user_id = users.id AND promotions.id =( SELECT MAX( pm.id) FROM `promotions` AS pm WHERE pm.user_id = $user_id AND pm.promoted_date <= '$today' ) WHERE users.`status` = 1");
        $department_id = $current_department[0]->department_id ?? '';
        return [
            "warehouses" => Warehouse::orderBy("name")->select("id", "name")->get(),
            "departments" => Department::orderBy("name")->select("*")->get(),
            "suppliers" => Supplier::orderBy("name")->select("id", "name")->get(),
            "units" => Unit::select("id", "name")->orderBy("name")->get(),
            "employees" => User::where('status', '=', 1)->orderBy("name")->select("id", "name", "fingerprint_no")->get(),
            "department_id" => $department_id
        ];
    }

    /**
     * @param InternalTransfer $internalTransfer
     * @return Factory|View
     */
    public function edit(InternalTransfer $internalTransfer)
    {
        if ($internalTransfer->type != InternalTransfer::OPERATION_TYPE_CHALLAN) {
            session()->flash('type', 'error');
            session()->flash('message', 'Invalid Action!!');
            return redirect()->back();
        }
        if ($internalTransfer->status > 1) {
            session()->flash('type', 'error');
            session()->flash('message', 'Authorized challan can not be edited!');
            return redirect()->back();
        }
        $whms = 'whms';
        $other = 'other';
        $internalTransfer_items = InternalTransferItems::with("uomName", "itemMeasurements")
            ->select('internal_transfer_items.*', 'requisition_items.name as item_name', 'requisition_items.code as item_code',
                'other_requisition_items.name as other_item_name', 'other_requisition_items.code as other_item_code')
            ->leftJoin('requisition_items', function ($join) use ($whms) {
                $join->on('requisition_items.id', '=', 'internal_transfer_items.item_id');
                $join->on('internal_transfer_items.item_type', '=', DB::raw("'" . $whms . "'"));
            })
            ->leftJoin('other_requisition_items', function ($join) use ($other) {
                $join->on('other_requisition_items.id', '=', 'internal_transfer_items.item_id');
                $join->on('internal_transfer_items.item_type', '=', DB::raw("'" . $other . "'"));
            })
            ->where('internal_transfer_id', $internalTransfer->id)
            ->get();
        $data = $this->getFormData();
        $departmentIds = $this->getDepartmentSupervisorIds();
        if ($departmentIds) {
            $data["departments_divisions"] = Department::orderBy("name")->select("*")->get()->whereIn('id', $departmentIds);
        }
        return view("internal-transfer.edit", compact('internalTransfer', 'internalTransfer_items', 'data'));
    }

    /**
     * @param RequestInternalTransfer $request
     * @return RedirectResponse
     */
    public function store(RequestInternalTransfer $request)
    {
        try {
            DB::beginTransaction();
            if ($request->validated()) {
                $source_warehouse_id = 0;
                $source_department_id = 0;
                $destination_warehouse_id = 0;
                $destination_department_id = 0;
                $to_supplier_id = 0;
                $dept_to_ware = 0;
                if ($request->transfer_from == 'warehouse') {
                    $source_warehouse_id = $request->source_warehouse_id;
                }
                if ($request->transfer_from == 'department') {
                    $source_department_id = $request->source_department_id;
                }
                if ($request->transfer_to == 'warehouse') {
                    $destination_warehouse_id = $request->destination_warehouse_id;
                    $dept_to_ware = 0;
                }
                if ($request->transfer_to == 'department') {
                    $destination_department_id = $request->destination_department_id;
                    $dept_to_ware = Department::find($destination_department_id)->is_warehouse;
                }
                if ($request->transfer_to == 'supplier') {
                    $to_supplier_id = $request->input("to_supplier_id") ?? 0;
                    $dept_to_ware = 0;
                    if (isset($request->to_supplier_name)) {
                        $supplier_data = [];
                        $supplier_data['name'] = $request->to_supplier_name;
                        $supplier = Supplier::create($supplier_data);
                        $to_supplier_id = $supplier->id;
                    }
                }
                if ($to_supplier_id > 0) {
                    $workflow_type = VENDOR_WORKFLOW;
                } else {
                    $workflow_type = GENERAL_WORKFLOW;
                }
                if ($request->input("is_returnable")) {
                    $return_status = RETURN_PENDING;
                } else {
                    $return_status = RETURN_NOT_APPLICABLE;
                }
                if ($dept_to_ware) {
                    foreach ($request->input("item_type") as $item_type) {
                        if ($item_type == 'other' || is_null($item_type)) {
                            session()->flash('type', 'warning');
                            session()->flash('message', 'Destination found as warehouse! Please select WHMS items to proceed. Other items not allowed!');
                            DB::rollBack();
                            return redirect()->back()->withInput();
                        }
                    }
                }
                $requestChallan = [
                    "reference" => $request->input("reference") ?? "",
                    "delivered_by" => $request->input("delivered_by") ?? 0,
                    "type" => InternalTransfer::OPERATION_TYPE_CHALLAN,
                    "workflow_type" => $workflow_type,
                    "status" => InternalTransfer::OPERATION_CREATED,
                    "is_returnable" => $request->input("is_returnable"),
                    "issue_at" => $request->input("issue_at"),
                    "challan_status" => CHALLAN_OPEN,
                    "return_status" => $return_status,
                    "source_warehouse_id" => $source_warehouse_id,
                    "source_department_id" => $source_department_id,
                    "destination_warehouse_id" => $destination_warehouse_id,
                    "destination_department_id" => $destination_department_id,
                    "to_supplier_id" => $to_supplier_id,
                    "created_by" => Auth::id(),
                    "note" => $request->input("note"),
                    "created_at" => now(),
                    "dept_to_ware" => $dept_to_ware,
                ];
                $challan_entry = InternalTransfer::create($requestChallan);
                $update_challan = array();
                if ($request->file('file')) {
                    $folder = DELIVERY_CHALLAN_FOLDER . '/' . Auth::id();
                    $update_challan['file_name'] = $request->file('file')->getClientOriginalName();
                    $update_challan['file_attachment_path'] = $request->file->store($folder);
                }
                $update_challan['challan'] = $challan_entry->id + 999;
                InternalTransfer::where('id', '=', $challan_entry->id)->update($update_challan);
                foreach ($request->input("item") as $key => $item) {
                    if (is_null($item)) {
                        $search_item_name = $request->input("search")[$key];
                        session()->flash('type', 'error');
                        session()->flash('message', "'$search_item_name' not found! Please select a valid item from suggestion box.");
                        DB::rollBack();
                        return redirect()->back()->withInput();
                    } else {
                        $challan_entry->items()->create([
                            "internal_transfer_id" => $challan_entry->id,
                            "operation_type" => InternalTransfer::OPERATION_TYPE_CHALLAN,
                            "item_id" => $request->input("item")[$key],
                            "item_type" => $request->input("item_type")[$key],
                            "qty" => $request->input("qty")[$key],
                            "uom" => $request->input("unit")[$key],
                            "measure_id" => $request->input("measure")[$key] ?? 0,
                            "remarks" => $request->input("remarks")[$key]
                        ]);
                    }
                }
                $show_challan = str_pad($update_challan['challan'], 7, '0', STR_PAD_LEFT);
                $redirect = redirect()->route("internal-transfer.index")->with('message', "Delivery Challan added successfully (challan No. - $show_challan)");
                DB::commit();
            } else {
                session()->flash('type', 'error');
                session()->flash('message', 'Something Went Wrong!!');
                $redirect = redirect()->back()->withInput();
                DB::rollBack();
            }
        } catch (\Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back()->withInput();
            DB::rollBack();
        }
        return $redirect;
    }

    /**
     * @param RequestInternalTransfer $request
     * @param InternalTransfer $internalTransfer
     * @return RedirectResponse
     */
    public function update(RequestInternalTransfer $request, InternalTransfer $internalTransfer)
    {
        try {
            DB::beginTransaction();
            if ($request->validated()) {
                $source_warehouse_id = 0;
                $source_department_id = 0;
                $destination_warehouse_id = 0;
                $destination_department_id = 0;
                $to_supplier_id = 0;
                $dept_to_ware = 0;
                if ($request->transfer_from == 'warehouse') {
                    $source_warehouse_id = $request->source_warehouse_id;
                }
                if ($request->transfer_from == 'department') {
                    $source_department_id = $request->source_department_id;
                }
                if ($request->transfer_to == 'warehouse') {
                    $dept_to_ware = 0;
                    $destination_warehouse_id = $request->destination_warehouse_id;
                }
                if ($request->transfer_to == 'department') {
                    $destination_department_id = $request->destination_department_id;
                    $dept_to_ware = Department::find($destination_department_id)->is_warehouse;
                }
                if ($request->transfer_to == 'supplier') {
                    $to_supplier_id = $request->input("to_supplier_id") ?? 0;
                    $dept_to_ware = 0;
                    if (isset($request->to_supplier_name)) {
                        $supplier_data = [];
                        $supplier_data['name'] = $request->to_supplier_name;
                        $supplier = Supplier::create($supplier_data);
                        $to_supplier_id = $supplier->id;
                    }
                }
                if ($to_supplier_id > 0) {
                    $workflow_type = VENDOR_WORKFLOW;
                } else {
                    $workflow_type = GENERAL_WORKFLOW;
                }
                if ($request->input("is_returnable")) {
                    $return_status = RETURN_PENDING;
                } else {
                    $return_status = RETURN_NOT_APPLICABLE;
                }
                if ($dept_to_ware) {
                    foreach ($request->input("item_type") as $item_type) {
                        if ($item_type == 'other' || is_null($item_type)) {
                            session()->flash('type', 'warning');
                            session()->flash('message', 'Destination found as warehouse! Please select WHMS items to proceed. Other items not allowed!');
                            DB::rollBack();
                            return redirect()->back()->withInput();
                        }
                    }
                }
                $update_challan = [
                    "reference" => $request->input("reference") ?? "",
                    "delivered_by" => $request->input("delivered_by") ?? 0,
                    "workflow_type" => $workflow_type,
                    "return_status" => $return_status,
                    "is_returnable" => $request->input("is_returnable"),
                    "issue_at" => $request->input("issue_at"),
                    "source_warehouse_id" => $source_warehouse_id,
                    "source_department_id" => $source_department_id,
                    "destination_warehouse_id" => $destination_warehouse_id,
                    "destination_department_id" => $destination_department_id,
                    "to_supplier_id" => $to_supplier_id,
                    "note" => $request->input("note"),
                    "updated_by" => Auth::id(),
                    "dept_to_ware" => $dept_to_ware,
                ];
                if ($request->file('file')) {
                    if (!empty($internalTransfer->file_attachment_path)) {
                        unlink(storage_path("app/public/" . $internalTransfer->file_attachment_path));
                    }
                    $folder = DELIVERY_CHALLAN_FOLDER . '/' . Auth::id();
                    $update_challan['file_name'] = $request->file('file')->getClientOriginalName();
                    $update_challan['file_attachment_path'] = $request->file->store($folder);
                }
                $internalTransfer->update($update_challan);
                $internalTransfer->items()->delete();
                foreach ($request->input("item") as $key => $item) {
                    if (is_null($item)) {
                        $search_item_name = $request->input("search")[$key];
                        session()->flash('type', 'error');
                        session()->flash('message', "'$search_item_name' not found! Please select a valid item from suggestion box.");
                        DB::rollBack();
                        return redirect()->back()->withInput();
                    } else {
                        $internalTransfer->items()->create([
                            "internal_transfer_id" => $internalTransfer->id,
                            "operation_type" => InternalTransfer::OPERATION_TYPE_CHALLAN,
                            "item_id" => $request->input("item")[$key],
                            "item_type" => $request->input("item_type")[$key],
                            "qty" => $request->input("qty")[$key],
                            "uom" => $request->input("unit")[$key],
                            "measure_id" => $request->input("measure")[$key] ?? 0,
                            "remarks" => $request->input("remarks")[$key]
                        ]);
                    }
                }
                DB::commit();
                session()->flash('message', 'Internal Transfer Updated Successfully');
                $redirect = redirect()->route("internal-transfer.index");
            } else {
                session()->flash('type', 'error');
                session()->flash('message', 'Something Went Wrong!!');
                $redirect = redirect()->back()->withInput();
                DB::rollBack();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
            $redirect = redirect()->back()->withInput();
        }
        return $redirect;
    }

    /**
     * @param InternalTransfer $internalTransfer
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request)
    {
        $data['challan'] = InternalTransfer::with("sourceDepartment", "sourceWarehouse", "sourceSupplier", "destinationDepartment", "destinationWarehouse", "destinationSupplier", "preparedBy",
            "authorizedBy", "securityCheckedInBy", "securityCheckedOutBy", "rejectedBy", "deliveredBy", "receivedBy", "updatedBy")
            ->where('id', '=', $request->challan_id)
            ->where('type', '=', InternalTransfer::OPERATION_TYPE_CHALLAN)
            ->where('deleted_at', '=', null)
            ->select("id", "parent_id", "reference", "type", "workflow_type", "challan_status", "is_return_challan", "issue_at", "challan", "source_warehouse_id",
                "source_department_id", "from_supplier_id", "destination_warehouse_id", "destination_department_id", "is_returnable", "to_supplier_id", "authorized_by",
                "dispatch_security_checked", "receive_security_checked", "delivered_by", "gate_pass_checkin", "gate_pass_checkout", "checkout_at", "checkin_at", "received_by",
                "comment", "rejected_by", "status", "return_status", "created_by", "note", "file_attachment_path", "file_name", "updated_by")
            ->first();
        if (!empty($data['challan']->parent_id) && $data['challan']->is_return_challan == 1) {
            $challan_id = $data['challan']->parent_id;
        } else {
            $challan_id = $request->challan_id;
        }
        $sql = "SELECT
                internal_transfer_items.*,
                IF
                (`internal_transfer_items`.`item_type` = 'whms',
                (
                SELECT
                    CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                FROM
                    requisition_items
                WHERE
                    id = `internal_transfer_items`.`item_id`
                ),
                ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_transfer_items`.`item_id` )
                ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                FROM
                internal_transfer_items
                LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_transfer_items.measure_id
                LEFT JOIN units ON units.id=internal_transfer_items.uom
                WHERE
                internal_transfer_items.internal_transfer_id = $challan_id AND internal_transfer_items.deleted_at IS NULL";
        $data['challan_items'] = DB::select($sql);
        $html = '';
        $html .= \Illuminate\Support\Facades\View::make('internal-transfer.details', $data);
        $res['html'] = $html;
        return response()->json($res);
    }

    /**
     * @param Request $request
     * @param InternalTransfer $internalTransfer
     * @return RedirectResponse
     */
    public function verification(Request $request, InternalTransfer $internalTransfer)
    {
        try {
            if (!$request->has('verification')) throw new Exception('Please check at least one checkbox.');

            $this->purgePhoto($request, $internalTransfer);

            $action = $request->input('verification');

            if ($internalTransfer[$action] == 0) {
                $internalTransfer->update([$action => auth()->user()->id]);
            }

            session()->flash('message', 'Internal Transfer Updated Successfully');
            $redirect = redirect()->route("internal-transfer.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param InternalTransfer $internalTransfer
     * @return RedirectResponse
     */
    public function delete(InternalTransfer $internalTransfer)
    {
        DB::beginTransaction();
        try {
            if ($internalTransfer->is_returnable && ($internalTransfer->challan_status == CHALLAN_PENDING_RETURN || $internalTransfer->challan_status == CHALLAN_CLOSE)) {
                $all_child = InternalTransfer::where('parent_id', '=', $internalTransfer->id)->get();
                foreach ($all_child as $child) {
                    if (!empty($child->file_attachment_path)) {
                        unlink(storage_path("app/public/" . $child->file_attachment_path));
                    }
                }
                InternalTransfer::where('parent_id', '=', $internalTransfer->id)->delete();
            }
            if (!empty($internalTransfer->file_attachment_path)) {
                unlink(storage_path("app/public/" . $internalTransfer->file_attachment_path));
            }
            InternalTransferItems::where('internal_transfer_id', '=', $internalTransfer->id)->delete();
            $internalTransfer->delete();
            DB::commit();
            $feedback['status'] = true;
            $feedback['message'] = 'Deleted Successfully';
        } catch (Exception $exception) {
            DB::rollBack();
            $feedback['status'] = false;
            $feedback['message'] = $exception->getMessage();
        }
        return response()->json($feedback);
    }

    /**
     * @param Request $request
     * @param InternalTransfer $internalTransfer
     */
    protected function purgePhoto(Request $request, InternalTransfer $internalTransfer)
    {
        $files = [];
        if ($request->hasFile("attachments")) {
            foreach ($request->file("attachments") as $attachment) {
                if ($attachment->isValid()) {
                    $photo = Image::make($attachment);
                    $fileName = "challan-" . $attachment->getFilename() . time();
                    $path = Storage::disk("public")->getAdapter()->getPathPrefix() . $fileName . 'png';
                    $photo->resize(200, 250)->encode('png', 60)->save($path, 60);

                    array_push($files, $fileName);
                }
            }
            $internalTransfer->update(['attachments' => json_encode($files)]);
        }
    }

    /**
     * @param $format
     * @param $id
     * @return void
     */
    public function generatePdf($format, $id)
    {
        $data['item'] = InternalTransfer::with("sourceDepartment", "sourceWarehouse", "sourceSupplier", "destinationDepartment", "destinationWarehouse", "destinationSupplier", "preparedBy",
            "authorizedBy", "securityCheckedInBy", "securityCheckedOutBy", "rejectedBy", "deliveredBy", "receivedBy")
            ->select("id", "parent_id", "reference", "type", "workflow_type", "challan_status", "is_return_challan", "issue_at", "challan", "source_warehouse_id",
                "source_department_id", "from_supplier_id", "destination_warehouse_id", "destination_department_id", "is_returnable", "to_supplier_id", "authorized_by",
                "dispatch_security_checked", "receive_security_checked", "delivered_by", "gate_pass_checkin", "gate_pass_checkout", "checkout_at", "checkin_at", "received_by", "comment", "rejected_by", "status", "return_status", "created_by", "note", "file_attachment_path", "file_name")
            ->where("id", '=', $id)
            ->where('type', '=', InternalTransfer::OPERATION_TYPE_CHALLAN)
            ->where('deleted_at', '=', null)
            ->first();
        if (!empty($data['item']->parent_id) && $data['item']->is_return_challan == 1) {
            $id = $data['item']->parent_id;
        }
        $sql = "SELECT
                internal_transfer_items.*,
                IF
                (`internal_transfer_items`.`item_type` = 'whms',
                (
                SELECT
                    CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                FROM
                    requisition_items
                WHERE
                    id = `internal_transfer_items`.`item_id`
                ),
                ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_transfer_items`.`item_id` )
                ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                FROM
                internal_transfer_items
                LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_transfer_items.measure_id
                LEFT JOIN units ON units.id=internal_transfer_items.uom
                WHERE
                internal_transfer_items.internal_transfer_id = $id AND internal_transfer_items.deleted_at IS NULL";
        $data['challan_items'] = DB::select($sql);
        $page_name = "internal-transfer.print.$format-page";
        $pdf = PDF::loadView($page_name, $data);
        $file_name = date('d-m-Y', time()) . '-internal-transfer-invoice[' . $data['item']->challan . '].pdf';
        return $pdf->stream($file_name, array('Attachment' => 0));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallanApprovalView(Request $request)
    {
        $data = [];
        $data['challan'] = InternalTransfer::with("sourceDepartment", "sourceWarehouse", "sourceSupplier", "destinationDepartment", "destinationWarehouse", "destinationSupplier", "preparedBy",
            "authorizedBy", "securityCheckedInBy", "securityCheckedOutBy", "rejectedBy", "deliveredBy", "receivedBy")
            ->where('id', '=', $request->challan_id)
            ->where('type', '=', InternalTransfer::OPERATION_TYPE_CHALLAN)
            ->where('deleted_at', '=', null)
            ->select("internal_transfers.*")
            ->first();
        if (!empty($data['challan']->parent_id) && $data['challan']->is_return_challan == 1) {
            $challan_id = $data['challan']->parent_id;
        } else {
            $challan_id = $request->challan_id;
        }
        $sql = "SELECT
                internal_transfer_items.*,
                IF
                (`internal_transfer_items`.`item_type` = 'whms',
                (
                SELECT
                    CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                FROM
                    requisition_items
                WHERE
                    id = `internal_transfer_items`.`item_id`
                ),
                ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_transfer_items`.`item_id` )
                ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                FROM
                internal_transfer_items
                LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_transfer_items.measure_id
                LEFT JOIN units ON units.id=internal_transfer_items.uom
                WHERE
                internal_transfer_items.internal_transfer_id = $challan_id AND internal_transfer_items.deleted_at IS NULL";
        $data['challan_items'] = DB::select($sql);
        $data['employees'] = User::where('status', '=', 1)->orderBy("name")->select("id", "name", "fingerprint_no")->get();

        $departmentIds=[];
        $data['departmentIds'] = $this->getDepartmentSupervisorIds();

        $html = '';
        if ($data['challan']->workflow_type == GENERAL_WORKFLOW) {
            $html .= \Illuminate\Support\Facades\View::make('internal-transfer.approval-action.general-action-modal', $data);
        } else {
            if ($data['challan']->is_return_challan) {
                $html .= \Illuminate\Support\Facades\View::make('internal-transfer.approval-action.vendor-return-action-modal', $data);
            } else {
                $html .= \Illuminate\Support\Facades\View::make('internal-transfer.approval-action.vendor-action-modal', $data);
            }
        }
        $res['html'] = $html;
        return response()->json($res);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallanReturnView(Request $request)
    {
        $data = [];
        $data['challan'] = InternalTransfer::with("sourceDepartment", "sourceWarehouse", "destinationDepartment", "destinationWarehouse", "destinationSupplier", "preparedBy",
            "authorizedBy", "securityCheckedInBy", "securityCheckedOutBy", "rejectedBy", "deliveredBy", "receivedBy", "parents")
            ->where('id', '=', $request->challan_id)
            ->where('type', '=', InternalTransfer::OPERATION_TYPE_CHALLAN)
            ->where('deleted_at', '=', null)
            ->select("id", "parent_id", "reference", "type", "workflow_type", "challan_status", "is_return_challan", "issue_at", "challan", "source_warehouse_id",
                "source_department_id", "destination_warehouse_id", "destination_department_id", "is_returnable", "to_supplier_id", "authorized_by",
                "dispatch_security_checked", "receive_security_checked", "delivered_by", "gate_pass_checkin", "gate_pass_checkout", "checkin_at", "checkout_at", "received_by", "comment", "rejected_by", "status", "return_status", "created_by", "note", "file_attachment_path", "file_name")
            ->first();
        if (!empty($data['challan']->parent_id) && $data['challan']->is_return_challan == 1) {
            $challan_id = $data['challan']->parent_id;
        } else {
            $challan_id = $request->challan_id;
        }
        $sql = "SELECT
                internal_transfer_items.*,
                IF
                (`internal_transfer_items`.`item_type` = 'whms',
                (
                SELECT
                    CONCAT( `name`, \"-\", COALESCE(`code`,'') )
                FROM
                    requisition_items
                WHERE
                    id = `internal_transfer_items`.`item_id`
                ),
                ( SELECT CONCAT( `name`, \"-\", COALESCE(`code`,'') ) FROM other_requisition_items WHERE id = `internal_transfer_items`.`item_id` )
                ) AS item_name_code,item_measurement_details.measure_name,units.`name` as unit_name
                FROM
                internal_transfer_items
                LEFT JOIN item_measurement_details ON item_measurement_details.id=internal_transfer_items.measure_id
                LEFT JOIN units ON units.id=internal_transfer_items.uom
                WHERE
                internal_transfer_items.internal_transfer_id = $challan_id AND internal_transfer_items.deleted_at IS NULL";
        $data['challan_items'] = DB::select($sql);
        $html = '';
        $html .= \Illuminate\Support\Facades\View::make('internal-transfer.approval-action.return-action-modal', $data);
        $res['html'] = $html;
        return response()->json($res);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function approvalAction(Request $request)
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $status = $request->status;
            $user = Auth::id();
            $challan_info = InternalTransfer::find($id);
            $message = 'Approve Successfully';
            $data = array();
            $data['status'] = $status;
            if ($challan_info->workflow_type == VENDOR_WORKFLOW) {
                if ($status == InternalTransfer::OPERATION_AUTHORIZED) {
                    $data['authorized_by'] = $user;
                } elseif ($status == InternalTransfer::OPERATION_SECURITY_CHECKED_OUT) {
                    $data['dispatch_security_checked'] = $user;
                    $data['gate_pass_checkout'] = '10' . $id;
                    $data['checkout_at'] = now();
                    if (!$challan_info->is_returnable && !$challan_info->is_return_challan) {
                        $data['challan_status'] = CHALLAN_CLOSE;
                    }
                    if ($challan_info->is_returnable && !$challan_info->is_return_challan) {
                        $data['challan_status'] = CHALLAN_PENDING_RETURN;
                    }
                } elseif ($status == InternalTransfer::OPERATION_SECURITY_CHECKED_IN) {
                    $data['receive_security_checked'] = $user;
                    $data['checkin_at'] = now();
                    $data['gate_pass_checkin'] = '11' . $id;
                } elseif ($status == InternalTransfer::OPERATION_RECEIVED) {
                    if (!$challan_info->is_returnable && $challan_info->is_return_challan) {
                        $data['challan_status'] = CHALLAN_CLOSE;
                        InternalTransfer::where('id', '=', $challan_info->parent_id)->update(['return_status' => RETURN_COMPLETE]);
                    }
                    $data['delivered_by'] = $request->delivered_by;
                    $data['received_by'] = $user;
                } elseif ($status == InternalTransfer::OPERATION_REJECT) {
                    InternalTransfer::where('id', '=', $challan_info->parent_id)->update(['challan_status' => CHALLAN_PENDING_RETURN]);
                    $message = 'Rejected Successfully';
                    $data['challan_status'] = CHALLAN_CLOSE;
                    $data['rejected_by'] = $user;
                    $data['comment'] = $request->comment;
                }
            } else {
                if ($status == InternalTransfer::OPERATION_AUTHORIZED) {
                    $data['authorized_by'] = $user;
                } elseif ($status == InternalTransfer::OPERATION_SECURITY_CHECKED_OUT) {
                    $data['dispatch_security_checked'] = $user;
                    $data['gate_pass_checkout'] = '10' . $id;
                    $data['checkout_at'] = now();
                    if ($challan_info->dept_to_ware) {
                        if (is_null($challan_info->parent_id)) {
                            InternalTransferDeptToWare::create(['internal_transfer_id' => $challan_info->id]);
                        } else {
                            InternalTransferDeptToWareReturn::create(['internal_transfer_id' => $challan_info->id]);
                        }
                    }
                } elseif ($status == InternalTransfer::OPERATION_SECURITY_CHECKED_IN) {
                    $data['receive_security_checked'] = $user;
                    $data['checkin_at'] = now();
                    $data['gate_pass_checkin'] = '11' . $id;
                } elseif ($status == InternalTransfer::OPERATION_RECEIVED) {
                    if (!$challan_info->is_returnable && !$challan_info->is_return_challan) {
                        $data['challan_status'] = CHALLAN_CLOSE;
                    }
                    if ($challan_info->is_returnable && !$challan_info->is_return_challan) {
                        $data['challan_status'] = CHALLAN_PENDING_RETURN;
                    }
                    if ($challan_info->is_return_challan) {
                        $data['challan_status'] = CHALLAN_CLOSE;
                        InternalTransfer::where('id', '=', $challan_info->parent_id)->update(['return_status' => RETURN_COMPLETE]);
                    }
                    $data['delivered_by'] = $request->delivered_by;
                    $data['received_by'] = $user;
                } elseif ($status == InternalTransfer::OPERATION_REJECT) {
                    InternalTransfer::where('id', '=', $challan_info->parent_id)->update(['challan_status' => CHALLAN_PENDING_RETURN]);
                    $is_source_warehouse = Department::find($challan_info->source_department_id)->is_warehouse;
                    if ($is_source_warehouse) {
                        InternalTransferSourceWarehouseReject::create(['internal_transfer_id' => $challan_info->id]);
                    }
                    $message = 'Rejected Successfully';
                    $data['challan_status'] = CHALLAN_CLOSE;
                    $data['rejected_by'] = $user;
                    $data['comment'] = $request->comment;
                }
            }
            InternalTransfer::where('id', '=', $id)->update($data);
            DB::commit();
            session()->flash('message', $message);
            return redirect()->route("internal-transfer.index");
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function returnInitiate(Request $request)
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $note = $request->note;
            $challan_info = InternalTransfer::find($id);
            InternalTransfer::where('id', '=', $id)->update(['challan_status' => CHALLAN_CLOSE]);
            $return_challan = [
                "parent_id" => $id,
                "whms_parent_id" => $challan_info->whms_parent_id,
                "reference" => $challan_info->reference,
                "type" => InternalTransfer::OPERATION_TYPE_CHALLAN,
                "workflow_type" => $challan_info->workflow_type,
                "challan_status" => CHALLAN_OPEN,
                "is_return_challan" => 1,
                "status" => InternalTransfer::OPERATION_CREATED,
                "return_status" => RETURN_NOT_APPLICABLE,
                "is_returnable" => 0,
                "issue_at" => now(),
                "delivered_by" => $challan_info->delivered_by,
                "created_by" => Auth::id(),
                "note" => $note,
                "created_at" => now(),
            ];
            if ($challan_info->source_warehouse_id > 0) {
                $return_challan['dept_to_ware'] = 0;
                $return_challan['destination_warehouse_id'] = $challan_info->source_warehouse_id;
            } else {
                $return_challan['destination_department_id'] = $challan_info->source_department_id;
                $return_challan['dept_to_ware'] = Department::find($challan_info->source_department_id)->is_warehouse;
            }
            if ($challan_info->destination_warehouse_id > 0) {
                $return_challan['source_warehouse_id'] = $challan_info->destination_warehouse_id;
            } elseif ($challan_info->destination_department_id > 0) {
                $return_challan['source_department_id'] = $challan_info->destination_department_id;
            } else {
                $return_challan['from_supplier_id'] = $challan_info->to_supplier_id;
            }
            $challan_entry = InternalTransfer::create($return_challan);
            $update_challan['challan'] = $challan_entry->id + 999;
            InternalTransfer::where('id', '=', $challan_entry->id)->update($update_challan);
            DB::commit();
            session()->flash('message', 'Return Successfully!');
            return redirect()->route("internal-transfer.index");
        } catch (Exception $exception) {
            DB::rollBack();
            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findUnitMeasurement(Request $request)
    {
        if ($request->item_type == 'whms') {
            $measurements['variant'] = MeasurementDetails::where('requisition_item_id', '=', $request->item_id)->where('deleted_at', NULL)->get();
            $measurements['unit'] = RequisitionItem::with('unitName')->where('id', $request->item_id)->first();
        } else {
            $measurements['variant'] = [];
            $measurements['unit'] = OtherRequisitionItem::with('unitName')->where('id', $request->item_id)->first();;
        }
        return response()->json($measurements);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function findItemName(Request $request)
    {
        $search = $request->search;
        if (!empty($search)) {
            $sql1 = "SELECT
                  requisition_items.id, requisition_items.name, requisition_items.code, 'whms' AS item_type
                  FROM
                  requisition_items
                  where requisition_items.deleted_at IS NULL AND CONCAT(requisition_items.name,COALESCE(requisition_items.code,'')) LIKE '%$search%'";
            $sql2 = "SELECT
                  other_requisition_items.id, other_requisition_items.name, other_requisition_items.code, 'other' AS item_type
                  FROM
                  other_requisition_items
                  where other_requisition_items.deleted_at IS NULL AND CONCAT(other_requisition_items.name,COALESCE(other_requisition_items.code,'')) LIKE '%$search%'";
            $sql = $sql1 . ' UNION ALL ' . $sql2;
            $response['items'] = DB::select($sql);
        } else {
            $response['items'] = [];
        }
        return response()->json($response);
    }

    /**
     * @return bool
     */
    public function sendChallanToWarehouse()
    {
        try {
            $response = true;
            $post_items = InternalTransfer::with("items", "preparedBy", "authorizedBy", "securityCheckedOutBy", "deliveredBy")
                ->join("internal_transfer_dept_to_warehouse", "internal_transfer_dept_to_warehouse.internal_transfer_id", "=", "internal_transfers.id")
                ->where("internal_transfer_dept_to_warehouse.serve_status", "=", 0)
                ->select("internal_transfers.*")->get();
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
                            $response = false;
                        }
                    } else {
                        $response = false;
                    }
                }
            }
            return $response;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function sendReturnChallanToWarehouse()
    {
        try {
            $response = true;
            $post_items = InternalTransfer::with("items", "preparedBy", "authorizedBy", "securityCheckedOutBy", "deliveredBy")
                ->join("internal_transfer_dept_to_ware_return", "internal_transfer_dept_to_ware_return.internal_transfer_id", "=", "internal_transfers.id")
                ->where("internal_transfer_dept_to_ware_return.serve_status", "=", 0)
                ->select("internal_transfers.*")->get();
            if ($post_items->count()) {
                if (Cache::has('item_token')) {
                    $token = Cache::get('item_token');
                    $token_type = 'Bearer';
                    $response = $this->postReturnData($token_type, $token, $post_items);
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
                            $response = $this->postReturnData($token_type, $token, $post_items);
                        } else {
                            $response = false;
                        }
                    } else {
                        $response = false;
                    }
                }
            }
            return $response;
        } catch (Exception $exception) {
            return false;
        }
    }


    /**
     * @return bool
     */
    public function sendRejectChallanToWarehouse()
    {
        try {
            $response = true;
            $post_items = InternalTransfer::with("rejectedBy")
                ->join("internal_transfer_source_warehouse_reject", "internal_transfer_source_warehouse_reject.internal_transfer_id", "=", "internal_transfers.id")
                ->where("internal_transfer_source_warehouse_reject.serve_status", "=", 0)
                ->select("internal_transfers.*")->get();
            if ($post_items->count()) {
                if (Cache::has('item_token')) {
                    $token = Cache::get('item_token');
                    $token_type = 'Bearer';
                    $response = $this->postRejectData($token_type, $token, $post_items);
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
                            $response = $this->postRejectData($token_type, $token, $post_items);
                        } else {
                            $response = false;
                        }
                    } else {
                        $response = false;
                    }
                }
            }
            return $response;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param $token_type
     * @param $token
     * @param $post_items
     * @return bool
     */
    public function postRejectData($token_type, $token, $post_items)
    {
        try {
            $headers_next = array
            (
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: ' . $token_type . ' ' . $token,
            );
            $url = env('WHMS_URL') . '/api/transfer/reject';
            $method = 'POST';
            $res = curlrequest($url, $headers_next, $method, $post_items);
            $result = $res['result'];
            $error_status = $res['error_status'];
            if (!$error_status) {
                $arr = json_decode($result);
                if (isset($arr->status) && $arr->status) {
                    if ($arr->data) {
                        DB::table('internal_transfer_source_warehouse_reject')->whereIn('internal_transfer_id', $arr->data)->update(['serve_status' => 1]);
                    }
                    return true;
                } else {
                    Cache::forget('item_token');

                    return false;
                }
            } else {
                Cache::forget('item_token');

                return false;
            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return false;
        }
    }

    /**
     * @param $token_type
     * @param $token
     * @param $post_items
     * @return bool
     */
    public function postReturnData($token_type, $token, $post_items)
    {
        try {
            $headers_next = array
            (
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: ' . $token_type . ' ' . $token,
            );
            $url = env('WHMS_URL') . '/api/transfer/post-return';
            $method = 'POST';
            $res = curlrequest($url, $headers_next, $method, $post_items);
            $result = $res['result'];
            $error_status = $res['error_status'];
            if (!$error_status) {
                $arr = json_decode($result);
                if (isset($arr->status) && $arr->status) {
                    if ($arr->data) {
                        DB::table('internal_transfer_dept_to_ware_return')->whereIn('internal_transfer_id', $arr->data)->update(['serve_status' => 1]);
                    }
                    return true;
                } else {
                    Cache::forget('item_token');

                    return false;
                }
            } else {
                Cache::forget('item_token');

                return false;
            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return false;
        }
    }


    /**
     * @param $token_type
     * @param $token
     * @param $post_items
     * @return bool
     */
    public function postData($token_type, $token, $post_items)
    {
        try {
            $headers_next = array
            (
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: ' . $token_type . ' ' . $token,
            );
            $url = env('WHMS_URL') . '/api/transfer/post-checkin';
            $method = 'POST';
            $res = curlrequest($url, $headers_next, $method, $post_items);
            $result = $res['result'];
            $error_status = $res['error_status'];
            if (!$error_status) {
                $arr = json_decode($result);
                if (isset($arr->status) && $arr->status) {
                    if ($arr->data) {
                        DB::table('internal_transfer_dept_to_warehouse')->whereIn('internal_transfer_id', $arr->data)->update(['serve_status' => 1]);
                    }
                    return true;
                } else {
                    Cache::forget('item_token');

                    return false;
                }
            } else {
                Cache::forget('item_token');

                return false;
            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return false;
        }
    }

    protected function getDepartmentSupervisorIds()
    {
        $divisionSupervisor = DivisionSupervisor::where("supervised_by", auth()->user()->id)->active()->orderByDesc("id")->pluck("office_division_id")->toArray();
        $departmentSupervisor = DepartmentSupervisor::where("supervised_by", auth()->user()->id)->active()->pluck("department_id")->toArray();

        if (count($divisionSupervisor) > 0) {
            $departmentIds = Department::whereIn("office_division_id", $divisionSupervisor)->pluck("id")->toArray();
        } elseif (count($departmentSupervisor) > 0) {
            $departmentIds = $departmentSupervisor;
        } else {
            $departmentIds = [];
        }

        return $departmentIds;
    }
}
