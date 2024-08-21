<?php

namespace App\Http\Controllers;

use App\Exports\Report\LoanReportExport;
use App\Exports\Report\SalarySheetBankExport;
use App\Exports\Report\SalarySheetExport;
use App\Exports\Report\TaxReportExport;
use App\Library\GenerateSalary;
use App\Models\Department;
use App\Models\DepartmentSupervisor;
use App\Models\DivisionSupervisor;
use App\Models\OfficeDivision;
use App\Models\Promotion;
use App\Models\Salary;
use App\Models\SalaryDepartment;
use App\Models\User;
use App\Models\UserLeave;
use App\Models\ZKTeco\DailyAttendance;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalaryExportController extends Controller
{
    use GenerateSalary;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $exportClasses = [
        "bank-statement" => SalarySheetBankExport::class,
        "tax-deduction" => TaxReportExport::class,
        "loan-deduction" => LoanReportExport::class,
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function downloadExportFile(Request $request)
    {
        $validated = $request->validate([
            'department_ids' => 'required',
            'month' => 'required',
            'year' => 'required',
            'export_file_type' => 'required',
        ]);

        if (!$validated) {
            return redirect()->back();
        }

        $dptIds = json_decode($request->department_ids);


        if (empty($dptIds)) {
            # $dptIds = Department::pluck('id')->toArray();
            $dptIds = [];
        }

        $fileName = $request->type . '-' . $request->month . '-' . $request->year;
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '-', $fileName);
        $fileName .= $request->export_file_type;


        if ($request->export_file_type == '.xlsx') {

            return Excel::download(new $this->exportClasses[$request->type]($dptIds, $request->month, $request->year), $fileName);

        } else if ($request->export_file_type == '.pdf') {
            $salaries = Salary::with("user.currentBank")
                ->whereIn("salary_department_id", $dptIds)
                ->where("month", $request->month)
                ->where("year", $request->year)
                ->get();

            return PDF::loadView('salary.salary_export_bank_statement_pdf', compact("salaries"))->download($fileName);
        }
        return redirect()->back()->withErrors("Invalid export file type");
    }

}
