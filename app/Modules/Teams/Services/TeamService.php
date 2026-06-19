<?php

namespace App\Modules\Teams\Services;

use App\Models\Team;
use App\Modules\Teams\Repositories\TeamRepository;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function __construct(private readonly TeamRepository $teamRepository)
    {
    }

    /** @param array<string, mixed> $payload */
    public function createTeam(array $payload): Team
    {
        return DB::transaction(function () use ($payload): Team {
            return $this->teamRepository->create([
                'name' => $payload['name'],
                'code' => $payload['code'] ?? null,
                'department_id' => $payload['department_id'] ?? null,
                'lead_employee_id' => $payload['lead_employee_id'] ?? null,
                'description' => $payload['description'] ?? null,
                'is_active' => (bool) ($payload['is_active'] ?? true),
            ]);
        });
    }

    /** @param array<string, mixed> $payload */
    public function updateTeam(Team $team, array $payload): Team
    {
        return DB::transaction(function () use ($team, $payload): Team {
            $this->teamRepository->update($team, [
                'name' => $payload['name'],
                'code' => $payload['code'] ?? null,
                'department_id' => $payload['department_id'] ?? null,
                'lead_employee_id' => $payload['lead_employee_id'] ?? null,
                'description' => $payload['description'] ?? null,
                'is_active' => (bool) ($payload['is_active'] ?? false),
            ]);

            return $team->fresh() ?? $team;
        });
    }

    public function deleteTeam(Team $team): void
    {
        DB::transaction(function () use ($team): void {
            $team->members()->detach();
            $this->teamRepository->delete($team);
        });
    }

    /** @param array<int, array<string, mixed>> $members */
    public function syncMembers(Team $team, array $members): void
    {
        DB::transaction(function () use ($team, $members): void {
            $syncData = [];
            foreach ($members as $member) {
                $employeeId = (int) ($member['employee_id'] ?? 0);
                if ($employeeId <= 0) {
                    continue;
                }

                $syncData[$employeeId] = [
                    'member_role' => (string) ($member['member_role'] ?? 'member'),
                    'joined_on' => $member['joined_on'] ?? null,
                    'left_on' => $member['left_on'] ?? null,
                    'is_active' => (bool) ($member['is_active'] ?? true),
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            $team->members()->sync($syncData);
        });
    }
}
