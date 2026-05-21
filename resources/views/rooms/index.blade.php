@extends('layouts.app')
@section('title', 'Room Management')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
    <div>
        <h1 class="page-title">Room Management</h1>
        <p class="page-subtitle">Manage dormitory rooms and their availability</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn-primary">+ Add Room</a>
</div>

<div class="glass-card p-4 mb-6">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        <div style="flex:1;min-width:180px;">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Room number..." value="{{ request('search') }}">
        </div>
        <div style="width:150px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
            </select>
        </div>
        <div style="width:150px;">
            <label class="form-label">Availability</label>
            <select name="availability" class="form-select">
                <option value="">All</option>
                <option value="available" {{ request('availability')=='available'?'selected':'' }}>Available</option>
                <option value="full" {{ request('availability')=='full'?'selected':'' }}>Full</option>
            </select>
        </div>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="{{ route('rooms.index') }}" class="btn-secondary">Reset</a>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
    @forelse($rooms as $room)
    @php
        $activeCount = $room->activeAllocations()->count();
        $pendingCount = $room->pendingApplications()->count();
        $totalReserved = $activeCount + $pendingCount;
        $pct = $room->capacity > 0 ? ($totalReserved / $room->capacity) * 100 : 0;
        $barColor = $pct >= 100 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : '#10b981');
    @endphp
    <div class="glass-card p-6" style="transition:all 0.3s ease;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="font-size:20px;font-weight:800;color:#e0e7ff;">{{ $room->room_number }}</h3>
            <span class="badge {{ $room->status==='active'?'badge-success':'badge-danger' }}">{{ ucfirst($room->status) }}</span>
        </div>

        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                <span style="color:#94a3b8;">Occupancy</span>
                <span style="color:#cbd5e1;font-weight:600;">{{ $activeCount }}/{{ $room->capacity }}</span>
            </div>
            <div class="capacity-bar">
                <div class="capacity-bar-fill" style="width:{{ min($pct, 100) }}%;background:{{ $barColor }};"></div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:{{ $pendingCount > 0 ? '8' : '16' }}px;">
            <span>{{ $room->available_slots }} slots available</span>
            <span>Capacity: {{ $room->capacity }}</span>
        </div>
        @if($pendingCount > 0)
        <div style="font-size:12px;color:#fbbf24;margin-bottom:16px;padding:6px 10px;background:rgba(251,191,36,0.08);border-radius:8px;">
            ⏳ {{ $pendingCount }} pending {{ Str::plural('application', $pendingCount) }}
        </div>
        @endif

        <div style="display:flex;gap:8px;">
            <a href="{{ route('rooms.show', $room->room_id) }}" class="btn-secondary btn-sm" style="flex:1;justify-content:center;">View</a>
            <a href="{{ route('rooms.edit', $room->room_id) }}" class="btn-secondary btn-sm" style="flex:1;justify-content:center;">Edit</a>
            @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('rooms.destroy', $room->room_id) }}" onsubmit="return confirm('Delete this room?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger btn-sm">Delete</button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state glass-card" style="grid-column:1/-1;">
        <p style="font-size:18px;font-weight:600;margin-bottom:8px;">No rooms found</p>
        <p>Create your first room to get started.</p>
    </div>
    @endforelse
</div>
<div style="margin-top:16px;">{{ $rooms->withQueryString()->links() }}</div>
@endsection
