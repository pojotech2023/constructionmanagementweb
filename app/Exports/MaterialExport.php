<?php

namespace App\Exports;

use App\Models\MaterialOrder;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MaterialExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $siteId;
    protected $materialType;
    protected $month;
    protected $siteName;
    protected $rows = [];
    protected $grandTotal = 0;

    public function __construct($siteId, $materialType = null, $month = null)
    {
        $this->siteId       = $siteId;
        $this->materialType = $materialType;
        $this->month        = $month;
        $this->siteName     = Site::find($siteId)->site_name ?? 'Unknown Site';
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function collection()
    {
        $query = MaterialOrder::with('vendor')->where('site_id', $this->siteId);

        if ($this->materialType) {
            $query->whereRaw('LOWER(TRIM(material_type)) = ?', [strtolower(trim($this->materialType))]);
        }

        if ($this->month) {
            $start = Carbon::parse($this->month . '-01');
            $query->whereBetween('date', [$start, $start->copy()->endOfMonth()]);
        }

        $records = $query->orderBy('date')->get();

        foreach ($records as $order) {
            $this->grandTotal += $order->price;
            $this->rows[] = [
                Carbon::parse($order->date)->format('d-m-Y'),
                $order->material_type,
                $order->quantity . ($order->unit ? ' ' . $order->unit : ''),
                $order->price,
                $order->vendor->name ?? '-',
                $order->remarks ?? '-',
                $order->image_url ?? '-',
            ];
        }

        $this->rows[] = ['', '', '', 'Grand Total (₹)', $this->grandTotal, '', ''];

        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return ['Date', 'Material Type', 'Quantity', 'Price (₹)', 'Vendor', 'Remarks', 'Image Link'];
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

                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Material Report - ' . $this->siteName);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(1)->setRowHeight(25);

                $sheet->getStyle('A3:G3')->getFont()->setBold(true);
                $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:G3')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE5E5E5');

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("D{$highestRow}:E{$highestRow}")->getFont()->setBold(true);

                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
