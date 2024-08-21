<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeExport;
use App\Helpers\Common;
use App\Http\Requests\employee\RequestEmployeeByOfficeDivisionDepartmentFilter;
use App\Http\Requests\employee\RequestEmployeeMiscellaneousUpdate;
use App\Http\Requests\employee\RequestEmployeeProfileExport;
use App\Http\Requests\employee\RequestEmployeeUpdate;
use App\Http\Requests\employee\RequestStoreEmployee;
use App\Http\Requests\employee\RequestUpdateProfile;
use App\Http\Requests\employee\RequestStoreEmployeeMiscellaneous;
use App\Http\Requests\RequestUpdatePassword;
use App\Imports\EmployeeImport;
use App\Models\ActionReason;
use App\Models\Address;
use App\Models\AssignRelaxDay;
use App\Models\Bank;
use App\Models\BankUser;
use App\Models\Branch;
use App\Models\Degree;
use App\Models\DegreeUser;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\Designation;
use App\Models\District;
use App\Models\Division;
use App\Models\DivisionSupervisor;
use App\Models\EmployeeStatus;
use App\Models\Institute;
use App\Models\JobHistory;
use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use App\Models\OfficeDivision;
use App\Models\PayGrade;
use App\Models\Promotion;
use App\Models\Roaster;
use App\Models\Roster;
use App\Models\User;
use App\Models\UserLeave;
use App\Models\Warehouse;
use App\Models\WorkSlot;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Exception;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use ZipArchive;

class EmployeeController extends Controller
{
    /**
     * @var null
     */
    protected $employeeId = null;

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
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $data = array(
            "officeDivisions" => OfficeDivision::orderBy("name", "asc")->select("id", "name")->get(),
            "designations" => Designation::orderBy("title", "asc")->select("id", "title")->limit(30)->get()
        );
        $data['filterToEmployee'] = 'admin';
        return view("employee.index", compact("data"));
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        $data = $this->getEmployeeFormData();

