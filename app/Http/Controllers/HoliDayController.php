<?php

namespace App\Http\Controllers;

use App\Http\Requests\leave\holidays\RequestHoliday;
use App\Models\Holiday;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class HoliDayController extends Controller
{
    /**+
     * @return Application|Factory|View
     */
    public function index()
    {
        $items = Holiday::orderBy("name")->select("id", "name")->paginate(\Functions::getPaginate());
        return view("holiday.index", compact("items"));
    }

    /**
     * @param RequestHoliday $request
     * @return RedirectResponse
     */
    public function store(RequestHoliday $request)
    {
        try {
            Holiday::create($request->validated());

            session()->flash("message", "Holiday Created Successfully");
            $redirect = redirect()->route("holiday.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Holiday $holiday
     * @return Application|Factory|View
     */
    public function edit(Holiday $holiday)
    {
        return view("holiday.edit", compact("holiday"));
    }

    /**
     * @param RequestHoliday $request
     * @param Holiday $holiday
     * @return RedirectResponse
     */
    public function update(RequestHoliday $request, Holiday $holiday)
    {
        try {
            $holiday->update($request->validated());

            session()->flash("message", "Holiday Updated Successfully");
            $redirect = redirect()->route("holiday.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Holiday $holiday
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(Holiday $holiday)
    {
        try {
            $feedback['status'] = $holiday->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
