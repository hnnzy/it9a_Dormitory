@extends('layouts.app')
@section('title', 'Room Allocation')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
    <div>
        <h1 class="page-title">Room Allocation</h1>
        <p class="page-subtitle">Assign students to available dormitory rooms</p>
    </div>
    <a href="{{ route('allocations.create') }}" class="btn-primary">+ Allocate Student</a>
</div>

<div class="glass-card p-4 mb-6">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Student name..." value="{{ request('search') }}">
        </div>
        <div style="width:150px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="{{ route('allocations.index') }}" class="btn-secondary">Reset</a>
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Student</th><th>Student #</th><th>Room</th><th>Date Assigned</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($allocations as $alloc)
        <tr>
            <td style="font-weight:600;">{{ $alloc->student->user->name ?? 'N/A' }}</td>
            <td>{{ $alloc->student->student_number ?? 'N/A' }}</td>
            <td><span class="badge badge-info">{{ $alloc->room->room_number ?? 'N/A' }}</span></td>
            <td>{{ $alloc->date_assigned->format('M d, Y') }}</td>
            <td><span class="badge {{ $alloc->status==='active'?'badge-success':'badge-danger' }}">{{ ucfirst($alloc->status) }}</span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    @if($alloc->status === 'active')
                    <form method="POST" action="{{ route('allocations.updateStatus', $alloc->allocation_id) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="inactive">
                        <button type="submit" class="btn-warning btn-sm" onclick="return confirm('Deactivate this allocation?')">Deactivate</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('allocations.updateStatus', $alloc->allocation_id) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="btn-success btn-sm" onclick="return confirm('Reactivate this allocation?')">Reactivate</button>
                    </form>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('allocations.destroy', $alloc->allocation_id) }}" onsubmit="return confirm('Delete this allocation?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm">Delete</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="empty-state">No allocations found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $allocations->withQueryString()->links() }}</div>
@endsection
