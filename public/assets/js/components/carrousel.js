class Carrousel {
    constructor(clase) {
        this._clase = clase;
        this._seccionCarrousel = document.querySelector(this._clase);
        this._imagenes = this._seccionCarrousel.querySelectorAll('.carousel-img');
        this._cantidadImagenes = this._imagenes.length;
        this._puntos = this._seccionCarrousel.querySelectorAll('.punto');

        this.imagenActual = 0; // Índice de la imagen actual
        this.touchStartX = 0;
        this.touchEndX = 0;

        this.inicializarCarrousel();
        this.startCarrousel();
        this.selectImage();
        this.habilitarImagenCompleta();
        this.addTouchEvents(); // Añadimos eventos touch
    }

    inicializarCarrousel() { 
        let porcentajeSlider = 100 * this._cantidadImagenes;

        let slider = this._seccionCarrousel.querySelector('.slider');
        slider.style.width = `${porcentajeSlider}%`;

        this._imagenes.forEach((imagen) => {
            imagen.style.width = `calc(100% / ${this._cantidadImagenes})`;
        });
    }

    startCarrousel() {
        let time = 3000;
        let slider = document.querySelector('.slider');
        
        setInterval(() => {
            this.moverSlider(this.imagenActual, slider);
            this.pintarPunto(this._puntos, this.imagenActual);

            this.imagenActual = (this.imagenActual + 1) % this._cantidadImagenes; // Ciclo infinito
        }, time);
    }

    moverSlider(posicion, slider) {
        let porcentajeDesplazamiento = (100 / this._cantidadImagenes) * posicion;
        slider.style.transform = `translateX(-${porcentajeDesplazamiento}%)`;
    }

    selectImage() {
        let slider = document.querySelector('.slider');

        this._puntos.forEach((puntoParticular, i) => {
            puntoParticular.addEventListener('click', () => {
                this.imagenActual = i;  // Actualizamos el índice de la imagen actual
                this.moverSlider(i, slider);  // Movemos el slider a la imagen seleccionada
                this.pintarPunto(this._puntos, i);
            });
        });
    }

    pintarPunto(puntos, i) {
        puntos.forEach((puntoParticular) => {
            puntoParticular.classList.remove('activo');
        });
        puntos[i].classList.add('activo');
    }

    habilitarImagenCompleta() {
        let modal = document.createElement('div');
        modal.id = 'fullscreenModal';
        modal.classList.add('fullscreen-modal');

        let closeBtn = document.createElement('span');
        closeBtn.classList.add('close');
        closeBtn.innerHTML = '&times;';

        let modalImg = document.createElement('img');
        modalImg.classList.add('modal-content');
        modalImg.id = 'fullscreenImage';

        modal.appendChild(closeBtn);
        modal.appendChild(modalImg);
        document.body.appendChild(modal);

        this._imagenes.forEach((imagen) => {
            imagen.addEventListener('click', () => {
                modal.style.display = 'block';
                modalImg.src = imagen.src;
            });
        });

        closeBtn.onclick = () => {
            modal.style.display = 'none';
        }

        modal.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    }

    // Nuevos eventos táctiles
    addTouchEvents() {
        const slider = this._seccionCarrousel.querySelector('.slider');

        // Capturar cuando el usuario empieza a tocar
        slider.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
        });

        // Capturar cuando el usuario mueve el dedo
        slider.addEventListener('touchmove', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
        });

        // Capturar cuando el usuario termina de tocar
        slider.addEventListener('touchend', () => {
            this.handleGesture(slider);
        });
    }

    handleGesture(slider) {
        // Calculamos la diferencia entre donde empezó y terminó el toque
        const diffX = this.touchStartX - this.touchEndX;

        // Si la diferencia es significativa, decidimos si mover a la izquierda o a la derecha
        if (diffX > 50) {
            // Deslizó a la izquierda (mostrar siguiente imagen)
            this.imagenActual = (this.imagenActual + 1) % this._cantidadImagenes;
        } else if (diffX < -50) {
            // Deslizó a la derecha (mostrar imagen anterior)
            this.imagenActual = (this.imagenActual - 1 + this._cantidadImagenes) % this._cantidadImagenes;
        }

        // Movemos el slider y actualizamos los puntos
        this.moverSlider(this.imagenActual, slider);
        this.pintarPunto(this._puntos, this.imagenActual);
    }
}
