<?php

namespace App\Modules\Attendance\Services;

use App\Models\AttendanceLog;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private readonly AttendanceRepository $attendanceRepository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function addManualLog(int $employeeId, array $payload, ?int $approvedBy = null): AttendanceLog
    {
        $attendanceDate = (string) $payload['attendance_date'];
        $entryType = (string) $payload['entry_type'];
        $entryTime = (string) $payload['entry_time'];
        $entryAt = $this->parseEntryDateTime($attendanceDate, $entryTime);
        $checkInAt = $entryType === 'checkin' ? $entryAt : null;
        $checkOutAt = $entryType === 'checkout' ? $entryAt : null;

        return DB::transaction(function () use ($employeeId, $attendanceDate, $checkInAt, $checkOutAt, $entryType, $payload, $approvedBy): AttendanceLog {
            return $this->attendanceRepository->create([
                'employee_id' => $employeeId,
                'attendance_date' => $attendanceDate,
                'check_in_at' => $checkInAt,
                'check_out_at' => $checkOutAt,
                'worked_minutes' => 0,
                'status' => 'present',
                'source' => 'manual-' . $entryType,
                'remarks' => $payload['remarks'] ?? null,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedBy ? now() : null,
            ]);
        });
    }
    // Parse the provided attendance date and entry time into a Carbon instance, trying multiple formats to accommodate different time input styles.
    private function parseEntryDateTime(string $attendanceDate, string $entryTime): Carbon
    {
        $value = trim($attendanceDate . ' ' . trim($entryTime));
        $formats = ['Y-m-d H:i', 'Y-m-d h:i A', 'Y-m-d h:i a'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
 
            }
        }

        return Carbon::parse($value);
    }
}
