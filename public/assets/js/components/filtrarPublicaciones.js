class filtrarPublicaciones {
    constructor() {

        this.formularios = document.querySelectorAll('.form-filtros');
        this.listado = document.querySelector('.publicaciones-list');
        this.estadoListado = document.getElementById('estado-listado');

        this.loader = document.getElementById('loader-publicaciones');

        this.solicitudActual = null;
        this.dialogoConfirmacion = document.getElementById('dialog-confirmar-gestion');
        this.mensajeConfirmacion = document.getElementById('mensaje-confirmacion-propiedad');

        this.botonConfirmar = this.dialogoConfirmacion?.querySelector('[data-dialog-confirmar]');

        this.botonCancelar = this.dialogoConfirmacion?.querySelector('[data-dialog-cancelar]');

        this.formularioPendiente = null;
        this.botonOrigenConfirmacion = null;

        if (!this.listado || this.formularios.length === 0) {
            return;
        }

        this.formularios.forEach(
            (formulario) => {
                formulario.addEventListener(
                    'submit',
                    (evento) => {
                        evento.preventDefault();

                        const url = this.construirUrlFormulario(formulario);

                        this.cargarListado(url, true);
                    }
                );

                formulario.addEventListener(
                    'reset',
                    (evento) => {
                        evento.preventDefault();

                        const url = new URL(formulario.action, window.location.origin);

                        this.cargarListado(url.toString(), true);
                    }
                );
            }
        );

        this.listado.addEventListener(
            'click',
            (evento) => {
                const enlace = evento.target.closest('a[data-listado-link]');

                if (!enlace) {
                    return;
                }

                const url = new URL(enlace.href, window.location.origin);

                if (url.origin !== window.location.origin) {
                    return;
                }

                evento.preventDefault();

                this.cargarListado(url.toString(), true);
            }
        );

        this.listado.addEventListener(
            'submit',
            (evento) => {
                const formulario = evento.target.closest('form[data-confirm-message]');

                if (!formulario || !this.dialogoConfirmacion) {
                    return;
                }

                evento.preventDefault();

                const mensaje = (formulario.dataset.confirmMessage || '').replace(/\s+/g, ' ').trim();

                this.formularioPendiente = formulario;

                this.botonOrigenConfirmacion = evento.submitter || null;

                this.mensajeConfirmacion.textContent = mensaje;

                const textoAccion = evento.submitter?.textContent.trim() || 'Confirmar';

                const esEliminacion = formulario.action.endsWith('/mis_publicaciones/eliminar');

                this.botonConfirmar.textContent = textoAccion;

                this.botonConfirmar.classList.toggle('dialogo-confirmacion-aceptar--peligro', esEliminacion);

                this.dialogoConfirmacion.showModal();

                this.botonCancelar?.focus();
            }
        );

        this.botonCancelar?.addEventListener(
            'click',
            () => this.cerrarDialogoConfirmacion()
        );

        this.botonConfirmar?.addEventListener(
            'click',
            () => {
                if (!this.formularioPendiente) {
                    return;
                }

                const formulario = this.formularioPendiente;

                this.formularioPendiente = null;
                this.botonOrigenConfirmacion = null;

                this.dialogoConfirmacion.close();

                HTMLFormElement.prototype.submit.call(formulario);
            }
        );

        this.dialogoConfirmacion?.addEventListener(
            'cancel',
            (evento) => {
                evento.preventDefault();

                this.cerrarDialogoConfirmacion();
            }
        );

        this.dialogoConfirmacion?.addEventListener(
            'click',
            (evento) => {
                if (evento.target === this.dialogoConfirmacion) {
                    this.cerrarDialogoConfirmacion();
                }
            }
        );

        window.addEventListener(
            'popstate',
            () => {
                this.cargarListado(window.location.href, false);
            }
        );

        this.sincronizarFormularios(new URL(window.location.href).searchParams);
    }

    cerrarDialogoConfirmacion() {
        if (!this.dialogoConfirmacion?.open) {
            return;
        }

        const botonParaRecuperarFoco = this.botonOrigenConfirmacion;

        this.formularioPendiente = null;
        this.botonOrigenConfirmacion = null;

        this.dialogoConfirmacion.close();

        botonParaRecuperarFoco?.focus();
    }

    construirUrlFormulario(formulario) {
        

        const url = new URL(formulario.action, window.location.origin);
        const parametros = new URLSearchParams(new FormData(formulario));

        const zona = parametros.get('zona');
        const precio = parametros.get('precio');

        if (!zona || zona.trim() === '') {
            parametros.delete('zona');
        } else {
            parametros.set('zona', zona.trim());
        }

        if (!precio || Number(precio) <= 0) {
            parametros.delete('precio');
        }

        /*Al aplicar nuevos filtros siempre se vuelve a la primera página.*/
        parametros.delete('pagina');

        url.search = parametros.toString();

        return url.toString();
    }

    async cargarListado(url, agregarAlHistorial) {

        if (this.solicitudActual) {
            this.solicitudActual.abort();
        }

        const controlador = new AbortController();

        this.solicitudActual = controlador;

        this.mostrarCarga(true);
        this.limpiarEstado();

        try {

            const respuesta = await fetch(
                url,
                {
                    method: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    signal: controlador.signal
                }
            );

            if (!respuesta.ok) {
                throw new Error('El servidor respondió con estado ' + respuesta.status);
            }

            const html = await respuesta.text();

            if (html.trim() === '') {
                throw new Error('El servidor devolvió una respuesta vacía');
            }

            /*Solo se reemplaza el listado si la respuesta fue correcta*/
            this.listado.innerHTML = html;

            const urlFinal = new URL(respuesta.url || url, window.location.origin);

            if (agregarAlHistorial) {
                const urlActual = window.location.pathname + window.location.search;

                const nuevaUrl = urlFinal.pathname + urlFinal.search;

                if (urlActual !== nuevaUrl) {
                    history.pushState({}, '', nuevaUrl);
                }
            }

            this.sincronizarFormularios(urlFinal.searchParams);

            this.reiniciarCarruseles();

            this.llevarAlInicioDelListado();

        } catch (error) {
            
            if (error.name === 'AbortError') {
                return;
            }

            console.error('Error al cargar el listado de publicaciones:', error);

            this.mostrarError('No se pudieron cargar las propiedades. ' + 'Intentá nuevamente.');

        } finally {
            
            if (this.solicitudActual === controlador) {
                this.solicitudActual = null;
                this.mostrarCarga(false);
            }
        }
    }

    sincronizarFormularios(parametros) {

        const zona = parametros.get('zona') || '';
        const precio = parametros.get('precio') || '0';
        const tipos = new Set(this.obtenerValoresArray(parametros, 'tipo'));
        const instalaciones = new Set(this.obtenerValoresArray(parametros, 'instalaciones'));

        
        this.formularios.forEach(
            (formulario) => {
                const campoZona = formulario.querySelector('[name="zona"]');
                const campoPrecio = formulario.querySelector('[name="precio"]');

                const valorPrecio = formulario.querySelector('.precio-valor');

                if (campoZona) {
                    campoZona.value = zona;
                }

                if (campoPrecio) {
                    campoPrecio.value = precio;
                }

                if (valorPrecio) {
                    valorPrecio.textContent
                        = Number(precio) > 0
                        ? 'USD 0 - USD '
                            + this.formatearPrecio(
                                precio
                            )
                        : '-';
                }

                formulario.querySelectorAll(
                    'input[name="tipo[]"]'
                ).forEach((checkbox) => {
                    checkbox.checked = tipos.has(
                        checkbox.value
                    );
                });

                formulario.querySelectorAll(
                    'input[name="instalaciones[]"]'
                ).forEach((checkbox) => {
                    checkbox.checked
                        = instalaciones.has(
                            checkbox.value
                        );
                });
            }
        );
    }

    obtenerValoresArray(parametros, nombre) {

        const valores = [];

        /*Acepta tipo[]=1 y la forma producida por php tipo[0]=1*/
        for (const [clave, valor] of parametros.entries()) {
            if (clave === `${nombre}[]` || clave.startsWith(`${nombre}[`)) {
                valores.push(valor);
            }
        }
        return valores;
    }

    reiniciarCarruseles() {

        if (typeof CarrouselPausa !== 'function') {
            return;
        }

        document.querySelectorAll(
            '.publicacion-item'
        ).forEach((publicacion) => {
            new CarrouselPausa(publicacion);
        });
    }

    llevarAlInicioDelListado() {

        const tituloListado = document.querySelector('.h2-titulo-publicaciones');

        if (!tituloListado) {
            return;
        }

        tituloListado.scrollIntoView({behavior: 'smooth', block: 'start'});

    }

    mostrarCarga(estaCargando) {

        if (this.loader) {
            this.loader.style.display = estaCargando ? 'flex' : 'none';
        }

        this.listado.setAttribute('aria-busy', estaCargando ? 'true' : 'false');
    }

    mostrarError(mensaje) {

        if (!this.estadoListado) {
            return;
        }

        this.estadoListado.textContent = mensaje;

        this.estadoListado.classList.add('estado-listado--error');

        this.estadoListado.setAttribute('role', 'alert');
    }

    limpiarEstado() {

        if (!this.estadoListado) {
            return;
        }

        this.estadoListado.textContent = '';
        this.estadoListado.classList.remove('estado-listado--error');
        this.estadoListado.setAttribute('role', 'status');
    }

    formatearPrecio(valor) {
        return new Intl.NumberFormat('es-AR').format(valor);
    }
}