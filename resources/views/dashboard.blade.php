<x-layouts::app :title="__('dashboard.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('dashboard.heading') }}</flux:heading>
            <flux:text>{{ __('dashboard.subheading') }}</flux:text>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card :label="__('dashboard.customers')" :value="$customerCount" icon="users" :href="route('customers.index')" />

            <x-stat-card :label="__('dashboard.suppliers')" :value="$supplierCount" icon="truck" :href="route('suppliers.index')" />

            <x-stat-card :label="__('dashboard.uninvoiced')" :value="$uninvoicedCount" icon="document-text" :href="auth()->user()->can('manage-finances') ? route('financial.uninvoiced') : null" />

            <x-stat-card :label="__('dashboard.synced_invoices')" :value="$syncedInvoiceCount" icon="check-circle" :href="auth()->user()->can('manage-finances') ? route('financial.invoices') : null" />
        </div>

        <!-- Customer Map -->
        @if($customers->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">{{ __('dashboard.customer_locations') }}</flux:heading>
            
            <div id="customer-map" style="height: 400px; width: 100%; background-color: #fff; border-radius: 0.5rem; border: 1px solid #e4e4e7;"></div>
        </div>
        @endif
    </div>

    @if($customers->isNotEmpty())
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customers = @json($customers);
            
            if (!customers || customers.length === 0) return;
            
            // Calculate bounds from customers
            let minLat = 90, maxLat = -90, minLng = 180, maxLng = -180;
            let validMarkers = 0;
            
            customers.forEach((customer) => {
                const lat = parseFloat(customer.latitude);
                const lng = parseFloat(customer.longitude);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    minLat = Math.min(minLat, lat);
                    maxLat = Math.max(maxLat, lat);
                    minLng = Math.min(minLng, lng);
                    maxLng = Math.max(maxLng, lng);
                    validMarkers++;
                }
            });
            
            if (validMarkers === 0) return;
            
            const nlMinLat = 50.75;
            const nlMaxLat = 53.50;
            const nlMinLng = 3.37;
            const nlMaxLng = 7.22;
            
            const allInNL = minLat >= nlMinLat && maxLat <= nlMaxLat && 
                           minLng >= nlMinLng && maxLng <= nlMaxLng;
            
            const centerLat = (nlMinLat + nlMaxLat) / 2;
            const centerLng = (nlMinLng + nlMaxLng) / 2;
            const map = L.map('customer-map').setView([centerLat, centerLng], 7);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            const markerGroup = L.featureGroup().addTo(map);

            // Add markers from pre-geocoded coordinates
            customers.forEach((customer) => {
                const lat = parseFloat(customer.latitude);
                const lng = parseFloat(customer.longitude);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = L.marker([lat, lng]).addTo(markerGroup);
                    
                    // Create popup with customer info
                    const popupContent = `
                        <div class="font-semibold text-sm">${customer.name}</div>
                        <div class="text-xs text-gray-600">
                            ${customer.address}<br>
                        </div>
                        ${customer.phone ? `<div class="text-xs mt-1"><a href="tel:${customer.phone}" class="text-blue-600 hover:underline">${customer.phone}</a></div>` : ''}
                        ${customer.email ? `<div class="text-xs"><a href="mailto:${customer.email}" class="text-blue-600 hover:underline">${customer.email}</a></div>` : ''}
                    `;
                    
                    marker.bindPopup(popupContent, { maxWidth: 200 });
                }
            });

            // Fit map only if all markers are in Netherlands
            try {
                if (allInNL && markerGroup.getLayers().length > 0) {
                    map.fitBounds(markerGroup.getBounds(), { padding: [50, 50], maxZoom: 10 });
                }
            } catch (e) {
                console.warn('fitBounds failed, keeping Netherlands view', e);
            }
        });
    </script>
    @endif
</x-layouts::app>
