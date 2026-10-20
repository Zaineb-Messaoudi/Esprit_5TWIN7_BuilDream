@props([
    'location' => '',
    'latitude' => null,
    'longitude' => null,
    'locationName' => 'location',
    'latitudeName' => 'latitude',
    'longitudeName' => 'longitude',
    'locationId' => 'location',
    'showInput' => true,
])

@php($mapId = 'solarshare-location-map-'.\Illuminate\Support\Str::uuid())

@once
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <style>
            .solarshare-location-map { min-height: 18rem; }
            .leaflet-container { font-family: inherit; z-index: 0; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            window.SolarShareLeaflet = window.SolarShareLeaflet || {
                maps: new Map(),
                locateLabel(map, marker, locationInput, latitudeInput, longitudeInput, lat, lng, label) {
                    marker.setLatLng([lat, lng]);
                    latitudeInput.value = Number(lat).toFixed(8);
                    longitudeInput.value = Number(lng).toFixed(8);
                    if (label) locationInput.value = label;
                    map.setView([lat, lng], Math.max(map.getZoom(), 13));
                },
                async reverseGeocode(lat, lng) {
                    const response = await fetch(`https://photon.komoot.io/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Reverse geocoding failed');
                    const payload = await response.json();
                    const feature = payload.features?.[0];
                    const props = feature?.properties || {};
                    return [props.name, props.street, props.city || props.town || props.village, props.country].filter(Boolean).join(', ');
                },
                async search(query) {
                    const response = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Location search failed');
                    return (await response.json()).features || [];
                },
                initPicker(root) {
                    if (!window.L || root.dataset.ready) return;
                    const mapElement = root.querySelector('[data-location-map]');
                    const locationInput = root.querySelector('[data-location-input]') || document.getElementById(root.dataset.locationInputId);
                    const latitudeInput = root.querySelector('[data-latitude-input]');
                    const longitudeInput = root.querySelector('[data-longitude-input]');
                    const searchButton = root.querySelector('[data-location-search]');
                    const status = root.querySelector('[data-location-status]');
                    const initialLat = Number(latitudeInput.value) || 36.8065;
                    const initialLng = Number(longitudeInput.value) || 10.1815;
                    const map = L.map(mapElement, { scrollWheelZoom: false }).setView([initialLat, initialLng], latitudeInput.value && longitudeInput.value ? 13 : 7);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
                    const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
                    const setStatus = (message, error = false) => { if (status) { status.textContent = message; status.classList.toggle('text-error-600', error); status.classList.toggle('text-gray-500', !error); } };
                    const selectPoint = async (lat, lng, label = '') => {
                        window.SolarShareLeaflet.locateLabel(map, marker, locationInput, latitudeInput, longitudeInput, lat, lng, label);
                        if (!label) {
                            setStatus('Finding the nearest address…');
                            try { window.SolarShareLeaflet.locateLabel(map, marker, locationInput, latitudeInput, longitudeInput, lat, lng, await window.SolarShareLeaflet.reverseGeocode(lat, lng)); }
                            catch { setStatus('Coordinates saved. Add a place name if needed.'); }
                        }
                        if (label) setStatus('Location selected on the map.');
                    };
                    map.on('click', (event) => selectPoint(event.latlng.lat, event.latlng.lng));
                    marker.on('dragend', (event) => { const point = event.target.getLatLng(); selectPoint(point.lat, point.lng); });
                    searchButton?.addEventListener('click', async () => {
                        if (!locationInput.value.trim()) return setStatus('Enter a city or place to search.', true);
                        setStatus('Searching…');
                        try {
                            const results = await window.SolarShareLeaflet.search(locationInput.value.trim());
                            const first = results[0];
                            if (!first) return setStatus('No matching place found.', true);
                            const [lng, lat] = first.geometry.coordinates;
                            const props = first.properties || {};
                            const label = [props.name, props.city || props.town || props.village, props.country].filter(Boolean).join(', ');
                            await selectPoint(lat, lng, label);
                        } catch { setStatus('The map service is temporarily unavailable.', true); }
                    });
                    window.SolarShareLeaflet.maps.set(mapElement.id, map);
                    root.dataset.ready = 'true';
                    setTimeout(() => map.invalidateSize(), 100);
                },
                init() { document.querySelectorAll('[data-location-picker]').forEach((root) => this.initPicker(root)); }
            };
            document.addEventListener('DOMContentLoaded', () => window.SolarShareLeaflet.init());
        </script>
    @endpush
@endonce

<div data-location-picker data-location-input-id="{{ $locationId }}" class="space-y-3">
    <div class="flex flex-wrap items-end gap-2">
        @if($showInput)<div class="min-w-0 flex-1">
            <label for="{{ $locationId }}" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Location') }}</label>
            <input id="{{ $locationId }}" name="{{ $locationName }}" value="{{ old($locationName, $location) }}" required data-location-input autocomplete="street-address" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>@endif
        <button type="button" data-location-search class="min-h-11 rounded-lg border border-brand-300 px-4 text-theme-sm font-semibold text-brand-700 hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('Find on map') }}</button>
    </div>
    <input type="hidden" name="{{ $latitudeName }}" value="{{ old($latitudeName, $latitude) }}" data-latitude-input>
    <input type="hidden" name="{{ $longitudeName }}" value="{{ old($longitudeName, $longitude) }}" data-longitude-input>
    <div id="{{ $mapId }}" data-location-map class="solarshare-location-map overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800"></div>
    <p data-location-status class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Click the map or drag the pin to choose the exact location.') }}</p>
    @error($locationName)<p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    @error($latitudeName)<p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    @error($longitudeName)<p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
</div>
