<?php

namespace App\Http\Controllers;

use App\Models\PrivateNote;
use App\Modules\Announcements\Repositories\AnnouncementRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AnnouncementRepository $announcementRepository)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('hr.dashboard.dashboard', [
            'latestAnnouncements' => $this->announcementRepository->latestPublished(20, $user),
            'canCreateAnnouncement' => $user?->hasPermission('announcement.create') ?? false,
            'canViewPrivateNotes' => $user?->hasPermission('note.view-private') ?? false,
            'canCreatePrivateNotes' => $user?->hasPermission('note.create-private') ?? false,
            'canUpdatePrivateNotes' => $user?->hasPermission('note.update-private') ?? false,
            'canDeletePrivateNotes' => $user?->hasPermission('note.delete-private') ?? false,
            'privateNotes' => $user
                ? PrivateNote::query()
                    ->where('user_id', (int) $user->id)
                    ->orderBy('is_completed')
                    ->orderByDesc('is_pinned')
                    ->orderByDesc('updated_at')
                    ->limit(100)
                    ->get()
                : collect(),
        ]);
    }

    public function storeQuickNote(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_if(! $user || ! $user->hasPermission('note.create-private'), 403);

        $validated = $request->validate([
            'note_body' => ['required', 'string', 'max:2000'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $noteBody = trim((string) $validated['note_body']);
        $title = trim((string) ($validated['title'] ?? ''));
        if ($title === '') {
            $title = mb_substr($noteBody, 0, 80);
        }

        $note = PrivateNote::query()->create([
            'user_id' => (int) $user->id,
            'title' => $title,
            'note_body' => $noteBody,
            'is_pinned' => false,
            'is_completed' => false,
            'completed_at' => null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Quick note added.',
                'note' => [
                    'id' => (int) $note->id,
                    'title' => (string) $note->title,
                    'note_body' => (string) $note->note_body,
                    'is_completed' => (bool) $note->is_completed,
                ],
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Quick note added.');
    }

    public function toggleQuickNote(Request $request, PrivateNote $privateNote): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_if(! $user || ! $user->hasPermission('note.update-private'), 403);
        abort_if((int) $privateNote->user_id !== (int) $user->id, 403);

        $isCompleted = ! (bool) $privateNote->is_completed;
        $privateNote->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $isCompleted ? 'Note marked as done.' : 'Note reopened.',
                'note' => [
                    'id' => (int) $privateNote->id,
                    'is_completed' => (bool) $isCompleted,
                ],
            ]);
        }

        return redirect()->route('dashboard')->with('success', $isCompleted ? 'Note marked as done.' : 'Note reopened.');
    }

    public function deleteQuickNote(Request $request, PrivateNote $privateNote): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_if(! $user || ! $user->hasPermission('note.delete-private'), 403);
        abort_if((int) $privateNote->user_id !== (int) $user->id, 403);

        $privateNote->forceDelete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Note deleted permanently.',
                'note_id' => (int) $privateNote->id,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Note deleted permanently.');
    }
}
