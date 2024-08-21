<?php

namespace App\Http\Controllers;

use App\Http\Requests\requisitionItem\RequestRequisitionItem;
use App\Models\OtherRequisitionItem;
use App\Models\RequisitionItem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class OtherRequisitionItemController extends Controller
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
        $items = OtherRequisitionItem::select("id", "name", "code")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('other-requisition-item.index', compact('items'));
    }

    public function create(){
        return view('other-requisition-item.create');
    }

    /**
     * @param OtherRequisitionItem $requisitionItem
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function edit(OtherRequisitionItem $otherRequisitionItem)
    {
        return view("other-requisition-item.edit", compact('otherRequisitionItem'));
    }

    /**
     * @param RequestRequisitionItem $request
     * @return RedirectResponse
     */
    public function store(RequestRequisitionItem $request)
    {
        try {
            OtherRequisitionItem::create($request->validated());
            session()->flash('message', 'Requisition Item Created Successfully');
            $redirect = redirect()->route("other-requisition-item.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back();
        }
        return $redirect;
    }

    /**
     * @param RequestRequisitionItem $request
     * @param RequisitionItem $requisitionItem
     * @return RedirectResponse
     */
    public function update(RequestRequisitionItem $request, OtherRequisitionItem $otherRequisitionItem)
    {
        try {
            $otherRequisitionItem->update($request->validated());
            session()->flash('message', 'Requisition Item Updated Successfully');
            $redirect = redirect()->route("other-requisition-item.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back();
        }
        return $redirect;
    }

    /**
     * @param RequisitionItem $requisitionItem
     * @return mixed
     */
    public function delete(OtherRequisitionItem $otherRequisitionItem)
    {
        try {
            $feedback['status'] = $otherRequisitionItem->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }
        return $feedback;
    }
}
