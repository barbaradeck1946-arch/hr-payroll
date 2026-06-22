<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollItem;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('hr.reports.index');
    }

    public function employees(Request $request): View
    {
        $filters = $this->employeeFilters($request);

        return view('hr.reports.employees', [
            'employees' => $this->employeeQuery($filters)->paginate($filters['per_page'])->withQueryString(),
            'departments' => $this->departmentsForSelect(),
            'filters' => $filters,
            'summary' => [
                'total' => Employee::query()->count(),
                'active' => Employee::query()->where('employment_status', 'active')->count(),
                'inactive' => Employee::query()->where('employment_status', 'inactive')->count(),
                'terminated' => Employee::query()->whereIn('employment_status', ['resigned', 'terminated'])->count(),
            ],
        ]);
    }

    public function exportEmployees(Request $request): StreamedResponse
    {
        $filters = $this->employeeFilters($request, false);
        $rows = $this->employeeQuery($filters)->get();

        return $this->csv('employee_report.csv', [
            'Employee Code',
            'Name',
            'Department',
            'Designation',
            'Salary Grade',
            'Work Email',
            'Phone',
            'Joining Date',
            'Status',
        ], $rows->map(fn (Employee $employee): array => [
            $employee->employee_code,
            trim($employee->first_name . ' ' . $employee->last_name),
            $employee->department?->name ?? '',
            $employee->designation?->name ?? '',
            $employee->salaryGrade?->grade_name ?? '',
            $employee->work_email,
            $employee->phone,
            $employee->date_of_joining,
            $employee->employment_status,
        ])->all());
    }

    public function attendance(Request $request): View
    {
        $filters = $this->attendanceFilters($request);
        $query = $this->attendanceQuery($filters);

        return view('hr.reports.attendance', [
            'logs' => (clone $query)->paginate($filters['per_page'])->withQueryString(),
            'employees' => $this->employeesForSelect(),
            'filters' => $filters,
            'summary' => [
                'present' => (clone $query)->where('status', 'present')->count(),
                'late' => (clone $query)->where('status', 'late')->count(),
                'absent' => (clone $query)->where('status', 'absent')->count(),
                'leave' => (clone $query)->where('status', 'leave')->count(),
                'worked_minutes' => (clone $query)->sum('worked_minutes'),
            ],
        ]);
    }

    public function exportAttendance(Request $request): StreamedResponse
    {
        $filters = $this->attendanceFilters($request, false);
        $rows = $this->attendanceQuery($filters)->get();

        return $this->csv('attendance_report_' . $filters['from_date'] . '_to_' . $filters['to_date'] . '.csv', [
            'Date',
            'Employee Code',
            'Employee Name',
            'Department',
            'Status',
            'Check In',
            'Check Out',
            'Worked Minutes',
            'Source',
            'Remarks',
        ], $rows->map(fn (AttendanceLog $log): array => [
            $log->attendance_date?->format('Y-m-d') ?? $log->attendance_date,
            $log->employee?->employee_code ?? '',
            trim(($log->employee?->first_name ?? '') . ' ' . ($log->employee?->last_name ?? '')),
            $log->employee?->department?->name ?? '',
            $log->status,
            $log->check_in_at?->format('Y-m-d H:i:s') ?? '',
            $log->check_out_at?->format('Y-m-d H:i:s') ?? '',
            $log->worked_minutes,
            $log->source,
            $log->remarks,
        ])->all());
    }

    public function payroll(Request $request): View
    {
        $filters = $this->payrollFilters($request);
        $query = $this->payrollQuery($filters);

        return view('hr.reports.payroll', [
            'items' => (clone $query)->paginate($filters['per_page'])->withQueryString(),
            'employees' => $this->employeesForSelect(),
            'filters' => $filters,
            'summary' => [
                'items' => (clone $query)->count(),
                'gross' => (clone $query)->reorder()->selectRaw('COALESCE(SUM(basic_salary + allowance_total + bonus_total), 0) as total')->value('total'),
                'deductions' => (clone $query)->sum('total_deduction'),
                'net' => (clone $query)->sum('net_payable'),
            ],
        ]);
    }

    public function exportPayroll(Request $request): StreamedResponse
    {
        $filters = $this->payrollFilters($request, false);
        $rows = $this->payrollQuery($filters)->get();

        return $this->csv('payroll_report_' . $filters['from_date'] . '_to_' . $filters['to_date'] . '.csv', [
            'Period',
            'Employee Code',
            'Employee',
            'Basic',
            'Allowances',
            'Bonus',
            'Loan Deduction',
            'Other Deduction',
            'PF',
            'Tax',
            'Total Deduction',
            'Net Payable',
            'Run Status',
            'Payment Status',
        ], $rows->map(fn (PayrollItem $item): array => [
            $item->payrollRun?->period_label ?: (($item->payrollRun?->period_start ?? '') . ' to ' . ($item->payrollRun?->period_end ?? '')),
            $item->employee?->employee_code ?? '',
            trim(($item->employee?->first_name ?? '') . ' ' . ($item->employee?->last_name ?? '')),
            $item->basic_salary,
            $item->allowance_total,
            $item->bonus_total,
            $item->loan_deduction,
            $item->other_deduction,
            $item->provident_fund_deduction,
            $item->tax_deduction,
            $item->total_deduction,
            $item->net_payable,
            $item->payrollRun?->status ?? '',
            $item->payment_status,
        ])->all());
    }

    /**
     * @return array<string, mixed>
     */
    private function employeeFilters(Request $request, bool $paginate = true): array
    {
        return [
            'q' => trim((string) $request->input('q')),
            'department_id' => (int) $request->input('department_id', 0),
            'status' => (string) $request->input('status', ''),
            'per_page' => $paginate ? max(10, min(100, (int) $request->input('per_page', 20))) : 1000,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder<Employee>
     */
    private function employeeQuery(array $filters): Builder
    {
        $q = (string) $filters['q'];

        return Employee::query()
            ->with(['department:id,name', 'designation:id,name', 'salaryGrade:id,grade_name'])
            ->when($q !== '', fn (Builder $query) => $query->where(function ($inner) use ($q): void {
                $inner->where('employee_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('work_email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            }))
            ->when((int) $filters['department_id'] > 0, fn (Builder $query) => $query->where('department_id', (int) $filters['department_id']))
            ->when((string) $filters['status'] !== '', fn (Builder $query) => $query->where('employment_status', $filters['status']))
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    /**
     * @return array<string, mixed>
     */
    private function attendanceFilters(Request $request, bool $paginate = true): array
    {
        $today = CarbonImmutable::now();

        return [
            'employee_id' => (int) $request->input('employee_id', 0),
            'status' => (string) $request->input('status', ''),
            'from_date' => (string) $request->input('from_date', $today->startOfMonth()->toDateString()),
            'to_date' => (string) $request->input('to_date', $today->toDateString()),
            'per_page' => $paginate ? max(10, min(100, (int) $request->input('per_page', 20))) : 1000,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder<AttendanceLog>
     */
    private function attendanceQuery(array $filters): Builder
    {
        return AttendanceLog::query()
            ->with(['employee:id,employee_code,first_name,last_name,department_id', 'employee.department:id,name'])
            ->whereBetween('attendance_date', [$filters['from_date'], $filters['to_date']])
            ->when((int) $filters['employee_id'] > 0, fn (Builder $query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->when((string) $filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->orderByDesc('attendance_date')
            ->orderByDesc('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function payrollFilters(Request $request, bool $paginate = true): array
    {
        $today = CarbonImmutable::now();

        return [
            'employee_id' => (int) $request->input('employee_id', 0),
            'status' => (string) $request->input('status', ''),
            'from_date' => (string) $request->input('from_date', $today->startOfMonth()->toDateString()),
            'to_date' => (string) $request->input('to_date', $today->endOfMonth()->toDateString()),
            'per_page' => $paginate ? max(10, min(100, (int) $request->input('per_page', 20))) : 1000,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder<PayrollItem>
     */
    private function payrollQuery(array $filters): Builder
    {
        return PayrollItem::query()
            ->with(['payrollRun', 'employee:id,employee_code,first_name,last_name'])
            ->whereHas('payrollRun', fn (Builder $query) => $query->whereBetween('period_start', [$filters['from_date'], $filters['to_date']]))
            ->when((int) $filters['employee_id'] > 0, fn (Builder $query) => $query->where('employee_id', (int) $filters['employee_id']))
            ->when((string) $filters['status'] !== '', fn (Builder $query) => $query->whereHas('payrollRun', fn (Builder $runQuery) => $runQuery->where('status', $filters['status'])))
            ->orderByDesc('id');
    }

    private function employeesForSelect(): \Illuminate\Support\Collection
    {
        return Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->whereNotIn('employment_status', ['resigned', 'terminated'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function departmentsForSelect(): \Illuminate\Support\Collection
    {
        return Department::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, mixed>> $rows
     */
    private function csv(string $fileName, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
