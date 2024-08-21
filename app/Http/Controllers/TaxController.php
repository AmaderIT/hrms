<?php

namespace App\Http\Controllers;

use App\Http\Requests\tax\RequestTax;
use App\Models\Tax;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class TaxController extends Controller
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
     * @return Factory|View
     */
    public function index()
    {
        $items = Tax::select("id", "name", "status")->orderByDesc("id")->paginate(\Functions::getPaginate());
        return view("tax.index", compact("items"));
    }

    /**
     * @param Tax $tax
     * @return Factory|View
     */
    public function edit(Tax $tax)
    {
        return view("tax.edit", compact("tax"));
    }

    /**
     * @param RequestTax $request
     * @return RedirectResponse
     */
    public function store(RequestTax $request)
    {
        try {
            Tax::create($request->validated());

            session()->flash("message", "Tax Created Successfully");
            $redirect = redirect()->route("tax.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestTax $request
     * @param Tax $tax
     * @return RedirectResponse
     */
    public function update(RequestTax $request, Tax $tax)
    {
        try {
            $tax->update($request->validated());

            session()->flash("message", "Tax Updated Successfully");
            $redirect = redirect()->route("tax.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Tax $tax
     * @return bool
     */
    public function changeStatus(Tax $tax)
    {
        try {
            $status = !$tax->status;

            $success = $tax->update(["status" => $status]);
            activity('change-status')->by(auth()->user())->log('Tax status has been changed');

        } catch (Exception $exception) {
            $success = false;
        }

        return (bool) $success;
    }

    /**
     * @param Tax $tax
     * @return RedirectResponse
     */
    public function copy(Tax $tax)
    {
        DB::beginTransaction();

        try {
            $newTax = Tax::create([
                "name"              => $tax["name"] . " - copy",
                "eligible_rebate"   => $tax["eligible_rebate"],
                "tax_rebate"        => $tax["tax_rebate"],
                "min_tax_amount"    => $tax["min_tax_amount"],
                "status"            => 0
            ]);

            $taxRules = $tax->load("rules");

            if($taxRules->rules->count() > 0) {
                foreach ($taxRules->rules as $rule) {
                    $newTax->rules()->create([
                        "slab"  => $rule["slab"],
                        "rate"  => $rule["rate"],
                        "gender"=> $rule["gender"]
                    ]);
                }
            }

            DB::commit();

            session()->flash("message", "Tax Copied Successfully");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry! Something went wrong!!");

            DB::rollBack();
        }

        return redirect()->back();
    }

    /**
     * @param Tax $tax
     * @return array
     */
    public function delete(Tax $tax)
    {
        try {
            $feedback['status'] = $tax->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @return Application|Factory|View
     */
    public function taxHistory(Request $request)
    {
        try {
            session()->flash("type", "error");
            if($request->has('search')) {
                $user = User::where('fingerprint_no', $request->input('search'))->first();
                if($user == null) {
                    session()->flash("message", "There is no such users with this fingerprint ID.");
                    return redirect('tax.history');
                }

                $salaries = Salary::with("user", "department")->where('year', date("Y"))->where('user_id', $user->id)->get();

            } else {
                $salaries = Salary::with('user', "department")->whereUserId(auth()->user()->id)->where('year', date("Y"))->get();
            }

            $total = $salaries->sum('taxable_amount');

            if($total > 0 || auth()->user()->roles->first()->name == User::ROLE_GENERAL_USER || auth()->user()->roles->first()->name == User::ROLE_ADMIN) {
                return view('tax.tax_history', compact('salaries', 'total'));
            }

            session()->flash("message", "This employee does not have any tax.");
            $redirect = back();
        } catch (\Exception $e) {
            session()->flash("type", "error");
            session()->flash("message", $e->getMessage());
            $redirect = redirect('tax.history');
        }

        return $redirect;
    }
}
