<?php

namespace App\Http\Controllers;

use App\Http\Requests\taxRule\RequestTaxRule;
use App\Models\Tax;
use App\Models\TaxRule;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class TaxRuleController extends Controller
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
     * @param Tax $tax
     * @return Factory|View
     */
    public function edit(Tax $tax)
    {
        $tax = $tax->load("rules");
        return view("tax-rule.edit", compact("tax"));
    }

    /**
     * @param RequestTaxRule $request
     * @param $tax
     * @return RedirectResponse
     */
    public function update(RequestTaxRule $request, Tax $tax)
    {
        try {
            DB::transaction(function () use ($request, $tax) {
                $tax->rules()->delete();

                # Male
                $taxRules = array();
                foreach ($request->input("slab_male") as $key => $value)
                {
                    $rules = array(
                        "tax_id"        => $tax->id,
                        "slab"          => $request->input("slab_male")[$key],
                        "rate"          => $request->input("tax_rate_male")[$key],
                        "gender"        => TaxRule::GENDER_MALE,
                        "created_at"    => now(),
                        "updated_at"    => now()
                    );

                    array_push($taxRules, $rules);
                }

                if($request->has("remaining_rate_male"))
                {
                    $rules = array(
                        "tax_id"        => $tax->id,
                        "slab"          => TaxRule::SLAB_REMAINING,
                        "rate"          => $request->input("remaining_rate_male"),
                        "gender"        => TaxRule::GENDER_MALE,
                        "created_at"    => now(),
                        "updated_at"    => now()
                    );

                    array_push($taxRules, $rules);
                }

                # Female
                foreach ($request->input("slab_female") as $key => $value)
                {
                    $rules = array(
                        "tax_id"        => $tax->id,
                        "slab"          => $request->input("slab_female")[$key],
                        "rate"          => $request->input("tax_rate_female")[$key],
                        "gender"        => TaxRule::GENDER_FEMALE,
                        "created_at"    => now(),
                        "updated_at"    => now()
                    );

                    array_push($taxRules, $rules);
                }

                if($request->has("remaining_rate_female"))
                {
                    $rules = array(
                        "tax_id"        => $tax->id,
                        "slab"          => TaxRule::SLAB_REMAINING,
                        "rate"          => $request->input("remaining_rate_female"),
                        "gender"        => TaxRule::GENDER_FEMALE,
                        "created_at"    => now(),
                        "updated_at"    => now()
                    );

                    array_push($taxRules, $rules);
                }

                # Rebate
                foreach ($request->input("slab_rebate") as $key => $value)
                {
                    $rules = array(
                        "tax_id"        => $tax->id,
                        "slab"          => $request->input("slab_rebate")[$key],
                        "rate"          => $request->input("tax_rate_rebate")[$key],
                        "gender"        => TaxRule::TYPE_REBATE,
                        "created_at"    => now(),
                        "updated_at"    => now()
                    );

                    array_push($taxRules, $rules);
                }

                if($request->has("remaining_rate_rebate"))
                {
                    $rules = array(
                        "tax_id"        => $tax->id,
                        "slab"          => TaxRule::SLAB_REMAINING,
                        "rate"          => $request->input("remaining_rate_rebate"),
                        "gender"        => TaxRule::TYPE_REBATE,
                        "created_at"    => now(),
                        "updated_at"    => now()
                    );

                    array_push($taxRules, $rules);
                }

                TaxRule::insert($taxRules);

                # Tax
                $tax->update(array(
                    "eligible_rebate"   => $request->input("eligible_rebate"),
                    "tax_rebate"        => $request->input("tax_rebate") ?? 0,
                    "min_tax_amount"    => $request->input("min_tax_amount")
                ));
            });

            session()->flash("message", "Tax Rule Updated Successfully");
            $redirect = redirect()->route("tax.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }
}
