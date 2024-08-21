<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use PDF;
use App\Http\Requests\leave\holidays\RequestPublicHoliday;
use App\Models\Holiday;
use App\Models\PublicHoliday;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;
use Yajra\DataTables\DataTables;

class PublicHolidayController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $year = date("Y");
            if ($request->year && $request->year > 0) {
                $year = $request->year;
            }
            $items = PublicHoliday::with("holiday")
                ->orderBy("from_date")
                ->select("public_holidays.id", "holiday_id", "from_date", "to_date", "remarks")
                ->whereYear('from_date', $year);
            return DataTables::make($items)
                ->editColumn('from_date', function (PublicHoliday $item) {
                    return $item->from_date->format('M d, Y') . " <span style='color: red;'>(" . $item->from_date->format('D') . ")</span>";
                })
                ->editColumn('to_date', function (PublicHoliday $item) {
                    return $item->to_date->format('M d, Y') . " <span style='color: red;'>(" . $item->to_date->format('D') . ")</span>";
                })
                ->addColumn('action', function (PublicHoliday $item) {
                    $str = '';
                    if (auth()->user()->can('Edit Public Holidays')) {
                        $str .= '<a onclick="setListScroll(this)" href="' . route("public-holiday.edit", ["publicHoliday" => $item->id]) . '"><i class="fa fa-edit" style="color: green"></i></a> ';
                    }
                    if (auth()->user()->can('Delete Public Holidays')) {
                        $x = "deleteAlert('" . route('public-holiday.delete', ['publicHoliday' => $item->id]) . "')";
                        $str .= '<a href="#" onclick="' . $x . '"><i class="fa fa-trash" style="color: red"></i></a> ';
                    }
                    return $str;
                })
                ->addColumn('day_count', function (PublicHoliday $item) {
                    $str = "-";
                    if ($item->from_date && $item->to_date) {
                        $str = date_diff(new \DateTime($item->from_date), new \DateTime($item->to_date))->days + 1;
                    }
                    return $str;
                })
                ->addIndexColumn()
                ->rawColumns(['from_date', 'to_date', 'action'])
                ->toJson();
        }

        return view("public-holiday.index");
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $holidays = Holiday::select("id", "name")->get();

        return view("public-holiday.create", compact("holidays"));
    }

    /**
     * @param PublicHoliday $publicHoliday
     * @return Application|Factory|View
     */
    public function edit(PublicHoliday $publicHoliday)
    {
        $holidays = Holiday::select("id", "name")->get();

        return view("public-holiday.edit", compact("publicHoliday", "holidays"));
    }

    /**
     * @param RequestPublicHoliday $request
     * @return RedirectResponse
     */
    public function store(RequestPublicHoliday $request)
    {
        try {
            $year_month_date = explode('-', $request->from_date);
            $existance = PublicHoliday::where('holiday_id', $request->holiday_id)
                ->whereYear('from_date', $year_month_date[0])
                ->first();
            if ($existance) {
                session()->flash("type", "error");
                session()->flash("message", "This public holiday already exists in $year_month_date[0]. Please try another holiday or year!");
                return redirect()->back();
            }
            PublicHoliday::create($request->validated());
            session()->flash("message", "Public Holiday Created Successfully");
            $redirect = redirect()->route("public-holiday.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestPublicHoliday $request
     * @param PublicHoliday $publicHoliday
     * @return RedirectResponse
     */
    public function update(RequestPublicHoliday $request, PublicHoliday $publicHoliday)
    {
        try {
            $year_month_date = explode('-', $request->from_date);
            $existance = PublicHoliday::where('holiday_id', $request->holiday_id)
                ->whereYear('from_date', $year_month_date[0])
                ->where('id', '<>', $publicHoliday->id)
                ->first();
            if ($existance) {
                session()->flash("type", "error");
                session()->flash("message", "This public holiday already exists in $year_month_date[0]. Please try another holiday or year!");
                return redirect()->back();
            }
            $publicHoliday->update($request->validated());
            session()->flash("message", "Public Holiday Updated Successfully");
            $redirect = redirect()->route("public-holiday.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param PublicHoliday $publicHoliday
     * @return mixed
     */
    public function delete(PublicHoliday $publicHoliday)
    {
        try {
            $feedback['status'] = $publicHoliday->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    public function publicHolidayDownload()
    {
        $data['holidays'] = PublicHoliday::with("holiday")
            ->orderBy("from_date")
            ->select("id", "holiday_id", "from_date", "to_date", "remarks")
            ->paginate(\Functions::getPaginate());

        $page_name = "public-holiday.print.public_holiday";
        $pdf = PDF::loadView($page_name, $data);
        $file_name = date('d-m-Y', time()) . '-public-holiday.pdf';
        return $pdf->download($file_name);
    }


}
