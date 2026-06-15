{{-- مسكن — صفحة تعديل بيانات العقار --}}
@extends('layouts.app')

@section('title', __('Edit Property - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Edit Property') }}</h1>
    <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="maskan-card">
    <div class="card-body">
        <form action="{{ route('properties.update', $property) }}" method="POST" class="form-maskan" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">{{ __('Property Title') }} <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $property->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Property Type') }} <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="resort" {{ old('type', $property->property_type) == 'resort' ? 'selected' : '' }}>{{ __('Resort') }}</option>
                        <option value="rest_house" {{ old('type', $property->property_type) == 'rest_house' ? 'selected' : '' }}>{{ __('Rest House') }}</option>
                        <option value="villa" {{ old('type', $property->property_type) == 'villa' ? 'selected' : '' }}>{{ __('Villa') }}</option>
                        <option value="house" {{ old('type', $property->property_type) == 'house' ? 'selected' : '' }}>{{ __('House') }}</option>
                        <option value="building" {{ old('type', $property->property_type) == 'building' ? 'selected' : '' }}>{{ __('Building') }}</option>
                        <option value="apartment" {{ old('type', $property->property_type) == 'apartment' ? 'selected' : '' }}>{{ __('Apartment') }}</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="4" required>{{ old('description', $property->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('City') }} <span class="text-danger">*</span></label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $property->location ?? '') }}" required>
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $property->latitude ?? '') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $property->longitude ?? '') }}">
            <div class="mb-3">
                <label class="form-label">{{ __('Property Location') }}</label>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-maskan" id="locateBtn">
                        <i class="fas fa-map-marker-alt ms-1"></i> {{ __('Locate Property') }}
                    </button>
                    <span id="locationStatus" class="text-muted small">
                        @if(old('latitude', $property->latitude ?? ''))
                            <span class="text-success"><i class="fas fa-check-circle"></i> {{ old('latitude', $property->latitude) }}, {{ old('longitude', $property->longitude) }}</span>
                        @else
                            {{ __('Not set') }}
                        @endif
                    </span>
                </div>
            </div>

            @push('scripts')
            <script>
                var currentLat = document.getElementById('latitude').value || '32.8872';
                var currentLng = document.getElementById('longitude').value || '13.1913';

                function setLocation(lat, lng) {
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    document.getElementById('locationStatus').innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' +
                        lat + ', ' + lng + '</span>';
                }

                window.addEventListener('message', function(e) {
                    if (e.data && e.data.type === 'location-selected') {
                        setLocation(e.data.latitude, e.data.longitude);
                    }
                });

                document.getElementById('locateBtn').addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        window.open('{{ route("location.picker") }}?lat=' + currentLat + '&lng=' + currentLng, 'picker', 'width=900,height=700');
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        function(pos) { setLocation(pos.coords.latitude.toFixed(6), pos.coords.longitude.toFixed(6)); },
                        function() { window.open('{{ route("location.picker") }}?lat=' + currentLat + '&lng=' + currentLng, 'picker', 'width=900,height=700'); },
                        { enableHighAccuracy: true }
                    );
                });
            </script>
            @endpush

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Price per Night') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price_per_night"
                           class="form-control @error('price_per_night') is-invalid @enderror"
                           value="{{ old('price_per_night', $property->price) }}" required>
                    @error('price_per_night') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Bedrooms') }} <span class="text-danger">*</span></label>
                    <input type="number" min="0" name="rooms_count"
                           class="form-control @error('rooms_count') is-invalid @enderror"
                           value="{{ old('rooms_count', $property->rooms_count) }}" required>
                    @error('rooms_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ __('Bathrooms') }} <span class="text-danger">*</span></label>
                    <input type="number" min="0" name="bathrooms_count"
                           class="form-control @error('bathrooms_count') is-invalid @enderror"
                           value="{{ old('bathrooms_count', $property->bathrooms_count) }}" required>
                    @error('bathrooms_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ __('Area (sqm)') }}</label>
                    <input type="number" min="0" name="area_sqm"
                           class="form-control @error('area_sqm') is-invalid @enderror"
                           value="{{ old('area_sqm', $property->area) }}">
                    @error('area_sqm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ __('Monthly Price (LYD)') }}</label>
                    <input type="number" step="0.01" min="0" name="price_per_month"
                           class="form-control @error('price_per_month') is-invalid @enderror"
                           value="{{ old('price_per_month', $property->price_per_month) }}">
                    @error('price_per_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ __('Deposit (LYD)') }}</label>
                    <input type="number" step="0.01" min="0" name="deposit"
                           class="form-control @error('deposit') is-invalid @enderror"
                           value="{{ old('deposit', $property->deposit) }}">
                    @error('deposit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('Amenities') }}</label>
                <div class="d-flex flex-wrap gap-4">
                    <div class="form-check">
                        <input type="checkbox" name="has_pool" value="1" class="form-check-input" id="has_pool" {{ old('has_pool', $property->has_pool) ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_pool">{{ __('Pool') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="has_parking" value="1" class="form-check-input" id="has_parking" {{ old('has_parking', $property->has_parking) ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_parking">{{ __('Parking') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="has_ac" value="1" class="form-check-input" id="has_ac" {{ old('has_ac', $property->has_ac) ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_ac">{{ __('Air Conditioning') }}</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="has_furniture" value="1" class="form-check-input" id="has_furniture" {{ old('has_furniture', $property->has_furniture) ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_furniture">{{ __('Furnished') }}</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('Property Images') }}</label>

                @if($property->images)
                <div class="mb-2">
                    <label class="form-label small text-muted">{{ __('Current Images') }}</label>
                    <div class="property-gallery">
                        @foreach($property->images as $img)
                        <div class="gallery-item">
                            <img src="{{ asset($img->image_path) }}" alt="{{ __('Property Image') }}">
                            <label class="delete-img" style="cursor: pointer;">
                                <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" style="display: none;">
                                <i class="fas fa-times"></i>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="image-uploader" onclick="this.querySelector('input').click()">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">{{ __('Click to add more images') }}<br><small>{{ __('Upload hint') }}</small></div>
                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                    <div class="image-preview-list"></div>
                </div>
                @error('images.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-maskan px-4">
                    <i class="fas fa-save ms-1"></i> {{ __('Save Changes') }}
                </button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-secondary px-4">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Image delete visual feedback
    document.querySelectorAll('.delete-img').forEach(function(label) {
        label.addEventListener('click', function(e) {
            var checkbox = this.querySelector('input[type="checkbox"]');
            var item = this.closest('.gallery-item');
            // The label click already toggles the checkbox, wait for it
            setTimeout(function() {
                if (checkbox.checked) {
                    item.style.opacity = '0.4';
                    item.style.filter = 'grayscale(1)';
                    item.style.outline = '2px solid var(--danger, #dc3545)';
                    item.style.outlineOffset = '-2px';
                    label.style.background = 'var(--danger, #dc3545)';
                } else {
                    item.style.opacity = '1';
                    item.style.filter = 'none';
                    item.style.outline = 'none';
                    label.style.background = 'rgba(192,57,43,0.9)';
                }
            }, 50);
        });
    });
</script>
@endpush

@endsection
