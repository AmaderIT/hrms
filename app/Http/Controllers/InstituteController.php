<?php

namespace App\Http\Controllers;

use App\Http\Requests\institute\RequestInstitute;
use App\Models\Institute;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class InstituteController extends Controller
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
        $items = Institute::select("id", "name")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('institute.index', compact('items'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        return view('institute.create');
    }

    /**
     * @param Institute $institute
     * @return Application|Factory|View
     */
    public function edit(Institute $institute)
    {
        return view("institute.edit", compact('institute'));
    }

    /**
     * @param RequestInstitute $request
     * @return RedirectResponse|string
     */
    public function store(RequestInstitute $request)
    {
        try {
            Institute::create($request->validated());

            session()->flash('message', 'Institute Created Successfully');
            $redirect = redirect()->route("institute.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestInstitute $request
     * @param Institute $institute
     * @return RedirectResponse
     */
    public function update(RequestInstitute $request, Institute $institute)
    {
        try {
            $institute->update($request->validated());

            session()->flash('message', 'Institute Updated Successfully');
            $redirect = redirect()->route("institute.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Institute $institute
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Institute $institute)
    {
        try {
            $feedback['status'] = $institute->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    public function storeAjx(RequestInstitute $request)
    {
        $this->store($request);
        return \response()->json([
            'status' => 'success',
            'institutes' => Institute::orderBy("name")->select("id", "name")->get(),
            'message' => 'Institute Created Successfully'
        ]);
    }
}
