<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\AttendanceCheckin;
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
    protected $fromDate;
    protected $toDate;
    protected $rows = [];
    protected $grandTotal = 0;
    protected $siteName;

    public function __construct($siteId, $month = null, $week = null, $fromDate = null, $toDate = null)
    {
        $this->siteId = $siteId;
        $this->month = $month;
        $this->week = $week;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

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

        if ($this->fromDate || $this->toDate) {
            if ($this->fromDate) {
                $query->whereDate('date', '>=', Carbon::parse($this->fromDate)->toDateString());
            }

            if ($this->toDate) {
                $query->whereDate('date', '<=', Carbon::parse($this->toDate)->toDateString());
            }
        } elseif ($this->month) {
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

            $dayCheckin = AttendanceCheckin::where('site_id', $attendance->site_id)
                ->where('date', $attendance->date)
                ->first();

            $checkInPhoto = $dayCheckin && $dayCheckin->check_in_photo ? asset('storage/' . $dayCheckin->check_in_photo) : '-';
            $checkOutPhoto = $dayCheckin && $dayCheckin->check_out_photo ? asset('storage/' . $dayCheckin->check_out_photo) : '-';

            $this->rows[] = [
                Carbon::parse($attendance->date)->format('d-m-Y'),
                ucfirst($attendance->category),
                $attendance->count,
                $amount,
                $total,
                $checkInPhoto,
                $checkOutPhoto,
            ];
        }

        // Add grand total row
        $this->rows[] = ['', '', '', 'Grand Total (₹)', $this->grandTotal, '', ''];

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
            'Check-in Photo Link',
            'Check-out Photo Link',
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
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'Attendance Details - ' . $this->siteName);

                // Apply styling for the title
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1')->getAlignment()->setVertical('center');

                // Add some spacing between title and table
                $sheet->getRowDimension(1)->setRowHeight(25);

                // 2️⃣ Style header row
                $sheet->getStyle('A3:G3')->getFont()->setBold(true);
                $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:G3')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE5E5E5'); // light gray

                // 3️⃣ Style total row
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("D{$highestRow}:E{$highestRow}")->getFont()->setBold(true);
                $sheet->getStyle("D{$highestRow}:E{$highestRow}")->getAlignment()->setHorizontal('right');

                // 4️⃣ Auto-size all columns
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
