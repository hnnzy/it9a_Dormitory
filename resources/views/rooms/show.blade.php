@extends('layouts.app')
@section('title', 'Room ' . $room->room_number)
@section('content')
<div class="page-header">
    <h1 class="page-title">Room {{ $room->room_number }}</h1>
    <p class="page-subtitle">Room details and current occupants</p>
</div>

@php
    $activeCount = $room->activeAllocations->count();
    $pendingCount = $room->pendingApplications->count();
    $totalReserved = $activeCount + $pendingCount;
    $pct = $room->capacity > 0 ? ($totalReserved / $room->capacity) * 100 : 0;
    $barColor = $pct >= 100 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : '#10b981');
@endphp

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;">
    <div class="glass-card p-6">
        <h3 style="font-size:16px;font-weight:700;color:#c7d2fe;margin-bottom:20px;">Room Info</h3>
        <div style="margin-bottom:16px;">
            <p class="form-label">Room Number</p>
            <p style="font-size:20px;font-weight:800;color:#e0e7ff;">{{ $room->room_number }}</p>
        </div>
        <div style="margin-bottom:16px;">
            <p class="form-label">Status</p>
            <span class="badge {{ $room->status==='active'?'badge-success':'badge-danger' }}">{{ ucfirst($room->status) }}</span>
        </div>
        <div style="margin-bottom:16px;">
            <p class="form-label">Capacity</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $room->capacity }} beds</p>
        </div>
        <div style="margin-bottom:16px;">
            <p class="form-label">Occupancy</p>
            <p style="font-size:24px;font-weight:800;color:#e0e7ff;">{{ $activeCount }}/{{ $room->capacity }}</p>
            <div class="capacity-bar" style="margin-top:8px;">
                <div class="capacity-bar-fill" style="width:{{ min($pct, 100) }}%;background:{{ $barColor }};"></div>
            </div>
        </div>
        @if($pendingCount > 0)
        <div style="margin-bottom:16px;padding:10px 14px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.2);border-radius:10px;">
            <p style="font-size:12px;color:#fbbf24;font-weight:600;margin-bottom:2px;">⏳ Pending Reservations</p>
            <p style="font-size:18px;font-weight:700;color:#fcd34d;">{{ $pendingCount }} pending</p>
            <p style="font-size:11px;color:#d97706;margin-top:2px;">{{ $room->available_slots }} slot{{ $room->available_slots !== 1 ? 's' : '' }} truly available</p>
        </div>
        @endif
        <div style="display:flex;gap:8px;margin-top:24px;">
            <a href="{{ route('rooms.edit', $room->room_id) }}" class="btn-secondary btn-sm">Edit</a>
            <a href="{{ route('rooms.index') }}" class="btn-secondary btn-sm">← Back</a>
        </div>
    </div>

    <div>
        <div class="glass-card p-6" style="margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:700;color:#c7d2fe;margin-bottom:20px;">Current Occupants</h3>
            @if($room->activeAllocations->count())
            <table class="data-table">
                <thead><tr><th>Student</th><th>Student #</th><th>Course</th><th>Assigned</th></tr></thead>
                <tbody>
                @foreach($room->activeAllocations as $alloc)
                <tr>
                    <td style="font-weight:600;">{{ $alloc->student->user->name ?? 'N/A' }}</td>
                    <td>{{ $alloc->student->student_number ?? 'N/A' }}</td>
                    <td>{{ $alloc->student->course ?? 'N/A' }}</td>
                    <td>{{ $alloc->date_assigned->format('M d, Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <p style="font-size:16px;font-weight:600;margin-bottom:4px;">No occupants</p>
                <p>This room is currently empty.</p>
            </div>
            @endif
        </div>

        @if($room->pendingApplications->count())
        <div class="glass-card p-6">
            <h3 style="font-size:16px;font-weight:700;color:#fbbf24;margin-bottom:20px;">⏳ Pending Applications</h3>
            <table class="data-table">
                <thead><tr><th>Student</th><th>Student #</th><th>Applied</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($room->pendingApplications as $app)
                <tr>
                    <td style="font-weight:600;">{{ $app->student->user->name ?? 'N/A' }}</td>
                    <td>{{ $app->student->student_number ?? 'N/A' }}</td>
                    <td>{{ $app->applied_at?->format('M d, Y') }}</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
