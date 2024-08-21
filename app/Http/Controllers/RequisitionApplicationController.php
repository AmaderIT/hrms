<?php

namespace App\Http\Controllers;

use App\Http\Requests\requisition\RequestRequisitionApplication;
use App\Models\Department;
use App\Models\Requisition;
use App\Models\RequisitionDetails;
use App\Models\RequisitionItem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class RequisitionApplicationController extends Controller
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
        $items = Requisition::with("department", "appliedBy", "details.item")->where("applied_by", auth()->user()->id)->orderByDesc("id")->paginate(\Functions::getPaginate());

        return view('apply-for-requisition.index', compact('items'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $data = [
            "departments"           => Department::orderByDesc("name")->whereIn('id', FilterController::getDepartmentIds())->get(),
            "requisitionItems"      => RequisitionItem::whereIn("parent_id", [182,227])->orderBy("name")->get(),
        ];

        return view("apply-for-requisition.create", compact("data"));
    }

    /**
     * @param Requisition $requisition
     * @return Application|Factory|View
     */
    public function edit(Requisition $requisition)
    {
        $data = [
            "departments"           => Department::orderByDesc("name")->whereIn('id', FilterController::getDepartmentIds())->get(),
            "requisitionItems"      => RequisitionItem::with('itemMeasurements')->whereIn("parent_id", [182,227])->orderBy("name")->get(),
            "requisition"           => $requisition->load("details.item")
        ];

        return view("apply-for-requisition.edit", compact("data"));
    }

    /**
     * @param RequestRequisitionApplication $request
     * @return RedirectResponse
     */
    public function store(RequestRequisitionApplication $request)
    {
        try {
            DB::transaction(function () use ($request) {
                # Requisition
                $requisition = Requisition::create([
                    "department_id"         => $request->input("department_id"),
                    "applied_by"            => auth()->user()->id,
                    "applied_date"          => today(),
                    "priority"              => $request->input("priority"),
                    "status"                => Requisition::STATUS_NEW,
                    "remarks"               => $request->input("remarks")
                ]);

                # Requisition Details
                $requisitionDetails = [];
                foreach ($request->input("requisition_item_id") as $key => $item) {
                    if (!is_null($request->input("requisition_item_id")[$key]) && !is_null($request->input("quantity")[$key]))
                    {
                        $detail['requisition_id'] = $requisition->id;
                        $detail['requisition_item_id'] = $item;
                        $detail['quantity'] = $request->input("quantity")[$key];
                        $detail['unit_id'] = RequisitionItem::where('id',$item)->first()->unit_id;
                        $detail['measure_id'] = $request->input("requisition_item_measure_id")[$key];
                        $requisitionDetails[] = $detail;
                    }
                }
                $requisition->details()->createMany($requisitionDetails);
            });

            session()->flash('message', 'Requisition Created Successfully');
            $redirect = redirect()->route("apply-for-requisition.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestRequisitionApplication $request
     * @param Requisition $requisition
     * @return RedirectResponse
     */
    public function update(RequestRequisitionApplication $request, Requisition $requisition)
    {
        try {
            DB::transaction(function () use ($requisition, $request) {
                if ($request->input("status") != $requisition->status) {
                    activity('supervisor-create')->by(auth()->user())->log('Requisition status has been changed');
                }

                # Requisition
                $requisition->update([
                    "department_id" => $request->input("department_id"),
                    "priority"      => $request->input("priority"),
                    "status"        => $requisition->status,
                    "remarks"       => $request->input("remarks")
                ]);

                # Requisition Details
                $requisitionDetails = [];
                foreach ($request->input("requisition_item_id") as $key => $item) {
                    if (!is_null($request->input("requisition_item_id")[$key]) && !is_null($request->input("quantity")[$key])) {
                        $detail['requisition_id'] = $requisition->id;
                        $detail['requisition_item_id'] = $item;
                        $detail['quantity'] = $request->input("quantity")[$key];
                        $detail['unit_id'] = RequisitionItem::where('id',$item)->first()->unit_id;
                        $detail['measure_id'] = $request->input("requisition_item_measure_id")[$key];
                        $requisitionDetails[] = $detail;
                    }
                }
                $requisition->details()->delete();
                $requisition->details()->createMany($requisitionDetails);
            });

            session()->flash('message', 'Requisition Updated Successfully');
            $redirect = redirect()->route("apply-for-requisition.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }


    /**
     * @param Requisition $requisition
     * @return mixed
     * @throws \Exception
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function receive(Request $request) {
        try {
            foreach ($request->input("id") as $key => $id) {
                RequisitionDetails::where("id", $id)->update([
                    "received_quantity" => $request->input("received_quantity")[$key]
                ]);
            }

            Requisition::where("id", $request->input("requisition_id"))->update([
                "status" => Requisition::STATUS_RECEIVED
            ]);

            session()->flash('message', 'Requisition Received Successfully');
            $redirect = redirect()->route("apply-for-requisition.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }
}
