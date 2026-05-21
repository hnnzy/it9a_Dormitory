@extends('layouts.app')
@section('title', 'Allocate Student')
@section('content')
<div class="page-header">
    <h1 class="page-title">Allocate Student to Room</h1>
    <p class="page-subtitle">Assign an unallocated student to an available room</p>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="glass-card p-8" style="max-width:600px;">
    <form method="POST" action="{{ route('allocations.store') }}">
        @csrf
        <div class="mb-5">
            <label class="form-label">Select Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">Choose a student...</option>
                @foreach($students as $student)
                <option value="{{ $student->student_id }}" {{ old('student_id')==$student->student_id?'selected':'' }}>
                    {{ $student->user->name }} ({{ $student->student_number }}) — {{ $student->course }}
                </option>
                @endforeach
            </select>
            @if($students->isEmpty())
            <p style="font-size:12px;color:#f59e0b;margin-top:6px;">⚠ No eligible students — all active students already have room allocations.</p>
            @else
            <p style="font-size:12px;color:#64748b;margin-top:6px;">Only active students without any existing room allocation are shown.</p>
            @endif
        </div>

        <div class="mb-6">
            <label class="form-label">Select Room</label>
            <select name="room_id" class="form-select" required>
                <option value="">Choose a room...</option>
                @foreach($rooms as $room)
                <option value="{{ $room->room_id }}" {{ old('room_id')==$room->room_id?'selected':'' }}>
                    {{ $room->room_number }} — {{ $room->available_slots }}/{{ $room->capacity }} slots available
                </option>
                @endforeach
            </select>
            @if($rooms->isEmpty())
            <p style="font-size:12px;color:#f59e0b;margin-top:6px;">⚠ No rooms with available slots.</p>
            @else
            <p style="font-size:12px;color:#64748b;margin-top:6px;">Only rooms with available slots are shown.</p>
            @endif
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-primary" {{ ($students->isEmpty() || $rooms->isEmpty()) ? 'disabled' : '' }}>Allocate Student</button>
            <a href="{{ route('allocations.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
