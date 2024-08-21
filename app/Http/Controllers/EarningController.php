<?php

namespace App\Http\Controllers;

use App\Http\Requests\earning\RequestEarning;
use App\Models\Earning;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class EarningController extends Controller
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
        $items = Earning::orderBy("name")->select("id", "name")->paginate(\Functions::getPaginate());
        return view('earning.index', compact('items'));
    }

    /**
     * @param Earning $earning
     * @return Factory|View
     */
    public function edit(Earning $earning)
    {
        return view("earning.edit", compact('earning'));
    }

    /**
     * @param RequestEarning $request
     * @return RedirectResponse
     */
    public function store(RequestEarning $request)
    {
        try {
            Earning::create($request->validated());

            session()->flash('message', 'Earning Created Successfully');
            $redirect = redirect()->route("earning.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestEarning $request
     * @param Earning $earning
     * @return RedirectResponse
     */
    public function update(RequestEarning $request, Earning $earning)
    {
        try {
            $earning->update($request->validated());

            session()->flash('message', 'Earning Updated Successfully');
            $redirect = redirect()->route("earning.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Earning $earning
     * @return mixed
     */
    public function delete(Earning $earning)
    {
        try {
            $feedback['status'] = $earning->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
