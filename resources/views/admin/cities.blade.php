{{-- مسكن — صفحة إدارة المدن للمسؤول --}}
@extends('layouts.app')

@section('title', __('Cities - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Manage Cities') }}</h1>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-md-5">
        <div class="maskan-card">
            <div class="card-body">
                <h5 class="mb-3">{{ __('Add City') }}</h5>
                <form method="POST" action="{{ route('admin.cities.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('City Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-maskan-primary d-flex justify-content-center align-items-center gap-2">
                        <i class="fas fa-save"></i> {{ __('Save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="maskan-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('Cities List') }}</span>
                <span class="badge bg-gold" style="font-size:0.8rem;">{{ count($cities) }}</span>
            </div>
            <div class="card-body p-0">
                @if(count($cities) > 0)
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>{{ __('City') }}</th>
                            <th style="width:80px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cities as $city)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong style="font-size:1.05rem;">{{ $city }}</strong></td>
                            <td>
                                <form method="POST" action="{{ route('admin.cities.destroy', $city) }}"
                                      onsubmit="return confirm('{{ __('Delete city?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn danger" title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-map-marker-alt fa-3x mb-3 d-block" style="color:var(--gold);"></i>
                    <p>{{ __('No cities configured') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection