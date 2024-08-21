<?php

namespace App\Http\Controllers;

use App\Http\Requests\bonus\RequestBonus;
use App\Models\Bonus;
use App\Models\SalaryDepartment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Exception;

class BonusController extends Controller
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
        $items = Bonus::where('status', 1)->orderBy('festival_name')->paginate(\Functions::getPaginate());
        return view('bonus.index', compact('items'));
    }

    /**
     * @param Bonus $bonus
     * @return Factory|View
     */
    public function edit(Bonus $bonus)
    {
        $paymentDetails = json_decode($bonus->payment_details, true);
        return view("bonus.edit", compact('bonus', 'paymentDetails'));
    }

    /**
     * @param RequestBonus $request
     * @return RedirectResponse
     */
    public function store(RequestBonus $request)
    {
        try {
            $inputs = $request->validated();

            $paymentDetails = [];
            if ((!empty($inputs['employment_period_one'])) && (!empty($inputs['percentage_one']))) {
                $paymentDetails[$inputs['employment_period_one']] = $inputs['percentage_one'];
            }

            if ((!empty($inputs['employment_period_two'])) && (!empty($inputs['percentage_two']))) {
                $paymentDetails[$inputs['employment_period_two']] = $inputs['percentage_two'];
            }

            $data = [
                "festival_name" => $inputs['festival_name'],
                "type" => $inputs['type'],
                "effective_date" => $inputs['effective_date'],
                "payment_details" => json_encode($paymentDetails),
                "created_by" => Auth::id(),
            ];

            Bonus::create($data);

            session()->flash('message', 'Bonus Successfully Created');
            $redirect = redirect()->route("bonus.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestBonus $request
     * @param Bonus $bonus
     * @return RedirectResponse
     */
    public function update(RequestBonus $request, Bonus $bonus)
    {
        try {
            $inputs = $request->validated();

            $paymentDetails = [];
            if ((!empty($inputs['employment_period_one'])) && (!empty($inputs['percentage_one']))) {
                $paymentDetails[$inputs['employment_period_one']] = $inputs['percentage_one'];
            }

            if ((!empty($inputs['employment_period_two'])) && (!empty($inputs['percentage_two']))) {
                $paymentDetails[$inputs['employment_period_two']] = $inputs['percentage_two'];
            }

            $data = [
                "festival_name" => $inputs['festival_name'],
                "type" => $inputs['type'],
                "effective_date" => $inputs['effective_date'],
                "payment_details" => json_encode($paymentDetails),
                "updated_by" => Auth::id(),
            ];

            $bonus->update($data);

            session()->flash('message', 'Bonus Successfully Updated');
            $redirect = redirect()->route("bonus.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Bonus $bonus
     * @return mixed
     */
    public function delete(Bonus $bonus)
    {
        try {
            $data = ['status' => 0, 'updated_by' => Auth::id()];
            $feedback['status'] = $bonus->update($data);
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
