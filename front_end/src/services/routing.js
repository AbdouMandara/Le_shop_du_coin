import axios from 'axios';

const OSRM_URL = 'https://router.project-osrm.org/route/v1/driving';

/**
 * Fetches a road-following route between two points using OSRM API.
 * @param {Array} start [lat, lng]
 * @param {Array} end [lat, lng]
 * @returns {Promise<Object|null>} Route details including coordinates, distance (km), and duration (min)
 */
export const getRoute = async (start, end) => {
    try {
        // OSRM expects coordinates in [lng,lat] format
        const startLngLat = `${start[1]},${start[0]}`;
        const endLngLat = `${end[1]},${end[0]}`;
        
        const url = `${OSRM_URL}/${startLngLat};${endLngLat}?overview=full&geometries=geojson`;
        const response = await axios.get(url);
        
        if (response.data.routes && response.data.routes.length > 0) {
            const route = response.data.routes[0];
            // Convert GeoJSON [lng, lat] back to Leaflet [lat, lng]
            const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
            
            return {
                coordinates,
                distance: route.distance / 1000, 
                duration: route.duration / 60 
            };
        }
    } catch (error) {
        console.error("OSRM Routing error:", error);
    }
    return null;
};
