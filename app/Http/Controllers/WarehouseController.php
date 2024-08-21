<?php

namespace App\Http\Controllers;

use App\Http\Requests\warehouse\RequestWarehouse;
use App\Models\Department;
use App\Models\Warehouse;
use Functions;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class WarehouseController extends Controller
{
    /**
     * WarehouseController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        $items = Warehouse::select("id","name","company_name","bin","code","email","phone","area","city","address")
            ->orderBy("id","desc")
            ->paginate(Functions::getPaginate());

        return view('warehouse.index', compact("items"));
    }

    public function create(){
        return view("warehouse.create");
    }

    /**
     * @param Warehouse $warehouse
     * @return Factory|View
     */
    public function edit(Warehouse $warehouse)
    {
        return view("warehouse.edit", compact('warehouse'));
    }

    /**
     * @param RequestWarehouse $request
     * @return RedirectResponse
     */
    public function store(RequestWarehouse $request)
    {
        try {
            Warehouse::create($request->validated());

            session()->flash('message', 'Warehouse Created Successfully');
            $redirect = redirect()->route("warehouse.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Warehouse Created Successfully');
            $redirect = redirect()->back()->withErrors()->withInput();
        }

        return $redirect;
    }

    /**
     * @param RequestWarehouse $request
     * @param Warehouse $warehouse
     * @return RedirectResponse
     */
    public function update(RequestWarehouse $request, Warehouse $warehouse)
    {
        try {
            $warehouse->update($request->validated());

            session()->flash('message', 'Warehouse Updated Successfully');
            $redirect = redirect()->route("warehouse.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Warehouse Created Successfully');
            $redirect = redirect()->back()->withErrors()->withInput();
        }

        return $redirect;
    }

    /**
     * @param Warehouse $warehouse
     * @return mixed
     */
    public function delete(Warehouse $warehouse)
    {
        try {
            $feedback['status'] = $warehouse->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
