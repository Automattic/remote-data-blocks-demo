const fs = require('fs');

// Center of Astoria, Oregon (approximate)
const ASTORIA_CENTER_LAT = 46.1879;
const ASTORIA_CENTER_LON = -123.8313;

// Max deviation for a single step in degrees
const MAX_STEP_LAT = 0.01;
const MAX_STEP_LON = 0.01;

// Geographical Boundaries (taken from your Python script)
const MIN_LAT_BOUNDARY = 46.17312686430337;
const MAX_LAT_BOUNDARY = 46.18236238589541;
const MIN_LON_BOUNDARY = -123.84838479222236;
const MAX_LON_BOUNDARY = -123.78146210805365;

// Number of random walk steps from the center for each point
const MIN_WALK_STEPS = 1;
const MAX_WALK_STEPS = 3;

const INPUT_FILE = 'mock-vehicle-data.json';
const OUTPUT_FILE = 'mock-vehicle-data.json'; // Overwrite the existing file

function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function getRandomFloat(min, max) {
    return Math.random() * (max - min) + min;
}

function performRandomWalk(startLat, startLon) {
    while (true) { // Keep trying until a point within bounds is found
        let currentLat = startLat;
        let currentLon = startLon;
        const numSteps = getRandomInt(MIN_WALK_STEPS, MAX_WALK_STEPS);

        for (let i = 0; i < numSteps; i++) {
            const latStep = getRandomFloat(-MAX_STEP_LAT, MAX_STEP_LAT);
            const lonStep = getRandomFloat(-MAX_STEP_LON, MAX_STEP_LON);

            currentLat += latStep;
            currentLon += lonStep;
        }

        // Check if the generated point is within boundaries
        if (
            currentLat >= MIN_LAT_BOUNDARY &&
            currentLat <= MAX_LAT_BOUNDARY &&
            currentLon >= MIN_LON_BOUNDARY &&
            currentLon <= MAX_LON_BOUNDARY
        ) {
            return { latitude: parseFloat(currentLat.toFixed(6)), longitude: parseFloat(currentLon.toFixed(6)) };
        }
        // If not in bounds, the loop continues and retries the random walk
    }
}

function updateCoordinates(data) {
    return data.map(vehicle => {
        const { latitude, longitude } = performRandomWalk(ASTORIA_CENTER_LAT, ASTORIA_CENTER_LON);
        vehicle.coordinates.latitude = latitude;
        vehicle.coordinates.longitude = longitude;
        return vehicle;
    });
}

function main() {
    try {
        const rawData = fs.readFileSync(INPUT_FILE, 'utf8');
        const vehicleData = JSON.parse(rawData);

        const updatedVehicleData = updateCoordinates(vehicleData);

        fs.writeFileSync(OUTPUT_FILE, JSON.stringify(updatedVehicleData, null, 2));
        console.log(`Successfully updated coordinates in ${OUTPUT_FILE}`);

    } catch (error) {
        if (error.code === 'ENOENT') {
            console.error(`Error: ${INPUT_FILE} not found.`);
        } else if (error instanceof SyntaxError) {
            console.error(`Error: Could not decode JSON from ${INPUT_FILE}.`);
        } else {
            console.error(`An unexpected error occurred: ${error.message}`);
        }
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}
