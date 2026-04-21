<?php

namespace App\Modules\Employees\Services;

use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeProfileUpdateRequest;
use App\Modules\Employees\Repositories\EmployeeProfileUpdateRequestRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeProfileUpdateRequestService
{
    public function __construct(
        private readonly EmployeeProfileUpdateRequestRepository $requestRepository
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function submit(Employee $employee, int $submittedByUserId, array $payload): EmployeeProfileUpdateRequest
    {
        $existingPending = $this->requestRepository->latestPendingForEmployee($employee->id);
        if ($existingPending) {
            $latestReviewed = $this->requestRepository->latestReviewedForEmployee($employee->id);

            if ($latestReviewed?->reviewed_at && $existingPending->submitted_at && $latestReviewed->reviewed_at->gte($existingPending->submitted_at)) {
                $this->requestRepository->update($existingPending, [
                    'approval_status' => 'rejected',
                    'review_comments' => 'Auto-closed stale pending request.',
                    'reviewed_by_user_id' => $latestReviewed->reviewed_by_user_id,
                    'reviewed_at' => $latestReviewed->reviewed_at,
                ]);
            } else {
                throw new RuntimeException('You already have a pending profile update request.');
            }
        }

        $rejectedRequest = $this->requestRepository->latestRejectedForEmployee($employee->id);

        return DB::transaction(function () use ($employee, $submittedByUserId, $payload, $rejectedRequest): EmployeeProfileUpdateRequest {
            return $this->requestRepository->create([
                'employee_id' => $employee->id,
                'submitted_by_user_id' => $submittedByUserId,
                'approval_status' => 'pending',
                'payload' => $this->extractPayload($payload),
                'submitted_at' => now(),
                'resubmission_of_id' => $rejectedRequest?->id,
            ]);
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function process(EmployeeProfileUpdateRequest $request, array $payload, int $reviewedByUserId): void
    {
        DB::transaction(function () use ($request, $payload, $reviewedByUserId): void {
            $decision = $payload['decision'];

            if ($request->approval_status !== 'pending') {
                throw new RuntimeException('Only pending requests can be processed.');
            }

            if ($decision === 'approve') {
                $this->applyApprovedPayload($request->employee, $request->payload ?? [], $reviewedByUserId);
            }

            EmployeeProfileUpdateRequest::query()
                ->where('employee_id', $request->employee_id)
                ->where('approval_status', 'pending')
                ->whereNull('reviewed_at')
                ->where('id', '!=', $request->id)
                ->update([
                    'approval_status' => 'rejected',
                    'review_comments' => 'Auto-closed because another profile update request was reviewed.',
                    'reviewed_by_user_id' => $reviewedByUserId,
                    'reviewed_at' => now(),
                ]);

            $this->requestRepository->update($request, [
                'approval_status' => $decision === 'approve' ? 'approved' : 'rejected',
                'review_comments' => $payload['review_comments'] ?? null,
                'reviewed_by_user_id' => $reviewedByUserId,
                'reviewed_at' => now(),
            ]);
        });
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function extractPayload(array $payload): array
    {
        return [
            'general_info' => [
                'first_name' => $payload['first_name'] ?? null,
                'last_name' => $payload['last_name'] ?? null,
                'gender' => $payload['gender'] ?? null,
                'date_of_birth' => $payload['date_of_birth'] ?? null,
                'nid_number' => $payload['nid_number'] ?? null,
                'passport_number' => $payload['passport_number'] ?? null,
                'tax_id' => $payload['tax_id'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'alternate_phone' => $payload['alternate_phone'] ?? null,
                'marital_status' => $payload['marital_status'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ],
            'addresses' => $this->sanitizeRows($payload['addresses'] ?? []),
            'bank_accounts' => $this->sanitizeRows($payload['bank_accounts'] ?? []),
            'emergency_contacts' => $this->sanitizeRows($payload['emergency_contacts'] ?? []),
            'documents' => $this->sanitizeRows($payload['documents'] ?? []),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function sanitizeRows(array $rows): array
    {
        return array_values(array_filter($rows, function ($row): bool {
            if (! is_array($row)) {
                return false;
            }

            foreach ($row as $value) {
                if ($value !== null && $value !== '' && $value !== false) {
                    return true;
                }
            }

            return false;
        }));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applyApprovedPayload(Employee $employee, array $payload, int $reviewedByUserId): void
    {
        $generalInfo = $payload['general_info'] ?? [];
        if (is_array($generalInfo) && $generalInfo !== []) {
            $employee->update([
                'first_name' => $generalInfo['first_name'] ?? $employee->first_name,
                'last_name' => $generalInfo['last_name'] ?? null,
                'gender' => $generalInfo['gender'] ?? null,
                'date_of_birth' => $generalInfo['date_of_birth'] ?? null,
                'nid_number' => $generalInfo['nid_number'] ?? null,
                'passport_number' => $generalInfo['passport_number'] ?? null,
                'tax_id' => $generalInfo['tax_id'] ?? null,
                'phone' => $generalInfo['phone'] ?? null,
                'alternate_phone' => $generalInfo['alternate_phone'] ?? null,
                'marital_status' => $generalInfo['marital_status'] ?? null,
                'notes' => $generalInfo['notes'] ?? null,
            ]);
        }

        $addresses = $this->sanitizeRows($payload['addresses'] ?? []);
        $banks = $this->sanitizeRows($payload['bank_accounts'] ?? []);
        $contacts = $this->sanitizeRows($payload['emergency_contacts'] ?? []);
        $documents = $this->sanitizeRows($payload['documents'] ?? []);

        EmployeeAddress::query()->where('employee_id', $employee->id)->delete();
        foreach ($addresses as $row) {
            EmployeeAddress::query()->create([
                'employee_id' => $employee->id,
                'address_type' => $row['address_type'] ?? 'present',
                'line_1' => $row['line_1'] ?? '',
                'line_2' => $row['line_2'] ?? null,
                'city' => $row['city'] ?? null,
                'state' => $row['state'] ?? null,
                'postal_code' => $row['postal_code'] ?? null,
                'country' => $row['country'] ?? null,
                'is_primary' => (bool) ($row['is_primary'] ?? false),
            ]);
        }

        EmployeeBankAccount::query()->where('employee_id', $employee->id)->delete();
        foreach ($banks as $row) {
            EmployeeBankAccount::query()->create([
                'employee_id' => $employee->id,
                'bank_name' => $row['bank_name'] ?? '',
                'branch_name' => $row['branch_name'] ?? null,
                'account_holder_name' => $row['account_holder_name'] ?? '',
                'account_number' => $row['account_number'] ?? '',
                'routing_number' => $row['routing_number'] ?? null,
                'account_type' => $row['account_type'] ?? null,
                'is_primary' => (bool) ($row['is_primary'] ?? false),
            ]);
        }

        EmployeeEmergencyContact::query()->where('employee_id', $employee->id)->delete();
        foreach ($contacts as $row) {
            EmployeeEmergencyContact::query()->create([
                'employee_id' => $employee->id,
                'name' => $row['name'] ?? '',
                'relationship' => $row['relationship'] ?? null,
                'phone' => $row['phone'] ?? '',
                'email' => $row['email'] ?? null,
                'address' => $row['address'] ?? null,
                'is_primary' => (bool) ($row['is_primary'] ?? false),
            ]);
        }

        EmployeeDocument::query()->where('employee_id', $employee->id)->delete();
        foreach ($documents as $row) {
            EmployeeDocument::query()->create([
                'employee_id' => $employee->id,
                'document_type' => $row['document_type'] ?? '',
                'title' => $row['title'] ?? '',
                'file_path' => $row['file_path'] ?? '',
                'issued_date' => $row['issued_date'] ?? null,
                'expiry_date' => $row['expiry_date'] ?? null,
                'uploaded_by' => $reviewedByUserId,
            ]);
        }
    }
}
