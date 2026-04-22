@extends('layouts.backend')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-plane"></i> Holidays - {{ $year }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('holidays.export-current-year', ['year' => $year]) }}" class="btn btn-custom-default">
                <i class="icon-cloud-download"></i> Export Excel (CSV)
            </a>
            @if(auth()->user()?->hasPermission('holiday.create'))
                <a href="{{ route('holidays.create') }}" class="btn btn-custom"><i class="icon-plus"></i> Add Holiday</a>
            @endif
        </div>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <ul class="nav nav-tabs holiday-tab-nav mb-3" id="holidayTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="holiday-table-tab" data-bs-toggle="tab" data-bs-target="#holiday-table-pane" type="button" role="tab" aria-controls="holiday-table-pane" aria-selected="true">
                                Table View
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="holiday-calendar-tab" data-bs-toggle="tab" data-bs-target="#holiday-calendar-pane" type="button" role="tab" aria-controls="holiday-calendar-pane" aria-selected="false">
                                Calendar View
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="holiday-table-pane" role="tabpanel" aria-labelledby="holiday-table-tab">
                            <form method="GET" class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <select name="year" class="form-control">
                                        @foreach($availableYears as $yearOption)
                                            <option value="{{ $yearOption }}" {{ (int) $year === (int) $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="per_page" class="form-control">
                                        @foreach([10,20,50,100] as $size)
                                            <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }} / page</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button class="btn btn-custom" type="submit"><i class="icon-magnifier"></i> Apply</button>
                                    <a href="{{ route('holidays.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i></a>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Optional</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($holidays as $holiday)
                                            <tr>
                                                <td>{{ $holiday->holiday_date?->format('d M Y') }}</td>
                                                <td>{{ $holiday->holiday_date?->format('l') }}</td>
                                                <td>{{ $holiday->title }}</td>
                                                <td><span class="holiday-badge holiday-badge-type">{{ ucfirst($holiday->holiday_type) }}</span></td>
                                                <td>
                                                    @if($holiday->is_optional)
                                                        <span class="holiday-badge holiday-badge-optional">Yes</span>
                                                    @else
                                                        <span class="holiday-badge holiday-badge-regular">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $holiday->description ?: '-' }}</td>
                                                <td class="action-buttons">
                                                    @if(auth()->user()?->hasPermission('holiday.update'))
                                                        <a href="{{ route('holidays.edit', $holiday) }}" title="Edit Holiday">
                                                            <i class="icon-pencil"></i>
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()?->hasPermission('holiday.delete'))
                                                        <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" style="display:inline;" onsubmit="return confirm('Delete this holiday?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" title="Delete Holiday"><i class="icon-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No holidays configured for {{ $year }}.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $holidays->links('pagination::bootstrap-5') }}
                        </div>

                        <div class="tab-pane fade" id="holiday-calendar-pane" role="tabpanel" aria-labelledby="holiday-calendar-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <button class="btn btn-custom-default" type="button" id="holiday-calendar-prev">
                                    <i class="icon-arrow-left"></i> Previous
                                </button>
                                <h4 class="mb-0" id="holiday-calendar-month-label"></h4>
                                <button class="btn btn-custom-default" type="button" id="holiday-calendar-next">
                                    Next <i class="icon-arrow-right"></i>
                                </button>
                            </div>
                            <p class="text-muted mb-2">Click any highlighted date to view holiday details.</p>
                            <div class="mb-2 small">
                                <span class="holiday-badge holiday-badge-holiday">Holiday</span>
                                <span class="holiday-badge weekend-legend">Weekend</span>
                            </div>
                            <div class="holiday-calendar-grid" id="holiday-calendar-grid"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade holiday-modal" id="holidayDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Holiday Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="holiday-details-body"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .holiday-tab-nav {
        border-bottom: 1px solid #d9e1e7;
    }
    .holiday-tab-nav .nav-link {
        color: #607d8b;
        font-weight: 500;
        border: 0;
        border-bottom: 2px solid transparent;
        padding: 10px 14px;
    }
    .holiday-tab-nav .nav-link.active {
        color: #2C3E50;
        border-color: #2C3E50;
        background: transparent;
    }
    .holiday-badge {
        display: inline-block;
        border-radius: 30px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 500;
        line-height: 1;
    }
    .holiday-badge-type {
        background: #dbe8ff;
        color: #1f3f66;
    }
    .holiday-badge-optional {
        background: #fff1d6;
        color: #7a4d00;
    }
    .holiday-badge-regular {
        background: #ddf8ea;
        color: #1f6b46;
    }
    .holiday-badge-holiday {
        background: #2C3E50;
        color: #fff;
    }
    .holiday-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }
    .holiday-calendar-cell {
        min-height: 110px;
        border: 1px solid #e6edf3;
        border-radius: 8px;
        padding: 8px;
        background: #fff;
    }
    .holiday-calendar-cell.header {
        min-height: auto;
        font-weight: 600;
        text-align: center;
        background: #f8f9fa;
    }
    .holiday-calendar-cell.empty {
        background: #f9fbfc;
    }
    .holiday-calendar-cell.day {
        cursor: default;
        transition: box-shadow 0.15s ease-in-out;
    }
    .holiday-calendar-cell.day.has-holiday {
        border-color: #2C3E50;
        background: #f3f7fb;
        cursor: pointer;
    }
    .holiday-calendar-cell.day.has-holiday:hover {
        box-shadow: 0 0 0 2px rgba(44, 62, 80, 0.15) inset;
    }
    .holiday-day-number {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .holiday-chip {
        display: block;
        font-size: 11px;
        line-height: 1.25;
        padding: 2px 6px;
        border-radius: 999px;
        background: #2C3E50;
        color: #fff;
        margin-bottom: 4px;
    }
    .holiday-chip.weekend {
        background: #6c757d;
    }
    .holiday-calendar-cell.day.weekend {
        border-color: #6c757d;
        background: #f2f3f5;
    }
    .holiday-calendar-cell.day.weekend:hover {
        box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.2) inset;
    }
    .holiday-calendar-cell.day.weekend.has-holiday {
        border-color: #2C3E50;
        background: linear-gradient(135deg, #f3f7fb 0%, #f3f7fb 55%, #f2f3f5 100%);
    }
    .weekend-legend {
        color: #fff;
        background: #6c757d;
    }
    .holiday-modal .modal-header {
        background: #f6f9fc;
        border-bottom: 1px solid #e3e9ef;
    }
    .holiday-modal .modal-title {
        color: #2C3E50;
        font-weight: 600;
    }
    .holiday-modal .modal-content {
        border: 1px solid #dfe8ef;
        border-radius: 8px;
    }
</style>
<script>
    (function () {
        const holidays = @json($calendarItems);
        const weekendDayIndexes = @json($weekendDayIndexes);
        const weekendSet = new Set((Array.isArray(weekendDayIndexes) ? weekendDayIndexes : []).map((value) => Number(value)));
        const holidaysByDate = holidays.reduce((carry, item) => {
            if (!carry[item.holiday_date]) {
                carry[item.holiday_date] = [];
            }
            carry[item.holiday_date].push(item);
            return carry;
        }, {});

        const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        const calendarGrid = document.getElementById('holiday-calendar-grid');
        const monthLabel = document.getElementById('holiday-calendar-month-label');
        const prevButton = document.getElementById('holiday-calendar-prev');
        const nextButton = document.getElementById('holiday-calendar-next');
        const detailsModalEl = document.getElementById('holidayDetailsModal');
        const detailsBody = document.getElementById('holiday-details-body');
        const detailsModal = detailsModalEl ? new bootstrap.Modal(detailsModalEl) : null;

        if (!calendarGrid || !monthLabel || !prevButton || !nextButton) {
            return;
        }

        const initialYear = Number(@json($year));
        let currentMonthDate = new Date(initialYear, new Date().getMonth(), 1);
        if (currentMonthDate.getFullYear() !== initialYear) {
            currentMonthDate = new Date(initialYear, 0, 1);
        }

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const renderCalendar = () => {
            const year = currentMonthDate.getFullYear();
            const month = currentMonthDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const startWeekDay = firstDay.getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();

            monthLabel.textContent = `${monthNames[month]} ${year}`;
            calendarGrid.innerHTML = '';

            weekDays.forEach((day) => {
                const header = document.createElement('div');
                header.className = 'holiday-calendar-cell header';
                header.textContent = day;
                calendarGrid.appendChild(header);
            });

            for (let i = 0; i < startWeekDay; i += 1) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'holiday-calendar-cell empty';
                calendarGrid.appendChild(emptyCell);
            }

            for (let day = 1; day <= totalDays; day += 1) {
                const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayHolidays = holidaysByDate[dateKey] || [];
                const dayOfWeek = new Date(year, month, day).getDay();
                const isWeekend = weekendSet.has(dayOfWeek);
                const dayCell = document.createElement('div');
                dayCell.className = `holiday-calendar-cell day ${isWeekend ? 'weekend' : ''} ${dayHolidays.length > 0 ? 'has-holiday' : ''}`;
                dayCell.dataset.date = dateKey;

                const number = document.createElement('div');
                number.className = 'holiday-day-number';
                number.textContent = String(day);
                dayCell.appendChild(number);

                if (isWeekend) {
                    const weekendChip = document.createElement('span');
                    weekendChip.className = 'holiday-chip weekend';
                    weekendChip.textContent = 'Weekend';
                    dayCell.appendChild(weekendChip);
                }

                dayHolidays.slice(0, 2).forEach((item) => {
                    const chip = document.createElement('span');
                    chip.className = 'holiday-chip';
                    chip.textContent = item.title;
                    dayCell.appendChild(chip);
                });

                if (dayHolidays.length > 2) {
                    const more = document.createElement('span');
                    more.className = 'holiday-chip';
                    more.textContent = `+${dayHolidays.length - 2} more`;
                    dayCell.appendChild(more);
                }

                dayCell.addEventListener('click', () => {
                    if (!isWeekend && dayHolidays.length === 0) {
                        return;
                    }
                    if (!detailsModal || !detailsBody) {
                        return;
                    }

                    const weekendHtml = isWeekend
                        ? `
                            <div class="border rounded p-2 mb-2">
                                <h6 class="mb-1">Weekend</h6>
                                <div class="small text-muted mb-1">${escapeHtml(dateKey)}</div>
                                <div>This date is a scheduled weekly weekend.</div>
                            </div>
                        `
                        : '';

                    const detailsHtml = dayHolidays.map((item) => `
                        <div class="border rounded p-2 mb-2">
                            <h6 class="mb-1">${escapeHtml(item.title)}</h6>
                            <div class="small text-muted mb-1">${escapeHtml(item.holiday_date)} | ${escapeHtml(item.holiday_type)}</div>
                            <div class="small mb-1">Optional: ${item.is_optional ? 'Yes' : 'No'}</div>
                            <div>${item.description ? escapeHtml(item.description) : '-'}</div>
                        </div>
                    `).join('');

                    detailsBody.innerHTML = `${weekendHtml}${detailsHtml}`;
                    detailsModal.show();
                });

                calendarGrid.appendChild(dayCell);
            }
        };

        prevButton.addEventListener('click', () => {
            const nextDate = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth() - 1, 1);
            if (nextDate.getFullYear() !== initialYear) {
                return;
            }
            currentMonthDate = nextDate;
            renderCalendar();
        });

        nextButton.addEventListener('click', () => {
            const nextDate = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth() + 1, 1);
            if (nextDate.getFullYear() !== initialYear) {
                return;
            }
            currentMonthDate = nextDate;
            renderCalendar();
        });

        renderCalendar();
    })();
</script>
@endpush
