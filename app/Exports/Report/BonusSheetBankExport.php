<?php

namespace App\Exports\Report;

use App\Http\Controllers\SalaryController;
use App\Models\Salary;
use App\Models\UserBonus;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BonusSheetBankExport implements FromCollection, WithHeadings, WithColumnFormatting, WithEvents
{
    protected $departmentIds = [];
    protected $month = [];
    protected $year = [];

    /**
     * @var array
     */
    protected $report = [];

    /**
     * Create a new controller instance
     *
     * SalarySheetExport constructor.
     * @param $departmentIds
     * @param $month
     * @param $year
     * @param array $payment_modes
     */
    public function __construct($departmentIds, $month, $year, $payment_modes = [])
    {
        $this->departmentIds = $departmentIds;
        $this->month = $month;
        $this->year = $year;
        $this->payment_modes = $payment_modes;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return ["", "", "", "Bank Statement for Bonus(BYSL Global Technology Group)"];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $reports = [
            [""],
            ["SL. No.", "ID. No", "Account Name", "Account No", "Amount in Taka", "Payment Mode", "Department"],
        ];

        $bonuses = UserBonus::with("user.currentBank")
            ->whereIn("bonus_department_id", $this->departmentIds)
            /*->where("month", $this->month)
            ->where("year", $this->year)*/
            ->get();

        $netPayable = 0;
        foreach ($bonuses as $key => $bonus) {
            $acc = !empty($bonus->user->currentBank->account_no) ? $bonus->user->currentBank->account_no : "";
            $amount = SalaryController::currencyFormat($bonus->net_payable_amount, 'BHD') ?? ""; #for 3 digit comma separator

            array_push($reports, [
                $key + 1,
                $bonus->user->fingerprint_no,
                $bonus->user->currentBank->account_name ?? "",
                $acc,
                $amount,
                $bonus->payment_mode,
                $bonus->department->name ?? '',
            ]);
            $netPayable += $bonus->net_payable_amount;
        }

        $netPayable = SalaryController::currencyFormat($netPayable, 'BHD') ?? "";

        array_push($reports, [
            "", "", "", "TOTAL", $netPayable
        ]);

        return collect($reports);
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(22);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(50);

                $event->sheet->getDelegate()->getStyle('A1:G1')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('538DD5');

                $event->sheet->getDelegate()->getStyle('A1:G1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->getColor()
                    ->setARGB('white');
            },
        ];
    }
}
