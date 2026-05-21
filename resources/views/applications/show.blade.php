@extends('layouts.app')
@section('title', 'Application Details')
@section('content')
<div class="page-header">
    <h1 class="page-title">Application #{{ $application->application_id }}</h1>
    <p class="page-subtitle">Detailed view of this dormitory application</p>
</div>

<div class="glass-card p-8" style="max-width:700px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
        <div>
            <p class="form-label">Student Name</p>
            <p style="font-size:16px;font-weight:600;color:#e0e7ff;">{{ $application->student->user->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="form-label">Student Number</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $application->student->student_number ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="form-label">Course</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $application->student->course ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="form-label">Year Level</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $application->student->year_level ?? 'N/A' }}</p>
        </div>
    </div>

    <hr style="border-color:rgba(99,102,241,0.1);margin:24px 0;">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
        <div>
            <p class="form-label">Preferred Room</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $application->preferredRoom->room_number ?? 'Any Available' }}</p>
        </div>
        <div>
            <p class="form-label">Status</p>
            <span class="badge {{ $application->status==='approved'?'badge-success':($application->status==='rejected'?'badge-danger':'badge-warning') }}" style="font-size:14px;">{{ ucfirst($application->status) }}</span>
        </div>
        <div>
            <p class="form-label">Applied At</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $application->applied_at?->format('M d, Y h:i A') }}</p>
        </div>
        <div>
            <p class="form-label">Reviewed At</p>
            <p style="font-size:16px;color:#cbd5e1;">{{ $application->reviewed_at?->format('M d, Y h:i A') ?? 'Not yet reviewed' }}</p>
        </div>
    </div>

    <div class="mb-6">
        <p class="form-label">Remarks</p>
        <p style="font-size:14px;color:#cbd5e1;line-height:1.6;padding:12px 16px;background:rgba(15,23,42,0.5);border-radius:12px;">{{ $application->remarks ?? 'No remarks provided.' }}</p>
    </div>

    @if(!auth()->user()->isStudent() && $application->status === 'pending')
    <hr style="border-color:rgba(99,102,241,0.1);margin:24px 0;">
    <h3 style="font-size:15px;font-weight:700;color:#c7d2fe;margin-bottom:16px;">Review Application</h3>
    <div style="display:flex;gap:12px;">
        <form method="POST" action="{{ route('applications.updateStatus', $application->application_id) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn-success" onclick="return confirm('Approve?')">✓ Approve</button>
        </form>
        <form method="POST" action="{{ route('applications.updateStatus', $application->application_id) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="rejected">
            <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Reject?')">✕ Reject</button>
        </form>
    </div>
    @endif

    <div style="margin-top:24px;">
        <a href="{{ route('applications.index') }}" class="btn-secondary">← Back to Applications</a>
    </div>
</div>
@endsection
