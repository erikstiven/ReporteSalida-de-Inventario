
console.log('Archivo Inactividad');
//Traemos el tiempo de session de cada usuario

var empresa_ruc = localStorage.getItem("ruc_empresa_lubamaqui");
var tiempo_inactividad = localStorage.getItem(empresa_ruc + "_time_session_minutes");

//console.log('Tu tiempod de inactividad es: ' + tiempo_inactividad);


function alertSession() {
    Swal.fire({
        closeOnClickOutside: false,
        title: 'Alerta',
        text: "Su sesion expiro, por falta de actividad",
        type: 'info',
        // showCancelButton: true,
        confirmButtonColor: '#3085d6',
        // cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar'
    }).then((result) => {
        window.location.replace("../../");
    })
}

function alertTimer() {

    let timerInterval
    Swal.fire({
        title: 'Alerta',
        html: 'Su sesion esta a punto por expirar!..',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        showConfirmButton: false,
        timer: 30000,
        onBeforeOpen: () => {
            // $('.swal2-confirm').attr('id','btnConfirm');
            $('.swal2-cancel').attr('id', 'btnCancel');
            // Swal.showLoading()
            timerInterval = setInterval(() => {
                const content = Swal.getContent()
                if (content) {
                    var hora_fin_session = window.localStorage.getItem(empresa_ruc + '_hora_fin_session');
                    myDate = new Date();
                    hours = myDate.getHours();
                    minutes = myDate.getMinutes();
                    // controlamos el 0 antes del numero ya que nos tomara como: ejemplo  3 y eso representara 30 y no 03
                    if (minutes < 10) {
                        minutes = '0' + minutes;
                    }
                    // lo mimso para las horas
                    // controlamos el 0 antes del numero ya que nos tomara como: ejemplo  3 y eso representara 30 y no 03

                    var hora = hours + ':' + minutes;

                    var hora_actual = hora;
                    var hora_session = hora_fin_session;

                    console.log(hora_actual + ' - ' + hora_session);

                    if (hora_actual >= hora_session) {
                        // const b = content.querySelector('btnCancel');
                        const btnCancel = document.getElementById('btnCancel');
                        if (btnCancel) {
                            btnCancel.textContent = `Cancelar (${(Swal.getTimerLeft() / 1000).toFixed(0)})`
                        }

                    } else {
                        Swal.close();
                    }
                }
            }, 100)
        },
        onClose: () => {
            clearInterval(timerInterval)
        }
    }).then((result) => {


        /* Read more about handling dismissals below */
        if (result.dismiss === Swal.DismissReason.timer) {
            console.log('I was closed by the timer');
            $.post("../../Include/logout_inactivity.php", function (respuesta) {
                // console.log('destruir session', respuesta);
                if (respuesta) {
                    alertSession();
                }
            });

        }
        else {
            console.log('llama de nuevo el interval');
            executed = false;
            // interval_actividad();
        }
    })

}

var executed = false;
var awayCallback = function () {

    console.log(new Date().toTimeString() + ": Inactivo");
    if (!executed) {
        executed = true;
        alertTimer();
    }
    console.log(executed);
};

var awayBackCallback = function () {
    console.log(new Date().toTimeString() + ": Activo");
    console.log('estas activo');
};
var onVisibleCallback = function () {
    console.log(new Date().toTimeString() + ": Mirando la pagina");
};

var onHiddenCallback = function () {
    console.log(new Date().toTimeString() + ": No esta mirando la pagina");
};




window.onload = resetTimer;
document.onmousemove = resetTimer;
document.onkeypress = resetTimer;


function resetTimer() {
    var aleatorio = Math.random()
    localStorage.setItem('actividad', aleatorio);
}

if (window.localStorage) {
    window.addEventListener('storage', event => {
        if (event.storageArea === localStorage) {
            awayBackCallback;
            executed = false;
        }
    }, false);
}



// llamando a la libreria para la inactividad
// onHidden: cuando no esta mirando la pagina
// onVisible: cuando esta mirando la pagina
// onAway:  cuando se ecuentra inactivo
// onAwayBack: cuando esta activo
// awayTimeout: el timeout que va a contar a partir de que tiempo esta inactivo



var idle = new Idle({
    onHidden: onHiddenCallback,
    onVisible: onVisibleCallback,
    onAway: awayCallback,
    onAwayBack: awayBackCallback,
    awayTimeout: 60000 * tiempo_inactividad // los parentecis son los minutos, es decir, dos horas menos cinco minutos, 1000 = (un segundo), 60000 (un minuto)
}).start();
