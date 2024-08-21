<?php

namespace App\Http\Controllers;

use App\Library\Filter;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Models\Meal;
use Illuminate\View\View;

class MealController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $filter = new Filter(User::class, ["fingerprint_no", "name"], $request->input("search"));

        $items = $filter->with('meal')->paginate(100);

        return view("meal.index", compact("items"));
    }

    /**
     * Change Status to meal
     *
     * @param Request $request
     * @return bool
     */
    public function changeStatus(Request $request)
    {
        try {
            $status = $request->input("status");
            $user_id = $request->input("user_id");
            $user = User::findOrFail($user_id);

            $success = Meal::updateOrCreate(
                [
                    "user_id" => $user_id,
                ],
                [
                    "user_id" => $user_id,
                    'status'     => $status,
                ]
            );

            if($status == 1) {
                activity('change-status')->by(auth()->user())->log('Active Meal Consumer '.$user->name.' status has been turned on');
            } else {
                activity('change-status')->by(auth()->user())->log('Active Meal Consumer '.$user->name.' status has been turned off');
            }

        } catch (\Exception $exception) {
            $success = false;
        }

        return (bool) $success;
    }

}
