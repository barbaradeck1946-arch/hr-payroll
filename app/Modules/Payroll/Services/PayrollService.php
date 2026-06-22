<?php

namespace App\Modules\Payroll\Services;

use App\Models\Bonus;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Models\EmployeeLoan;
use App\Models\EmployeeProvidentFund;
use App\Models\LoanInstallment;
use App\Models\PayrollItem;
use App\Models\PayrollItemDeduction;
use App\Models\PayrollRun;
use App\Models\ProvidentFundTransaction;
use App\Models\SalaryTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function saveSalaryTemplate(array $payload, ?SalaryTemplate $template = null): SalaryTemplate
    {
        $payload = array_merge([
            'house_rent' => 0,
            'medical_allowance' => 0,
            'conveyance_allowance' => 0,
            'other_allowance' => 0,
            'provident_fund_percent' => 0,
            'tax_percent' => 0,
        ], array_filter($payload, fn ($value) => $value !== null && $value !== ''));

        $template ??= new SalaryTemplate();
        $template->fill($payload);
        $template->save();

        return $template;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function assignSalaryTemplate(array $payload): void
    {
        $employee = Employee::query()
            ->find((int) $payload['employee_id']);

        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }

        $payload = array_merge([
            'pay_frequency' => null,
            'house_rent' => 0,
            'medical_allowance' => 0,
            'conveyance_allowance' => 0,
            'other_allowance' => 0,
            'provident_fund_percent' => 0,
            'tax_percent' => 0,
            'ctc_amount' => null,
            'notes' => null,
            'effective_to' => null,
        ], array_filter($payload, fn ($value) => $value !== null && $value !== ''));

        $grossSalary = (float) $payload['basic_salary']
            + (float) $payload['house_rent']
            + (float) $payload['medical_allowance']
            + (float) $payload['conveyance_allowance']
            + (float) $payload['other_allowance'];

        DB::table('employee_salary_templates')->updateOrInsert(
            [
                'employee_id' => (int) $payload['employee_id'],
                'effective_from' => $payload['effective_from'],
            ],
            [
                'salary_template_id' => (int) $payload['salary_template_id'],
                'pay_frequency' => $payload['pay_frequency'] ?: null,
                'basic_salary' => $payload['basic_salary'],
                'house_rent' => $payload['house_rent'],
                'medical_allowance' => $payload['medical_allowance'],
                'conveyance_allowance' => $payload['conveyance_allowance'],
                'other_allowance' => $payload['other_allowance'],
                'gross_salary' => $grossSalary,
                'provident_fund_percent' => $payload['provident_fund_percent'],
                'tax_percent' => $payload['tax_percent'],
                'ctc_amount' => $payload['ctc_amount'],
                'notes' => $payload['notes'],
                'effective_to' => $payload['effective_to'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function saveBonus(array $payload, int $userId, ?Bonus $bonus = null): Bonus
    {
        $bonus ??= new Bonus(['created_by' => $userId]);
        $bonus->fill($payload);
        $bonus->created_by ??= $userId;
        $bonus->save();

        return $bonus;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function generateBonusBatch(array $payload, int $userId): int
    {
        $employees = Employee::query()
            ->with('salaryGrade:id,min_salary')
            ->where('employment_status', 'active')
            ->when((int) ($payload['employee_id'] ?? 0) > 0, fn ($query) => $query->where('id', (int) $payload['employee_id']))
            ->get();

        if ($employees->isEmpty()) {
            throw new RuntimeException('No active employees found for bonus generation.');
        }

        $created = 0;
        DB::transaction(function () use ($employees, $payload, $userId, &$created): void {
            foreach ($employees as $employee) {
                $amount = $this->calculateBonusAmount($employee, $payload);
                if ($amount <= 0) {
                    continue;
                }

                Bonus::query()->create([
                    'employee_id' => $employee->id,
                    'title' => $payload['title'],
                    'amount' => $amount,
                    'bonus_date' => $payload['bonus_date'],
                    'bonus_type' => $payload['bonus_type'],
                    'remarks' => $payload['remarks'] ?? null,
                    'created_by' => $userId,
                ]);

                $created++;
            }
        });

        if ($created === 0) {
            throw new RuntimeException('Bonus rule did not produce any payable bonus amount.');
        }

        return $created;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function saveDeduction(array $payload, ?EmployeeDeduction $deduction = null): EmployeeDeduction
    {
        $deduction ??= new EmployeeDeduction();
        $deduction->fill($payload);
        $deduction->save();

        return $deduction;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function saveProvidentFund(array $payload): EmployeeProvidentFund
    {
        $payload['opening_balance'] = $payload['opening_balance'] ?? 0;

        return EmployeeProvidentFund::query()->updateOrCreate(
            ['employee_id' => (int) $payload['employee_id']],
            $payload
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function saveLoan(array $payload, ?EmployeeLoan $loan = null): EmployeeLoan
    {
        return DB::transaction(function () use ($payload, $loan): EmployeeLoan {
            $payload['interest_rate_percent'] = $payload['interest_rate_percent'] ?? 0;
            $payload['first_installment_date'] = $payload['first_installment_date'] ?? null;

            $loan ??= new EmployeeLoan();
            $isNewLoan = ! $loan->exists;
            $loan->fill($payload);
            $loan->save();

            if ($loan->status === 'active' && ($isNewLoan || $loan->installments()->count() === 0)) {
                $this->createLoanInstallments($loan, $payload);
            }

            return $loan;
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function applyLoan(array $payload, int $userId): EmployeeLoan
    {
        $payload['status'] = 'pending_supervisor';
        $payload['applied_by'] = $userId;

        return $this->saveLoan($payload);
    }

    public function approveLoan(EmployeeLoan $loan, string $step, int $userId, ?string $remarks = null): EmployeeLoan
    {
        return DB::transaction(function () use ($loan, $step, $userId, $remarks): EmployeeLoan {
            $loan = EmployeeLoan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($step === 'supervisor') {
                if ($loan->status !== 'pending_supervisor') {
                    throw new RuntimeException('Only supervisor-pending loans can be supervisor approved.');
                }

                $loan->update([
                    'status' => 'pending_final',
                    'supervisor_approved_by' => $userId,
                    'supervisor_approved_at' => now(),
                    'remarks' => $remarks ?: $loan->remarks,
                ]);

                return $loan->refresh();
            }

            if ($step !== 'final') {
                throw new RuntimeException('Invalid loan approval step.');
            }

            if (! in_array($loan->status, ['pending_supervisor', 'pending_final'], true)) {
                throw new RuntimeException('Only pending loans can be final approved.');
            }

            $loan->update([
                'status' => 'active',
                'final_approved_by' => $userId,
                'final_approved_at' => now(),
                'remarks' => $remarks ?: $loan->remarks,
            ]);

            if ($loan->installments()->count() === 0) {
                $this->createLoanInstallments($loan, $loan->toArray());
            }

            return $loan->refresh();
        });
    }

    public function rejectLoan(EmployeeLoan $loan, int $userId, ?string $remarks = null): EmployeeLoan
    {
        if (! in_array($loan->status, ['pending_supervisor', 'pending_final'], true)) {
            throw new RuntimeException('Only pending loans can be rejected.');
        }

        $loan->update([
            'status' => 'rejected',
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'remarks' => $remarks ?: $loan->remarks,
        ]);

        return $loan->refresh();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function rescheduleLoan(EmployeeLoan $loan, array $payload): EmployeeLoan
    {
        return DB::transaction(function () use ($loan, $payload): EmployeeLoan {
            if ($loan->installments()->where('status', 'paid')->exists()) {
                throw new RuntimeException('Paid loan installments exist. This loan cannot be rescheduled.');
            }

            $payload['interest_rate_percent'] = $payload['interest_rate_percent'] ?? 0;
            $payload['first_installment_date'] = $payload['first_installment_date'] ?? null;

            $loan->fill($payload);
            $loan->save();
            $loan->installments()->delete();
            $this->createLoanInstallments($loan, $payload);

            return $loan->refresh();
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateLoanStatus(EmployeeLoan $loan, array $payload): EmployeeLoan
    {
        $loan->update([
            'status' => $payload['status'],
            'remarks' => $payload['remarks'] ?? $loan->remarks,
        ]);

        return $loan->refresh();
    }

    public function markLoanInstallmentPaid(LoanInstallment $installment, ?string $paidDate = null): LoanInstallment
    {
        return DB::transaction(function () use ($installment, $paidDate): LoanInstallment {
            $installment = LoanInstallment::query()->lockForUpdate()->findOrFail($installment->id);

            if ($installment->status === 'paid') {
                return $installment;
            }

            $installment->update([
                'paid_amount' => $installment->amount,
                'paid_date' => $paidDate ?: now()->toDateString(),
                'status' => 'paid',
            ]);

            $this->refreshLoanStatus($installment->loan()->first());

            return $installment->refresh();
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function generatePayrollRun(array $payload, int $processedBy): PayrollRun
    {
        return DB::transaction(function () use ($payload, $processedBy): PayrollRun {
            $periodStart = CarbonImmutable::parse($payload['period_start']);
            $periodEnd = CarbonImmutable::parse($payload['period_end']);

            $run = PayrollRun::query()
                ->where('pay_frequency', $payload['pay_frequency'])
                ->where('period_start', $periodStart->toDateString())
                ->where('period_end', $periodEnd->toDateString())
                ->first();

            if ($run && $run->status !== 'draft') {
                if ((int) ($payload['employee_id'] ?? 0) > 0) {
                    return $this->addEmployeeToProcessedRun($run, (int) $payload['employee_id'], $processedBy);
                }

                throw new RuntimeException('This payroll period is already finalized. Create a new period or review the existing run.');
            }

            if (! $run) {
                $run = PayrollRun::query()->create([
                    'pay_frequency' => $payload['pay_frequency'],
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ]);
            }

            ProvidentFundTransaction::query()->where('payroll_run_id', $run->id)->delete();
            $run->items()->delete();

            $run->update([
                'payroll_year' => $periodStart->year,
                'payroll_month' => $periodStart->month,
                'payroll_week' => $payload['payroll_week'] ?? null,
                'period_label' => $payload['period_label'] ?: $periodStart->format('M Y'),
                'pay_date' => $payload['pay_date'] ?: null,
                'status' => 'draft',
                'gross_total' => 0,
                'deduction_total' => 0,
                'net_total' => 0,
                'processed_by' => null,
                'processed_at' => null,
            ]);

            $employees = Employee::query()
                ->with('salaryGrade:id,min_salary')
                ->where('employment_status', 'active')
                ->when((int) ($payload['employee_id'] ?? 0) > 0, fn ($query) => $query->where('id', (int) $payload['employee_id']))
                ->get();

            if ($employees->isEmpty()) {
                throw new RuntimeException('No active employees found for payroll generation.');
            }

            foreach ($employees as $employee) {
                $this->createPayrollItem($run, $employee, $periodStart, $periodEnd);
            }

            $this->refreshRunTotals($run);

            return $run->refresh();
        });
    }

    public function finalizePayrollRun(PayrollRun $run, int $processedBy): PayrollRun
    {
        return DB::transaction(function () use ($run, $processedBy): PayrollRun {
            $run = PayrollRun::query()->lockForUpdate()->with('items')->findOrFail($run->id);

            if ($run->status !== 'draft') {
                throw new RuntimeException('Only draft payroll runs can be finalized.');
            }

            if ($run->items->isEmpty()) {
                throw new RuntimeException('Payroll run has no payslips to finalize.');
            }

            ProvidentFundTransaction::query()->where('payroll_run_id', $run->id)->delete();

            foreach ($run->items as $item) {
                $this->payDueLoanInstallmentsForPayrollItem($run, $item);
                $this->recordProvidentFundContribution($run, $item, $processedBy);
            }

            $run->update([
                'status' => 'processed',
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]);

            return $run->refresh();
        });
    }

    private function addEmployeeToProcessedRun(PayrollRun $run, int $employeeId, int $processedBy): PayrollRun
    {
        $run = PayrollRun::query()->lockForUpdate()->findOrFail($run->id);

        if ($run->status !== 'processed') {
            throw new RuntimeException('Only processed payroll runs can receive a missing employee payslip. Create a new payroll period for approved or paid runs.');
        }

        if ($run->items()->where('employee_id', $employeeId)->exists()) {
            throw new RuntimeException('This employee already exists in the selected payroll run.');
        }

        $employee = Employee::query()
            ->with('salaryGrade:id,min_salary')
            ->where('employment_status', 'active')
            ->find($employeeId);

        if (! $employee) {
            throw new RuntimeException('Selected employee is not active or does not exist.');
        }

        $item = $this->createPayrollItem(
            $run,
            $employee,
            CarbonImmutable::parse($run->period_start),
            CarbonImmutable::parse($run->period_end)
        );

        $this->payDueLoanInstallmentsForPayrollItem($run, $item);
        $this->recordProvidentFundContribution($run, $item, $processedBy);
        $this->refreshRunTotals($run);

        return $run->refresh();
    }

    private function createPayrollItem(PayrollRun $run, Employee $employee, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): PayrollItem
    {
        $assignment = $this->activeSalaryAssignment($employee->id, $periodEnd);
        $basicSalary = (float) ($assignment?->basic_salary ?? $employee->salaryGrade?->min_salary ?? 0);
        $allowanceTotal = (float) (($assignment?->house_rent ?? 0) + ($assignment?->medical_allowance ?? 0) + ($assignment?->conveyance_allowance ?? 0) + ($assignment?->other_allowance ?? 0));
        $bonusTotal = (float) Bonus::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('bonus_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('amount');
        $loanDeduction = (float) LoanInstallment::query()
            ->whereHas('loan', fn ($query) => $query->where('employee_id', $employee->id)->where('status', 'active'))
            ->whereBetween('due_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('status', 'pending')
            ->sum('amount');
        $providentFundDeduction = $this->providentFundDeduction($employee->id, $basicSalary, $assignment);
        $taxDeduction = round(($basicSalary * (float) ($assignment?->tax_percent ?? 0)) / 100, 2);

        $item = PayrollItem::query()->create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'pay_frequency' => $run->pay_frequency,
            'basic_salary' => $basicSalary,
            'allowance_total' => $allowanceTotal,
            'bonus_total' => $bonusTotal,
            'loan_deduction' => $loanDeduction,
            'provident_fund_deduction' => $providentFundDeduction,
            'tax_deduction' => $taxDeduction,
            'payment_status' => 'pending',
        ]);

        $otherDeduction = $this->attachActiveDeductions($item, $employee->id, $periodStart, $periodEnd);
        $totalDeduction = $loanDeduction + $otherDeduction + $providentFundDeduction + $taxDeduction;
        $gross = $basicSalary + $allowanceTotal + $bonusTotal;

        $item->update([
            'other_deduction' => $otherDeduction,
            'total_deduction' => $totalDeduction,
            'net_payable' => $gross - $totalDeduction,
        ]);

        return $item->refresh();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createLoanInstallments(EmployeeLoan $loan, array $payload): void
    {
        $firstDueDate = CarbonImmutable::parse($payload['first_installment_date'] ?: $payload['issued_date']);

        for ($i = 1; $i <= (int) $payload['installment_count']; $i++) {
            LoanInstallment::query()->create([
                'employee_loan_id' => $loan->id,
                'installment_no' => $i,
                'due_date' => $firstDueDate->addMonthsNoOverflow($i - 1)->toDateString(),
                'amount' => $payload['installment_amount'],
                'paid_amount' => 0,
                'status' => 'pending',
            ]);
        }
    }

    private function payDueLoanInstallmentsForPayrollItem(PayrollRun $run, PayrollItem $item): void
    {
        if ((float) $item->loan_deduction <= 0) {
            return;
        }

        $installments = LoanInstallment::query()
            ->whereHas('loan', fn ($query) => $query
                ->where('employee_id', $item->employee_id)
                ->where('status', 'active'))
            ->whereBetween('due_date', [$run->period_start, $run->period_end])
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->orderBy('installment_no')
            ->get();

        foreach ($installments as $installment) {
            $installment->update([
                'paid_amount' => $installment->amount,
                'paid_date' => $run->pay_date ?: $run->period_end,
                'status' => 'paid',
            ]);

            $this->refreshLoanStatus($installment->loan()->first());
        }
    }

    private function refreshRunTotals(PayrollRun $run): void
    {
        $run->update([
            'gross_total' => PayrollItem::query()
                ->where('payroll_run_id', $run->id)
                ->selectRaw('COALESCE(SUM(basic_salary + allowance_total + bonus_total), 0) as total')
                ->value('total'),
            'deduction_total' => $run->items()->sum('total_deduction'),
            'net_total' => $run->items()->sum('net_payable'),
        ]);
    }

    private function recordProvidentFundContribution(PayrollRun $run, PayrollItem $item, int $processedBy): void
    {
        if ((float) $item->provident_fund_deduction <= 0) {
            return;
        }

        ProvidentFundTransaction::query()->updateOrCreate(
            [
                'payroll_run_id' => $run->id,
                'employee_id' => $item->employee_id,
                'reference_no' => 'PF-' . $run->id . '-' . $item->employee_id,
            ],
            [
                'transaction_date' => $run->period_end,
                'transaction_type' => 'contribution',
                'employee_contribution' => $item->provident_fund_deduction,
                'employer_contribution' => $item->provident_fund_deduction,
                'reason' => 'Payroll provident fund contribution',
                'recorded_by' => $processedBy,
            ]
        );
    }

    private function refreshLoanStatus(?EmployeeLoan $loan): void
    {
        if (! $loan) {
            return;
        }

        if ($loan->installments()->where('status', '!=', 'paid')->doesntExist()) {
            $loan->update(['status' => 'closed']);
        }
    }

    private function activeSalaryAssignment(int $employeeId, CarbonImmutable $periodEnd): ?object
    {
        return DB::table('employee_salary_templates')
            ->where('employee_id', $employeeId)
            ->where('effective_from', '<=', $periodEnd->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>=', $periodEnd->toDateString()))
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function calculateBonusAmount(Employee $employee, array $payload): float
    {
        $calculationType = (string) $payload['calculation_type'];
        if ($calculationType === 'fixed') {
            return round((float) ($payload['amount'] ?? 0), 2);
        }

        $assignment = $this->activeSalaryAssignment($employee->id, CarbonImmutable::parse($payload['bonus_date']));
        $basicSalary = (float) ($assignment?->basic_salary ?? $employee->salaryGrade?->min_salary ?? 0);
        $allowanceTotal = (float) (($assignment?->house_rent ?? 0)
            + ($assignment?->medical_allowance ?? 0)
            + ($assignment?->conveyance_allowance ?? 0)
            + ($assignment?->other_allowance ?? 0));

        $baseAmount = $calculationType === 'gross_percent'
            ? $basicSalary + $allowanceTotal
            : $basicSalary;

        return round(($baseAmount * (float) ($payload['percentage'] ?? 0)) / 100, 2);
    }

    private function providentFundDeduction(int $employeeId, float $basicSalary, ?object $assignment): float
    {
        $fund = EmployeeProvidentFund::query()->where('employee_id', $employeeId)->first();
        $percent = (float) ($fund?->employee_contribution_percent ?? $assignment?->provident_fund_percent ?? 0);

        return round(($basicSalary * $percent) / 100, 2);
    }

    private function attachActiveDeductions(PayrollItem $item, int $employeeId, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): float
    {
        $deductions = EmployeeDeduction::query()
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->where('effective_from', '<=', $periodEnd->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>=', $periodStart->toDateString()))
            ->get();

        $total = 0.0;
        foreach ($deductions as $deduction) {
            $amount = (float) $deduction->amount;
            $total += $amount;
            PayrollItemDeduction::query()->create([
                'payroll_item_id' => $item->id,
                'employee_deduction_id' => $deduction->id,
                'deduction_type' => $deduction->deduction_type,
                'amount' => $amount,
                'reason' => $deduction->reason,
                'comments' => $deduction->comments,
            ]);
        }

        return $total;
    }
}
