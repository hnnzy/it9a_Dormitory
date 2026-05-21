@extends('layouts.app')
@section('title', auth()->user()->isStudent() ? 'My Applications' : 'Application Monitoring')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
    <div>
        <h1 class="page-title">{{ auth()->user()->isStudent() ? 'My Applications' : 'Application Monitoring' }}</h1>
        <p class="page-subtitle">{{ auth()->user()->isStudent() ? 'Track your dormitory applications' : 'Review and manage student applications' }}</p>
    </div>
    @if(auth()->user()->isStudent())
    <a href="{{ route('applications.create') }}" class="btn-primary">+ New Application</a>
    @endif
</div>

{{-- Status Filter --}}
<div class="glass-card p-4 mb-6">
    <form method="GET" style="display:flex;gap:12px;align-items:end;">
        <div style="width:180px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
            </select>
        </div>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="{{ route('applications.index') }}" class="btn-secondary">Reset</a>
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="data-table">
        <thead>
            <tr>
                @if(!auth()->user()->isStudent())<th>Student</th>@endif
                <th>Preferred Room</th>
                <th>Remarks</th>
                <th>Status</th>
                <th>Applied</th>
                <th>Reviewed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($applications as $app)
        <tr>
            @if(!auth()->user()->isStudent())
            <td>
                <div>
                    <p style="font-weight:600;">{{ $app->student->user->name ?? 'N/A' }}</p>
                    <p style="font-size:12px;color:#64748b;">{{ $app->student->student_number ?? '' }}</p>
                </div>
            </td>
            @endif
            <td>{{ $app->preferredRoom->room_number ?? 'Any Available' }}</td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $app->remarks ?? '—' }}</td>
            <td><span class="badge {{ $app->status==='approved'?'badge-success':($app->status==='rejected'?'badge-danger':'badge-warning') }}">{{ ucfirst($app->status) }}</span></td>
            <td>{{ $app->applied_at?->format('M d, Y') }}</td>
            <td>{{ $app->reviewed_at?->format('M d, Y') ?? '—' }}</td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="{{ route('applications.show', $app->application_id) }}" class="btn-secondary btn-sm">View</a>
                    @if(!auth()->user()->isStudent() && $app->status === 'pending')
                    <form method="POST" action="{{ route('applications.updateStatus', $app->application_id) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn-success btn-sm" onclick="return confirm('Approve this application?')">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('applications.updateStatus', $app->application_id) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Reject this application?')">Reject</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="empty-state">No applications found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $applications->withQueryString()->links() }}</div>
@endsection
