<?php

namespace App\Http\Controllers;

use PDF;
use App\Http\Requests\roles\RequestUpdateEmployeeRole;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\OfficeDivision;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    function __construct()
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
        $roles = Role::select('id', 'name')->orderBy('name')->paginate(\Functions::getPaginate());
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $permissions = Permission::select(['id', 'name', 'group_name', 'description'])->orderBy('group_name', 'DESC')->get();
        return view('roles.create', compact('permissions'));
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
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));
        activity('role-create')->by(auth()->user())->log('Role created');

        return redirect()->route('roles.index')->with('message', 'Role created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $role
     * @return Response
     */
    public function edit($role)
    {
        $role = Role::find($role);
        $permissions = Permission::select(['id', 'name', 'group_name', 'description'])->orderBy('group_name', 'DESC')->get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $role)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permission'));
        activity('role-update')->by(auth()->user())->log('Role updated');

        return redirect()->route('roles.index')->with('message', 'Role updated successfully');
    }

    /**
     * @param Role $role
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(Role $role)
    {
        try {
            $feedback['status'] = $role->delete();
            activity('role-delete')->by(auth()->user())->log('Role deleted');
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * Show list of roles and total users under each role
     *
     * @return Response
     */
    public function roleUsers()
    {
        $roles = Role::select('id', 'name')->withCount("users")->paginate(\Functions::getPaginate());

        return view('roles.roleUsers', compact('roles'));
    }

    /**
     * Show list of users under selected role role
     *
     * @param Role $role
     * @return Response
     */
    public function roleUserList(Role $role)
    {

        $items = Role::where('id', $role->id)->first();

        $items = $items->setRelation('users', $items->users()->paginate(\Functions::getPaginate()));

        return view('roles.roleUserList', compact('items'));
    }

    /**
     * @return Factory|View
     */
    public function editEmployeeRole()
    {
        $employees = User::with("roles")->where('status', 1)->orderBy("name")->select("id", "name", "fingerprint_no")->get();
        $roles = Role::select("id", "name")->get();

        return view("roles.update-employee-role", compact("employees", "roles"));
    }

    /**
     * @param RequestUpdateEmployeeRole $request
     * @return Factory|View
     */
    public function updateEmployeeRole(RequestUpdateEmployeeRole $request)
    {

        DB::beginTransaction();

        try {
            $employee = User::with("currentPromotion", "roles")->where("id", $request->input("user_id"))->select("id", "name")->first();

            $employee->roles()->sync($request->role_id);
            DB::commit();
            session()->flash("message", "Role Updated Successfully");

        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception);

            DB::rollBack();
        }

        return redirect()->back();
    }

    /**
     * @param User $employee
     * @param $officeDivision
     * @param $department
     */
    protected function syncSupervisor(User $employee, $officeDivision, $department)
    {
        $officeDivision = OfficeDivision::find($officeDivision);
        $department = Department::find($department);

        # Check whether employee is still supervisor or not
        $supervisor = DepartmentSupervisor::active()
            ->whereDepartmentId($department->id)
            ->first();

        if (!is_null($supervisor)) {
            # Flag down employee as a supervisor
            User::where("id", $supervisor->supervised_by)->update(["is_supervisor" => 0]);

            # Revoke Current Supervisor role from Supervisor. And set it to General User
            $currentSupervisor = User::find($supervisor->supervised_by);
            $currentSupervisor->roles()->sync(Role::findByName(User::ROLE_GENERAL_USER)->id);
        }

        # Revoke Current Supervisor from that department
        DepartmentSupervisor::active()
            ->whereDepartmentId($department->id)
            ->update([
                "status" => DepartmentSupervisor::STATUS_DISABLE
            ]);

        # Enable employee as a supervisor
        User::where("id", $employee->supervised_by)->update(["is_supervisor" => 1]);

        # Assign supervisor to the given department
        DepartmentSupervisor::create([
            "office_division_id" => $officeDivision->id,
            "department_id" => $department->id,
            "supervised_by" => $employee->id,
            "status" => DepartmentSupervisor::STATUS_ACTIVE
        ]);
    }

    public function getGroupPermissions($group_name = Null)
    {
        return Permission::select(['id', 'name', 'description'])->where('group_name', $group_name)->get();
    }
}
