<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\MaterialOrder;
use App\Models\OtherUtilities;
use App\Models\OtherUtilitiesSub;
use App\Models\Site;
use App\Models\Subcontractor;
use App\Models\SubcontractorPayment;
use App\Models\SubcontractorService;
use App\Models\Wages;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SiteReportExport implements FromArray, WithEvents, WithTitle
{
    private Site $site;
    private int $totalRow = 7;
    private int $utilityTitleRow = 8;
    private int $utilityHeaderRow = 9;
    private int $utilityTotalRow = 10;
    private int $sectionTotalRow = 11;
    private int $grandTotalRow = 8;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    public function title(): string
    {
        return 'Full Site Report';
    }

    public function array(): array
    {
        $attendances = Attendance::where('site_id', $this->site->id)
            ->orderBy('date')
            ->orderBy('category')
            ->get();
        $materials = MaterialOrder::with('vendor')
            ->where('site_id', $this->site->id)
            ->orderBy('date')
            ->orderBy('material_type')
            ->get();
        $services = SubcontractorService::with('subcontractor')
            ->where('site_id', $this->site->id)
            ->orderBy('date')
            ->orderBy('subcontractor_type')
            ->get();
        $materialUtilities = OtherUtilities::where('site_id', $this->site->id)
            ->orderBy('created_at')
            ->get();
        $subcontractorUtilities = OtherUtilitiesSub::where('site_id', $this->site->id)
            ->orderBy('created_at')
            ->get();

        $attendanceRows = [];
        $attendanceTotal = 0;
        foreach ($attendances as $attendance) {
            $wage = (float) (Wages::where('site_id', $this->site->id)
                ->where('category', $attendance->category)
                ->whereDate('date', '<=', $attendance->date)
                ->orderByDesc('date')
                ->value('amount') ?? 0);
            $total = (float) $attendance->count * $wage;
            $attendanceTotal += $total;
            $attendanceRows[] = [
                Carbon::parse($attendance->date)->format('d-m-Y'),
                ucfirst($attendance->category),
                (int) $attendance->count,
                $wage,
                $total,
            ];
        }

        $materialRows = [];
        $materialTotal = 0;
        foreach ($materials as $material) {
            $materialTotal += (float) $material->price;
            $materialRows[] = [
                Carbon::parse($material->date)->format('d-m-Y'),
                ucfirst($material->material_type),
                optional($material->vendor)->name ?: '-',
                (float) $material->quantity,
                $material->unit ?: '-',
                (float) $material->price,
            ];
        }

        $serviceRows = [];
        $subcontractorTotal = 0;
        foreach ($services as $service) {
            $subcontractorTotal += (float) $service->amount;
            $serviceRows[] = [
                Carbon::parse($service->date)->format('d-m-Y'),
                ucfirst($service->subcontractor_type),
                optional($service->subcontractor)->name ?: '-',
                (int) $service->no_counts,
                $service->remarks ?: '-',
                (float) $service->amount,
            ];
        }

        // Petty Cash and Rental Management payments are tracked as subcontractor
        // payments against a sentinel "wrapper" subcontractor per site — fold
        // them into the same Subcontractor Report table instead of a separate one.
        $pettyCashSubcontractor = Subcontractor::where('email', 'pettycash.site.' . $this->site->id . '@local.invalid')->first();
        $pettyCashPayments = $pettyCashSubcontractor
            ? SubcontractorPayment::where('subcontractor_id', $pettyCashSubcontractor->id)->orderBy('date')->get()
            : collect();
        foreach ($pettyCashPayments as $payment) {
            $subcontractorTotal += (float) $payment->payment;
            $serviceRows[] = [
                Carbon::parse($payment->date)->format('d-m-Y'),
                'Petty Cash',
                $this->site->site_name,
                '-',
                $payment->remarks ?: '-',
                (float) $payment->payment,
            ];
        }

        $rentalSubcontractor = Subcontractor::where('email', 'rental.site.' . $this->site->id . '@local.invalid')->first();
        $rentalPayments = $rentalSubcontractor
            ? SubcontractorPayment::where('subcontractor_id', $rentalSubcontractor->id)->orderBy('date')->get()
            : collect();
        foreach ($rentalPayments as $payment) {
            $subcontractorTotal += (float) $payment->payment;
            $serviceRows[] = [
                Carbon::parse($payment->date)->format('d-m-Y'),
                'Rental Management',
                $this->site->site_name,
                '-',
                $payment->remarks ?: '-',
                (float) $payment->payment,
            ];
        }

        $rows = [
            [$this->site->site_name],
            ['Generated On', now()->format('d-m-Y h:i A')],
            ['ATTENDANCE REPORT', '', '', '', '', '', 'MATERIALS REPORT', '', '', '', '', '', '', 'SUBCONTRACTOR REPORT'],
            ['Date', 'Category', 'Count', 'Wage', 'Amount', '', 'Date', 'Material', 'Vendor', 'Quantity', 'Unit', 'Amount', '', 'Date', 'Type', 'Subcontractor', 'Count', 'Remarks', 'Amount'],
        ];

        $recordCount = max(count($attendanceRows), count($materialRows), count($serviceRows), 1);
        for ($index = 0; $index < $recordCount; $index++) {
            $attendance = $attendanceRows[$index] ?? ($index === 0 && !$attendanceRows ? ['No records', '', '', '', ''] : array_fill(0, 5, ''));
            $material = $materialRows[$index] ?? ($index === 0 && !$materialRows ? ['No records', '', '', '', '', ''] : array_fill(0, 6, ''));
            $service = $serviceRows[$index] ?? ($index === 0 && !$serviceRows ? ['No records', '', '', '', '', ''] : array_fill(0, 6, ''));
            $rows[] = array_merge($attendance, [''], $material, [''], $service);
        }

        $rows[] = [
            'ATTENDANCE SUBTOTAL', '', '', '', $attendanceTotal, '',
            'MATERIALS SUBTOTAL', '', '', '', '', $materialTotal, '',
            'SUBCONTRACTOR SUBTOTAL', '', '', '', '', $subcontractorTotal,
        ];
        $this->totalRow = count($rows);

        $rows[] = array_fill(0, 19, '');
        $utilityTitle = array_fill(0, 19, '');
        $utilityTitle[6] = 'MATERIALS OTHERS';
        $utilityTitle[13] = 'SUBCONTRACTOR OTHERS';
        $rows[] = $utilityTitle;
        $this->utilityTitleRow = count($rows);

        $utilityHeader = array_fill(0, 19, '');
        $utilityHeader[6] = 'Date';
        $utilityHeader[7] = 'Remarks';
        $utilityHeader[11] = 'Amount';
        $utilityHeader[13] = 'Date';
        $utilityHeader[14] = 'Remarks';
        $utilityHeader[18] = 'Amount';
        $rows[] = $utilityHeader;
        $this->utilityHeaderRow = count($rows);

        $materialUtilityTotal = (float) $materialUtilities->sum('amount');
        $subcontractorUtilityTotal = (float) $subcontractorUtilities->sum('amount');
        $utilityCount = max($materialUtilities->count(), $subcontractorUtilities->count(), 1);
        for ($index = 0; $index < $utilityCount; $index++) {
            $row = array_fill(0, 19, '');

            if ($materialUtility = $materialUtilities->get($index)) {
                $row[6] = Carbon::parse($materialUtility->created_at)->format('d-m-Y');
                $row[7] = $materialUtility->remarks ?: '-';
                $row[11] = (float) $materialUtility->amount;
            } elseif ($index === 0) {
                $row[6] = 'No records';
            }

            if ($subcontractorUtility = $subcontractorUtilities->get($index)) {
                $row[13] = Carbon::parse($subcontractorUtility->created_at)->format('d-m-Y');
                $row[14] = $subcontractorUtility->remarks ?: '-';
                $row[18] = (float) $subcontractorUtility->amount;
            } elseif ($index === 0) {
                $row[13] = 'No records';
            }

            $rows[] = $row;
        }

        $utilityTotal = array_fill(0, 19, '');
        $utilityTotal[6] = 'MATERIALS OTHERS SUBTOTAL';
        $utilityTotal[11] = $materialUtilityTotal;
        $utilityTotal[13] = 'SUBCONTRACTOR OTHERS SUBTOTAL';
        $utilityTotal[18] = $subcontractorUtilityTotal;
        $rows[] = $utilityTotal;
        $this->utilityTotalRow = count($rows);

        $rows[] = array_fill(0, 19, '');

        $sectionTotal = array_fill(0, 19, '');
        $sectionTotal[6] = 'MATERIALS TOTAL';
        $sectionTotal[11] = $materialTotal + $materialUtilityTotal;
        $sectionTotal[13] = 'SUBCONTRACTOR TOTAL';
        $sectionTotal[18] = $subcontractorTotal + $subcontractorUtilityTotal;
        $rows[] = $sectionTotal;
        $this->sectionTotalRow = count($rows);

        $grandTotal = $attendanceTotal + $materialTotal + $materialUtilityTotal
            + $subcontractorTotal + $subcontractorUtilityTotal;
        $rows[] = array_fill(0, 19, '');
        $rows[] = ['EXPENSES TOTAL: Rs. ' . number_format($grandTotal, 2)];
        $this->grandTotalRow = count($rows);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:S1');
                $sheet->getStyle('A1:S1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1:S1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle('A2:B2')->getFont()->setBold(true);

                foreach (['A3:E3', 'G3:L3', 'N3:S3'] as $range) {
                    $sheet->mergeCells($range);
                    $sheet->getStyle($range)->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach (['A4:E4', 'G4:L4', 'N4:S4'] as $range) {
                    $sheet->getStyle($range)->getFont()->setBold(true);
                    $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach (["A{$this->totalRow}:E{$this->totalRow}", "G{$this->totalRow}:L{$this->totalRow}", "N{$this->totalRow}:S{$this->totalRow}"] as $range) {
                    $sheet->getStyle($range)->getFont()->setBold(true);
                    $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                }

                foreach (["G{$this->utilityTitleRow}:L{$this->utilityTitleRow}", "N{$this->utilityTitleRow}:S{$this->utilityTitleRow}"] as $range) {
                    $sheet->mergeCells($range);
                    $sheet->getStyle($range)->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach (["G{$this->utilityHeaderRow}:L{$this->utilityHeaderRow}", "N{$this->utilityHeaderRow}:S{$this->utilityHeaderRow}"] as $range) {
                    $sheet->getStyle($range)->getFont()->setBold(true);
                    $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach (["G{$this->utilityTotalRow}:L{$this->utilityTotalRow}", "N{$this->utilityTotalRow}:S{$this->utilityTotalRow}"] as $range) {
                    $sheet->getStyle($range)->getFont()->setBold(true);
                    $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                }

                foreach (["G{$this->sectionTotalRow}:L{$this->sectionTotalRow}", "N{$this->sectionTotalRow}:S{$this->sectionTotalRow}"] as $range) {
                    $sheet->getStyle($range)->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);
                }

                $sheet->mergeCells("A{$this->grandTotalRow}:S{$this->grandTotalRow}");
                $sheet->getStyle("A{$this->grandTotalRow}:S{$this->grandTotalRow}")->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle("A{$this->grandTotalRow}:S{$this->grandTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$this->grandTotalRow}:S{$this->grandTotalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);

                foreach (['D5:E' . $this->totalRow, 'L5:L' . $this->totalRow, 'S5:S' . $this->totalRow] as $range) {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                foreach (["L{$this->utilityHeaderRow}:L{$this->sectionTotalRow}", "S{$this->utilityHeaderRow}:S{$this->sectionTotalRow}"] as $range) {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                $sheet->getStyle("A1:S{$this->grandTotalRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                foreach (range('A', 'S') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('F')->setWidth(3);
                $sheet->getColumnDimension('M')->setWidth(3);
                $sheet->freezePane('A5');
            },
        ];
    }
}
