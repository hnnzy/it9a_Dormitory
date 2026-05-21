@extends('layouts.app')
@section('title', 'Apply for Dormitory')
@section('content')
<div class="page-header">
    <h1 class="page-title">Apply for Dormitory</h1>
    <p class="page-subtitle">Submit your application for dormitory accommodation</p>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="glass-card p-8" style="max-width:600px;">
    <form method="POST" action="{{ route('applications.store') }}">
        @csrf
        <div class="mb-5">
            <label class="form-label">Preferred Room (Optional)</label>
            <select name="preferred_room_id" class="form-select">
                <option value="">Any Available Room</option>
                @foreach($rooms as $room)
                <option value="{{ $room->room_id }}">{{ $room->room_number }} — {{ $room->available_slots }}/{{ $room->capacity }} slots available</option>
                @endforeach
            </select>
            <p style="font-size:12px;color:#64748b;margin-top:6px;">Selecting a preferred room does not guarantee assignment.</p>
        </div>
        <div class="mb-6">
            <label class="form-label">Remarks / Reason</label>
            <textarea name="remarks" class="form-input" rows="4" placeholder="Tell us why you'd like dormitory accommodation..." style="resize:vertical;">{{ old('remarks') }}</textarea>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-primary">Submit Application</button>
            <a href="{{ route('applications.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
