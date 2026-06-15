{{-- مسكن — منتقي الموقع على الخريطة لاختيار إحداثيات العقار --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Select Location on Map') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; overflow: hidden; }
        #map { width: 100vw; height: 100vh; }
        .confirm-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(8px);
            padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.1); z-index: 1000;
        }
        .confirm-bar .coords {
            direction: ltr; font-size: 14px; color: #2C3138;
        }
        .confirm-bar .coords i { color: #A3700E; margin-inline-end: 6px; }
        .btn-confirm {
            background: #1a3a5c; color: #fff; border: none; padding: 10px 32px;
            border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;
            transition: background 0.2s; font-family: 'Cairo', sans-serif;
        }
        .btn-confirm:hover { background: #13294b; }
        .btn-confirm i { margin-inline-end: 8px; }
        .close-btn {
            position: fixed; top: 16px; {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 16px;
            width: 40px; height: 40px; border-radius: 50%; background: white;
            border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            cursor: pointer; z-index: 1000; font-size: 18px; color: #666;
            display: flex; align-items: center; justify-content: center;
        }
        .close-btn:hover { color: #C0392B; }
    </style>
</head>
<body>
    <button class="close-btn" onclick="window.close()"><i class="fas fa-times"></i></button>
    <div id="map"></div>

    <div class="confirm-bar">
        <div class="coords">
            <i class="fas fa-map-marker-alt"></i>
            <span id="coordDisplay">32.887200, 13.191300</span>
        </div>
        <button class="btn-confirm" id="confirmBtn">
            <i class="fas fa-check"></i> {{ __('Confirm Location') }}
        </button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var params = new URLSearchParams(window.location.search);
        var lat = parseFloat(params.get('lat')) || 32.8872;
        var lng = parseFloat(params.get('lng')) || 13.1913;
        var currentLat = lat;
        var currentLng = lng;

        var map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

        function updateCoords(lat, lng) {
            currentLat = lat.toFixed(6);
            currentLng = lng.toFixed(6);
            document.getElementById('coordDisplay').textContent = currentLat + ', ' + currentLng;
        }

        marker.on('dragend', function(e) {
            var pos = marker.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });

        document.getElementById('confirmBtn').addEventListener('click', function() {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'location-selected',
                    latitude: currentLat,
                    longitude: currentLng
                }, '*');
            }
            window.close();
        });
    </script>
</body>
</html>
