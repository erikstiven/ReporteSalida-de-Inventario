// funcionesAjax.js
function enviaDatosBack(datos, exitoCallback, errorCallback) {
    $.ajax({
        url: '../../Include/Clases/Isp/Webservice2.class.php',
        type: 'POST',
        dataType: 'json',
        data: datos,
        success: exitoCallback,
        error: errorCallback
    });
}
