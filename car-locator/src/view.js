import domReady from '@wordpress/dom-ready';

/* global document, leaflet */

// Helper function to add CSS for the legend to the document head
function addLegendCSSOnce() {
	if (document.getElementById('leaflet-map-legend-styles')) {
		return; // Style already added
	}
	const css = `
        .info.legend {
            padding: 6px 8px;
            font: 14px/16px Arial, Helvetica, sans-serif;
            background: white;
            background: rgba(255,255,255,0.8);
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border-radius: 5px;
            line-height: 20px;
            color: #555;
        }
        .info.legend h4 {
            margin: 0 0 5px;
            color: #333;
            text-align: center;
            font-weight: bold;
        }
        .info.legend div {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }
        .info.legend div:last-child {
            margin-bottom: 0;
        }
        .info.legend img {
            width: 15px; /* Scaled down from 25px */
            height: 25px; /* Scaled down from 41px (maintaining aspect ratio) */
            margin-right: 8px;
        }
    `;
	const head = document.head || document.getElementsByTagName('head')[0];
	const style = document.createElement('style');
	style.id = 'leaflet-map-legend-styles'; // Add an ID to check for existence

	if (style.styleSheet) {
		// This is required for IE8 and below.
		style.styleSheet.cssText = css;
	} else {
		style.appendChild(document.createTextNode(css));
	}
	head.appendChild(style);
}

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

		// Add Legend
		const LegendControl = leaflet.Control.extend({
			onAdd: function (mapInstance) {
				const div = leaflet.DomUtil.create('div', 'info legend');
				const statuses = [
					{ label: 'Available', iconUrl: availableIcon.options.iconUrl },
					{ label: 'Reserved', iconUrl: reservedIcon.options.iconUrl },
					{ label: 'In Use', iconUrl: inUseIcon.options.iconUrl },
				];
				let legendHtml = '<h4>Vehicle Status</h4>';
				statuses.forEach(status => {
					legendHtml += `<div><img src="${status.iconUrl}" alt="${status.label}"> ${status.label}</div>`;
				});
				div.innerHTML = legendHtml;
				return div;
			},
			onRemove: function (mapInstance) {
				// Nothing to do here
			}
		});
		new LegendControl({ position: 'bottomright' }).addTo(map);
	});
}

// When the document is ready, find all maps and initialize them with Leaflet.
domReady(() => {
	addLegendCSSOnce(); // Add CSS for the legend
	initMaps(document.querySelectorAll('.wp-block-rdb-demo-car-map[data-map-coordinates]'));
});