        $deptInfos = $this->__getDepartmentInfos();
        return view("employee.create", compact("data", "deptInfos"));
    }

    /**
     * @param User $employee
     * @return Factory|View
     */
    public function createMiscellaneous(User $employee)
    {
        $data = $this->getEmployeeFormData();
        return \view("employee.create-miscellaneous", compact("data", "employee"));
    }

    /**
     * @param User $employee
     * @return Application|Factory|View
     */
    public function edit(User $employee)
    {
        $data = $this->getEmployeeFormData();
        //$employee = $employee->load("profile", "currentPromotion", "currentStatus");
        $employee = $employee->load(["profile", "currentPromotion", "currentStatus" => function ($query) {
            $query->where("action_reason_id", 2)->orderByDesc('action_date');
        }]);
        $departments = Department::where("office_division_id", $employee->currentPromotion->office_division_id)->select("id", "name")->get();
        $deptInfos = $this->__getDepartmentInfos();
        return view("employee.edit", compact("data", "employee", "departments", "deptInfos"));
    }

    /**
     * @param User $employee
     * @return Application|Factory|View
     */
    public function editMiscellaneous(User $employee)
    {
        if (!is_null($employee->presentAddress)) {
            $presentAddressDistrict = District::where("division_id", $employee->presentAddress->division_id)->get();
            $permanentAddressDistrict = $employee->permanentAddress ? District::where("division_id", $employee->permanentAddress->division_id)->get() : null;
        } else {
            $presentAddressDistrict = null;
            $permanentAddressDistrict = null;
        }

        $data = $this->getEmployeeFormData();
        $employee = $employee->load("profile", "currentPromotion", "presentAddress", "permanentAddress", "currentBank", "degrees", "jobHistories");
        $departments = Department::where("office_division_id", $employee->currentPromotion->office_division_id)->select("id", "name")->get();

        return view("employee.edit-miscellaneous", compact("data", "employee", "presentAddressDistrict", "permanentAddressDistrict", "departments"));
    }

    /**
     * Employee Creation
     *
     * @param RequestStoreEmployee $request
     * @return RedirectResponse
     */
    public function store(RequestStoreEmployee $request)
    {
        try {
            $joining_date = strtotime($request->input("joining_date"));
            $current_date = strtotime(date('Y-m-d'));
            $join_date = explode('-', $request->input("joining_date"));
            $getCurrentDateObj = new DateTime();
            $getPastTwoMonthsInitialDate = $getCurrentDateObj->modify("-59 days")->format('Y-m-d');
            $getCurrentDateObj = new DateTime();
            $getFutureTwoMonthsLastDate = $getCurrentDateObj->modify("+59 days")->format('Y-m-d');
            if (($joining_date >= strtotime($getPastTwoMonthsInitialDate)) && ($joining_date <= strtotime($getFutureTwoMonthsLastDate))) {
                //echo "true";
            } else {
                session()->flash("type", "error");
                session()->flash("message", "Joining date must be within past and future date range!");
                return redirect()->back()->withInput($request->all());
            }

            $this_year = date('Y');
            if ($this_year != $join_date[0]) {
                session()->flash("type", "error");
                session()->flash("message", "Joining date must be in this year!");
                return redirect()->back()->withInput($request->all());
            }
            $dob = strtotime($request->input("dob"));
            if ($dob >= $current_date) {
                session()->flash("type", "error");
                session()->flash("message", "Date Of Birth can't be greater than or equal to current date!");
                return redirect()->back()->withInput($request->all());
            }

            $getDOBObj = new DateTime($request->input("dob"));
            $currentDateObj = new DateTime($request->input("joining_date"));
            $getAge = $getDOBObj->diff($currentDateObj)->y;
            if ($getAge < 18) {
                session()->flash("type", "error");
                session()->flash("message", "Below 18 years old! Invalid For Fill Up This Form!!!");
                return redirect()->back()->withInput($request->all());
            }
            $employeeTrackDevice = 'no';
            $trackEmpUuid = "";
            DB::transaction(function () use ($request, &$employeeTrackDevice, &$trackEmpUuid) {
                # Employee
                $employee = User::create(array(
                    "name" => $request->input("name"),
                    "email" => $request->input("email") ?? null,
                    "phone" => $request->input("phone"),
                    "password" => bcrypt($request->input("password")),
                    "fingerprint_no" => $request->input("fingerprint_no"),
                    "photo" => $this->purgePhoto($request) ?? "",
                    "payment_mode" => $request->input("payment_mode"),
                    "provision_duration" => $request->input('provision_duration'),
                    "provision_end_date" => date("Y-m-d", strtotime($request->input("joining_date") . " +" . (int)$request->input('provision_duration') . " Months")),
                ));

                # Profile
                $employee->profile()->create(array(
                    "gender" => $request->input("gender"),
                    "religion" => $request->input("religion"),
                    "dob" => !empty($request->input("dob")) ? $request->input("dob") : "",
                    "marital_status" => $request->input("marital_status"),
                    "emergency_contact" => $request->input("emergency_contact"),
                    "relation" => $request->input("relation"),
                    "blood_group" => $request->input("blood_group"),
                    "nid" => $request->input("nid") ?? null,
                    "tin" => $request->input("tin") ?? null,
                    "personal_email" => $request->input("personal_email"),
                    "personal_phone" => $request->input("personal_phone")
                ));

                # Employee Status
                $employee->promotions()->create(array(
                    "office_division_id" => $request->input("office_division_id"),
                    "department_id" => $request->input("department_id"),
                    "designation_id" => $request->input("designation_id"),
                    "pay_grade_id" => $request->input("pay_grade_id"),
                    "salary" => $request->input("salary"),
                    "promoted_date" => !empty($request->input("joining_date")) ? $request->input("joining_date") : null,
                    "type" => Promotion::TYPE_JOIN,
                    "employment_type" => $request->input("type"),
                    "workslot_id" => $request->input("workslot_id")
                ));

                # Employee Action Reason
                $employee->employeeStatus()->create(array(
                    "action_reason_id" => 2,
                    "action_taken_by" => auth()->user()->id,
                    "action_date" => $request->input("joining_date")
                ));

                # Assign User Leave Start
                $calculateLeave = Common::calculateLeaveBalance($request->input("department_id"), $request->input("joining_date"));
                if (!empty($calculateLeave)) {
                    UserLeave::create([
                        "user_id" => $employee->id,
                        "initial_leave" => $calculateLeave['initial_leave'],
                        "total_initial_leave" => $calculateLeave['total_initial_leave'],
                        "leaves" => $calculateLeave['leaves'],
                        "total_leaves" => $calculateLeave['total_leaves'],
                        "year" => $calculateLeave['year']
                    ]);
                }
                # Assign User Leave End

                /**
                 * Sync with Departmental Supervisor
                 * Sync with Divisional Supervisor
                 * Start
                 **/
                if (!empty($request->input('supervisor_type'))) {
                    if ($request->input('supervisor_type') == User::SUPERVISOR_DEPARTMENT) {
                        $responseDeptSupervisor = $this->syncDepartmentalSupervisor($employee->id, $request->input("office_division_id"), $request->input("department_id"));
                        if (!empty($responseDeptSupervisor['msg'])) {
                            throw new \Exception($responseDeptSupervisor['msg']);
                        }
                    } elseif ($request->input('supervisor_type') == User::SUPERVISOR_OFFICE_DIVISION) {
                        $responseDivisionSupervisor = $this->syncDivisionalSupervisor($employee->id, $request->input("office_division_id"), $request->input("department_id"));
                        if (!empty($responseDivisionSupervisor['msg'])) {
                            throw new \Exception($responseDivisionSupervisor['msg']);
                        }
                    }
                }
                /** End **/
                # Assign Role
                $role = Role::findById($request->input("role_id"));
                $employee->assignRole($role);

                $this->employeeId = $employee->id;
                $trackEmpUuid = $employee->uuid;
                # Sync with Attendance Server
                if (env("ZKTECO_SYNC_USER") === true) {
                    $employeeTrackDevice = 'no';
                    $existsEmployee = Common::checkEmployeeDeviceDataExistsOrNot($employee->fingerprint_no);
                    Log::info("#Device Existing Employee Response Status Start[Store]#");
                    Log::info($existsEmployee);
                    Log::info("#Device Existing Employee Response Status End#");
                    if ($existsEmployee) {
                        $employeeTrackDevice = 'yes';
                        $employee->update(['sync_device' => 1]);
                    } else {
                        $getDeviceResponse = $this->syncWithZKTeco(array(
                            "emp_code" => $employee->fingerprint_no,
                            "first_name" => $employee->name,
                            "last_name" => "",
                            "area" => array(2),
                            "department" => 1
                        ));
                        Log::info("#Device Response Start[Store]#");
                        Log::info($getDeviceResponse);
                        Log::info("#Device Response End#");
                        if (!empty($getDeviceResponse)) {
                            $employeeTrackDevice = 'yes';
                            $employee->update(['sync_device' => 1]);
                        }
                    }
                }
            });
            session()->flash("message", "Employee Created Successfully");
            $redirect = redirect()->route("employee.index")->with('employeeTrackDevice', $employeeTrackDevice)->with('trackEmpUuid', $trackEmpUuid);
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->all());
        }

        return $redirect;
    }

    /**
     * Store Employee Miscellaneous Data
     *
     * @param RequestStoreEmployeeMiscellaneous $request
     * @param User $employee
     * @return RedirectResponse
     */
    public function storeMiscellaneous(Request $request, User $employee)
    {
        try {
            DB::transaction(function () use ($request, $employee) {
                # Present Address
                $employee->presentAddress()->create(array(
                    "type" => Address::TYPE_PRESENT,
                    "address" => $request->input("present_address.address"),
                    "zip" => $request->input("present_address.zip") ?? null,
                    "division_id" => $request->input("present_address.division_id"),
                    "district_id" => $request->input("present_address.district_id")
                ));

                # Permanent Address
                $employee->permanentAddress()->create(array(
                    "type" => Address::TYPE_PERMANENT,
                    "address" => $request->input("permanent_address.address"),
                    "zip" => $request->input("permanent_address.zip") ?? null,
                    "division_id" => $request->input("permanent_address.division_id"),
                    "district_id" => $request->input("permanent_address.district_id")
                ));

                # Employee Bank
                if (!is_null($request->input("account_number"))) {
                    $employee->banks()->attach(Bank::find($request->input("bank_id")), array(
                        "branch_id" => $request->input("branch_id"),
                        "account_type" => $request->input("account_type"),
                        "account_name" => $request->input("account_name"),
                        "account_no" => $request->input("account_number"),
                        "nominee_name" => $request->input("nominee"),
                        "relation_with_nominee" => $request->input("relation_with_nominee"),
                        "nominee_contact" => $request->input("nominee_contact"),
                        "tax_opening_balance" => $request->input("tax_opening_balance")
                    ));
                }

                # TIN
                if ($request->has("tin")) {
                    $employee->profile()->update(array(
                        "tin" => $request->input("tin")
                    ));
                }

                # Employee Education
                if ($request->has("degree_id")) {
                    $employeeEducation = array();
                    foreach ($request->input("degree_id") as $index => $value) {
                        $education = array(
                            "degree_id" => $request->input("degree_id")[$index],
                            "institute_id" => $request->input("institute_id")[$index],
                            "passing_year" => $request->input("passing_year")[$index],
                            "result" => $request->input("result")[$index],
                        );
                        array_push($employeeEducation, $education);
                    }
                    $employee->degrees()->attach($employeeEducation);
                }

                # Employee Working History
                if (!is_null($request->input("organization")[0])) {
                    $employeeJobHistories = array();
                    foreach ($request->input("organization") as $index => $value) {
                        $jobHistories = array(
                            "organization_name" => $request->input("organization")[$index],
                            "designation" => $request->input("designation")[$index] ? $request->input("designation")[$index] : null,
                            "start_date" => $request->input("start_date")[$index],
                            "end_date" => $request->input("end_date")[$index],
                        );
                        array_push($employeeJobHistories, $jobHistories);
                    }
                    $employee->jobHistories()->createMany($employeeJobHistories);
                }
            });

            session()->flash("message", "Employee Created Successfully");
            $redirect = redirect()->route("employee.index");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", $exception->getMessage());
            $redirect = redirect()->back()->withInput($request->all());
        }

        return $redirect;
    }


    /**
     * @param User $employee
     * @return bool
     */
    public function changeStatus(User $employee)
    {
        try {
            $status = !$employee->status;

            $success = $employee->update(["status" => $status]);
        } catch (Exception $exception) {
            $success = false;
        }

        return (bool)$success;
    }

    /**
     * @param RequestEmployeeUpdate $request
     * @param User $employee
     * @return RedirectResponse
     */
    public function update(RequestEmployeeUpdate $request, User $employee)
    {
        try {
            $errorMsg = "";
            $current_date = strtotime(date('Y-m-d'));
            $dob = strtotime($request->input("dob"));
            if ($dob >= $current_date) {
                session()->flash("type", "error");
                session()->flash("message", "Date Of Birth can't be greater than or equal to current date!");
                return redirect()->back()->withInput($request->all());
            }
            $getDOBObj = new DateTime($request->input("dob"));
            $currentDateObj = new DateTime($request->input("joining_date"));
            $getAge = $getDOBObj->diff($currentDateObj)->y;
            if ($getAge < 18) {
                session()->flash("type", "error");
                session()->flash("message", "Below 18 years old! Invalid For Fill Up This Form!!!");
                return redirect()->back()->withInput($request->all());
            }
            DB::transaction(function () use ($request, $employee) {
                $photoName = $employee->photo ? $employee->photo : null;
                if ($request->hasFile("photo") and $request->file("photo")->isValid()) {
                    $photoFile = $request->file("photo");
                    $photo = Image::make($photoFile);
                    $fileName = $employee->fingerprint_no . "." . User::PHOTO_EXTENSION;
//                    $path = Storage::disk("photo")->getAdapter()->getPathPrefix() . $fileName;
                    $path = public_path("photo/{$fileName}");
                    $photo->resize(200, 250)->encode(User::PHOTO_EXTENSION, 100)->save($path, 100);
                }

                # Employee
                $employee->update(array(
                    "name" => $request->input("name"),
                    "email" => $request->input("email"),
                    "phone" => $request->input("phone"),
                    "photo" => $photoName,
                    "payment_mode" => $request->input("payment_mode"),
                    "provision_duration" => $request->input('provision_duration'),
                    "provision_end_date" => date("Y-m-d", strtotime($request->input("joining_date") . " +" . (int)$request->input('provision_duration') . " Months")),
                ));

                if (!is_null($request->input("password"))) $employee->update(array("password" => bcrypt($request->input("password"))));

                # Profile
                $employee->profile()->update(array(
                    "gender" => $request->input("gender"),
                    "religion" => $request->input("religion"),
                    "dob" => !empty($request->input("dob")) ? $request->input("dob") : "",
                    "marital_status" => $request->input("marital_status"),
                    "emergency_contact" => $request->input("emergency_contact"),
                    "relation" => $request->input("relation"),
                    "blood_group" => $request->input("blood_group"),
                    "nid" => $request->input("nid"),
                    "tin" => $request->input("tin"),
                    "personal_email" => $request->input("personal_email"),
                    "personal_phone" => $request->input("personal_phone")
                ));

                # Status
                $promotion_data = array(
                    "designation_id" => $request->input("designation_id")
                );

                if (auth()->user()->can('Employee Edit Joining Date')) {
                    if (empty($request->input("joining_date"))) {
                        throw new \Exception("Joining date required!!!");
                    }
                    $responseJoiningRelatedInfos = $employee->getLatestJoiningRelatedDateFromPromotion();
                    if (!empty($responseJoiningRelatedInfos['terminatedDate']) && $request->input("joining_date") <= $responseJoiningRelatedInfos['terminatedDate']) {
                        throw new \Exception("Invalid Joining Date. Last Employment Close Date is " . $responseJoiningRelatedInfos['terminatedDate']);
                    } elseif (!empty($responseJoiningRelatedInfos['minimumRowAfterJoiningDate']) && $request->input("joining_date") >= $responseJoiningRelatedInfos['minimumRowAfterJoiningDate']) {
                        throw new \Exception("Invalid Joining Date. Last Action Date is " . $responseJoiningRelatedInfos['minimumRowAfterJoiningDate']);
                    }
                    if (empty($responseJoiningRelatedInfos['joiningDate'])) {
                        throw new \Exception("Mismatch data into promotion information!!!");
                    }
                    if ($request->input("joining_date") != $responseJoiningRelatedInfos['joiningDate']) {
                        $getEmployeeJoiningDateRow = Promotion::select("id", "promoted_date")->where(["user_id" => $employee->id, "id" => $responseJoiningRelatedInfos['joiningDateRowID']])->first();
                        $getEmployeeJoiningDateRow->promoted_date = $request->input("joining_date");
                        $getEmployeeJoiningDateRow->save();

                        # Action Reason
                        $employee->employeeStatus()->where(['action_reason_id' => 2])->orderByDesc('id')->first()->update(array(
                            "action_taken_by" => auth()->user()->id,
                            "action_date" => $request->input("joining_date")
                        ));
                        $lastPromotedInfos = $employee->lastPromotion()->first();
                        if (!empty($lastPromotedInfos->id)) {
                            $joiningDateYear = date('Y', strtotime($request->input("joining_date")));
                            $currentYear = date('Y');
                            $leaveCalculatedDate = $request->input("joining_date");
                            if ($joiningDateYear < $currentYear) {
                                $leaveCalculatedDate = date('Y') . '-01-01';
                            }
                            $calculateLeave = Common::syncLeaveBalanceEmployeeWise($employee->id, $lastPromotedInfos->department_id, $leaveCalculatedDate);
                            if (isset($calculateLeave['errorMsg'])) {
                                throw new \Exception($calculateLeave['errorMsg']);
                            }
                            if (!empty($calculateLeave)) {
                                UserLeave::updateOrCreate([
                                    'user_id' => $employee->id,
                                    'year' => $calculateLeave['year']
                                ], [
                                    'user_id' => $employee->id,
                                    'initial_leave' => $calculateLeave['initial_leave'],
                                    'total_initial_leave' => $calculateLeave['total_initial_leave'],
                                    'leaves' => $calculateLeave['leaves'],
                                    'total_leaves' => $calculateLeave['total_leaves'],
                                    'year' => $calculateLeave['year']
                                ]);
                            }
                        }
                    }
                }
                if (auth()->user()->can('Employee Edit Employment Type')) {
                    if (empty($request->input("type"))) {
                        throw new \Exception("Employment type required!!!");
                    }
                    $promotion_data["employment_type"] = $request->input("type");
                }
                if (auth()->user()->can('Employee Edit Work Slot')) {
                    if (empty($request->input("workslot_id"))) {
                        throw new \Exception("Workslot required!!!");
                    }
                    $promotion_data["workslot_id"] = $request->input("workslot_id");
                }
                if (auth()->user()->can('Employee Edit Pay Grade')) {
                    if (empty($request->input("pay_grade_id"))) {
                        throw new \Exception("Pay grade required!!!");
                    }
                    $promotion_data["pay_grade_id"] = $request->input("pay_grade_id");
                }

                if (auth()->user()->can('Edit Employee Salary') && isset($request->salary) && !empty($request->salary)) {
                    $promotion_data["salary"] = $request->input("salary");
                }
                $employee->currentPromotion->update($promotion_data);
            });

            session()->flash("message", "Employee Updated Successfully");
            $redirect = redirect()->back();
        } catch (Exception $exception) {
            $errorMsg = $exception->getMessage();
            if (!empty($errorMsg)) {
                return redirect()->back()->withInput($request->all())->withErrors($errorMsg);
            }
            session()->flash("type", "error");
            session()->flash("message", "Please fill all the required fields!");
            $redirect = redirect()->back()->withInput($request->all())->withErrors($request->messages());
        }

        return $redirect;
    }

    /**
     * @param RequestEmployeeMiscellaneousUpdate $request
     * @param User $employee
     * @return RedirectResponse
     */
    public function updateMiscellaneous(Request $request, User $employee)
    {
        try {
            DB::transaction(function () use ($request, $employee) {

                #  Present Address
                Address::updateOrCreate(array("user_id" => $employee->id, "type" => Address::TYPE_PRESENT), array(
                    "type" => Address::TYPE_PRESENT,
                    "address" => $request->input("present_address.address"),
                    "zip" => $request->input("present_address.zip") ?? null,
                    "division_id" => $request->input("present_address.division_id"),
                    "district_id" => $request->input("present_address.district_id")
                ));

                # Permanent Address
                Address::updateOrCreate(array("user_id" => $employee->id, "type" => Address::TYPE_PERMANENT), array(
                    "type" => Address::TYPE_PERMANENT,
                    "address" => $request->input("permanent_address.address"),
                    "zip" => $request->input("permanent_address.zip") ?? null,
                    "division_id" => $request->input("permanent_address.division_id"),
                    "district_id" => $request->input("permanent_address.district_id")
                ));

                # Bank
                if (($request->has("account_number") and $request->input("account_number") != "") || ($request->has("account_name") and $request->input("account_name") != "")) {
                    BankUser::updateOrCreate(array("user_id" => $employee->id), array(
                        "bank_id" => $request->input("bank_id"),
                        "branch_id" => $request->input("branch_id"),
                        "account_type" => $request->input("account_type"),
                        "account_name" => $request->input("account_name"),
                        "account_no" => $request->input("account_number"),
                        "nominee_name" => $request->input("nominee"),
                        "relation_with_nominee" => $request->input("relation_with_nominee"),
                        "nominee_contact" => $request->input("nominee_contact")
                    ));
                }

                # TIN
                if ($request->has("tin")) {
                    $employee->profile()->update(array(
                        "tin" => $request->input("tin")
                    ));
                }

                # Employee Education
                DegreeUser::where("user_id", $employee->id)->delete();
                if ($request->has("degree_id")) {
                    $employeeEduErrorMsg = "";
                    $employeeEducation = array();
                    foreach ($request->input("degree_id") as $index => $value) {
                        if (!empty($request->input("degree_id")[$index])) {
                            if (empty($request->input("institute_id")[$index])) {
                                $employeeEduErrorMsg .= "Institute field is required!!!|";
                            }
                            if (empty($request->input("passing_year")[$index])) {
                                $employeeEduErrorMsg .= "Passing year field is required!!!";
                            }
                        }
                        if (!empty($employeeEduErrorMsg)) {
                            throw new \Exception($employeeEduErrorMsg);
                        }
                        if (!empty($request->input('degree_id')[$index])) {
                            $education = array(
                                "user_id" => $employee->id,
                                "degree_id" => (isset($request->input("degree_id")[$index]) && $request->input("degree_id")[$index] != null) ? $request->input("degree_id")[$index] : null,
                                "institute_id" => (isset($request->input("institute_id")[$index]) && $request->input("institute_id")[$index] != null) ? $request->input("institute_id")[$index] : null,
                                "passing_year" => (isset($request->input("passing_year")[$index]) && $request->input("passing_year")[$index] != null) ? $request->input("passing_year")[$index] : null,
                                "result" => (isset($request->input("result")[$index]) && $request->input("result")[$index] != null) ? $request->input("result")[$index] : null,
                            );
                            array_push($employeeEducation, $education);
                        }
                    }
                    DegreeUser::insert($employeeEducation);
                }

                # Employee Working History
                JobHistory::where("user_id", $employee->id)->delete();
                if ($request->has("organization")) {
                    $employeeOrgErrorMsg = "";
                    $professionalExp = array();
                    foreach ($request->input("organization") as $index => $value) {
                        if (!empty($request->input("organization")[$index])) {
                            if (empty($request->input("designation")[$index])) {
                                $employeeOrgErrorMsg .= "Designation field is required!!!|";
                            }
                            if (empty($request->input("start_date")[$index])) {
                                $employeeOrgErrorMsg .= "Start Date field is required!!!";
                            }
                        }
                        if (!empty($employeeOrgErrorMsg)) {
                            throw new \Exception($employeeOrgErrorMsg);
                        }
                        if (!empty($request->input('organization')[$index])) {
                            $professionalArr = array(
                                "user_id" => $employee->id,
                                "organization_name" => (isset($request->input("organization")[$index]) && $request->input("organization")[$index] != null) ? $request->input("organization")[$index] : null,
                                "designation" => (isset($request->input("designation")[$index]) && $request->input("designation")[$index] != null) ? $request->input("designation")[$index] : null,
                                "start_date" => (isset($request->input("start_date")[$index]) && $request->input("start_date")[$index] != null) ? $request->input("start_date")[$index] : null,
                                "end_date" => (isset($request->input("end_date")[$index]) && $request->input("end_date")[$index] != null) ? $request->input("end_date")[$index] : null
                            );
                            array_push($professionalExp, $professionalArr);
                        }
                    }
                    JobHistory::insert($professionalExp);
                }
            });

            session()->flash("message", "Employee Updated Successfully");
            $redirect = redirect()->back();
        } catch (Exception $exception) {
            if (!empty($exception->getMessage())) {
                $expConcateMsg = explode('|', $exception->getMessage());
                return redirect()->back()->withInput($request->all())->withErrors($expConcateMsg);
            } else {
                session()->flash("type", "error");
                session()->flash("message", $exception->getMessage());
                $redirect = redirect()->back();
            }
        }
        return $redirect;
    }

    /**
     * @param User $employee
     * @return Factory|View
     */
    public function profile(User $employee)
    {
        if ($employee->id == auth()->user()->id) {
            $data = $employee;

            $presentAddressDistrict = null;
            $permanentAddressDistrict = null;

            if (!is_null($employee->presentAddress)) {
                $presentAddressDistrict = District::where("division_id", $employee->presentAddress->division_id)->get();
            }

            if (isset($employee->permanentAddress)) {
                $permanentAddressDistrict = District::where("division_id", $employee->permanentAddress->division_id)->get();
            }

            $data = $this->getEmployeeFormData();
            $employee = $employee->load("profile", "currentPromotion", "currentStatus", "presentAddress", "permanentAddress", "currentBank", "degrees", "jobHistories");
            $departments = Department::where("office_division_id", $employee->currentPromotion->office_division_id)->select("id", "name")->get();
            $getRoles = $employee->getRoleNames();

            $redirect = view("employee.profile", compact("data", "employee", "departments", "presentAddressDistrict", "permanentAddressDistrict", "getRoles"));
        } else {
            $redirect = redirect()->back();
        }

        return $redirect;
    }

    /**
     * @param RequestUpdateProfile $request
     * @param User $employee
     * @return RedirectResponse
     */
    public function updateProfile(RequestUpdateProfile $request, User $employee)
    {
        try {
            DB::transaction(function () use ($request, $employee) {
                $photoName = $employee->photo ? $employee->photo : null;
                if ($request->hasFile("photo") and $request->file("photo")->isValid()) {
                    $photoFile = $request->file("photo");
                    $photo = Image::make($photoFile);
                    $fileName = $employee->fingerprint_no . "." . User::PHOTO_EXTENSION;
                    $path = public_path("photo/" . $fileName);
                    $photo->resize(200, 250)->encode(User::PHOTO_EXTENSION, 100)->save($path, 100);
                }

                # Profile
                $employee->profile()->update(array(
                    "gender" => $request->input("gender"),
                    "religion" => $request->input("religion"),
                    "marital_status" => $request->input("marital_status"),
                    "blood_group" => $request->input("blood_group"),
                    "personal_phone" => $request->input("personal_phone")
                ));

                #  Present Address
                Address::updateOrCreate(array("user_id" => $employee->id, "type" => Address::TYPE_PRESENT), array(
                    "type" => Address::TYPE_PRESENT,
                    "address" => $request->input("present_address.address"),
                    "zip" => $request->input("present_address.zip") ?? null,
                    "division_id" => $request->input("present_address.division_id"),
                    "district_id" => $request->input("present_address.district_id")
                ));

                # Employee Education
                if ($request->has("degree_id")) {
                    $employeeEduErrorMsg = "";
                    $employeeEducation = array();
                    foreach ($request->input("degree_id") as $index => $value) {
                        if (!empty($request->input("degree_id")[$index])) {
                            if (empty($request->input("institute_id")[$index])) {
                                $employeeEduErrorMsg .= "Institute field is required!!!|";
                            }
                            if (empty($request->input("passing_year")[$index])) {
                                $employeeEduErrorMsg .= "Passing year field is required!!!";
                            }
                        }
                        if (!empty($employeeEduErrorMsg)) {
                            throw new \Exception($employeeEduErrorMsg);
                        }
                        if (!empty($request->input("degree_id")[$index])) {
                            $education = array(
                                "user_id" => auth()->user()->id,
                                "degree_id" => !empty($request->input("degree_id")[$index]) ? $request->input("degree_id")[$index] : null,
                                "institute_id" => !empty($request->input("institute_id")[$index]) ? $request->input("institute_id")[$index] : null,
                                "passing_year" => !empty($request->input("passing_year")[$index]) ? $request->input("passing_year")[$index] : null,
                                "result" => !empty($request->input("result")[$index]) ? $request->input("result")[$index] : null,
                            );
                            array_push($employeeEducation, $education);
                        }

                    }
                    DegreeUser::where("user_id", auth()->user()->id)->delete();
                    DegreeUser::insert($employeeEducation);
                } else {
                    $employee->degrees()->delete();
                }

                # Employee Working History
                if ($request->has("organization")) {
                    $employee->jobHistories()->delete();
                    $employeeOrgErrorMsg = "";
                    foreach ($request->input("organization") as $index => $value) {
                        if (!empty($request->input("organization")[$index])) {
                            if (empty($request->input("designation")[$index])) {
                                $employeeOrgErrorMsg .= "Designation field is required!!!|";
                            }
                            if (empty($request->input("start_date")[$index])) {
                                $employeeOrgErrorMsg .= "Start Date field is required!!!";
                            }
                        }
                        if (!empty($employeeOrgErrorMsg)) {
                            throw new \Exception($employeeOrgErrorMsg);
                        }
                        if (!empty($request->input("organization")[$index])) {
                            JobHistory::create(array(
                                "user_id" => $employee->id,
                                "organization_name" => !empty($request->input("organization")[$index]) ? $request->input("organization")[$index] : null,
                                "designation" => !empty($request->input("designation")[$index]) ? $request->input("designation")[$index] : null,
                                "start_date" => !empty($request->input("start_date")[$index]) ? $request->input("start_date")[$index] : null,
                                "end_date" => !empty($request->input("end_date")[$index]) ? $request->input("end_date")[$index] : null,
                            ));
                        }
                    }
                } else {
                    $employee->jobHistories()->delete();
                }
            });
            session()->flash("message", "Profile Updated Successfully");
            $redirect = redirect()->back();
        } catch (Exception $exception) {
            if (!empty($exception->getMessage())) {
                $expConcateMsg = explode('|', $exception->getMessage());
                return redirect()->back()->withInput($request->all())->withErrors($expConcateMsg);
            } else {
                session()->flash("type", "error");
                session()->flash("message", "Please fill all the required fields!");
            }
            $redirect = redirect()->back()->withInput($request->all())->withErrors($request->messages());
        }

        return $redirect;
    }

    /**
     * @param User $employee
     * @return Factory|\Illuminate\Contracts\View\View
     */
    public function fullProfile(User $employee)
    {
        $userLeave = UserLeave::where("user_id", $employee->id)->where("year", date("Y"))->first();

        $employee = $employee->load(
            "profile", "promotions", "promotions.allDepartment", "currentPromotion.officeDivision", "currentPromotion.department",
            "currentPromotion.designation", "currentPromotion.payGrade", "currentPromotion.workSlot",
            "presentAddress.division", "presentAddress.district", "permanentAddress.division", "permanentAddress.district",
            "currentBank.bank", "currentBank.branch", "supervisedBy", "currentStatus", "loans", "userLoans", "salaries"
        );

        return \view('employee.full-profile', compact('employee', 'userLeave'));
    }

    /**
     * @param User $employee
     * @return Application|Factory|View
     */
    public function changePassword(User $employee)
    {
        if ($employee->id !== auth()->user()->id) abort(403, "Access Denied");

        return view("employee.change-password", compact("employee"));
    }

    /**
     * @param RequestUpdatePassword $request
     * @param User $employee
     * @return RedirectResponse
     */
    public function updatePassword(RequestUpdatePassword $request, User $employee)
    {
        try {
            # Check with Previous Password
            $matchedWithPrevious = Hash::check($request->input("current"), $employee->password);

            # Update Employee Password
            if ($matchedWithPrevious !== false) {
                $employee->update(array("password" => bcrypt($request->input("new")), 'last_login_at' => Carbon::now()));
                session()->flash("message", "Password Updated Successfully");

                # Log employee activity
                activity('password-update')->by(auth()->user())->log('Password Updated');

                $redirect = redirect('/');
            } else {
                session()->flash("type", "error");
                session()->flash("message", "Current password is incorrect!!");

                $redirect = redirect()->back()->withInput($request->all());
            }
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry!! Please try again");
            $redirect = redirect()->back()->withInput($request->all());
        }

        return $redirect;
    }

    /**
     * @param User $employee
     * @return mixed
     */
    public function resetPassword(User $employee)
    {
        try {
            $feedback['status'] = true;

            # Set employee password
            $employee->update(array("password" => bcrypt("123456")));

            session()->flash("message", "Password Reset Successful");

            # Log employee activity
            activity('password-reset')->by(auth()->user())->log('Password Reset');

            $redirect = redirect()->back();

        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @param User $employee
     * @return mixed
     */
    public function delete(User $employee)
    {
        try {
            $feedback['status'] = true;

            DB::transaction(function () use ($employee) {
                $employee->delete();
                $employee->profile()->delete();
                $employee->promotions()->delete();
                $employee->employeeStatus()->delete();
                $employee->addresses()->delete();
                $employee->banks()->delete();
                $employee->degrees()->delete();
                $employee->jobHistories()->delete();
            });
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }

    /**
     * @return array
     */
    protected function getEmployeeFormData(): array
    {
        return array(
            "officeDivisions" => OfficeDivision::orderBy('name', 'asc')->select("id", "name")->get(),
            "designations" => Designation::orderBy("title", "asc")->select("id", "title")->get(),
            "divisions" => Division::orderBy('name', 'asc')->select("id", "name")->get(),
            "districts" => District::orderBy('name', 'asc')->select("id", "name", "division_id")->get(),
            "banks" => Bank::orderBy('name', 'asc')->select("id", "name")->get(),
            "branches" => Branch::orderBy('name', 'asc')->select("id", "name")->get(),
            "degrees" => Degree::orderBy('name', 'asc')->select("id", "name")->get(),
            "institutes" => Institute::orderBy("name", "asc")->select("id", "name")->get(),
            "supervisors" => User::where("is_supervisor", 1)->select("id", "name", "email")->get(),
            "payGrades" => PayGrade::orderBy('name', 'asc')->select("id", "name", "range_start_from", "range_end_to")->get(),
            "workSlots" => WorkSlot::orderBy('title', 'asc')->select("id", "title")->get(),
            "roles" => Role::orderBy('name', 'asc')->get(),
        );
    }

    /**
     * @param Request $request
     */
    public function getInstitutes(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $institutes = Institute::orderby('name', 'asc')->select('id', 'name')->limit(5)->get();
        } else {
            $institutes = Institute::orderby('name', 'asc')->select('id', 'name')->where('name', 'like', '%' . $search . '%')->limit(5)->get();
        }

        $response = array();
        foreach ($institutes as $institute) {
            $response[] = array(
                "id" => $institute->id,
                "text" => $institute->name,
            );
        }

        echo json_encode($response);
        exit;
    }

    /**
     * @param Request $request
     * @param null $employee
     * @return mixed|string|null
     */
    protected function purgePhoto(Request $request, $employee = null)
    {
        $fileName = null;
        if ($request->hasFile("photo") and $request->file("photo")->isValid()) {
            $photoFile = $request->file("photo");
            $photo = Image::make($photoFile);
            $fileName = $employee === null ? $photoFile->getFilename() . time() . "." . User::PHOTO_EXTENSION : $employee->fingerprint_no . "." . User::PHOTO_EXTENSION;
            $path = Storage::disk("public")->getAdapter()->getPathPrefix() . $fileName;
            $photo->resize(200, 250)->encode(User::PHOTO_EXTENSION, 100)->save($path, 100);
        }

        return $fileName;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function import(Request $request)
    {
        try {
            Excel::import(new EmployeeImport(), $request->file("file"), null, \Maatwebsite\Excel\Excel::CSV);
            activity('employee-import')->by(auth()->user())->log('Employee csv imported');

            session()->flash("message", "Employee Imported Successfully");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry!! Something went wrong!");
        }

        return redirect()->back();
    }

    /**
     * @return BinaryFileResponse
     */
    public function export()
    {
        activity('employee-export')->by(auth()->user())->log('Employee csv exported');
        return Excel::download(new EmployeeExport(), now() . "employees.csv");
    }

    /**
     * @param $payload
     * @return Response|mixed|null
     */
    protected function syncWithZKTeco($payload)
    {
        try {
            # Get JWT Token
            $jwtToken = $this->getToken();

            # Check whether department exists or not
            $department = $this->syncDepartment($jwtToken, $payload);

            # Insert employee data to Attendance Server whether all required data exists
            if ($department == true) {
                $payload["department"] = $department;

                $http_response_header = array(
                    "Content-Type" => "application/json",
                    "Authorization" => "JWT " . $jwtToken
                );
                $url = env("ZKTECO_SERVER_PORT") . "/personnel/api/employees/";

                $response = Http::withHeaders($http_response_header)->post($url, $payload);
                return $response->json();
            }
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param $jwtToken
     * @param $data
     * @return bool|Response
     */
    protected function syncDepartment($jwtToken, $data)
    {
        try {
            $http_response_header = array(
                "Content-Type" => "application/json",
                "Authorization" => "JWT " . $jwtToken
            );
            $url = env("ZKTECO_SERVER_PORT") . "/personnel/api/departments/?dept_code=" . $data["department"];

            $department = Http::withHeaders($http_response_header)->get($url);
            $department = $department->json()["data"];

            if (count($department) == 0) $department = false;
            else $department = $department[0]["id"];
        } catch (Exception $exception) {
            $department = false;
        }

        return $department;
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

    /**
     * @return Response|mixed|null
     */
    protected function getToken()
    {
        try {
            $http_response_header = array(
                "Content-Type" => "application/json"
            );
            $url = env("ZKTECO_SERVER_PORT") . "/jwt-api-token-auth/";
            $payLoad = array(
                "username" => env("ZKTECO_BIOTIME_USERNAME"),
                "password" => env("ZKTECO_BIOTIME_PASSWORD")
            );

            $response = Http::withHeaders($http_response_header)->post($url, $payLoad);
            $jwtToken = $response->json()["token"];
        } catch (Exception $exception) {
            $jwtToken = null;
        }

        return $jwtToken;
    }

    /**
     * @param Division $division
     * @return JsonResponse
     */
    public function districtByDivision(Division $division)
    {
        return response()->json(["data" => $division->load("districts")]);
    }

    /**
     * @param RequestEmployeeByOfficeDivisionDepartmentFilter $request
     * @return Factory|View
     */
    public function searchByOfficeDivisionDepartment(RequestEmployeeByOfficeDivisionDepartmentFilter $request)
    {
        $office_division_id = $request->input("office_division_id");
        $departments = $request->input("department_id");
        if (!$request->has("department_id")) $departments = [];
        /*$items = User::with("currentPromotion")->whereHas("currentPromotion", function ($query) use ($request, $departments) {
            if(!$request->has("department_id")) {
                return $query->where("office_division_id", $request->input("office_division_id"));
            } elseif ($request->has("department_id")) {
                return $query->where("office_division_id", $request->input("office_division_id"))->whereIn("department_id", $departments);
            }
        })->paginate(\Functions::getPaginate());*/
        $items = User::with("currentPromotion")->get();
        $items = $items->filter(function ($query) use ($request, $departments) {
            if (!$request->has("department_id")) {
                return $query->currentPromotion->office_division_id == $request->input("office_division_id");
            } elseif ($request->has("department_id")) {
                return $query->currentPromotion->office_division_id == $request->input("office_division_id") and in_array($query->currentPromotion->department_id, $departments);
            }
        })->values();
        $items = \Functions::customPaginate($items, route("employee.searchByOfficeDivisionDepartment"));

        $items = [];
        $data = array(
            "banks" => Bank::orderByDesc("id")->select("id", "name")->get(),
            "branches" => Branch::orderByDesc("id")->select("id", "name")->get(),
            "institutes" => Institute::orderByDesc("id")->select("id", "name")->get(),
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "departments" => Department::where("office_division_id", $request->input("office_division_id"))->get()
        );
        return view("employee.index", compact("items", "data", "office_division_id", "departments"));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmployeeByDepartment(Request $request)
    {
        $all_users = User::with("currentPromotion")->active()->get();
        $employees = [];
        foreach ($all_users as $employee) {
            if (in_array($employee->currentPromotion->department_id, $request->input("department_id"))) {
                $employees[] = $employee;
            }
        }
        return response()->json(array("data" => $employees));
    }


    /**
     * @return RedirectResponse
     */
    public function syncWithBioTime()
    {
        try {
            # Get JWT Token
            $jwtToken = $this->getToken();

            $deSynchronizedEmployees = $this->deSynchronizedEmployees($jwtToken);
            $this->synchronizeDeSynchronizedEmployees($deSynchronizedEmployees);

            session()->flash("message", "Synchronization finished Successfully");
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry! Something went wrong!!");
        }

        return redirect()->route("home");
    }

    /**
     * @return Factory|View
     */
    public function exportProfile()
    {
        $data = [
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
        ];

        return view("employee.export-profile", compact("data"));
    }

    /**
     * @param RequestEmployeeProfileExport $request
     * @return BinaryFileResponse
     * @throws \Throwable
     */
    public function generateExportProfile(RequestEmployeeProfileExport $request)
    {
        try {
            $employees = User::with("currentPromotion")->whereHas("currentPromotion", function ($query) use ($request) {
                $query->where("office_division_id", $request->input("office_division_id"))
                    ->whereIn("department_id", $request->input("department_id"));
            });
            if ($request->has("user_id")) $employees = $employees->whereIn("id", $request->input("user_id") ?? []);
            $employees = $employees->get();

            # Remove all previous files
            array_map('unlink', array_filter((array)glob(base_path("reports/*"))));

            $files = [];
            foreach ($employees as $employee) {
                $data = array(
                    "banks" => Bank::orderByDesc("id")->select("id", "name")->get(),
                    "branches" => Branch::orderByDesc("id")->select("id", "name")->get(),
                    "institutes" => Institute::orderByDesc("id")->select("id", "name")->get(),
                    "officeDivisions" => OfficeDivision::select("id", "name")->get(),
                    "employee" => $employee
                );

                $view = view('employee.export-profile-pdf', compact("data"));
                $html = $view->render();
                $pdf = PDF::loadHTML($html);
                $pdf->setPaper('a4', 'landscape');
                $pdf->save(base_path("reports/" . $employee->fingerprint_no . '.pdf'));
                $files[] = base_path("reports/" . $employee->fingerprint_no . '.pdf');
            }

            $zip = new ZipArchive();
            $zipFileName = "profile.zip";
            $destinationPath = base_path("reports/{$zipFileName}");

            if ($zip->open($destinationPath, ZipArchive::CREATE) == TRUE) {
                $files = File::files(base_path("reports"));

                # Add File to Zip Queue
                foreach ($files as $file) {
                    $relativeNameInZipFile = basename($file);
                    $zip->addFile($file, $relativeNameInZipFile);
                }

                # Close the connection to ZIP
                $zip->close();

                # Unlink all generated files
                foreach ($files as $file) unlink($file);
            }

            $response = response()->download($destinationPath);;
        } catch (Exception $exception) {
            session()->flash("type", "error");
            session()->flash("message", "Sorry! Employee not exists!");

            $response = redirect()->back();
        }

        return $response;
    }

    /**
     * @param $jwtToken
     * @return array
     */
    protected function deSynchronizedEmployees($jwtToken)
    {
        $http_response_header = array(
            "Content-Type" => "application/json",
            "Authorization" => "JWT " . $jwtToken
        );

        $data = [];

        $employees = User::with("currentPromotion")->select("id", "name", "fingerprint_no")->get();
        foreach ($employees as $employee) {
            $url = env("ZKTECO_SERVER_PORT") . "/personnel/api/employees/?emp_code={$employee->fingerprint_no}";
            $response = Http::withHeaders($http_response_header)->get($url);
            $response = $response->json()["data"];

            if (count($response) == 0) array_push($data, $employee);
        }

        return $data;
    }

    /**
     * @param $deSynchronizedEmployees
     */
    protected function synchronizeDeSynchronizedEmployees($deSynchronizedEmployees)
    {
        foreach ($deSynchronizedEmployees as $deSynchronizedEmployee) {
            $this->syncWithZKTeco(array(
                "emp_code" => $deSynchronizedEmployee->fingerprint_no,
                "first_name" => $deSynchronizedEmployee->name,
                "last_name" => "",
                "area" => array(2),
                "department" => $deSynchronizedEmployee->currentPromotion->department_id
            ));
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDatatable(Request $request)
    {
        $authUser = auth()->user();

        $items = User::with([
            "currentPromotion" => function ($query) {
                $query->with("department", "officeDivision", "designation");
            },
            "currentPromotion.officeDivision",
            "currentPromotion.department",
            "currentPromotion.designation",
            "currentStatus",
        ])
            ->join("promotions", function ($join) {
                $join->on('promotions.user_id', 'users.id');
                $join->on('promotions.id', DB::raw("(select max(id) from promotions p where p.user_id = users.id limit 1)"));
            })
            ->join('profiles', 'profiles.user_id', 'users.id')
            ->orderBy("users.status", 'DESC')
            ->orderBy("promotions.id", 'DESC')
            ->groupBy('users.id')
            ->select([
                "users.id",
                "users.uuid",
                "name",
                "email",
                "phone",
                "fingerprint_no",
                "status",
                "photo",
                "office_division_id",
                "department_id",
                "designation_id",
                "promotions.type as promotion_type",
                "promoted_date",
                "profiles.dob",
                "sync_device"
            ]);

        if ($request->has('office_division_id') && $request->office_division_id != null) {
            $items->where('promotions.office_division_id', $request->office_division_id);
        }
        if ($request->has('department_id') && is_array($request->department_id) && count($request->department_id) > 0) {
            $items->whereIn('promotions.department_id', $request->department_id);
        }
        if ($request->has('status_filter') && $request->status_filter != null) {
            $items->where('status', $request->status_filter);
        }
        if ($request->has('designation_id') && $request->designation_id != null) {
            $items->where('designation_id', $request->designation_id);
        }

        if ($request->has('department_ids')) {
            $departmentsIDs = json_decode($request->department_ids, true);
            if (is_array($departmentsIDs) && count($departmentsIDs) > 0) {
                $items->whereIn('department_id', $departmentsIDs)->where('users.status', 1);
            }
        }
        if ($request->has('status') && $request->status != null) {
            $items->where('status', $request->status);
        }
        return DataTables::eloquent($items)
            ->editColumn('photo', function ($item) {
                $imgSrc = file_exists("photo/" . $item->fingerprint_no . ".jpg") ? asset("photo/" . $item->fingerprint_no . ".jpg") : asset('assets/media/svg/avatars/001-boy.svg');
                return '<div class="symbol flex-shrink-0" style="width: 35px; height: auto"><img src=' . $imgSrc . '></div>';
                //return '<img src='.asset("photo/".$item->fingerprint_no.".jpg") . "?" . uniqid() .'onerror="this.onerror=null; this.src='.asset('assets/media/svg/avatars/001-boy.svg').';" />';
            })
            ->editColumn('name', function ($item) {
                return '<a href="#" onclick=showProfile("' . $item->uuid . '") data-toggle="modal" data-target="#profileModal-' . $item->uuid . '">' . $item->name . '</a>';
            })
            ->editColumn('status', function ($item) {
                $checkdVal = '';
                if ($item->status === \App\Models\User::STATUS_ACTIVE) {
                    $checkdVal = 'checked';
                }
                if (empty($checkdVal) && $item->promotion_type == Promotion::TYPE_TERMINATED) {
                    //if (empty($checkdVal)) {
                    return '<span class="switch switch-outline switch-icon switch-primary">
                                <label>
                                    <input type="checkbox"' . $checkdVal . '

                                            name="status" data-toggle="modal" data-target="#employee-rejoin-modal" data-promoted-date="' . $item->promoted_date . '" id="rejoin_employee_' . $item->uuid . '"onclick="changeInactiveStatus(' . "'" . $item->uuid . "'," . $item->id . ')"/>

                                    <span></span>
                                </label>
                            </span>';
                }
                return '<span class="switch switch-outline switch-icon switch-primary">
                                <label>
                                    <input type="checkbox"' . $checkdVal . '
                                            name="status" id="' . $item->uuid . '" onclick="changeStatus(' . "'" . $item->uuid . "'," . $checkdVal . ')"/>
                                    <span></span>
                                </label>
                            </span>';
            })
            ->addColumn('action', function (User $obj) use ($authUser) {
                $str = "";
                $str .= '<div class="symbol flex-shrink-0" style="width: 100px; height: auto">';
                $str .= '<a href="' . route('employee.fullProfile', ['employee' => $obj->uuid]) . '" target="_blank"><i class="fa fa-asterisk" aria-hidden="true" style="color: lawngreen"></i></a>&nbsp;';

                if ($authUser->can('Sync Employee Device') && !$obj->sync_device) {
                    $str .= '<a href="javascript:;" onclick="syncEmployeeWithDevice(' . "'" . $obj->uuid . "'" . ')"><i class="fa fa-dot-circle" style = "color: yellowgreen" ></i ></a>&nbsp;';
                }
                if ($authUser->can('Reset Employee Password')) {
                    $dob = "";
                    if (!empty($obj->dob)) {
                        $getExpDob = explode('-', $obj->dob);
                        $dob = $getExpDob[0] . $getExpDob[1] . $getExpDob[2];
                    }
                    $dob = base64_encode($dob);
                    //$resetPassUrl = "'" . route('employee.resetPassword', ['employee' => $obj->uuid]) . "'";
                    //$str .= '<a href="#" onclick = "resetAlert(' . $resetPassUrl . ')"><i class="fa fa-key" aria-hidden="true" style="color: red"></i></a>&nbsp;';
                    $str .= '<a href="#" data-toggle="modal" data-target="#employee-password-reset-modal" onclick="resetPassword(' . "'" . $obj->uuid . "','" . $dob . "'" . ')"><i class="fa fa-key" aria-hidden="true" style="color: red"></i></a>&nbsp;';
                }
                if ($authUser->can('Edit Employee Info')) {
                    $str .= '<a href="' . route('employee.edit', ['employee' => $obj->uuid]) . '"><i class="fa fa-edit" style = "color: green" ></i ></a>&nbsp;';
                }
                if ($authUser->can('Delete Employee')) {
                    $deleteUrl = "'" . route('employee.delete', ['employee' => $obj->uuid]) . "'";
                    $str .= '<a href="#" onclick="deleteAlert(' . $deleteUrl . ')" ><i class="fa fa-trash" style = "color: red" ></i ></a>';
                }
                $str .= '</div>';

                return $str;
            })
            ->rawColumns(['photo', 'name', 'status', 'action'])
            ->toJson();
    }

    /**
     * @param Request $request
     * @return Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function showProfile(Request $request)
    {
        try {
            if (empty($request->uuid)) {
                throw new \Exception("Missing Employee ID!!!");
            }
            $employee = User::with(["profile", "currentPromotion" => function ($query) {
                $query->with("officeDivision", "department", "designation", "payGrade", "workSlot");
            }, "currentStatus", "presentAddress" => function ($query) {
                $query->with("division", "district");
            }, "permanentAddress" => function ($query) {
                $query->with("division", "district");
            }, "jobHistories", "degrees", "currentBank"])
                ->orderByDesc("id")
                ->select("id", "name", "email", "phone", "fingerprint_no", "status", "photo")
                ->where(['users.uuid' => $request->uuid])
                ->first();
            if (empty($employee->id)) {
                throw new \Exception("Missing Employee Information!!!");
            }
            $data = array(
                "banks" => Bank::orderByDesc("id")->select("id", "name")->get(),
                "branches" => Branch::orderByDesc("id")->select("id", "name")->get(),
                "institutes" => Institute::orderByDesc("id")->select("id", "name")->get(),
                "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            );
            return view('employee.show-profile', compact('employee', 'data'));
        } catch (\Exception $ex) {
            session()->flash("type", "error");
            session()->flash("message", $ex->getMessage());
            return redirect()->back();
        }
    }

    /**
     * @param Request $request
     */
    public function getBanks(Request $request)
    {
        $search = $request->search;
        getBanks($search);
    }

    /**
     * @param Request $request
     */
    public function getBranches(Request $request)
    {
        $search = $request->search;
        getBranches($search);
    }

    /**
     * @param Request $request
     */
    public function getInstituteWithFilterWise(Request $request)
    {
        $search = $request->search;
        getInstitutesFilter($search);
    }

    public function modalDepartment(Request $request)
    {
        $officeDivisions = OfficeDivision::select("id", "name");
        $officeDivisions = $officeDivisions->get();
        return response()->json([
            'officeDivisionId' => $request->office_division_id,
            'officeDivisions' => $officeDivisions
        ]);
    }

    public function modalDesignation()
    {
        return response()->json([
            'status' => "success"
        ]);
    }

    public function modalInstitution()
    {
        return response()->json([
            'status' => "success"
        ]);
    }

    private function __getDepartmentInfos()
    {
        $items = User::select("id", "name", "email", "fingerprint_no")->where(['status' => User::STATUS_ACTIVE])->orderBy("name")->get();
        $officeDivisions = OfficeDivision::select("id", "name");
        $officeDivisions = $officeDivisions->get();
        $warehouses = Warehouse::select("id", "name")->get();
        $leaveTypes = LeaveType::select("id", "name")->orderByDesc("id")->get();
        $trackingType = "employee";
        return [
            'items' => $items,
            'officeDivisions' => $officeDivisions,
            'warehouses' => $warehouses,
            'leaveTypes' => $leaveTypes,
            'trackingType' => $trackingType
        ];
    }

    public function rejoinEmployee(Request $request, User $employee)
    {
        try {
            $box = $request->all();
            $postData = array();
            parse_str($box['values'], $postData);
            $rejoinDate = date('Y-m-d', strtotime($postData['re_joining_date']));
            $rule['re_joining_date'] = 'required';
            $rule['employment_type'] = 'required';
            $validator = Validator::make($postData, $rule);
            if ($validator->passes()) {
                $empID = base64_decode($postData['trackID']);
                if (empty($empID)) {
                    throw new \Exception("Missing Employee ID!!!");
                }
                $employee = User::join("promotions", function ($join) {
                    $join->on('promotions.user_id', 'users.id');
                    $join->on('promotions.id', DB::raw("(select max(p.id) from promotions p where p.user_id = users.id limit 1)"));
                })
                    ->join('profiles', 'profiles.user_id', 'users.id')
                    ->select([
                        "users.id",
                        "promotions.user_id",
                        "office_division_id",
                        "department_id",
                        "designation_id",
                        "pay_grade_id",
                        "salary",
                        "workslot_id",
                        "promotions.id as promotion_id",
                        "promoted_date",
                        "profiles.dob"
                        //])->where(['users.id' => $empID, 'users.status' => User::STATUS_DISABLE])->first();
                    ])->where(['users.id' => $empID, 'users.status' => User::STATUS_DISABLE, 'promotions.type' => Promotion::TYPE_TERMINATED])->first();

                if (empty($employee->id)) {
                    throw new \Exception("Invalid Employee Information!!!");
                }
                if (empty($employee->dob)) {
                    throw new \Exception("Employee DOB not found!!!");
                }
                if ($rejoinDate <= date('Y-m-d', strtotime($employee->promoted_date))) {
                    throw new \Exception("Re-join Date not less than Equal to Employment Close Date!!!");
                }

                DB::beginTransaction();

                $getResponse = Common::modifyPromotionEmploymentTypeEmployeeWise($empID);
                if (!empty($getResponse['errorMsg'])) {
                    throw new \Exception($getResponse['errorMsg']);
                }

                $joinId = ActionReason::where("name", ActionReason::TYPE_JOIN)->pluck("id")->first();
                $actionJoinID = ActionReason::where(["parent_id" => $joinId])->select("id")->first();

                $calculateLeave = $this->__calculateLeaveBalanceEmployeeWise($empID, $employee->department_id, $rejoinDate);
                if (isset($calculateLeave['errorMsg'])) {
                    throw new \Exception($calculateLeave['errorMsg']);
                }
                $dob = "";
                if (!empty($employee->dob)) {
                    $getExpDob = explode('-', $employee->dob);
                    $dob = $getExpDob[0] . $getExpDob[1] . $getExpDob[2];
                }
                $employee->update([
                    "status" => User::STATUS_ACTIVE,
                    "password" => bcrypt($dob),
                    "last_login_at" => Null
                ]);

                //$terminate = Promotion::where('user_id', $empID)->where('type', Promotion::TYPE_TERMINATED)->orderBy('id', 'DESC')->first();
                $terminate = Promotion::where('user_id', $empID)->orderBy('id', 'DESC')->first();
                if (!empty($terminate->id)) {
                    Roster::where("user_id", $empID)->where('active_date', '>', $terminate->promoted_date)->delete();
                }

                EmployeeStatus::updateOrCreate(['user_id' => $empID, 'action_date' => $rejoinDate], [
                    "action_reason_id" => $actionJoinID->id,
                    "action_taken_by" => auth()->user()->id,
                    "action_date" => $rejoinDate,
                    "user_id" => $empID,
                    "status_reasons" => !empty($postData['rejoin_reasons']) ? $postData['rejoin_reasons'] : ""
                ]);

                Promotion::updateOrCreate(['id' => $employee->promotion_id, 'user_id' => $empID, 'promoted_date' => $rejoinDate, 'type' => Promotion::TYPE_REJOIN], [
                    "office_division_id" => $employee->office_division_id,
                    "user_id" => $empID,
                    "department_id" => $employee->department_id,
                    "designation_id" => $employee->designation_id,
                    "pay_grade_id" => $employee->pay_grade_id,
                    "promoted_date" => $rejoinDate,
                    "type" => Promotion::TYPE_REJOIN,
                    "employment_type" => !empty($postData['employment_type']) ? $postData['employment_type'] : NULL,
                    "workslot_id" => $employee->workslot_id
                ]);

                if (!empty($calculateLeave)) {
                    UserLeave::updateOrCreate([
                        'user_id' => $empID,
                        'year' => $calculateLeave['year']
                    ], [
                        'user_id' => $empID,
                        'initial_leave' => $calculateLeave['initial_leave'],
                        'total_initial_leave' => $calculateLeave['total_initial_leave'],
                        'leaves' => $calculateLeave['leaves'],
                        'total_leaves' => $calculateLeave['total_leaves'],
                        'year' => $calculateLeave['year']
                    ]);
                }
                $getAssignRelaxDay = AssignRelaxDay::where('user_id', $empID)->get();
                if (!empty($getAssignRelaxDay) && count($getAssignRelaxDay) > 0) {
                    AssignRelaxDay::where('user_id', $empID)->delete();
                }

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Re-join successfully!!!']);
            } else {
                foreach ($validator->errors()->all() as $error) {
                    return response()->json(['status' => false, 'message' => $error]);
                }
                return response()->json(['status' => false, 'message' => 'Something went wrong!']);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $ex->getMessage()]);
        }
    }

    private function __calculateLeaveBalanceEmployeeWise($empID, $departmentID, $rejoinDate)
    {
        try {
            $errorMsg = "";
            $current_year = date("Y", strtotime($rejoinDate));
            $users = DB::select("SELECT users.id, users.`name`, users.email,users.fingerprint_no,prm.promoted_date,
                       prm.department_id, prm.office_division_id FROM `users`
                       INNER JOIN promotions AS prm ON prm.user_id = users.id
                       AND prm.id =( SELECT MAX( pm.id ) FROM `promotions` AS pm WHERE pm.user_id = users.id)
                       WHERE users.id=$empID");
            # Leave Allocations for Current Year on the associated Department
            $leaveAllocation = LeaveAllocation::join('leave_allocation_details', 'leave_allocation_details.leave_allocation_id', '=', 'leave_allocations.id')
                ->where(["year" => $current_year, 'department_id' => $departmentID])
                ->get();
            $department_wise_leave = [];
            foreach ($leaveAllocation as $leave) {
                $department_wise_leave[$leave->department_id][$leave->leave_type_id] = $leave->total_days;
            }
            if (empty($department_wise_leave) && count($department_wise_leave) <= 0) {
                throw new \Exception("Leave allocation is not found for year " . $current_year);
            }
            foreach ($users as $user) {
                $initialLeave = [];
                $initialLeaveBalance = [];
                $currentLeave = [];
                $currentLeaveBalance = [];
                $year_month_date = explode('-', $rejoinDate);
                $totalInitialLeave = 0;
                $totalCurrentLeave = 0;
                foreach ($department_wise_leave[$user->department_id] as $leave_type_id => $balance) {
                    $initialLeave['leave_type_id'] = $leave_type_id;
                    if ($year_month_date[0] < $current_year) {
                        $initialLeave['total_days'] = $balance;
                    } else {
                        if ($year_month_date[1] < 12) {
                            $calculate_month = 12 - $year_month_date[1];
                            $leave_amount_for_month = ($balance * $calculate_month) / 12;
                        } else {
                            $leave_amount_for_month = 0;
                        }
                        $calculate_day = (30 - $year_month_date[2]) + 1;
                        if ($calculate_day >= 15) {
                            $per_month_avg_leave = $balance / 12;
                            $leave_amount_for_day = ($per_month_avg_leave * $calculate_day) / 30;
                        } else {
                            $leave_amount_for_day = 0;
                        }
                        $total_leave = $leave_amount_for_month + $leave_amount_for_day;
                        $integer_leave = floor($total_leave);
                        $fraction_leave = $total_leave - $integer_leave;
                        if ($fraction_leave > .5) {
                            $fraction_leave = 1;
                        } else {
                            if ($fraction_leave > 0) {
                                $fraction_leave = 0.5;
                            }
                        }
                        $initialLeave['total_days'] = $integer_leave + $fraction_leave;
                    }
                    $currentLeave['leave_type_id'] = $leave_type_id;
                    $used = 0;
                    $currentLeave['total_days'] = ($initialLeave['total_days'] - $used) < 0 ? 0 : ($initialLeave['total_days'] - $used);
                    $totalInitialLeave = $totalInitialLeave + $initialLeave['total_days'];
                    $totalCurrentLeave = $totalCurrentLeave + $currentLeave['total_days'];
                    $initialLeaveBalance[] = $initialLeave;
                    $currentLeaveBalance[] = $currentLeave;
                }
                return [
                    'user_id' => $user->id,
                    'initial_leave' => json_encode($initialLeaveBalance),
                    'total_initial_leave' => $totalInitialLeave,
                    'leaves' => json_encode($currentLeaveBalance),
                    'total_leaves' => $totalCurrentLeave,
                    'year' => $current_year
                ];
            }
        } catch (\Exception $ex) {
            $errorMsg = $ex->getMessage();
        }
        if (!empty($errorMsg)) {
            return ['errorMsg' => $errorMsg];
        }
    }

    public function profileDownload(User $employee)
    {
        //$data = $this->getEmployeeFormData();
        $employee = $employee->load("profile", "currentPromotion", "currentStatus");
        //$departments = Department::where("office_division_id", $employee->currentPromotion->office_division_id)->select("id", "name")->get();
        //$deptInfos = $this->__getDepartmentInfos();
        $data = array(
            "banks" => Bank::orderByDesc("id")->select("id", "name")->get(),
            "branches" => Branch::orderByDesc("id")->select("id", "name")->get(),
            "institutes" => Institute::orderByDesc("id")->select("id", "name")->get(),
            "officeDivisions" => OfficeDivision::select("id", "name")->get(),
            "employee" => $employee
        );
        $view = view('employee.pdf.profile-download-pdf', compact("data"));
        $html = $view->render();
        $pdf = PDF::loadHTML($html);
        //$pdf = PDF::loadView('employee.pdf.profile-download-pdf', compact("data"));
        $pdf->setPaper('a4', 'potrait');
        //$pdf->SetProtection(['copy', 'print'], '', 'pass');
        $file_name = date('d-m-Y', time()) . '-profile.pdf';
        //return $pdf->download($file_name);
        return $pdf->stream($file_name);
    }

    public function syncDepartmentalSupervisor($employeeID, $officeDivisionID, $departmentID)
    {
        $errorMsg = "";
        try {
            if (empty($departmentID)) {
                throw new \Exception("Missing Department ID!!!");
            }
            if (empty($officeDivisionID)) {
                throw new \Exception("Missing Division ID!!!");
            }
            if (empty($employeeID)) {
                throw new \Exception("Missing Employee ID!!!");
            }
            User::where("id", $employeeID)->update(["is_supervisor" => User::SUPERVISOR_DEPARTMENT]);
            DepartmentSupervisor::updateOrCreate(
                ['status' => '1', 'supervised_by' => $employeeID, 'department_id' => $departmentID],
                [
                    'office_division_id' => $officeDivisionID,
                    'department_id' => $departmentID,
                    'supervised_by' => $employeeID
                ]
            );
        } catch (\Exception $ex) {
            $errorMsg = $ex->getMessage();
        }
        return [
            'msg' => $errorMsg
        ];
    }

    public function syncDivisionalSupervisor($employeeID, $officeDivisionID, $departmentID)
    {
        $errorMsg = "";
        try {
            if (empty($departmentID)) {
                throw new \Exception("Missing Department ID!!!");
            }
            if (empty($officeDivisionID)) {
                throw new \Exception("Missing Division ID!!!");
            }
            if (empty($employeeID)) {
                throw new \Exception("Missing Employee ID!!!");
            }
            User::where("id", $employeeID)->update(["is_supervisor" => User::SUPERVISOR_OFFICE_DIVISION]);
            DivisionSupervisor::updateOrCreate(
                ['status' => '1', 'supervised_by' => $employeeID, 'office_division_id' => $officeDivisionID],
                [
                    'office_division_id' => $officeDivisionID,
                    'supervised_by' => $employeeID
                ]
            );
        } catch (\Exception $ex) {
            $errorMsg = $ex->getMessage();
        }
        return [
            'msg' => $errorMsg
        ];
    }

    public function modalOfficeDivision()
    {
        return response()->json([
            'status' => "success"
        ]);
    }

    public function getDesignations(Request $request)
    {
        $search = $request->search;
        return Common::getDesignations($search);
    }

    public function resetEmployeePassword(Request $request)
    {
        try {
            $box = $request->all();
            $postData = array();
            parse_str($box['values'], $postData);
            $rule['reset_password_emp'] = 'required';
            $validator = Validator::make($postData, $rule);
            if ($validator->passes()) {
                if (empty($postData['reset_password_emp'])) {
                    throw new \Exception("Missing Password!!!");
                }
                if (empty($postData['hidden_emp_id'])) {
                    throw new \Exception("Missing employee ID!!!");
                }
                $employee = User::where('uuid', $postData['hidden_emp_id'])->first();
                if (empty($employee->id)) {
                    throw new \Exception("Invalid Employee Information!!!");
                }
                $employee->update([
                    "password" => bcrypt($postData['reset_password_emp']),
                ]);
                return response()->json(['status' => true, 'message' => 'Password reset successfully!!!']);
            } else {
                foreach ($validator->errors()->all() as $error) {
                    return response()->json(['status' => false, 'message' => $error]);
                }
                return response()->json(['status' => false, 'message' => 'Something went wrong!']);
            }
        } catch (\Exception $ex) {
            return response()->json(['status' => false, 'message' => $ex->getMessage()]);
        }
    }


    public function syncEmployeeDevice(Request $request)
    {
        try {
            $errorMsg = '';
            if (empty($request->input('uuid'))) {
                throw new \Exception("Invalid employee ID!!!");
            }
            $employee = User::where('uuid', $request->input('uuid'))->first();
            if (empty($employee->id)) {
                throw new \Exception("Employee record not found!!!");
            }
            if (env("ZKTECO_SYNC_USER") === true) {
                $existsEmployee = Common::checkEmployeeDeviceDataExistsOrNot($employee->fingerprint_no);
                Log::info("#Device Existing Employee Response Status Start[Sync Button]#");
                Log::info($existsEmployee);
                Log::info("#Device Existing Employee Response Status End#");
                if (!$existsEmployee) {
                    $getDeviceResponse = $this->syncWithZKTeco(array(
                        "emp_code" => $employee->fingerprint_no,
                        "first_name" => $employee->name,
                        "last_name" => "",
                        "area" => array(2),
                        "department" => 1
                    ));
                    Log::info("#Device Response Start[Sync Button]#");
                    Log::info($getDeviceResponse);
                    Log::info("#Device Response End#");
                    if (empty($getDeviceResponse)) {
                        throw new \Exception("Failed to sync device.");
                    }
                    $employee->update(["sync_device" => 1]);
                }elseif($existsEmployee){
                    $employee->update(['sync_device' => 1]);
                }
            }
        } catch (\Exception $exception) {
            $errorMsg = $exception->getMessage();
            return response()->json(['status' => false, 'message' => $errorMsg]);
        }
        if (empty($errorMsg)) {
            return response()->json(['status' => true, 'message' => "Successfully sync attendance device"]);
        }
    }
}
