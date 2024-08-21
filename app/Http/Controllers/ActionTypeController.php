<?php

namespace App\Http\Controllers;

use App\Http\Requests\action\RequestActionType;
use App\Models\ActionReason;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class ActionTypeController extends Controller
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
            ->types()
            ->select("id", "parent_id", "name")
            ->orderBy("name")
            ->paginate(\Functions::getPaginate());

        return view("action-type.index", compact("items"));
    }

    /**
     * @param ActionReason $actionType
     * @return Application|Factory|View
     */
    public function edit(ActionReason $actionType)
    {
        return view("action-type.edit", compact("actionType"));
    }

    /**
     * @param RequestActionType $request
     * @return RedirectResponse
     */
    public function store(RequestActionType $request)
    {
        try {
            ActionReason::create($request->validated());

            session()->flash("message", "Action Type Created Successfully");
            $redirect = redirect()->route("action-type.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestActionType $request
     * @param ActionReason $actionType
     * @return RedirectResponse
     */
    public function update(RequestActionType $request, ActionReason $actionType)
    {
        try {
            $actionType->update($request->validated());

            session()->flash('message', 'Action Type Updated Successfully');
            $redirect = redirect()->route("action-type.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param ActionReason $actionType
     * @return mixed
     */
    public function delete(ActionReason $actionType)
    {
        // TODO: Remove parent(s) with all children
        try {
            $feedback['status'] = $actionType->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
