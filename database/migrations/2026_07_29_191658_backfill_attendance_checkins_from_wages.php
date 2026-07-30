<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $wages = DB::table('wages')
            ->whereNotNull('check_in_time')
            ->orWhereNotNull('check_in_photo')
            ->orWhereNotNull('check_out_time')
            ->orWhereNotNull('check_out_photo')
            ->orderBy('date')
            ->get(['site_id', 'date', 'check_in_time', 'check_in_photo', 'check_out_time', 'check_out_photo', 'created_by']);

        $grouped = [];
        foreach ($wages as $wage) {
            $key = $wage->site_id . '|' . $wage->date;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'site_id'         => $wage->site_id,
                    'date'            => $wage->date,
                    'check_in_time'   => null,
                    'check_in_photo'  => null,
                    'check_out_time'  => null,
                    'check_out_photo' => null,
                    'created_by'      => $wage->created_by,
                ];
            }

            $grouped[$key]['check_in_time']   = $grouped[$key]['check_in_time']   ?? $wage->check_in_time;
            $grouped[$key]['check_in_photo']  = $grouped[$key]['check_in_photo']  ?? $wage->check_in_photo;
            $grouped[$key]['check_out_time']  = $grouped[$key]['check_out_time']  ?? $wage->check_out_time;
            $grouped[$key]['check_out_photo'] = $grouped[$key]['check_out_photo'] ?? $wage->check_out_photo;
        }

        foreach ($grouped as $row) {
            $existing = DB::table('attendance_checkins')
                ->where('site_id', $row['site_id'])
                ->where('date', $row['date'])
                ->first();

            if ($existing) {
                continue;
            }

            DB::table('attendance_checkins')->insert([
                'site_id'         => $row['site_id'],
                'date'            => $row['date'],
                'check_in_time'   => $row['check_in_time'],
                'check_in_photo'  => $row['check_in_photo'],
                'check_out_time'  => $row['check_out_time'],
                'check_out_photo' => $row['check_out_photo'],
                'created_by'      => $row['created_by'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data backfill only — no schema change to reverse.
    }
};
