<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Attendance\Http\Requests\StoreAttendanceRequest;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceRepository $attendanceRepository,
        private readonly AttendanceService $attendanceService
    ) {
    }
    /// Display a listing of attendance logs with filtering and pagination.

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $hasAllAccess = $this->hasAllAccess($user);
        $scopedEmployeeIds = $hasAllAccess ? null : $this->scopedEmployeeIds($user);

        $filters = [
            'from_date' => (string) $request->input('from_date', now()->startOfMonth()->format('Y-m-d')),
            'to_date' => (string) $request->input('to_date', now()->format('Y-m-d')),
            'employee_id' => (int) $request->input('employee_id', 0),
            'per_page' => max(10, min(100, (int) $request->input('per_page', 20))),
        ];

        if ($scopedEmployeeIds !== null && $filters['employee_id'] > 0 && ! in_array($filters['employee_id'], $scopedEmployeeIds, true)) {
            $filters['employee_id'] = 0;
        }

        return view('hr.attendance.index', [
            'attendanceRows' => $this->attendanceRepository->paginateSummary($filters, $scopedEmployeeIds),
            'employees' => $this->attendanceRepository->listEmployeesForScope($scopedEmployeeIds),
                'filters' => $filters,
                'canManageAttendance' => $user->hasPermission('attendance.manage'),
            'currentEmployeeId' => $user->employee?->id,
            'hasAllAccess' => $hasAllAccess,
        ]);
    }

    // Show the form for creating a new attendance log.
    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $user = $request->user();
        $hasAllAccess = $this->hasAllAccess($user);
        $validated = $request->validated();

        $employeeId = (int) ($validated['employee_id'] ?? 0);
        if (! $hasAllAccess) {
            $employeeId = (int) ($user->employee?->id ?? 0);
        }

        if ($employeeId <= 0) {
            return back()->withErrors(['employee_id' => 'No employee profile is linked to your account.'])->withInput();
        }

        $this->attendanceService->addManualLog($employeeId, $validated, $user->id);

        return redirect()->route('attendance.index')->with('success', 'Attendance log added successfully.');
    }

    // Export attendance logs as a CSV file based on filters and user access scope.
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $hasAllAccess = $this->hasAllAccess($user);
        $scopedEmployeeIds = $hasAllAccess ? null : $this->scopedEmployeeIds($user);

        $filters = [
            'from_date' => (string) $request->input('from_date', now()->startOfMonth()->format('Y-m-d')),
            'to_date' => (string) $request->input('to_date', now()->format('Y-m-d')),
            'employee_id' => (int) $request->input('employee_id', 0),
        ];

        if ($scopedEmployeeIds !== null && $filters['employee_id'] > 0 && ! in_array($filters['employee_id'], $scopedEmployeeIds, true)) {
            $filters['employee_id'] = 0;
        }

        $rows = $this->attendanceRepository->listRawLogsForExport($filters, $scopedEmployeeIds);
        $fileName = 'attendance_' . $filters['from_date'] . '_to_' . $filters['to_date'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = static function () use ($rows): void {

            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            $dailyBounds = [];
            foreach ($rows as $row) {
                $key = (string) $row->employee_id . '|' . (string) $row->attendance_date;
                if (! isset($dailyBounds[$key])) {
                    $dailyBounds[$key] = [
                        'first' => null,
                        'last' => null,
                    ];
                }

                if ($row->check_in_at) {
                    $checkIn = Carbon::parse((string) $row->check_in_at);
                        if ($dailyBounds[$key]['first'] === null || $checkIn->lt($dailyBounds[$key]['first'])) {
                        $dailyBounds[$key]['first'] = $checkIn;
                    }
                }

                if ($row->check_out_at) {
                    $checkOut = Carbon::parse((string) $row->check_out_at);
                    if ($dailyBounds[$key]['last'] === null || $checkOut->gt($dailyBounds[$key]['last'])) {
                        $dailyBounds[$key]['last'] = $checkOut;
                    }
                }
            }

              fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Log ID',
                'Date',
                'Employee',
                'Employee Code',
                'Check-in',
                'Check-out',
                'Duration',
                'Status',
                'Source',
                'Remarks',
                'Created At',
            ]);


            // Write each attendance log row to the CSV output, calculating duration based on check-in and check-out times.
            foreach ($rows as $row) {
                $checkIn = $row->check_in_at ? Carbon::parse((string) $row->check_in_at)->format('Y-m-d H:i:s') : '';
                $checkOut = $row->check_out_at ? Carbon::parse((string) $row->check_out_at)->format('Y-m-d H:i:s') : '';
                $employeeName = trim((string) $row->first_name . ' ' . (string) $row->last_name);
                $durationLabel = '';
                $key = (string) $row->employee_id . '|' . (string) $row->attendance_date;
                $first = $dailyBounds[$key]['first'] ?? null;
                $last = $dailyBounds[$key]['last'] ?? null;
                if ($first instanceof Carbon && $last instanceof Carbon) {
                    $minutes = max(0, $first->diffInMinutes($last, false));
                    $durationLabel = intdiv($minutes, 60) . 'h ' . ($minutes % 60) . 'm';
                }

                fputcsv($output, [
                    (string) $row->id,
                    (string) $row->attendance_date,
                    $employeeName,
                    (string) $row->employee_code,
                    $checkIn,
                    $checkOut,
                    $durationLabel,
                    (string) $row->status,
                    (string) $row->source,
                    (string) ($row->remarks ?? ''),
                    $row->created_at ? Carbon::parse((string) $row->created_at)->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($output);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * @return array<int, int>|null
     */
    private function scopedEmployeeIds(User $user): ?array
    {
        $employee = $user->employee;
        if (! $employee) {
            return [];
        }

        $ids = [$employee->id];
        $subordinateIds = $employee->subordinates()->pluck('id')->all();

        return array_values(array_unique(array_merge($ids, $subordinateIds)));
    }
    // Check if the user has any of the privileged roles and the necessary permissions to access attendance management features.
    private function hasAllAccess(User $user): bool
    {
        $hasPrivilegedRole = $user->roles()->whereIn('slug', ['super-admin', 'hr-manager', 'admin'])->exists();
        $hasPermission = $user->hasAnyPermission(['attendance.view', 'attendance.manage', 'attendance.report']);

        return $hasPrivilegedRole && $hasPermission;
    }
}
