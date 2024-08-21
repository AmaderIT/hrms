<?php

namespace App\Http\Controllers;

use App\Http\Requests\district\RequestDistrict;
use App\Models\District;
use App\Models\Division;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class DistrictController extends Controller
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
        $items = District::with("division")
                ->select("id", "name", "division_id")
                ->orderBy("name")
                ->paginate(\Functions::getPaginate());

        return view('district.index', compact('items'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $items = Division::select("id", "name")->get();
        return view('district.create', compact('items'));
    }

    /**
     * @param District $district
     * @return Application|Factory|View
     */
    public function edit(District $district)
    {
        $items = Division::select("id", "name")->get();
        return view("district.edit", compact('district', 'items'));
    }

    /**
     * @param RequestDistrict $request
     * @return RedirectResponse|string
     */
    public function store(RequestDistrict $request)
    {
        try {
            District::create($request->validated());

            session()->flash('message', 'District Created Successfully');
            $redirect = redirect()->route("district.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestDistrict $request
     * @param District $district
     * @return RedirectResponse
     */
    public function update(RequestDistrict $request, District $district)
    {
        try {
            $district->update($request->validated());

            session()->flash('message', 'District Updated Successfully');
            $redirect = redirect()->route("district.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param District $district
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(District $district)
    {
        try {
            $feedback['status'] = $district->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
