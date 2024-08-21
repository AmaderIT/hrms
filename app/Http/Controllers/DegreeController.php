<?php

namespace App\Http\Controllers;

use App\Http\Requests\degree\RequestDegree;
use App\Models\Degree;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Mockery\Exception;

class DegreeController extends Controller
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
        $items = Degree::select('id', 'name')->orderBy("name")->paginate(\Functions::getPaginate());
        return view('degree.index', compact('items'));
    }

    /**
     * @param Degree $degree
     * @return Application|Factory|View
     */
    public function edit(Degree $degree)
    {
        return view("degree.edit", compact('degree'));
    }

    /**
     * @param RequestDegree $request
     * @return RedirectResponse|string
     */
    public function store(RequestDegree $request)
    {
        try {
            Degree::create($request->validated());
            session()->flash('message', 'Degree Created Successfully');
            $redirect = redirect()->route("degree.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestDegree $request
     * @param Degree $degree
     * @return RedirectResponse
     */
    public function update(RequestDegree $request, Degree $degree)
    {
        try {
            $degree->update($request->validated());

            session()->flash('message', 'Degree Updated Successfully');
            $redirect = redirect()->route("degree.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    public function delete(Degree $degree)
    {
        try {
            $feedback['status'] = $degree->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
