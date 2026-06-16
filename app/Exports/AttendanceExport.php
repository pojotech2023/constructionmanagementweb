<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Wages;
use App\Models\Site;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $siteId;
    protected $month;
    protected $week;
    protected $rows = [];
    protected $grandTotal = 0;
    protected $siteName;

    public function __construct($siteId, $month = null, $week = null)
    {
        $this->siteId = $siteId;
        $this->month = $month;
        $this->week = $week;

        // Fetch site name
        $this->siteName = Site::find($siteId)->site_name ?? 'Unknown Site';
    }

    public function startCell(): string
    {
        // Start headings after the site name (title row)
        return 'A3';
    }

    public function collection()
    {
        $query = Attendance::where('site_id', $this->siteId);

        if ($this->month) {
            $startDate = Carbon::parse($this->month . '-01');
            $endDate = $startDate->copy()->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);

            if ($this->week) {
                $firstOfMonth = $startDate->copy()->startOfMonth();
                $calendarStart = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
                $weekStart = $calendarStart->copy()->addDays(($this->week - 1) * 7);
                $weekEnd = $weekStart->copy()->addDays(6);
                $query->whereBetween('date', [$weekStart, $weekEnd]);
            }
        }

        $records = $query->orderBy('date')->get();

        foreach ($records as $attendance) {
            $wage = Wages::where('site_id', $attendance->site_id)
                ->where('category', $attendance->category)
                ->where('date', '<=', $attendance->date)
                ->orderByDesc('date')
                ->first();

            $amount = $wage ? $wage->amount : 0;
            $total = $attendance->count * $amount;
            $this->grandTotal += $total;

            $this->rows[] = [
                Carbon::parse($attendance->date)->format('Y-m-d'),
                ucfirst($attendance->category),
                $attendance->count,
                $amount,
                $total,
            ];
        }

        // Add grand total row
        $this->rows[] = ['', '', '', 'Grand Total (₹)', $this->grandTotal];

        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Count',
            'Wage (₹)',
            'Total Amount (₹)',
        ];
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

                // 1️⃣ Merge and center the site name title in the first row
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'Attendance Details - ' . $this->siteName);

                // Apply styling for the title
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1')->getAlignment()->setVertical('center');

                // Add some spacing between title and table
                $sheet->getRowDimension(1)->setRowHeight(25);

                // 2️⃣ Style header row
                $sheet->getStyle('A3:E3')->getFont()->setBold(true);
                $sheet->getStyle('A3:E3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:E3')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE5E5E5'); // light gray

                // 3️⃣ Style total row
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("D{$highestRow}:E{$highestRow}")->getFont()->setBold(true);
                $sheet->getStyle("D{$highestRow}:E{$highestRow}")->getAlignment()->setHorizontal('right');

                // 4️⃣ Auto-size all columns
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
