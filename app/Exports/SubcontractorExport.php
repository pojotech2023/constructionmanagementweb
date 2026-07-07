<?php

namespace App\Exports;

use App\Models\Site;
use App\Models\SubcontractorService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SubcontractorExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $siteId;
    protected $month;
    protected $siteName;
    protected $rows = [];
    protected $grandTotal = 0;

    public function __construct($siteId, $month = null)
    {
        $this->siteId   = $siteId;
        $this->month    = $month;
        $this->siteName = Site::find($siteId)->site_name ?? 'Unknown Site';
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function collection()
    {
        $query = SubcontractorService::with('subcontractor')->where('site_id', $this->siteId);

        if ($this->month) {
            $start = Carbon::parse($this->month . '-01');
            $query->whereBetween('date', [$start, $start->copy()->endOfMonth()]);
        }

        $records = $query->orderBy('date')->get();

        foreach ($records as $service) {
            $this->grandTotal += $service->amount;
            $this->rows[] = [
                Carbon::parse($service->date)->format('d-m-Y'),
                $service->subcontractor->name ?? '-',
                $service->subcontractor_type,
                $service->no_counts ?? '-',
                $service->amount,
                $service->remarks ?? '-',
            ];
        }

        $this->rows[] = ['', '', '', '', 'Grand Total (₹)', $this->grandTotal];

        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return ['Date', 'Subcontractor', 'Type', 'Count', 'Amount (₹)', 'Remarks'];
    }

    public function map($row): array
    {
        return $row;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Subcontractor Report - ' . $this->siteName);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(1)->setRowHeight(25);

                $sheet->getStyle('A3:F3')->getFont()->setBold(true);
                $sheet->getStyle('A3:F3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:F3')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE5E5E5');

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("E{$highestRow}:F{$highestRow}")->getFont()->setBold(true);

                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
