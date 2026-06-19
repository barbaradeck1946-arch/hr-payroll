<?php

namespace App\Modules\Teams\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Modules\Teams\Http\Requests\StoreTeamRequest;
use App\Modules\Teams\Http\Requests\SyncTeamMembersRequest;
use App\Modules\Teams\Http\Requests\UpdateTeamRequest;
use App\Modules\Teams\Repositories\TeamRepository;
use App\Modules\Teams\Services\TeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly TeamService $teamService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'department_id' => (int) $request->input('department_id', 0),
            'status' => (string) $request->input('status', ''),
            'per_page' => max(10, min(100, (int) $request->input('per_page', 20))),
        ];

        return view('hr.teams.index', [
            'teams' => $this->teamRepository->paginate($filters),
            'departments' => $this->teamRepository->listDepartments(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('hr.teams.form', [
            'mode' => 'create',
            'departments' => $this->teamRepository->listDepartments(),
            'employees' => $this->teamRepository->listActiveEmployees(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $this->teamService->createTeam($request->validated());

        return redirect()->route('teams.index')->with('success', 'Team created successfully.');
    }

    public function edit(Team $team): View
    {
        return view('hr.teams.form', [
            'mode' => 'edit',
            'team' => $team,
            'departments' => $this->teamRepository->listDepartments(),
            'employees' => $this->teamRepository->listActiveEmployees(),
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $this->teamService->updateTeam($team, $request->validated());

        return redirect()->route('teams.index')->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->teamService->deleteTeam($team);

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }

    public function members(Team $team): View
    {
        return view('hr.teams.members', [
            'team' => $this->teamRepository->withMembers($team),
            'employees' => $this->teamRepository->listActiveEmployees(),
        ]);
    }

    public function syncMembers(SyncTeamMembersRequest $request, Team $team): RedirectResponse
    {
        $this->teamService->syncMembers($team, $request->validated()['members'] ?? []);

        return redirect()->route('teams.members', $team)->with('success', 'Team members updated successfully.');
    }
}
