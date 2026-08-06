class Carrousel {

    constructor(elemento, opciones = {}) {

        this._seccionCarrousel = typeof elemento === 'string' ? document.querySelector(elemento) : elemento;

        if (!this._seccionCarrousel) return;

        const datos = this._seccionCarrousel.dataset;

        const booleano = (valor, defecto) => valor === undefined ? defecto : valor === 'true';

        const intervalo = Number(datos.carrouselInterval);

        this._opciones = {
            autoplay: booleano(
                datos.carrouselAutoplay,
                true
            ),
            pauseButton: booleano(
                datos.carrouselPauseButton,
                false
            ),
            transition: datos.carrouselTransition === 'fade'
                ? 'fade'
                : 'slide',
            showDots: booleano(
                datos.carrouselShowDots,
                true
            ),
            touch: booleano(
                datos.carrouselTouch,
                false
            ),
            fullscreen: booleano(
                datos.carrouselFullscreen,
                false
            ),
            horizontal: booleano(
                datos.carrouselHorizontal,
                true
            ),
            interval: Number.isFinite(intervalo)
                && intervalo >= 1000
                ? intervalo
                : 3000,
            ...opciones
        };

        this._slider = this._seccionCarrousel.querySelector('[data-carrousel-slider]');

        this._imagenes = this._seccionCarrousel.querySelectorAll('[data-carrousel-item]');

        this._puntos = this._seccionCarrousel.querySelectorAll('[data-carrousel-punto]');

        this._contenedorPuntos = this._seccionCarrousel.querySelector('[data-carrousel-puntos]');

        this.playButton = this._seccionCarrousel.querySelector('[data-carrousel-pausa]');

        this._cantidadImagenes = this._imagenes.length;

        this.imagenActual = 0;
        this.intervalId = null;
        this.isPlaying = false;

        this.touchStartX = 0;
        this.touchEndX = 0;

        this._eventos = new AbortController();

        if (!this._slider || this._cantidadImagenes === 0) {
            this.ocultarControlesSinContenido();
            return;
        }

        this._seccionCarrousel.carrouselPaw?.destroy();

        this._seccionCarrousel.carrouselPaw = this;

        this.inicializarCarrousel();
        this.selectImage();
        this.inicializarBotonPausa();

        if (this._opciones.fullscreen) {
            this.habilitarImagenCompleta();
        }

        if (this._opciones.touch) {
            this.addTouchEvents();
        }

        if (this._opciones.autoplay) {
            this.startCarrousel();
        } else {
            this.stopCarrousel();
        }
    }

    static buscarElementos(contexto = document) {

        const elementos = [];

        if (contexto.matches?.('[data-carrousel]')) {
            elementos.push(contexto);
        }

        contexto.querySelectorAll?.(
            '[data-carrousel]'
        ).forEach((elemento) => {
            elementos.push(elemento);
        });

        return elementos;
    }

    static inicializarTodos(contexto = document) {
        Carrousel.buscarElementos(contexto).forEach(
            (elemento) => {
                new Carrousel(elemento);
            }
        );
    }

    static destruirTodos(contexto = document) {
        Carrousel.buscarElementos(contexto).forEach(
            (elemento) => {
                elemento.carrouselPaw?.destroy();
            }
        );
    }

    inicializarCarrousel() {

        if (!this._opciones.showDots && this._contenedorPuntos) {
            this._contenedorPuntos.hidden = true;
        }

        /*Si hay una cola imagen no mostrar el boton de reproduccion*/
        if (this._cantidadImagenes <= 1 && this.playButton) {
            this.playButton.hidden = true;
        }

        if (this._opciones.transition === 'fade') {
            this.moverSlider(0);
            return;
        }

        const porcentajeSlider = 100 * this._cantidadImagenes;

        const porcentajeImagen = 100 / this._cantidadImagenes;

        this._slider.style.display = 'flex';
        this._slider.style.flexWrap = 'nowrap';

        this._slider.style.flexDirection = this._opciones.horizontal ? 'row' : 'column';

        if (this._opciones.horizontal) {
            this._slider.style.width = `${porcentajeSlider}%`;
        } else {
            this._slider.style.height = `${porcentajeSlider}%`;
        }

        this._imagenes.forEach((imagen) => {
            imagen.style.flex = `0 0 ${porcentajeImagen}%`;

            if (this._opciones.horizontal) {
                imagen.style.width = `${porcentajeImagen}%`;
            } else {
                imagen.style.height = `${porcentajeImagen}%`;
            }
        });

        this.moverSlider(0);
    }

    startCarrousel() {
        
        if (this._cantidadImagenes <= 1 || this.intervalId !== null) {
            return;
        }

        this.isPlaying = true;

        this.intervalId = window.setInterval(
            () => this.nextImage(),
            this._opciones.interval
        );

        this.actualizarBotonPausa();
    }

    stopCarrousel() {
        if (this.intervalId !== null) {
            window.clearInterval(this.intervalId);
            this.intervalId = null;
        }

        this.isPlaying = false;

        this.actualizarBotonPausa();
    }

    nextImage() {
        this.moverSlider(this.imagenActual + 1);
    }

    moverSlider(posicion) {
    
        this.imagenActual = (posicion + this._cantidadImagenes) % this._cantidadImagenes;

        if (this._opciones.transition === 'fade') {
            this._imagenes.forEach(
                (imagen, indice) => {
                    imagen.classList.toggle(
                        'show',
                        indice === this.imagenActual
                    );
                }
            );
        } else {
            const desplazamiento = (100 / this._cantidadImagenes) * this.imagenActual;

            this._slider.style.transform = this._opciones.horizontal ? `translateX(-${desplazamiento}%)` : `translateY(-${desplazamiento}%)`;
        }

        this.pintarPunto(this._puntos, this.imagenActual);
    }

    selectImage() {
        if (!this._opciones.showDots) {
            return;
        }

        this._puntos.forEach(
            (punto, indice) => {
                punto.addEventListener(
                    'click',
                    () => this.moverSlider(indice),
                    {signal: this._eventos.signal}
                );
            }
        );
    }

    pintarPunto(puntos, indiceActivo) {
        if (!this._opciones.showDots) {
            return;
        }

        puntos.forEach(
            (punto, indice) => {
                punto.classList.toggle('activo', indice === indiceActivo);
            }
        );
    }

    inicializarBotonPausa() {
        if (!this._opciones.pauseButton || !this.playButton) {
            return;
        }

        this.playButton.addEventListener(
            'click',
            (evento) => {
                evento.preventDefault();

                this.togglePlayPause();
            },
            {
                signal: this._eventos.signal
            }
        );

        this.actualizarBotonPausa();
    }

    togglePlayPause() {
        if (this.isPlaying) {
            this.stopCarrousel();
        } else {
            this.startCarrousel();
        }
    }

    actualizarBotonPausa() {
        if (!this._opciones?.pauseButton || !this.playButton) {
            return;
        }

        this.playButton.innerHTML = this.isPlaying ? '&#10074;&#10074;' : '&#9658;';

        this.playButton.setAttribute(
            'aria-label',
            this.isPlaying ? 'Pausar carrusel de imágenes' : 'Reproducir carrusel de imágenes'
        );
    }

    habilitarImagenCompleta() {
        const modal = this._seccionCarrousel.querySelector('[data-carrousel-modal]');

        const modalImg = modal?.querySelector('[data-carrousel-modal-imagen]');

        const closeBtn = modal?.querySelector('[data-carrousel-modal-cerrar]');

        if (!modal || !modalImg || !closeBtn) {
            return;
        }

        const cerrarModal = () => {
            modal.style.display = 'none';
        };

        this._imagenes.forEach((elemento) => {
            
            const imagen = elemento.matches('img') ? elemento : elemento.querySelector('img');

            if (!imagen) {
                return;
            }

            imagen.addEventListener(
                'click',
                () => {
                    modal.style.display = 'block';
                    modalImg.src = imagen.src;
                    modalImg.alt = imagen.alt;
                },
                {
                    signal: this._eventos.signal
                }
            );
        });

        closeBtn.addEventListener(
            'click',
            cerrarModal,
            {
                signal: this._eventos.signal
            }
        );

        modal.addEventListener(
            'click',
            (evento) => {
                if (evento.target === modal) {
                    cerrarModal();
                }
            },
            {
                signal: this._eventos.signal
            }
        );
    }

    addTouchEvents() {
        if (this._cantidadImagenes <= 1) {
            return;
        }

        this._slider.addEventListener(
            'touchstart',
            (evento) => {
                this.touchStartX = evento.changedTouches[0].screenX;
                this.touchEndX = this.touchStartX;
            },
            {
                signal: this._eventos.signal
            }
        );

        this._slider.addEventListener(
            'touchmove',
            (evento) => {
                this.touchEndX = evento.changedTouches[0].screenX;
            },
            {
                signal: this._eventos.signal
            }
        );

        this._slider.addEventListener(
            'touchend',
            () => this.handleGesture(),
            {
                signal: this._eventos.signal
            }
        );
    }

    handleGesture() {
        const diferenciaX = this.touchStartX - this.touchEndX;

        if (diferenciaX > 50) {
            this.moverSlider(this.imagenActual + 1);
        } else if (diferenciaX < -50) {
            this.moverSlider(this.imagenActual - 1);
        }
    }

    ocultarControlesSinContenido() {
        if (this._contenedorPuntos) {
            this._contenedorPuntos.hidden = true;
        }

        if (this.playButton) {
            this.playButton.hidden = true;
        }
    }

    destroy() {
        this.stopCarrousel();
        this._eventos.abort();

        if (this._seccionCarrousel?.carrouselPaw === this) {
            delete this._seccionCarrousel.carrouselPaw;
        }
    }
}