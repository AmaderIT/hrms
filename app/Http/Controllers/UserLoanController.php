<?php

namespace App\Http\Controllers;

use App\Http\Requests\loan\RequestCustomLoan;
use App\Models\Loan;
use App\Models\OfficeDivision;
use App\Models\User;
use App\Models\UserLoan;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class UserLoanController extends Controller
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
        $data = array(
            "officeDivisions" => OfficeDivision::select("id", "name")->get()
        );

        return view("user-loan.custom-loan", compact("data"));
    }

    /**
     * @param RequestCustomLoan $request
     * @return RedirectResponse
     */
    public function pay(RequestCustomLoan $request)
    {
        try {
            $data = $request->validated();
            $data['is_custom_payment'] = UserLoan::YES;
            $data['status'] = UserLoan::DEDUCTED;
            $data['created_by'] = auth()->id();
            UserLoan::create($data);
            session()->flash("message", "Loan Deducted Successfully");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something went wrong!!!');
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function customPayment(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            if (empty($request->uuid)) {
                throw new Exception('Loan identity can\'t be matched!');
            }

            if (empty($request->remark)) {
                throw new Exception('Remark can\'t be empty!');
            }

            $userLoan = UserLoan::where(['uuid' => $request->uuid, 'status' => UserLoan::AMOUNT_APPROVED])->first();

            if (empty($userLoan)) {
                throw new Exception('Installment not found!!');
            }

            if ($userLoan->loan->status != Loan::STATUS_ACTIVE || $userLoan->loan->loan_paid_status != Loan::STATUS_APPROVED || $userLoan->loan->hr_approval_status != Loan::STATUS_APPROVED) {
                throw new Exception('Mismatch employee loan info!! Please try again!');
            }

            # Update Instalment Table
            $userLoan->status = UserLoan::DEDUCTED;
            $userLoan->updated_by = $userId;
            $userLoan->remark = trim(strip_tags($request->remark));
            $userLoan->is_custom_payment = 'Y';
            $userLoan->save();

            # Update if loan amount is paid
            if ($this->checkLoanAmountPaidOrNot($userLoan->loan)) {
                $userLoan->loan->status = Loan::STATUS_PAID;
                $userLoan->loan->instalment_paid_at = date('Y-m-d');
                $userLoan->loan->update();
            }

            DB::commit();

            session()->flash('message', 'Loan has been paid successfully');
        } catch (Exception $exception) {
            DB::rollBack();

            session()->flash('type', 'error');
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->back();
    }

    public function checkLoanAmountPaidOrNot(Loan $loan)
    {
        $totalPaidAmount = UserLoan::where(['loan_id' => $loan->id, 'status' => UserLoan::DEDUCTED])->sum('amount_paid');

        if (round(($totalPaidAmount + .5)) >= $loan->loan_amount) {
            return true;
        } else {
            return false;
        }
    }
}
