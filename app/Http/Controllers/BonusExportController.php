<?php

namespace App\Http\Controllers;

use App\Exports\Report\BonusSheetBankExport;
use App\Exports\Report\BonusTaxReportExport;
use App\Library\GenerateSalary;
use App\Models\UserBonus;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BonusExportController extends Controller
{
    use GenerateSalary;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $exportClasses = [
        "bank-statement" => BonusSheetBankExport::class,
        "tax-deduction" => BonusTaxReportExport::class
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
            $dptIds = [];
        }

        $fileName = 'bonus-' . $request->type . '-' . $request->month . '-' . $request->year;
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '-', $fileName);
        $fileName .= $request->export_file_type;


        if ($request->export_file_type == '.xlsx') {

            return Excel::download(new $this->exportClasses[$request->type]($dptIds, $request->month, $request->year), $fileName);

        } else if ($request->export_file_type == '.pdf') {
            $bonuses = UserBonus::with("user.currentBank")
                ->whereIn("bonus_department_id", $dptIds)
                /*->where("month", $request->month)
                ->where("year", $request->year)*/
                ->get();

            return PDF::loadView('user-bonus.bonus_export_bank_statement_pdf', compact("bonuses"))->download($fileName);
        }
        return redirect()->back()->withErrors("Invalid export file type");
    }

}
