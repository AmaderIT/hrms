<?php

namespace App\Http\Controllers;

use App\Http\Requests\paygrade\RequestPaygrade;
use App\Models\Earning;
use App\Models\Deduction;
use App\Models\PayGrade;
use App\Models\PayGradeDeduction;
use App\Models\PayGradeEarning;
use App\Models\Profile;
use App\Models\Promotion;
use App\Models\Tax;
use App\Models\TaxRule;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class PayGradeController extends Controller
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
        $items = PayGrade::with("earnings", "deductions")->select("id", "name", "based_on", "percentage_of_basic", "overtime_formula")->orderBy("name")->paginate(\Functions::getPaginate());
        return view('paygrade.index', compact('items'));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        $data = $this->formData();
        return view('paygrade.create',compact("data"));
    }

    /**
     * @param PayGrade $paygrade
     * @return Application|Factory|View
     */
    public function edit(PayGrade $paygrade)
    {
        $data = $this->formData();
        $paygrade = $paygrade->load("earnings");

        return view("paygrade.edit", compact("paygrade", "data"));
    }

    /**
     * @param RequestPaygrade $request
     * @return RedirectResponse|string
     */
    public function store(RequestPaygrade $request)
    {
        try {
            DB::transaction(function () use ($request) {
                # PayGrade
                $payGrade = PayGrade::create($request->validated());

                # PayGrade Earnings
                $payGradeEarnings = [];
                if($request->input("earning_type") !== null) {
                    foreach ($request->input("earning_type") as $key => $value) {
                        if (!is_null($request->input("earning_type")[$key]) AND !is_null($request->input("earning_value")[$key]) AND $request->input("earning_non_taxable") != 0) {
                            array_push($payGradeEarnings, [
                                "pay_grade_id" => $payGrade->id,
                                "earning_id" => $request->input("earning_id")[$key],
                                "type" => $request->input("earning_type")[$key],
                                "value" => $request->input("earning_value")[$key],
                                "tax_exempted" => $request->input("earning_tax_exempted")[$key] ?? 0,
                                "tax_exempted_percentage" => $request->input("earning_tax_exempted_percentage")[$key] ?? 0,
                                "non_taxable" => $request->input("earning_non_taxable")[$key],
                                "created_at" => now()
                            ]);
                        }
                    }
                    $payGrade->earnings()->createMany($payGradeEarnings);
                }

                # PayGrade Deductions
                $payGradeDeductions = [];
                if($request->input("deduction_type") !== null) {
                    foreach ($request->input("deduction_type") as $key => $value) {
                        if (!is_null($request->input("deduction_type")[$key]) AND !is_null($request->input("deduction_value")[$key])) {
                            array_push($payGradeDeductions, [
                                "pay_grade_id" => $payGrade->id,
                                "deduction_id" => $request->input("deduction_id")[$key],
                                "type" => $request->input("deduction_type")[$key],
                                "value" => $request->input("deduction_value")[$key],
                                "created_at" => now()
                            ]);
                        }
                    }
                    $payGrade->deductions()->createMany($payGradeDeductions);
                }

                session()->flash('message', 'PayGrade Created Successfully');
            });
            $redirect = redirect()->route("paygrade.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestPaygrade $request
     * @param PayGrade $paygrade
     * @return RedirectResponse
     */
    public function update(RequestPaygrade $request, PayGrade $paygrade)
    {
        try {
            DB::transaction(function () use ($request, $paygrade) {
                $paygrade->update($request->validated());

                # Update PayGrade Earnings
                if($request->input("earning_type") !== null) {
                    foreach ($request->input("earning_type") as $key => $value) {
                        if (!is_null($request->input("earning_type")[$key])) {
                            PayGradeEarning::updateOrCreate([
                                "pay_grade_id" => $paygrade->id,
                                "earning_id" => $request->input("earning_id")[$key],
                            ], [
                                "pay_grade_id" => $paygrade->id,
                                "earning_id" => $request->input("earning_id")[$key],
                                "type" => $request->input("earning_type")[$key],
                                "value" => $request->input("earning_value")[$key] ?? 0,
                                "tax_exempted" => $request->input("earning_tax_exempted")[$key] ?? 0,
                                "tax_exempted_percentage" => $request->input("earning_tax_exempted_percentage")[$key] ?? 0,
                                "non_taxable" => $request->input("earning_non_taxable")[$key],
                            ]);
                        }
                    }
                }

                # Update PayGrade Deductions
                if($request->input("deduction_type") !== null) {
                    foreach ($request->input("deduction_type") as $key => $value) {
                        if (!is_null($request->input("deduction_type")[$key]) AND !is_null($request->input("deduction_value")[$key])) {
                            PayGradeDeduction::updateOrCreate([
                                "pay_grade_id" => $paygrade->id,
                                "deduction_id" => $request->input("deduction_id")[$key],
                            ], [
                                "pay_grade_id" => $paygrade->id,
                                "deduction_id" => $request->input("deduction_id")[$key],
                                "type" => $request->input("deduction_type")[$key],
                                "value" => $request->input("deduction_value")[$key],
                            ]);
                        }
                    }
                }
            });

            session()->flash('message', 'PayGrade Updated Successfully');
            $redirect = redirect()->route("paygrade.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param PayGrade $paygrade
     * @return mixed
     */
    public function delete(PayGrade $paygrade)
    {
        try {
            $feedback['status'] = $paygrade->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @return Factory|View
     */
    public function generateSalarySheet()
    {
        $items = Promotion::with("user", "department", "designation", "payGrade")
            ->select("id", "user_id", "department_id", "designation_id", "salary", "pay_grade_id")
            ->orderByDesc("id")
            ->paginate(\Functions::getPaginate());

        return view('paygrade.generate_salary_sheet', compact('items'));
    }

    /**
     * @param User $user
     * @return Factory|View
     */
    public function generatePaySlip(User $user)
    {
        try {
            $data = array(
                "employee"              => $user,
                "employeeEarnings"      => $user->employeeEarnings(),
                "employeeDeductions"    => $user->employeeDeductions(),
                "employeeTaxableAmount" => $this->employeeTaxableAmountForCurrentMonth($user)
            );

            activity('payslip-generated')->by(auth()->user())->log('Pay Slip has been generated');
            $redirect = view('paygrade.generate_pay_slip', compact("data"));
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry! Something wrong with Pay Slip generation");
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function pdfDownload(User $user)
    {
        $data = array(
            "employee"              => $user,
            "employeeEarnings"      => $user->employeeEarnings(),
            "employeeDeductions"    => $user->employeeDeductions(),
            "employeeTaxableAmount" => $this->employeeTaxableAmountForCurrentMonth($user)
        );

        activity('payslip-download')->by(auth()->user())->log('Pay Slip has been exported');

        $pdf = PDF::loadView('paygrade.pay_slip_pdf', compact("data"));
        return $pdf->download('invoice.pdf','paygrade.pay_slip_pdf', compact("data"));
    }

    /**
     * @param User $user
     * @return float|int
     */
    protected function employeeTaxableAmountForCurrentMonth(User $user)
    {
        try {
            $employeeGender = $user->load("profile")->profile->gender;
            $latestPromotion = $user->latestPromotion();
            $salary = $latestPromotion->salary;
            $employeePayGrade = $latestPromotion->payGrade;
            $basicSalary = $salary * ($employeePayGrade->percentage_of_basic / 100);

            $employeeTax = $employeePayGrade->load("tax.rules")->tax;
            $employeeTaxRules = $employeePayGrade->load("tax.rules")->tax->rules->groupBy("gender");

            if($employeeGender === Profile::GENDER_MALE) {
                $gender = TaxRule::GENDER_MALE;
            } elseif($employeeGender === Profile::GENDER_FEMALE) {
                $gender = TaxRule::GENDER_FEMALE;
            }

            $taxRules = $employeeTaxRules[$gender];

            $taxRulesPerMonth = array();
            $taxRatesPerMonth = array();
            $temp = 0;
            foreach ($taxRules as $taxRule)
            {
                if($taxRule->slab != TaxRule::SLAB_REMAINING) {
                    $temp += $taxRule->slab / 12;
                    array_push($taxRulesPerMonth, $temp);
                    array_push($taxRatesPerMonth, $taxRule->rate);
                } elseif($taxRule->slab == TaxRule::SLAB_REMAINING) {
                    array_push($taxRulesPerMonth, PHP_INT_MAX);
                    array_push($taxRatesPerMonth, $taxRule->rate);
                }
            }

            $taxRulesPerMonth = array_reverse($taxRulesPerMonth);
            $taxRatesPerMonth = array_reverse($taxRatesPerMonth);

            $taxableAmount = 0;
            for($i = 0; $i < count($taxRulesPerMonth); $i++)
            {
                if($basicSalary >= $taxRulesPerMonth[$i])
                {
                    $taxableInThisMonth = $basicSalary - $taxRulesPerMonth[$i];
                    $taxableAmount += $taxableInThisMonth * ($taxRatesPerMonth[$i-1] / 100);
                    $basicSalary -= $taxableInThisMonth;
                }
            }

            # Rebate manipulation
            if($taxableAmount > 0) {
                $employeeMinTaxPerMonth = $employeeTax->min_tax_amount / 12;
                if (isset($employeeTax->eligible_rebate)) $taxableAmount *= ($employeeTax->eligible_rebate / 100);
                if (isset($employeeTax->tax_rebate)) $taxableAmount *= ($employeeTax->tax_rebate / 100);
                $taxableAmount = max(array($taxableAmount, $employeeMinTaxPerMonth));
            }
        } catch (Exception $exception) {
            $taxableAmount = 0;
        }

        return $taxableAmount;
    }

    /**
     * @param int $number
     * @return false|string
     */
    public static function convertToWord($number = 0)
    {
        $number = round($number, 2);

        $formatter = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );
        $word = $formatter->format($number);
        $word = ucwords($word);

        return $word;
    }

    /**
     * @return array
     */
    protected function formData()
    {
        return array(
            "earnings"   => Earning::select("id", "name")->get(),
            "deductions" => Deduction::select("id", "name")->get(),
            "tax"        => Tax::with("rules")->active()->first()
        );
    }
}
