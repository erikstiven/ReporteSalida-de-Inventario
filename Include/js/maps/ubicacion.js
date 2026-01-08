function mostrarUbicacionContrato(data, zoom) {
    var map;
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap'
    };
                    
    // Display a map on the web page
    map = new google.maps.Map(document.getElementById('map'), mapOptions);
    map.setTilt(50);
        
    // Multiple markers location, latitude, and longitude
	var markers = [];
	var infoWindowContent = [];
		for (j = 0; j < data.length; j++) {
			id = data[j]['id'];
			nombre = data[j]['nombre'];
			codigo = data[j]['codigo'];
			direccion = data[j]['direccion'];
			referencia = data[j]['referencia'];
			deuda = data[j]['deuda'];
			lat = data[j]['lat'];
			lon = data[j]['lon'];
			
					
			markers.push([nombre, lat, lon]);
			infoWindowContent.push(['<div class="info_content" align="center">' +
									'<h4 class="text-primary">'+nombre+'</h4>' +
									'<p class="text-weight: bold;">'+codigo+'</p>' +
									'<p class="text-weight: bold;">'+direccion+'</p>' +
									'<p class="text-weight: bold;">'+referencia+'</p>' +
									'<p class="text-weight: bold; text-danger">'+deuda+'</p>' +
									'</div>']);
			
		}
   
                        
    // Add multiple markers to map
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    
    // Place each marker on the map  
    for( i = 0; i < markers.length; i++ ) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });
        
        // Add info window to marker    
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infoWindow.setContent(infoWindowContent[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));

        // Center the map to fit all markers on the screen
        map.fitBounds(bounds);
    }

    // Set zoom level
    var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
        this.setZoom(zoom);
        google.maps.event.removeListener(boundsListener);
    });   
}

function mostrarMarcadorUbicacion(lat, lon, zoom) {
    var map;
    var nombre = 'Ubicacion: ' + lat + ', ' + lon;
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap'
    };
                    
    // Display a map on the web page
    map = new google.maps.Map(document.getElementById('map'), mapOptions);
    map.setTilt(50);
        
    // Multiple markers location, latitude, and longitude
    var markers = [];
    markers.push([nombre, lat, lon]);
    var infoWindowContent = [];
    infoWindowContent.push(['<div class="info_content" align="center">' +
                            '<p class="text-primary">'+nombre+'</p>'
                            ]);
                        
    // Add multiple markers to map
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    
    // Place each marker on the map  
    for( i = 0; i < markers.length; i++ ) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });
        
        // Add info window to marker    
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infoWindow.setContent(infoWindowContent[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));

        // Center the map to fit all markers on the screen
        map.fitBounds(bounds);
    }

    // Set zoom level
    var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
        this.setZoom(zoom);
        google.maps.event.removeListener(boundsListener);
    });
    
}