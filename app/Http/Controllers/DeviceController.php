<?php

namespace App\Http\Controllers;

use App\Http\Requests\bank\RequestBank;
use App\Http\Requests\device\RequestDevice;
use App\Models\Bank;
use App\Models\Device;
use App\Models\Promotion;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Exception;
use Yajra\DataTables\DataTables;

class DeviceController extends Controller
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
        $items = Device::select("id", "name", "ip", "port", "serial")->orderByDesc("id")->paginate(\Functions::getPaginate());
        return view('device.index', compact('items'));
    }

    /**
     * @param Device $device
     * @return Application|Factory|View
     */
    public function edit(Device $device)
    {
        return view("device.edit", compact('device'));
    }

    /**
     * @param RequestDevice $request
     * @return RedirectResponse|string
     */
    public function store(RequestDevice $request)
    {
        try {
            Device::create($request->validated());

            session()->flash('message', 'Device Created Successfully');
            $redirect = redirect()->route("zkteco-device.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something happened wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestDevice $request
     * @param Device $device
     * @return RedirectResponse
     */
    public function update(RequestDevice $request, Device $device)
    {
        try {
            $device->update($request->validated());

            session()->flash('message', 'Device Updated Successfully');
            $redirect = redirect()->route("zkteco-device.index");
        } catch (Exception $exception) {
            session()->flash('type', 'error');
            session()->flash('message', 'Sorry! Something happened wrong!');
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param Device $device
     * @return RedirectResponse
     */
    public function delete(Device $device)
    {
        try {
            $feedback['status'] = $device->delete();
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    public function viewAttendanceDeviceList()
    {
        return view("device.online-device-list");

    }


    public function getAttendanceDeviceListByApi()
    {
        $authUser = auth()->user();
        $res = Device:: getAttendanceDeviceListByApi();


        return DataTables::make($res['data']?? [])
            ->addColumn('active_status', function ($row) {

                $sts = ["De-Active", 'Active'];
                $stsColors = ["#f64e60", '#4ec54e'];

                return '<span style="color: ' . $stsColors[$row['state']] . '">' . $sts[$row['state']] ?? 'Unknown' . '</span>';
            })
            ->editColumn('last_activity', function ($row) {

                return date('d M, Y h:i:s A',strtotime($row['last_activity']));
            })
            ->rawColumns(['active_status'])
            ->toJson();

    }
}
