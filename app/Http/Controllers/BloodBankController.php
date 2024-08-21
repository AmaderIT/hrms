<?php

namespace App\Http\Controllers;

use App\Models\OfficeDivision;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BloodBankController extends Controller
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


    public function index()
    {
        $data = [
            'officeDivisions' => OfficeDivision::select(['id', 'name'])->whereIn('id', FilterController::getDivisionIds())->get()
        ];
        return view('blood-bank.index', ['card_title' => 'Blood Bank Listing', 'data' => $data]);
    }

    public function getDataTable(Request $request)
    {

        $items = User::join('profiles', 'profiles.user_id', 'users.id')
            ->select(
                [
                    'users.id',
                    'users.name',
                    'users.fingerprint_no',
                    'users.email',
                    'users.phone',
                    'profiles.blood_group',
                    'profiles.personal_phone',
                    'profiles.dob',
                ])
            ->where('profiles.blood_group', '<>', Null)
            ->where('status', User::STATUS_ACTIVE)
            ->with(["currentPromotion" => function ($query) {
                $query->with("officeDivision", "department");
            }]);



        if (isset($request->blood_group) && $request->blood_group != 'all') {

            $items->where('profiles.blood_group', $request->blood_group);
        }


        return DataTables::eloquent($items)
            ->editColumn('photo', function ($item) {
                $path = "photo/" . $item->fingerprint_no . ".jpg";
                $imgSrc = file_exists($path) ? asset($path) : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
            })
            ->addColumn('age', function ($item) {
                return Carbon::parse($item->dob)->age;
            })
            ->addColumn('blood_group', function ($item) {
                return '<span style="font-weight: 600">' . $item->blood_group . '</span>';
            })
            ->rawColumns(['photo', 'age', 'blood_group'])
            ->toJson();
    }

}
