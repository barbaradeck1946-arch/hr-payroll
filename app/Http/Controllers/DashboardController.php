<?php

namespace App\Http\Controllers;

use App\Modules\Announcements\Repositories\AnnouncementRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AnnouncementRepository $announcementRepository)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.dashboard.dashboard', [
            'latestAnnouncements' => $this->announcementRepository->latestPublished(20, $request->user()),
            'canCreateAnnouncement' => $request->user()?->hasPermission('announcement.create') ?? false,
        ]);
    }
}
