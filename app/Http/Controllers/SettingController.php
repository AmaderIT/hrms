<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class SettingController extends Controller
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
    public function edit()
    {
        $data = [];
        $settings = Setting::all();

        foreach ($settings as $setting) $data[$setting->name] = $setting->value;

        return view("setting.edit", compact("data"));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            $settings = $request->except("_token");

            foreach ($settings as $key => $value) {
                Setting::where("name", $key)->update([
                    "name"      => $key,
                    "value"     => $value,
                ]);
            }

            session()->flash('message', 'Setting Updated Successfully');
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash('message', 'Sorry! Something happened wrong!!');
        }

        return redirect()->route("setting.edit");
    }
}
