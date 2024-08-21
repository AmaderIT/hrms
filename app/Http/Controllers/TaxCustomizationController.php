<?php

namespace App\Http\Controllers;

use App\Http\Requests\bank\RequestBank;
use App\Http\Requests\taxCustomization\RequestTaxCustomization;
use App\Models\Bank;
use App\Models\Department;
use App\Models\OfficeDivision;
use App\Models\Salary;
use App\Models\TaxCustomization;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class TaxCustomizationController extends Controller
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
        $items = TaxCustomization::with("user")->paginate(\Functions::getPaginate());
        return view('tax-customization.index', compact('items'));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        $users = User::active()->select("id", "name", "email", "fingerprint_no", "status")->get();
        return view('tax-customization.create', compact('users'));
    }

    /**
     * @param TaxCustomization $taxCustomization
     * @return Application|Factory|View
     */
    public function edit(TaxCustomization $taxCustomization)
    {
        $users = User::active()->select("id", "name", "email", "fingerprint_no", "status")->get();
        return view("tax-customization.edit", compact('users', "taxCustomization"));
    }

    /**
     * @param RequestTaxCustomization $request
     * @return RedirectResponse
     */
    public function store(RequestTaxCustomization $request)
    {
        try {
            TaxCustomization::firstOrCreate($request->validated());
            session()->flash('message', 'Tax Customization Requested Successfully');
            $redirect = redirect()->route("tax-customization.index");
        } catch (Exception $exception) {
            session()->flash("type", "danger");
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestTaxCustomization $request
     * @param TaxCustomization $taxCustomization
     * @return RedirectResponse
     */
    public function update(RequestTaxCustomization $request, TaxCustomization $taxCustomization)
    {
        try {
            $taxCustomization->update($request->validated());

            session()->flash('message', 'Tax Customization Updated Successfully');
            $redirect = redirect()->route("tax-customization.index");
        } catch (Exception $exception) {
            session()->flash("type", "danger");
            session()->flash('message', $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param TaxCustomization $taxCustomization
     * @return mixed
     */
    public function delete(TaxCustomization $taxCustomization)
    {
        try {
            $feedback['status'] = $taxCustomization->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param User $user
     * @return Factory|View
     */
    public function details(User $user)
    {
        $items = Salary::where("user_id", $user->id)->orderByDesc("id")->get();
        $data = [
            "items"                 => $items,
            "totalTaxableAmount"    => $items->sum("taxable_amount"),
            "totalPayableAmount"    => $items->sum("payable_tax_amount"), 2,
            "totalDueAmount"        => $items->sum("taxable_amount") - $items->sum("payable_tax_amount"),
        ];
        return \view("tax-customization.details", compact('data'));
    }
}
