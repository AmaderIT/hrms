<?php

namespace App\Http\Controllers;

use App\Http\Requests\action\RequestActionReason;
use App\Models\ActionReason;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class ActionReasonController extends Controller
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
        $items = ActionReason::with("parent")
                    ->reasons()
                    ->select("id", "parent_id", "name", "reason")
                    ->orderBy("name")
                    ->paginate(\Functions::getPaginate());

        return view("action-reason.index", compact("items"));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $types = ActionReason::types()->get();
        return view("action-reason.create", compact("types"));
    }

    /**
     * @param ActionReason $actionReason
     * @return Application|Factory|View
     */
    public function edit(ActionReason $actionReason)
    {
        $types = ActionReason::types()->get();
        return view("action-reason.edit", compact("actionReason", "types"));
    }

    /**
     * @param RequestActionReason $request
     * @return RedirectResponse
     */
    public function store(RequestActionReason $request)
    {
        try {
            ActionReason::create($request->validated());

            session()->flash("message", "Action Reason Created Successfully");
            $redirect = redirect()->route("action-reason.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestActionReason $request
     * @param ActionReason $actionReason
     * @return RedirectResponse
     */
    public function update(RequestActionReason $request, ActionReason $actionReason)
    {
        try {
            $actionReason->update($request->validated());

            session()->flash("message", "Action Reason Updated Successfully");
            $redirect = redirect()->route("action-reason.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param ActionReason $actionReason
     * @return mixed
     */
    public function delete(ActionReason $actionReason)
    {
        try {
            $feedback['status'] = $actionReason->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
