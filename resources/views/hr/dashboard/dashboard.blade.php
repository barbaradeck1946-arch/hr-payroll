@extends('layouts.backend')

@section('content')
<div class="wrapper-page">

    <div class="page-title">
        <h1><i class="icon-grid"></i>
            Dashboard
        </h1>
    </div>
    @include('partials.flash')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-dutch widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        Today's Sale
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-layers"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-jade widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-doc"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-green widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-wallet"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0">
                        <div class="card-body bg-blue widget">
                            <div class="d-flex">
                                <div class="align-items-center">
                                    <h4 class="text-white">
                                        
                                    </h4>
                                    <h6 class="text-white">
                                        Total Products
                                    </h6>
                                </div>
                                <div class="ms-auto align-items-center">
                                    <span class="text-white widget-icon"><i class="icon-bag"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card no-border">
                        <div class="content_wrapper">
                            <div class="table_banner clearfix">
                                <h5 class="table_banner_title">
                                    Latest Notices & Announcements
                                </h5>
                            <div class="float-end d-flex gap-2">
                                <a href="{{ route('announcements.index') }}" class="btn btn-custom-default btn-sm">View All</a>
                                @if($canCreateAnnouncement ?? false)
                                    <a href="{{ route('announcements.create') }}" class="btn btn-custom btn-sm">Add New</a>
                                @endif
                            </div>
                            </div>
                            <div class="dashboard-notice-meta px-3 pt-2 pb-0">
                                <span class="dashboard-notice-chip"><i class="icon-bell"></i> Notices: {{ ($latestAnnouncements ?? collect())->count() }}</span>
                                <span class="dashboard-notice-chip"><i class="icon-clock"></i> Non-expired only</span>
                            </div>
                            <div class="table_body dash-table-widget" style="padding: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-bordered dashboard-notice-table" style="margin-bottom: 0;">
                                        <thead>
                                            <tr>
                                                <th><i class="icon-doc me-1"></i> Title</th>
                                                <th><i class="icon-tag me-1"></i> Type</th>
                                                <th><i class="icon-clock me-1"></i> Published At</th>
                                                <th><i class="icon-eye me-1"></i> Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($latestAnnouncements ?? collect()) as $item)
                                                <tr>
                                                    <td>
                                                    <strong>{{ $item->title }}</strong>
                                                    </td>
                                                    <td>
                                                    <span class="badge {{ $item->announcement_type === 'notice' ? 'bg-info' : 'bg-primary' }}">
                                                        <i class="{{ $item->announcement_type === 'notice' ? 'icon-bell' : 'icon-doc' }}"></i>
                                                        {{ ucfirst($item->announcement_type) }}
                                                    </span>
                                                    </td>
                                                    <td>{{ $item->publish_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                                    <td class="action-buttons">
                                                        <a href="{{ route('announcements.show', $item) }}" title="Details"><i class="icon-eye"></i></a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No active notices/announcements available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <div class="card no-border no-bgc clearfix">
                        <div class="table_banner d-flex align-items-center justify-content-between">
                            <h5 class="table_banner_title">Quick Notes</h5>
                            <span class="table_banner_title mb-0"><i class="icon-notebook"></i></span>
                        </div>
                        <div class="bg-white">
                            <div class="slimScrollNote">
                                <div class="todo-box-wrap">
                                    <ul
                                        class="todo-list"
                                        id="quick-note-list"
                                        data-csrf="{{ csrf_token() }}"
                                        data-can-update="{{ ($canUpdatePrivateNotes ?? false) ? '1' : '0' }}"
                                        data-can-delete="{{ ($canDeletePrivateNotes ?? false) ? '1' : '0' }}"
                                    >
                                        @if(!($canViewPrivateNotes ?? false))
                                            <li class="todo-item quick-note-empty">
                                                <div class="text-muted small p-2">You do not have permission to view private notes.</div>
                                            </li>
                                        @elseif(($privateNotes ?? collect())->isEmpty())
                                            <li class="todo-item quick-note-empty">
                                                <div class="text-muted small p-2">No notes yet. Add your first private note below.</div>
                                            </li>
                                        @else
                                            @foreach(($privateNotes ?? collect()) as $note)
                                                @php($noteInputId = 'quick_note_' . $note->id)
                                                <li class="todo-item" data-note-id="{{ $note->id }}">
                                                    <div class="d-flex align-items-start gap-2">
                                                        @if($canUpdatePrivateNotes ?? false)
                                                            <form method="POST" action="{{ route('dashboard.quick-notes.toggle', $note) }}" class="checkbox checkbox-default pt-1 quick-note-toggle-form">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input class="to-do quick-note-toggle" type="checkbox" id="{{ $noteInputId }}" {{ $note->is_completed ? 'checked' : '' }}>
                                                                <label for="{{ $noteInputId }}"></label>
                                                            </form>
                                                        @endif
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold quick-note-title {{ $note->is_completed ? 'text-decoration-line-through text-muted' : '' }}">{{ $note->title }}</div>
                                                            <div class="small quick-note-body {{ $note->is_completed ? 'text-decoration-line-through text-muted' : 'text-muted' }}">{{ $note->note_body }}</div>
                                                        </div>
                                                        @if($canUpdatePrivateNotes ?? false)
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-custom-default quick-note-edit-btn"
                                                                title="Edit note"
                                                                data-action="{{ route('dashboard.quick-notes.update', $note) }}"
                                                            >
                                                                <i class="icon-pencil"></i>
                                                            </button>
                                                        @endif
                                                        @if($canDeletePrivateNotes ?? false)
                                                            <form method="POST" action="{{ route('dashboard.quick-notes.delete', $note) }}" class="quick-note-delete-form" onsubmit="return confirm('Delete this note permanently?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete note"><i class="icon-trash"></i></button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </li>
                                                @if(! $loop->last)
                                                    <li><hr class="light-grey-hr"></li>
                                                @endif
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="new-todo">
                            @if($canCreatePrivateNotes ?? false)
                            <form method="POST" action="{{ route('dashboard.quick-notes.store') }}" id="add_todo" class="quick-note-add-form">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="note_body" class="form-control" style="border: 1px solid #fff !IMPORTANT; width: 100% !IMPORTANT;" placeholder="Add new private note" required maxlength="2000">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-success todo-submit"><i class="fa fa-plus"></i></button>
                                    </span>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.page-content  -->
</div>
@endsection

@push('scripts')
<script>
    // Quick Notes JavaScript
(function () {
    var list = document.getElementById('quick-note-list');
    var addForm = document.querySelector('.quick-note-add-form');
    var csrf = list ? (list.getAttribute('data-csrf') || '') : '';
    var canUpdate = list ? list.getAttribute('data-can-update') === '1' : false;
    var canDelete = list ? list.getAttribute('data-can-delete') === '1' : false;

    // Helper function to send AJAX requests for forms
    function fetchForm(form) {
        return fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        }).then(function (res) { return res.json(); });
    }

    // Helper function to send AJAX requests for non-form actions
    function fetchAction(url, payload) {
        var formData = new FormData();
        Object.keys(payload).forEach(function (key) {
            formData.append(key, payload[key]);
        });
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        }).then(function (res) { return res.json(); });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function removeDividers() {
        if (!list) {
            return;
        }
        list.querySelectorAll('li').forEach(function (li) {
            if (li.querySelector('hr.light-grey-hr')) {
                li.remove();
            }
        });
    }

    function rebuildDividers() {
        if (!list) {
            return;
        }
        removeDividers();
        var items = Array.prototype.slice.call(list.querySelectorAll('.todo-item:not(.quick-note-empty)'));
        items.forEach(function (item, index) {
            if (index === items.length - 1) {
                return;
            }
            var divider = document.createElement('li');
            divider.className = 'quick-note-divider';
            divider.innerHTML = '<hr class="light-grey-hr">';
            item.insertAdjacentElement('afterend', divider);
        });
    }

    function ensureEmptyState() {
        if (!list) {
            return;
        }
        var items = list.querySelectorAll('.todo-item:not(.quick-note-empty)');
        var empty = list.querySelector('.quick-note-empty');
        if (items.length === 0 && !empty) {
            var emptyLi = document.createElement('li');
            emptyLi.className = 'todo-item quick-note-empty';
            emptyLi.innerHTML = '<div class="text-muted small p-2">No notes yet. Add your first private note below.</div>';
            list.appendChild(emptyLi);
            return;
        }
        if (items.length > 0 && empty) {
            empty.remove();
        }
    }

    function makeRowHtml(id, title, body) {
        var html = '<div class="d-flex align-items-start gap-2">';
        if (canUpdate) {
            html +=
                '<form method="POST" action="/dashboard/quick-notes/' + id + '/toggle" class="checkbox checkbox-default pt-1 quick-note-toggle-form">' +
                '  <input type="hidden" name="_token" value="' + csrf + '">' +
                '  <input type="hidden" name="_method" value="PATCH">' +
                '  <input class="to-do quick-note-toggle" type="checkbox" id="quick_note_' + id + '">' +
                '  <label for="quick_note_' + id + '"></label>' +
                '</form>';
        }
        html +=
            '<div class="flex-grow-1">' +
            '  <div class="fw-semibold quick-note-title">' + title + '</div>' +
            '  <div class="small quick-note-body text-muted">' + body + '</div>' +
            '</div>';
        if (canUpdate) {
            html +=
                '<button type="button" class="btn btn-sm btn-custom-default quick-note-edit-btn" title="Edit note" data-action="/dashboard/quick-notes/' + id + '">' +
                '  <i class="icon-pencil"></i>' +
                '</button>';
        }
        if (canDelete) {
            html +=
                '<form method="POST" action="/dashboard/quick-notes/' + id + '" class="quick-note-delete-form">' +
                '  <input type="hidden" name="_token" value="' + csrf + '">' +
                '  <input type="hidden" name="_method" value="DELETE">' +
                '  <button type="submit" class="btn btn-sm btn-danger" title="Delete note"><i class="icon-trash"></i></button>' +
                '</form>';
        }
        html += '</div>';
        return html;
    }

    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            event.preventDefault();
            fetchForm(addForm).then(function (json) {
                if (!json || !json.ok || !json.note || !list) {
                    return;
                }

                list.querySelectorAll('.quick-note-empty').forEach(function (el) { el.remove(); });

                var id = Number(json.note.id);
                var title = escapeHtml(json.note.title || '');
                var body = escapeHtml(json.note.note_body || '');

                var row = document.createElement('li');
                row.className = 'todo-item';
                row.setAttribute('data-note-id', String(id));
                row.innerHTML = makeRowHtml(id, title, body);

                list.prepend(row);
                rebuildDividers();
                ensureEmptyState();
                addForm.reset();
            }).catch(function () {});
        });
    }

    document.addEventListener('change', function (event) {
        var checkbox = event.target.closest('.quick-note-toggle');
        if (!checkbox) {
            return;
        }

        var form = checkbox.closest('.quick-note-toggle-form');
        var item = checkbox.closest('.todo-item');
        if (!form || !item) {
            return;
        }

        fetchForm(form).then(function (json) {
            if (!json || !json.ok || !json.note) {
                return;
            }
            var done = Boolean(json.note.is_completed);
            var title = item.querySelector('.quick-note-title');
            var body = item.querySelector('.quick-note-body');
            if (title) {
                title.classList.toggle('text-decoration-line-through', done);
                title.classList.toggle('text-muted', done);
            }
            if (body) {
                body.classList.toggle('text-decoration-line-through', done);
                body.classList.add('text-muted');
            }
        }).catch(function () {
            checkbox.checked = !checkbox.checked;
        });
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.quick-note-edit-btn');
        if (!button) {
            return;
        }
        var item = button.closest('.todo-item');
        if (!item) {
            return;
        }
        var bodyNode = item.querySelector('.quick-note-body');
        var titleNode = item.querySelector('.quick-note-title');
        var currentBody = bodyNode ? (bodyNode.textContent || '').trim() : '';
        var currentTitle = titleNode ? (titleNode.textContent || '').trim() : '';
        var nextBody = window.prompt('Edit note', currentBody);
        if (nextBody === null) {
            return;
        }
        nextBody = nextBody.trim();
        if (nextBody === '') {
            return;
        }

        fetchAction(button.getAttribute('data-action') || '', {
            _token: csrf,
            _method: 'PATCH',
            title: currentTitle,
            note_body: nextBody
        }).then(function (json) {
            if (!json || !json.ok || !json.note) {
                return;
            }
            if (titleNode) {
                titleNode.textContent = json.note.title || '';
            }
            if (bodyNode) {
                bodyNode.textContent = json.note.note_body || '';
            }
        }).catch(function () {});
    });

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('.quick-note-delete-form');
        if (!form) {
            return;
        }

        event.preventDefault();
        if (!window.confirm('Delete this note permanently?')) {
            return;
        }

        fetchForm(form).then(function (json) {
            if (!json || !json.ok) {
                return;
            }
            var item = form.closest('.todo-item');
            if (item) {
                item.remove();
            }
            rebuildDividers();
            ensureEmptyState();
        }).catch(function () {});
    });

    rebuildDividers();
    ensureEmptyState();
})();
</script>
@endpush
