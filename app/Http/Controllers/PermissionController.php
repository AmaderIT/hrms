<?php


namespace App\Http\Controllers;


use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;


class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */


    public function index()
    {
        $permissions = Permission::orderByDesc('id')->paginate(\Functions::getPaginate());

        return view('permission.index', compact('permissions'));
    }

    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();
        $data = Permission::select([
            "id",
            "name",
            "group_name"
        ])
            ->orderBy("group_name", "DESC");

        return DataTables::eloquent($data)
            ->addColumn('roles', function (Permission $obj) use ($authUser) {
                $str = "";
                $data = DB::table('role_has_permissions')
                    ->join('roles', 'roles.id', 'role_id')
                    ->where('permission_id', $obj->id)
                    ->select(['roles.id', 'roles.name'])
                    ->groupBy('role_id')
                    ->get();

                foreach ($data as $d) {
                    $str .= '<a href="' . route('roles.edit', $d->id) . '"><span  class="badge badge-secondary role-btn">' . $d->name . '</span></a> ';
                }
                return $str;
            })
            ->addColumn('users', function (Permission $obj) use ($authUser) {
                $str = "";
                $data = DB::table('role_has_permissions')
                    ->join('roles', 'roles.id', 'role_id')
                    ->join('model_has_roles', 'model_has_roles.role_id', 'role_has_permissions.role_id')
                    ->join('users', 'users.id', 'model_has_roles.model_id')
                    ->where('permission_id', $obj->id)
                    ->select(['users.id', 'users.name'])
                    ->count();

                if ($data > 0) {

                    $str .= '<a href="' . route('permission.user-list', ['permission' => $obj->id]) . '"><span class="badge badge-secondary role-btn">' . $data . '</span></a> ';
                }
                return $str;
            })
            ->addColumn('action', function (Permission $obj) use ($authUser) {
                $str = "";

                if ($authUser->can('Edit Permission')) {
                    $str .= '<a href="' . route('permission.edit', ['permission' => $obj->id]) . '"><i class="fa fa-edit"style="color: green"></i></a>';
                }


                return $str;
            })
            ->rawColumns(['action', 'roles', 'users'])
            ->toJson();
    }

    public function create()
    {

        $groups = Permission::groupBy('group_name')->pluck('group_name');
        return view('permission.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name',
        ]);

        $role = Permission::create(
            [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'group_name' => $request->input('group_name')
            ]
        );

        return redirect()->route('permission.index')->with('message', 'Permission created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $permission
     * @return Response
     */
    public function edit($permission)
    {
        $groups = Permission::where('group_name', '<>', Null)->groupBy('group_name')->pluck('group_name');
        $permission = Permission::find($permission);

        return view('permission.edit', compact('permission', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws ValidationException
     */
    public function update(Request $request, Permission $permission)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);
        $permission->description = $request->input('description');
        $permission->group_name = $request->input('group_name');
        $permission->save();

        activity('Permission-update')->by(auth()->user())->log('Permission updated');

        return redirect()->route('permission.index')->with('message', 'Permission updated successfully');
    }


    public function getUserList($permissionId, Request $request)
    {

        $permission = Permission::find($permissionId);

        if ($request->ajax()) {
            $userIds = [];
            foreach ($permission->roles as $role) {
                foreach ($role->users as $user) {
                    $userIds [] = $user->id;
                }
            }
            $data = User::select(['users.id', 'users.name', 'users.email', 'users.phone', 'users.fingerprint_no'])
                ->whereIn('users.id', $userIds)
                ->with(['currentPromotion', 'currentPromotion.officeDivision', 'currentPromotion.department', 'currentPromotion.designation']);

            return DataTables::eloquent($data)
                ->addColumn('photo', function (User $obj) {

                    $imgUrl = asset('/photo/' . $obj->fingerprint_no . '.jpg');

                    return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img onerror="imgError(this)" class="image-checking"  src="' . $imgUrl . '" /></div>';
                })
                ->rawColumns(['photo'])
                ->toJson();
        }

        return view('permission.user-list', compact('permission'));
    }
}
