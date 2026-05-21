@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<h2 class="text-xl font-bold text-white text-center mb-6">Sign in to your account</h2>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-5">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
    </div>
    <div class="mb-5">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
    </div>
    <div class="mb-6 flex items-center gap-2">
        <input type="checkbox" name="remember" id="remember" class="rounded" style="accent-color:#6366f1;">
        <label for="remember" class="text-sm text-slate-400">Remember me</label>
    </div>
    <button type="submit" class="btn-primary">Sign In</button>
</form>

<p class="text-center text-slate-500 text-sm mt-6">
    Don't have an account?
    <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold">Register as Student</a>
</p>

@endsection
