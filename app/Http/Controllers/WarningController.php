<?php

namespace App\Http\Controllers;

use App\Http\Requests\warning\RequestWarning;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class WarningController extends Controller
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
        $items = Warning::with("user", "warnBy")
                ->select("id", "user_id", "memo_no", "level", "subject", "warned_by", "warning_date")
                ->orderBy('id','desc')
                ->paginate(10);

        return view('warning.index', compact('items'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $items = User::select("id", "name")->get();
        return view('warning.create', compact('items'));
    }

    /**
     * @param Warning $warning
     * @return Application|Factory|View
     */
    public function edit(Warning $warning)
    {
        $users = User::select("id", "name")->get();
        return view("warning.edit", compact('users','warning'));
    }

    /**
     * @param RequestWarning $request
     * @return RedirectResponse
     */
    public function store(RequestWarning $request)
    {
        try {
            Warning::create($request->validated());
            session()->flash('message', 'Warning Created Successfully');
            $redirect = redirect()->route("warning.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestWarning $request
     * @param Warning $warning
     * @return RedirectResponse
     */
    public function update(RequestWarning $request, Warning $warning)
    {
        try {
            $warning->update($request->validated());
            session()->flash('message', 'Warning Updated Successfully');
            $redirect = redirect()->route("warning.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }
}
