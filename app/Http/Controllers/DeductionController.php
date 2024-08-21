<?php

namespace App\Http\Controllers;

use App\Http\Requests\deduction\RequestDeduction;
use App\Models\Deduction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class DeductionController extends Controller
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
        $items = Deduction::orderBy("name")->select("id", "name")->paginate(\Functions::getPaginate());
        return view("deduction.index", compact('items'));
    }

    /**
     * @param Deduction $deduction
     * @return Application|Factory|View
     */
    public function edit(Deduction $deduction)
    {
        return view("deduction.edit", compact('deduction'));
    }

    /**
     * @param RequestDeduction $request
     * @return RedirectResponse
     */
    public function store(RequestDeduction $request)
    {
        try {
            Deduction::create($request->validated());

            session()->flash('message', 'Deduction Created Successfully');
            $redirect = redirect()->route("deduction.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestDeduction $request
     * @param Deduction $deduction
     * @return RedirectResponse
     */
    public function update(RequestDeduction $request, Deduction $deduction)
    {
        try {
            $deduction->update($request->validated());

            session()->flash('message', 'Deduction Updated Successfully');
            $redirect = redirect()->route("deduction.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Deduction $deduction
     * @return mixed
     */
    public function delete(Deduction $deduction)
    {
        try {
            $feedback['status'] = $deduction->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
