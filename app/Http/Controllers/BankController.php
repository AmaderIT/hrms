<?php

namespace App\Http\Controllers;

use App\Http\Requests\bank\RequestBank;
use App\Models\Bank;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BankController extends Controller
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
        $items = Bank::select("id", "name")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('bank.index', compact('items'));
    }

    /**
     * @param Bank $bank
     * @return Application|Factory|View
     */
    public function edit(Bank $bank)
    {
        return view("bank.edit", compact('bank'));
    }

    /**
     * @param RequestBank $request
     * @return RedirectResponse|string
     */
    public function store(RequestBank $request)
    {
        try {
            Bank::create($request->validated());

            session()->flash('message', 'Bank Created Successfully');
            $redirect = redirect()->route("bank.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestBank $request
     * @param Bank $bank
     * @return RedirectResponse
     */
    public function update(RequestBank $request, Bank $bank)
    {
        try {
            $bank->update($request->validated());

            session()->flash('message', 'Bank Updated Successfully');
            $redirect = redirect()->route("bank.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Bank $bank
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(Bank $bank)
    {
        try {
            $feedback['status'] = $bank->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
