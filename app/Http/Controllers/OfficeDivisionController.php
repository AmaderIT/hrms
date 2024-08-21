<?php

namespace App\Http\Controllers;

use App\Http\Requests\officeDivision\RequestOfficeDivision;
use App\Library\Filter;
use App\Models\OfficeDivision;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class OfficeDivisionController extends Controller
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
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $filter = new Filter(OfficeDivision::class, ["name"], $request->input("search"));
        $items = $filter->select('id','name')->orderBy('name')->paginate(\Functions::getPaginate());
        return view('office-division.index', compact('items'));
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return Factory|View
     */
    public function edit(OfficeDivision $officeDivision)
    {
        return view("office-division.edit", compact("officeDivision"));
    }

    /**
     * @param RequestOfficeDivision $request
     * @return RedirectResponse
     */
    public function store(RequestOfficeDivision $request)
    {
        try {
            OfficeDivision::create($request->validated());
            session()->flash("message", "Division Created Successfully");
            $redirect = redirect()->route("office-division.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestOfficeDivision $request
     * @param OfficeDivision $officeDivision
     * @return RedirectResponse
     */
    public function update(RequestOfficeDivision $request, OfficeDivision $officeDivision)
    {
        try {
            $officeDivision->update($request->validated());
            session()->flash("message", "Division Updated Successfully");
            $redirect = redirect()->route("office-division.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param OfficeDivision $officeDivision
     * @return mixed
     */
    public function delete(OfficeDivision $officeDivision)
    {
        try {
            $feedback['status'] = $officeDivision->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
    public function storeAjx(RequestOfficeDivision $request){
        $this->store($request);
        return \response()->json([
            'status' => 'success',
            'officeDivisions' => OfficeDivision::orderBy("name")->select("id", "name")->get(),
            'message' => 'Office Division Created Successfully'
        ]);
    }
}
