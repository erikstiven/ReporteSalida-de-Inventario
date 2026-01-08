function calcularGeoreferencia(idEquipo) {
    var lat = null;
    var lon = null;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (objPosition)
        {
            lat = objPosition.coords.latitude;
            lon = objPosition.coords.longitude;
            
            $("#latitudId").val(lat);
            $("#longitudId").val(lon);

            if(idEquipo != null){
                guardarEquipo(idEquipo);
            }

        }, function (objPositionError)
        {
            switch (objPositionError.code)
            {
                case objPositionError.PERMISSION_DENIED:
                    alert("No se ha permitido el acceso a la posicion del usuario.");
                    break;
                case objPositionError.POSITION_UNAVAILABLE:
                    alert("No se ha podido acceder a la informacion de su posición.");
                    break;
                case objPositionError.TIMEOUT:
                    alert("El servicio ha tardado demasiado tiempo en responder.");
                    break;
                default:
                    alert("Error desconocido.");
            }
        }, {
            maximumAge: 75000,
            timeout: 15000
        });
    } else {
        alert("Su navegador no soporta la API de geolocalizacion.");
        $("#longitudId").val("");
        $("#latitudId").val("");
    }
}