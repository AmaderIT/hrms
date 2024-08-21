<?php

namespace App\Http\Controllers;

use App\Exports\DivisionExport;
use App\Http\Requests\division\RequestDivision;
use App\Imports\DivisionImport;
use App\Models\Division;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DivisionController extends Controller
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
        $items = Division::select('id', 'name')->orderBy('name')->paginate(\Functions::getPaginate());
        return view('division.index', compact('items'));
    }

    public function create()
    {
        return view('division.create');
    }

    /**
     * @param Division $division
     * @return Application|Factory|View
     */
    public function edit(Division $division)
    {
        return view("division.edit", compact('division'));
    }

    /**
     * @param RequestDivision $request
     * @return RedirectResponse|string
     */
    public function store(RequestDivision $request)
    {
        try {
            Division::create($request->validated());
            session()->flash('message', 'Division Created Successfully');
            $redirect = redirect()->route("division.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestDivision $request
     * @param Division $division
     * @return RedirectResponse
     */
    public function update(RequestDivision $request, Division $division)
    {
        try {
            $division->update($request->validated());

            session()->flash('message', 'Division Updated Successfully');
            $redirect = redirect()->route("division.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Division $division
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Division $division)
    {
        try {
            $feedback['status'] = $division->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function import(Request $request)
    {
        activity('division-import')
            ->by(auth()->user())
            ->log('Division csv imported');

        Excel::import(new DivisionImport(), $request->file('file'), null, \Maatwebsite\Excel\Excel::CSV);

        session()->flash('message', 'Division Imported Successfully');
        return redirect()->back();
    }

    /**
     * @return BinaryFileResponse
     */
    public function export()
    {
        activity('division-export')
            ->by(auth()->user())
            ->log('Division csv exported');

        return Excel::download(new DivisionExport(), now().'divisions.csv');
    }
}
