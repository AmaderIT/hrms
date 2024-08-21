<?php

namespace App\Http\Controllers;

use App\Http\Requests\designation\RequestDesignation;
use App\Models\Designation;
use App\Models\Promotion;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Mockery\Exception;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\DataTables;

class DesignationController extends Controller
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
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getDataTable($request);
        }
        return view('designation.index');
    }


    private function getDataTable($request)
    {
        $authUser = auth()->user();
        $items = Designation::select('id', 'title')->orderBy("title");

        $col = "designation_id";
        $usedTables = FilterController::getUsedTableByColumn($col);

        return DataTables::eloquent($items)
            ->addColumn('action', function (Designation $obj) use ($authUser, $usedTables, $col) {
                $str = "";

                if ($authUser->can('Edit Designation')) {
                    $str .= '<a href="' . route('designation.edit', ['designation' => $obj->id]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }
                if ($authUser->can('Delete Designation')) {

                    $ut = FilterController::getUsedTableById($usedTables, $col, $obj->id);

                    if (count($ut) > 0) {
                        $msg = "'Unable to delete because of used on " . implode(", ", $ut) . " tables.'";
                        $str .= '<a href="#" onclick="swal.fire(' . $msg . ')" ><i class="fa fa-trash" style="color: red"></i></a>';

                    } else {
                        $delteUrl = "'" . route('designation.delete', ['designation' => $obj->id]) . "'";
                        $str .= '<a href="#" onclick="deleteAlert(' . $delteUrl . ')" ><i class="fa fa-trash" style="color: red"></i></a>';
                    }

                }

                return $str;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(Designation $designation)
    {
        $trackingType = "";
        return view("designation.create", compact('designation', 'trackingType'));
    }


    /**
     * @param Designation $designation
     * @return Application|Factory|View
     */
    public function edit(Designation $designation)
    {
        return view("designation.edit", compact('designation'));
    }

    /**
     * @param RequestDesignation $request
     * @return RedirectResponse|string
     */
    public function store(RequestDesignation $request)
    {
        try {
            Designation::create($request->validated());
            $redirect = redirect()->route("designation.index")->with('message', 'Designation Created Successfully');
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestDesignation $request
     * @param Designation $designation
     * @return RedirectResponse
     */
    public function update(RequestDesignation $request, Designation $designation)
    {
        try {
            $designation->update($request->validated());

            session()->flash('message', 'Designation Updated Successfully');
            $redirect = redirect()->route("designation.index");
        } catch (Exception $exception) {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    public function delete(Designation $designation)
    {
        try {
            if (auth()->user()->can('Delete Designation')) {

                $col = "designation_id";
                $usedTables = FilterController::getUsedTableByColumn($col);
                $ut = FilterController::getUsedTableById($usedTables, $col, $designation->id);

                if (count($ut) > 0) {
                    $feedback['status'] = false;
                    $feedback['message'] = "Unable to delete because of used on " . implode(", ", $ut) . " tables.";
                } else {
                    $feedback['status'] = $designation->delete();
                }
            }
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    public function storeAjx(RequestDesignation $request)
    {
        $this->store($request);
        return \response()->json([
            'status' => 'success',
            'designations' => Designation::orderBy("title")->select("id", "title")->get(),
            'message' => 'Designation Created Successfully'
        ]);
    }
}
