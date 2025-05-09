import domReady from '@wordpress/dom-ready';

/* global document, leaflet */

// Define icons for different vehicle types
const reservedIcon = new leaflet.Icon({
	iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
	shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
	iconSize: [25, 41],
	iconAnchor: [12, 41],
	popupAnchor: [1, -34],
	shadowSize: [41, 41]
});

const inUseIcon = new leaflet.Icon({
	iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
	shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
	iconSize: [25, 41],
	iconAnchor: [12, 41],
	popupAnchor: [1, -34],
	shadowSize: [41, 41]
});

const availableIcon = new leaflet.Icon({
	iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
	shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
	iconSize: [25, 41],
	iconAnchor: [12, 41],
	popupAnchor: [1, -34],
	shadowSize: [41, 41]
});

const defaultIcon = new leaflet.Icon({
	iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
	shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
	iconSize: [25, 41],
	iconAnchor: [12, 41],
	popupAnchor: [1, -34],
	shadowSize: [41, 41]
});

const vehicleIcons = {
	'reserved': reservedIcon,
	'available': availableIcon,
	'in use': inUseIcon,
};

export function initMaps(mapElements) {
	mapElements.forEach(element => {
		const data = element?.dataset.mapCoordinates ?? '';

		let coordinates = [];
		try {
			coordinates = JSON.parse(data) ?? [];
		} catch (error) { }

		delete element.dataset.mapCoordinates;

		// Ensure leaflet is available and coordinates[0] exists before setting vie
		const map = leaflet.map(element).setView([coordinates[0].x, coordinates[0].y], 13); // Adjusted zoom
		const layerGroup = leaflet.layerGroup().addTo(map);

		leaflet
			.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			})
			.addTo(map);

		coordinates
			.filter(location => typeof location.x === 'number' && typeof location.y === 'number') // Ensure x and y are numbers
			.forEach(location => {
				const iconToUse = vehicleIcons[location.status] || defaultIcon;
				const marker = leaflet.marker([location.x, location.y], {
					icon: iconToUse,
					title: location.id
				});

				// Prepare popup content
				let popupContent = `<strong>${location.type || 'N/A'}</strong> - ${(location.status ? location.status.charAt(0).toUpperCase() + location.status.slice(1) : 'N/A')}`;
				popupContent += `<br><strong>Charge:</strong> ${typeof location.charge === 'number' ? location.charge + '%' : 'N/A'}`;

				marker.bindPopup(popupContent);
				marker.addTo(layerGroup);
			});

		// Fly to the first valid coordinate
		if (coordinates[0] && typeof coordinates[0].x === 'number' && typeof coordinates[0].y === 'number') {
			map.flyTo([coordinates[0].x, coordinates[0].y], 13); // Adjusted zoom
		} else if (coordinates.length > 0) {
			// Fallback if first coordinate is invalid but others might exist, center on a default view or the first valid one
			// For simplicity, this example doesn't find the next valid, but you could iterate here.
			// map.setView([DEFAULT_LAT, DEFAULT_LON], DEFAULT_ZOOM);
			console.warn('First coordinate was invalid, map might not be centered as expected.');
		}
	});
}

// When the document is ready, find all maps and initialize them with Leaflet.
domReady(() => {
	initMaps(document.querySelectorAll('.wp-block-rdb-demo-car-map[data-map-coordinates]'));
});
