<?php

namespace App\Http\Controllers;

use App\Http\Requests\workslot\RequestWorkSlot;
use App\Models\WorkSlot;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class WorkSlotController extends Controller
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
        $items = WorkSlot::latest()->select("id", "title", "start_time", "end_time", "late_count_time", "over_time", "overtime_count", "total_work_hour", "is_flexible")
            ->orderBy("title")
            ->paginate(\Functions::getPaginate());

        return view('work-slot.index', compact('items'));
    }

    /**
     * @param WorkSlot $workSlot
     * @return Application|Factory|View
     */
    public function edit(WorkSlot $workSlot)
    {
        return view("work-slot.edit", compact('workSlot'));
    }

    /**
     * @param RequestWorkSlot $request
     * @return RedirectResponse
     */
    public function store(RequestWorkSlot $request)
    {
        try {
            WorkSlot::create($request->validated());
            session()->flash('message', 'Work Slot Created Successfully');
            $redirect = redirect()->route("work-slot.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestWorkSlot $request
     * @param WorkSlot $workSlot
     * @return RedirectResponse
     */
    public function update(RequestWorkSlot $request, WorkSlot $workSlot)
    {
        try {
            $workSlot->update($request->validated());

            session()->flash('message', 'Work Slot Updated Successfully');
            $redirect = redirect()->route("work-slot.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param WorkSlot $workSlot
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(WorkSlot $workSlot)
    {
        try {
            $feedback['status'] = $workSlot->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
}
