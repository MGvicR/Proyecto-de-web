document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('map');

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const map = L.map('map').setView([19.4326, -99.1332], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const zonas = [
        { lat: 19.4195, lng: -99.1620, label: 'Roma - Condesa' },
        { lat: 19.3467, lng: -99.1617, label: 'Coyoacán' },
        { lat: 19.4338, lng: -99.1946, label: 'Polanco' },
        { lat: 19.3910, lng: -99.1420, label: 'Del Valle' },
        { lat: 19.4840, lng: -99.1270, label: 'Gustavo A. Madero' },
    ];

    zonas.forEach((punto) => {
        L.marker([punto.lat, punto.lng]).addTo(map).bindPopup(punto.label);
    });
});
