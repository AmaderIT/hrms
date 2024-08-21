<?php

namespace App\Http\Controllers;

use App\Http\Requests\unit\RequestUnit;
use App\Models\Unit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class UnitController extends Controller
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
        $items = Unit::select("id", "name", "description")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('unit.index', compact('items'));
    }

    /**
     * @param Unit $unit
     * @return Application|Factory|View
     */
    public function edit(Unit $unit)
    {
        return view("unit.edit", compact('unit'));
    }

    /**
     * @param RequestUnit $request
     * @return RedirectResponse|string
     */
    public function store(RequestUnit $request)
    {
        try {
            Unit::create($request->validated());

            session()->flash('message', 'Unit Created Successfully');
            $redirect = redirect()->route("unit.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
            $redirect = redirect()->back()->withInput();
        }

        return $redirect;
    }

    /**
     * @param RequestUnit $request
     * @param Unit $unit
     * @return RedirectResponse
     */
    public function update(RequestUnit $request, Unit $unit)
    {
        try {
            $unit->update($request->validated());

            session()->flash('message', 'Unit Updated Successfully');
            $redirect = redirect()->route("unit.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!');
            $redirect = redirect()->back()->withInput();
        }

        return $redirect;
    }

    /**
     * @param Unit $unit
     * @return RedirectResponse
     */
    public function delete(Unit $unit)
    {
        try {
            $feedback['status'] = $unit->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
