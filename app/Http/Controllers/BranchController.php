<?php

namespace App\Http\Controllers;

use App\Http\Requests\branch\RequestBranch;
use App\Models\Bank;
use App\Models\Branch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class BranchController extends Controller
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
        $items = Branch::select("id", "name")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('branch.index', compact('items'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $items = Bank::select("id", "name")->get();
        return view('branch.create', compact('items'));
    }

    /**
     * @param Branch $branch
     * @return Application|Factory|View
     */
    public function edit(Branch $branch)
    {
        $items = Bank::select("id", "name")->get();
        return view("branch.edit", compact("branch", "items"));
    }

    /**
     * @param RequestBranch $request
     * @return RedirectResponse
     */
    public function store(RequestBranch $request)
    {
        try {
            Branch::create($request->validated());

            session()->flash('message', 'Branch Created Successfully');
            $redirect = redirect()->route("branch.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestBranch $request
     * @param Branch $branch
     * @return RedirectResponse
     */
    public function update(RequestBranch $request, Branch $branch)
    {
        try {
            $branch->update($request->validated());

            session()->flash('message', 'Branch Updated Successfully');
            $redirect = redirect()->route("branch.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Branch $branch
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(Branch $branch)
    {
        try {
            $feedback['status'] = $branch->delete();
            session()->flash('message', 'Branch Deleted Successfully');
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
