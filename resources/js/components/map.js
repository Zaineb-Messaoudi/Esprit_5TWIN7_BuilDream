import jsVectorMap from 'jsvectormap';
import 'jsvectormap/dist/maps/world';
import 'jsvectormap/dist/jsvectormap.min.css';

const locations = [
    { name: 'Tunis', coords: [36.8065, 10.1815] },
    { name: 'Sousse', coords: [35.8256, 10.6084] },
    { name: 'Sfax', coords: [34.7406, 10.7603] },
    { name: 'Gabes', coords: [33.8815, 10.0982] },
];

const mapConfigs = {
    mapOne: [
        { name: 'Egypt', coords: [26.8206, 30.8025] },
        { name: 'United Kingdom', coords: [55.3781, 3.436] },
        { name: 'United States', coords: [37.0902, -95.7129] },
    ],
    mapBasic: [],
    mapMarker: [locations[0]],
    mapMultiple: locations,
    mapCustom: locations.map((location, index) => ({
        ...location,
        style: {
            fill: ['#465fff', '#12b76a', '#f79009', '#7a5af8'][index],
            r: index === 0 ? 8 : 6,
        },
    })),
};
const mapInstances = [];

const applyMapTheme = () => {
    const regionFill = document.documentElement.classList.contains('dark') ? '#344054' : '#e4e7ec';
    mapInstances.forEach((map) => {
        Object.values(map.regions).forEach((region) => {
            region.element.setStyle('fill', regionFill);
        });
    });
};

const createMap = (id, markers) => {
    const container = document.getElementById(id);
    if (!container) return;

    const isDark = document.documentElement.classList.contains('dark');
    const map = new jsVectorMap({
        selector: `#${id}`,
        map: 'world',
        zoomButtons: true,
        zoomOnScroll: false,
        regionStyle: {
            initial: {
                fontFamily: 'Outfit',
                fill: isDark ? '#344054' : '#e4e7ec',
            },
            hover: {
                fillOpacity: 1,
                fill: '#465fff',
            },
        },
        markers,
        markerStyle: {
            initial: {
                strokeWidth: 1,
                stroke: '#ffffff',
                fill: '#465fff',
                fillOpacity: 1,
                r: 5,
            },
            hover: {
                fill: '#3641f5',
                fillOpacity: 1,
            },
        },
    });
    mapInstances.push(map);
};

export const initMap = () => {
    Object.entries(mapConfigs).forEach(([id, markers]) => createMap(id, markers));
    window.addEventListener('theme-changed', applyMapTheme);
};

export default initMap;
