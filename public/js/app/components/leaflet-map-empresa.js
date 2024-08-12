export default function leafletMapEmpresa() {
    return {
        init: function () {
            console.log('start leafletMapEmpresa');

            const map = L.map(this.$refs.leafletMap.id).setView([51.505, -0.09], 13);

            const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
        }
    }
}
