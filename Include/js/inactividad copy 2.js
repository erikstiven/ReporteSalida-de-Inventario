(function () {

    var Idle;

    if (!document.addEventListener) {
        if (document.attachEvent) {
            document.addEventListener = function (event, callback, useCapture) {
                return document.attachEvent("on" + event, callback, useCapture);
            };
        } else {
            document.addEventListener = function () {
                return {};
            };
        }
    }

    if (!document.removeEventListener) {
        if (document.detachEvent) {
            document.removeEventListener = function (event, callback) {
                return document.detachEvent("on" + event, callback);
            };
        } else {
            document.removeEventListener = function () {
                return {};
            };
        }
    }

    "use strict";

    Idle = {};

    Idle = (function () {
        Idle.isAway = false;

        Idle.awayTimeout = 3000;

        Idle.awayTimestamp = 0;

        Idle.awayTimer = null;

        Idle.onAway = null;

        Idle.onAwayBack = null;

        Idle.onVisible = null;

        Idle.onHidden = null;

        function Idle(options) {
            var activeMethod, activity;
            if (options) {
                this.awayTimeout = parseInt(options.awayTimeout, 10);
                this.onAway = options.onAway;
                this.onAwayBack = options.onAwayBack;
                this.onVisible = options.onVisible;
                this.onHidden = options.onHidden;
            }
            activity = this;
            activeMethod = function () {
                return activity.onActive();
            };
            window.addEventListener('click', activeMethod);
            window.addEventListener('mousemove', activeMethod);
            window.addEventListener('mouseenter', activeMethod);
            window.addEventListener('keydown', activeMethod);
            window.addEventListener('scroll', activeMethod);
            window.addEventListener('mousewheel', activeMethod);
            window.addEventListener('touchmove', activeMethod);
            window.addEventListener('touchstart', activeMethod);
        }

        Idle.prototype.onActive = function () {
            var aleatorio = Math.random()
            localStorage.setItem('actividad', aleatorio);
            this.awayTimestamp = new Date().getTime() + this.awayTimeout;
            if (this.isAway) {
                if (this.onAwayBack) {
                    this.onAwayBack();
                }
                this.start();
            }
            this.isAway = false;
            return true;
        };

        Idle.prototype.start = function () {
            var activity;
            if (!this.listener) {
                this.listener = (function () {
                    return activity.handleVisibilityChange();
                });
                document.addEventListener("visibilitychange", this.listener, false);
                document.addEventListener("webkitvisibilitychange", this.listener, false);
                document.addEventListener("msvisibilitychange", this.listener, false);
            }
            this.awayTimestamp = new Date().getTime() + this.awayTimeout;
            if (this.awayTimer !== null) {
                clearTimeout(this.awayTimer);
            }
            activity = this;
            this.awayTimer = setTimeout((function () {
                return activity.checkAway();
            }), this.awayTimeout + 100);
            return this;
        };

        Idle.prototype.stop = function () {
            if (this.awayTimer !== null) {
                clearTimeout(this.awayTimer);
            }
            if (this.listener !== null) {
                document.removeEventListener("visibilitychange", this.listener);
                document.removeEventListener("webkitvisibilitychange", this.listener);
                document.removeEventListener("msvisibilitychange", this.listener);
                this.listener = null;
            }
            return this;
        };

        Idle.prototype.setAwayTimeout = function (ms) {
            this.awayTimeout = parseInt(ms, 10);
            return this;
        };

        Idle.prototype.checkAway = function () {
            var activity, t;
            t = new Date().getTime();
            if (t < this.awayTimestamp) {
                this.isAway = false;
                activity = this;
                this.awayTimer = setTimeout((function () {
                    return activity.checkAway();
                }), this.awayTimestamp - t + 100);
                return;
            }
            if (this.awayTimer !== null) {
                clearTimeout(this.awayTimer);
            }
            this.isAway = true;
            if (this.onAway) {
                return this.onAway();
            }
        };

        Idle.prototype.handleVisibilityChange = function () {
            if (document.hidden || document.msHidden || document.webkitHidden) {
                if (this.onHidden) {
                    return this.onHidden();
                }
            } else {
                if (this.onVisible) {
                    return this.onVisible();
                }
            }
        };

        return Idle;

    })();

    if (typeof define === 'function' && define.amd) {
        define([], Idle);
    } else if (typeof exports === 'object') {
        module.exports = Idle;
    } else {
        window.Idle = Idle;
    }

}).call(this);





console.log('Archivo Inactividad');
//Traemos el tiempo de session de cada usuario
var tiempo_inactividad = localStorage.getItem("time_session_minutes");
var window_name = localStorage.getItem("name_window");
//console.log('Tu tiempod de inactividad es: ' + tiempo_inactividad);

/*
if (window_name) {
    alert('Has refrescado la pagina o has abierto el proyecto en otra pestaña, ten cuidado con los procesos en ejecucion');
};
*/
localStorage.setItem('name_window', 'ventana_unica');


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


            if (window.localStorage) {
                window.addEventListener('storage', event => {
                    if (event.storageArea === localStorage) {
                        Swal.close();
                    }
                }, false);
            }

            // Swal.showLoading()
            timerInterval = setInterval(() => {

                const content = Swal.getContent()
                if (content) {
                    // const b = content.querySelector('btnCancel');
                    const btnCancel = document.getElementById('btnCancel');
                    if (btnCancel) {
                        btnCancel.textContent = `Cancelar (${(Swal.getTimerLeft() / 1000).toFixed(0)})`
                    }
                }
            }, 100)
        },
        onClose: () => {
            clearInterval(timerInterval)
        }
    }).then((result) => {

        /* Read more about handling dismissals below */
        if (result.dismiss === Swal.DismissReason) {
            console.log('I was closed by the');
            $.post("../../Include/logout_inactivity.php", function (respuesta) {
                // console.log('destruir session', respuesta);
                if (respuesta) {
                    alertSession();
                }
            });

        }
        else {
            console.log('llama de nuevo el interval2');
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


// llamando a la libreria para la inactividad
// onHidden: cuando no esta mirando la pagina
// onVisible: cuando esta mirando la pagina
// onAway:  cuando se ecuentra inactivo
// onAwayBack: cuando esta activo
// awayTimeout: el timeout que va a contar a partir de que tiempo esta inactivo


idle = new Idle({
    onHidden: onHiddenCallback,
    onVisible: onVisibleCallback,
    onAway: awayCallback,
    onAwayBack: awayBackCallback,
    awayTimeout: 60000 * tiempo_inactividad // los parentecis son los minutos, es decir, dos horas menos cinco minutos, 1000 = (un segundo), 60000 (un minuto)
}).start();
