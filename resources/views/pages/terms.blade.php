{{-- مسكن — صفحة الشروط والأحكام --}}
@extends('layouts.guest')

@section('title', __('Terms & Conditions - Maskan'))

@section('content')
<div style="max-height:60vh;overflow-y:auto;padding:0 4px;margin-bottom:1rem;white-space:pre-wrap;line-height:1.7;font-size:0.9rem;color:rgba(255,255,255,0.85);">
    {{ $content }}
</div>
<div style="text-align:center;margin-top:0.5rem;">
    <a href="{{ route('home') }}" style="color:var(--gold);font-size:0.85rem;">&larr; {{ __('Back to Home') }}</a>
</div>
@endsection
