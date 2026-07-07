<?php

namespace App\Exports;

use App\Models\OtherUtilities;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OtherUtilitiesExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $siteId;
    protected $fromDate;
    protected $toDate;
    protected $siteName;
    protected $rows = [];

    public function __construct($siteId, $fromDate = null, $toDate = null)
    {
        $this->siteId   = $siteId;
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        $this->siteName = Site::find($siteId)->site_name ?? 'Unknown Site';
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function collection()
    {
        $query = OtherUtilities::where('site_id', $this->siteId);

        if ($this->fromDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->fromDate)->toDateString());
        }

        if ($this->toDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->toDate)->toDateString());
        }

        $records = $query->orderBy('id', 'desc')->get();

        foreach ($records as $utility) {
            $this->rows[] = [
                $utility->created_at ? Carbon::parse($utility->created_at)->format('d-m-Y') : '',
                (float) $utility->amount,
                $utility->remarks ?? '-',
            ];
        }

        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return ['Date', 'Amount (₹)', 'Remarks'];
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

                $sheet->mergeCells('A1:C1');
                $sheet->setCellValue('A1', 'Other Utilities Report - ' . $this->siteName);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(1)->setRowHeight(25);

                $sheet->getStyle('A3:C3')->getFont()->setBold(true);
                $sheet->getStyle('A3:C3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:C3')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE5E5E5');

                foreach (range('A', 'C') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
