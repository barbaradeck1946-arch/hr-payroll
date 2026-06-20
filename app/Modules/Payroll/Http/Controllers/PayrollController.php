<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\EmployeeDeduction;
use App\Models\EmployeeLoan;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\SalaryTemplate;
use App\Modules\Payroll\Repositories\PayrollRepository;
use App\Modules\Payroll\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollRepository $payrollRepository,
        private readonly PayrollService $payrollService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('hr.payroll.runs.index', [
            'runs' => $this->payrollRepository->runs($filters),
            'filters' => $filters,
            'employees' => $this->payrollRepository->employeesForSelect(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        if ((int) $request->input('employee_id', 0) === 0) {
            $request->merge(['employee_id' => null]);
        }

        $validated = $request->validate([
            'pay_frequency' => ['required', Rule::in(['weekly', 'monthly'])],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'period_label' => ['nullable', 'string', 'max:100'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'pay_date' => ['nullable', 'date'],
            'payroll_week' => ['nullable', 'integer', 'min:1', 'max:53'],
        ]);

        try {
            $run = $this->payrollService->generatePayrollRun($validated, (int) $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('payroll.runs.show', $run)->with('success', 'Payroll draft generated successfully. Review before final submission.');
    }

    public function showRun(PayrollRun $run): View
    {
        $run->load([
            'items.employee:id,employee_code,first_name,last_name,department_id,designation_id',
            'items.employee.department:id,name',
            'items.employee.designation:id,name',
            'processor:id,name',
        ]);

        return view('hr.payroll.runs.show', ['run' => $run]);
    }

    public function finalizeRun(Request $request, PayrollRun $run): RedirectResponse
    {
        try {
            $this->payrollService->finalizePayrollRun($run, (int) $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('payroll.runs.show', $run)->with('success', 'Payroll finalized successfully.');
    }

    public function showItem(PayrollItem $item): View
    {
        $item->load([
            'payrollRun',
            'employee:id,employee_code,first_name,last_name,department_id,designation_id',
            'employee.department:id,name',
            'employee.designation:id,name',
            'deductions',
        ]);

        return view('hr.payroll.runs.item', ['item' => $item]);
    }

    public function markItemPaid(Request $request, PayrollItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $item->loadMissing('payrollRun');
        if ($item->payrollRun?->status !== 'processed') {
            return back()->with('error', 'Only finalized payroll items can be marked as paid.');
        }

        $item->update([
            'payment_status' => 'paid',
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        return back()->with('success', 'Payment status updated.');
    }

    public function salaryTemplates(Request $request): View
    {
        $filters = $this->filters($request);

        return view('hr.payroll.salary_templates.index', [
            'templates' => $this->payrollRepository->salaryTemplates($filters),
            'filters' => $filters,
        ]);
    }

    public function createSalaryTemplate(): View
    {
        return view('hr.payroll.salary_templates.form', ['mode' => 'create']);
    }

    public function storeSalaryTemplate(Request $request): RedirectResponse
    {
        $this->payrollService->saveSalaryTemplate($this->validateSalaryTemplate($request));

        return redirect()->route('payroll.salary-templates.index')->with('success', 'Salary template created successfully.');
    }

    public function editSalaryTemplate(SalaryTemplate $template): View
    {
        return view('hr.payroll.salary_templates.form', ['mode' => 'edit', 'template' => $template]);
    }

    public function updateSalaryTemplate(Request $request, SalaryTemplate $template): RedirectResponse
    {
        $this->payrollService->saveSalaryTemplate($this->validateSalaryTemplate($request, $template), $template);

        return redirect()->route('payroll.salary-templates.index')->with('success', 'Salary template updated successfully.');
    }

    public function assignSalaryTemplateForm(): View
    {
        return view('hr.payroll.salary_templates.assign', [
            'employees' => $this->payrollRepository->employeesForSelect(),
            'templates' => $this->payrollRepository->templatesForSelect(),
        ]);
    }

    public function assignSalaryTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'salary_template_id' => ['required', 'integer', 'exists:salary_templates,id'],
            'pay_frequency' => ['nullable', Rule::in(['weekly', 'monthly'])],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_rent' => ['nullable', 'numeric', 'min:0'],
            'medical_allowance' => ['nullable', 'numeric', 'min:0'],
            'conveyance_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'provident_fund_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ctc_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        try {
            $this->payrollService->assignSalaryTemplate($validated);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('payroll.salary-templates.index')->with('success', 'Salary template assigned successfully.');
    }

    public function bonuses(Request $request): View
    {
        $filters = $this->filters($request);

        return view('hr.payroll.bonuses.index', [
            'bonuses' => $this->payrollRepository->bonuses($filters),
            'filters' => $filters,
            'employees' => $this->payrollRepository->employeesForSelect(),
        ]);
    }

    public function storeBonus(Request $request): RedirectResponse
    {
        $this->payrollService->saveBonus($this->validateBonus($request), (int) $request->user()->id);

        return back()->with('success', 'Bonus saved successfully.');
    }

    public function destroyBonus(Bonus $bonus): RedirectResponse
    {
        $bonus->delete();

        return back()->with('success', 'Bonus deleted successfully.');
    }

    public function loans(Request $request): View
    {
        $filters = $this->filters($request);

        return view('hr.payroll.loans.index', [
            'loans' => $this->payrollRepository->loans($filters),
            'filters' => $filters,
            'employees' => $this->payrollRepository->employeesForSelect(),
        ]);
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $this->payrollService->saveLoan($this->validateLoan($request));

        return back()->with('success', 'Loan saved successfully.');
    }

    public function deductions(Request $request): View
    {
        $filters = $this->filters($request);

        return view('hr.payroll.deductions.index', [
            'deductions' => $this->payrollRepository->deductions($filters),
            'filters' => $filters,
            'employees' => $this->payrollRepository->employeesForSelect(),
        ]);
    }

    public function storeDeduction(Request $request): RedirectResponse
    {
        $this->payrollService->saveDeduction($this->validateDeduction($request));

        return back()->with('success', 'Deduction saved successfully.');
    }

    public function destroyDeduction(EmployeeDeduction $deduction): RedirectResponse
    {
        $deduction->delete();

        return back()->with('success', 'Deduction deleted successfully.');
    }

    public function providentFunds(Request $request): View
    {
        $filters = $this->filters($request);

        return view('hr.payroll.provident_funds.index', [
            'funds' => $this->payrollRepository->providentFunds($filters),
            'filters' => $filters,
            'employees' => $this->payrollRepository->employeesForSelect(),
        ]);
    }

    public function storeProvidentFund(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'employee_contribution_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'employer_contribution_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
        ]);

        $this->payrollService->saveProvidentFund($validated);

        return back()->with('success', 'Provident fund setup saved successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->input('q')),
            'status' => (string) $request->input('status', ''),
            'employee_id' => (int) $request->input('employee_id', 0),
            'per_page' => max(10, min(100, (int) $request->input('per_page', 20))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSalaryTemplate(Request $request, ?SalaryTemplate $template = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(SalaryTemplate::class, 'name')->ignore($template?->id)],
            'code' => ['required', 'string', 'max:30', Rule::unique(SalaryTemplate::class, 'code')->ignore($template?->id)],
            'pay_frequency' => ['required', Rule::in(['weekly', 'monthly'])],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_rent' => ['nullable', 'numeric', 'min:0'],
            'medical_allowance' => ['nullable', 'numeric', 'min:0'],
            'conveyance_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'provident_fund_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBonus(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'bonus_date' => ['required', 'date'],
            'bonus_type' => ['required', 'string', 'max:40'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLoan(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'loan_reference' => ['required', 'string', 'max:255', Rule::unique(EmployeeLoan::class, 'loan_reference')],
            'principal_amount' => ['required', 'numeric', 'min:0'],
            'interest_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:240'],
            'installment_amount' => ['required', 'numeric', 'min:0'],
            'issued_date' => ['required', 'date'],
            'first_installment_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'closed', 'paused'])],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDeduction(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'deduction_type' => ['required', 'string', 'max:50'],
            'calculation_type' => ['required', Rule::in(['fixed', 'percent'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'frequency' => ['required', Rule::in(['weekly', 'monthly', 'one_time'])],
            'reason' => ['nullable', 'string', 'max:255'],
            'comments' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
