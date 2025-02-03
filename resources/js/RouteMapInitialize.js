document.addEventListener(
    'alpine:init',
    () => {
        Alpine.data(
            'mapComponent',
            () => ({
                initMap() {
                    var chicago = new google.maps.LatLng(41.850033, -87.6500523);
                    var mapOptions = {
                        zoom: 7,
                        center: chicago
                    }
                    var map = new google.maps.Map(document.getElementById('map'), mapOptions);
                    console.log("OPA");
                }
            })
        )
    }
)
