<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\UserMeal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserMealController extends Controller
{
    public function insertActiveMealConsumer()
    {
        try {
            $today = Carbon::today()->format('Y-m-d');
            $activeMealConsumers = Meal::active()->get();

            foreach($activeMealConsumers as $key => $activeMealConsumer) {
                UserMeal::firstOrCreate([
                    "user_id" => $activeMealConsumer->user_id,
                    "date"    => $today
                ], [
                    "user_id" => $activeMealConsumer->user_id,
                    "status" => $activeMealConsumer->status,
                ]);
            }

            $response = [ "success" => true ];
        } catch (\Exception $exception) {
            $response = [
                "success" => false,
                "message" => $exception->getMessage()
            ];
        }

        return $response;

    }

    public function changeStatus(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $user = auth()->user();

        try {
            $status = $request->input("status");

            if($user->dailyMeal == null) {
                $message = 'Meal request accepted!!';
                if(optional($user->meal)->status == 1) {
                    $status = 0;
                    $message = 'Meal cancel request accepted!!';
                }
            } else {
                if(optional($user->meal)->status == 1) {
                    $status = !$request->input("status");
                    $message = 'Meal cancel request accepted!!';
                } else {
                    $message = 'Meal request accepted!!';
                }
            }

            $success = UserMeal::updateOrCreate(
                [
                    "user_id" => $request->input("user_id"),
                    "date" => $today,

                ],
                [
                    "user_id" => $request->input("user_id"),
                    'status'     => $status,
                ]
            );

        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $success = false;
        }

        return [
            'status' => (bool) $success,
            'message' => $message,
        ];
    }

    public function changeStatusOfTomorrow(Request $request)
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        try {
            $status = $request->input("status");
            $success = UserMeal::updateOrCreate(
                [
                    "user_id" => $request->input("user_id"),
                    "date" => $tomorrow,

                ],
                [
                    "user_id" => $request->input("user_id"),
                    'status'     => $status,
                ]
            );

        } catch (\Exception $exception) {
            $success = false;
        }

        return (bool) $success;
    }

}
