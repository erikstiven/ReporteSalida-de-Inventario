<?php

/**
 * CONTROL DE SESSION
 * CONTROL INACTIVIDAD INDEX
 */
$tiempo_session = $_SESSION['U_TIEMPO_SESION'];
$empresa_ruc = $_SESSION['EMPRESA_RUC'];
?>


<script>
    (function() {
        var Idle;
        if (!document.addEventListener) {
            if (document.attachEvent) {
                document.addEventListener = function(event, callback, useCapture) {
                    return document.attachEvent("on" + event, callback, useCapture);
                };
            } else {
                document.addEventListener = function() {
                    return {};
                };
            }
        }

        if (!document.removeEventListener) {
            if (document.detachEvent) {
                document.removeEventListener = function(event, callback) {
                    return document.detachEvent("on" + event, callback);
                };
            } else {
                document.removeEventListener = function() {
                    return {};
                };
            }
        }

        "use strict";

        Idle = {};

        Idle = (function() {
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
                activeMethod = function() {
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

            Idle.prototype.onActive = function() {
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

            Idle.prototype.start = function() {
                var activity;
                if (!this.listener) {
                    this.listener = (function() {
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
                this.awayTimer = setTimeout((function() {
                    return activity.checkAway();
                }), this.awayTimeout + 100);
                return this;
            };

            Idle.prototype.stop = function() {
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

            Idle.prototype.setAwayTimeout = function(ms) {
                this.awayTimeout = parseInt(ms, 10);
                return this;
            };

            Idle.prototype.checkAway = function() {
                var activity, t;
                t = new Date().getTime();
                if (t < this.awayTimestamp) {
                    this.isAway = false;
                    activity = this;
                    this.awayTimer = setTimeout((function() {
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

            Idle.prototype.handleVisibilityChange = function() {
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



    let tiempo_session_get = "<?= $tiempo_session ?>";
    let empresa_ruc = "<?= $empresa_ruc ?>";



    // Calcula  el tiempo de session en horas
    Date.prototype.addMins = function(m) {
        this.setTime(this.getTime() + (m * 60 * 1000));
        return this;
    }

    function calcularHora(e) {
        let numeroalazar = tiempo_session_get;
        let fecha = new Date();
        let formateador = new Intl.DateTimeFormat("es-ES", {
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
        });
        tiempo_en_horas = formateador.format(fecha.addMins(numeroalazar));
        window.localStorage.setItem(empresa_ruc + '_hora_fin_session', tiempo_en_horas);
        window.localStorage.setItem(empresa_ruc + '_estado_session', 'Activo');
        // console.log("Hora cierre sesion: " + tiempo_en_horas);
    }

    calcularHora();


    let mostrar_mensaje = 0;
    $(document).ready(function() {
        $(this).mousemove(function(e) {
            // console.log("mouse");
            Date.prototype.addMins = function(m) {
                this.setTime(this.getTime() + (m * 60 * 1000));
                return this;
            }
            let numeroalazar = tiempo_session_get
            let fecha = new Date();
            let formateador = new Intl.DateTimeFormat("es-ES", {
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
            });

            window.localStorage.setItem(empresa_ruc + '_hora_fin_session', formateador.format(fecha.addMins(numeroalazar)));
            var estado_session = window.localStorage.getItem(empresa_ruc + '_estado_session');
            if (estado_session == 'Inactivo') {
                if (mostrar_mensaje == 0) {
                    Swal.fire({
                        closeOnClickOutside: false,
                        title: 'Alerta',
                        text: "Su sesion expiro, por falta de actividad -",
                        type: 'info',
                        // showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        // cancelButtonColor: '#d33',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        top.location.reload(true);
                    })
                    top.location.reload(true);
                }
                mostrar_mensaje = 1;
            }

            // console.log(formateador.format(fecha.addMins(numeroalazar)));
        });
    })






    function alertSession() {
        Swal.fire({
            closeOnClickOutside: false,
            title: 'Alerta',
            text: "Su sesion expiro, por falta de actividad -",
            type: 'info',
            // showCancelButton: true,
            confirmButtonColor: '#3085d6',
            // cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            top.location.reload(true);
            // window.location.replace("../../");
            // var url = window.location.href;
            // console.log(url);
            // url = (url.indexOf('?') > -1) ? url += '&logout=ON' : url += '?logout=ON';

            // window.location.href = url;

            // if (result.value) {

            // }
        })
        window.localStorage.setItem(empresa_ruc + '_estado_session', 'Inactivo');
        top.location.reload(true);
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
            if (result.dismiss === Swal.DismissReason.timer) {
                // console.log('I was closed by the timer');
                $.post("../../Include/logout_inactivity.php", function(respuesta) {
                    // console.log('destruir session', respuesta);
                    if (respuesta) {
                        alertSession();
                    }
                });

            } else {
                // console.log('llama de nuevo el interval');
                executed = false;
                // interval_actividad();
            }
        })
    }

    var executed = false;
    var awayCallback = function() {


        var hora_fin_session = window.localStorage.getItem(empresa_ruc + '_hora_fin_session');
        myDate = new Date();
        hours = myDate.getHours();
        minutes = myDate.getMinutes();
        seconds = myDate.getSeconds() + 3;
        // controlamos el 0 antes del numero ya que nos tomara como: ejemplo  3 y eso representara 30 y no 03
        if (minutes < 10) {
            minutes = '0' + String(minutes);
        }

        if (seconds < 10) {
            seconds = '0' + seconds;
        }
        var hora = hours + ':' + minutes + ':' + seconds;

        var hora_actual = hora;
        var hora_session = hora_fin_session;

        // console.log(hora_actual + ' - ' + hora_session);

        if (hora_actual >= hora_session) {
            // console.log(new Date().toTimeString() + ": Inactivo");
            if (!executed) {
                executed = true;
                alertTimer();
            }
        } else {
            executed = false;
        }
        // console.log(executed);
    };

    var awayBackCallback = function() {
        // console.log(new Date().toTimeString() + ": Activo");
    };
    var onVisibleCallback = function() {
        // console.log(new Date().toTimeString() + ": Mirando la pagina");
    };

    var onHiddenCallback = function() {
        // console.log(new Date().toTimeString() + ": No esta mirando la pagina");
    };





    /* Mensaje cuando esta duplicada la pestaña */
    /*
    window.addEventListener('beforeunload', function(e) {
        sessionStorage.tabsopened--;
    });

    $(document).ready(function() {
        if (sessionStorage.tabsopened == 'NaN' || sessionStorage.tabsopened == null || sessionStorage.tabsopened == 'undefined') {
            sessionStorage.setItem('tabsopened', 0);
        }
        sessionStorage.tabsopened++;

        if (sessionStorage.tabsopened >= 2) {
            alert("Pestaña Duplicada. Si duplicas la pestaña es posible que pierdas la informacion de la pestaña anterior. Por favor evita duplicar la pestaña y de preferencia usa el navegador Chrome. !!!");
            window.location.reload();
            sessionStorage.setItem('tabsopened', 0);
        }

    });
    */
    /* Mensaje cuando esta duplicada la pestaña */
    


    /*
    // Cuando el usuario pierde el foco o sale de tu pestaña (sitio web)
    window.addEventListener("blur", () => {
        //document.title = "Breakup";
        window.location.reload();
    });

    // Cuando el enfoque del usuario vuelve a tu pestaña (sitio web) nuevamente
    window.addEventListener("focus", () => {
        //document.title = "Patch Up";
        console.log(window.location.href);
        // window.location.reload();
    });
    */










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
        awayTimeout: 30000 * tiempo_session_get * 2 // los parentecis son los minutos, es decir, dos horas menos cinco minutos
    }).start();
</script>