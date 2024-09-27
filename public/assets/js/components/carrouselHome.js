class CarrouselHome {
    constructor(clase) {
        this._clase = clase;
        this._seccionCarrousel = document.querySelector(this._clase);
        this._imagenes = this._seccionCarrousel.querySelectorAll('.carousel-img');
        this._cantidadImagenes = this._imagenes.length;
        this._puntos = this._seccionCarrousel.querySelectorAll('.punto');

        this.imagenActual = 0; // Índice de la imagen actual

        this.inicializarCarrousel();
        this.selectImage();
        // this.startAutomaticSlider(); // Llama al método para el desplazamiento automático
    }

    inicializarCarrousel() { 
        let slider = this._seccionCarrousel.querySelector('.home-carousel-wrapper');
        let anchoPorImagen = 100; // Cada imagen ocupará el 100%
        let anchoTotal = anchoPorImagen * this._cantidadImagenes; // Calcula el ancho total
    
        slider.style.width = `${anchoTotal}%`; // Establece el ancho total en CSS
    
        this._imagenes.forEach((imagen) => {
            imagen.style.width = `${anchoPorImagen}%`; // Asegura que cada imagen ocupe el 100%
        });
    
        // Establecer el desplazamiento inicial según la cantidad de imágenes
        let desplazamientoInicial;
    
        if (this._cantidadImagenes === 5) {
            desplazamientoInicial = 40; // La primera imagen arranca en 40%
        } else if (this._cantidadImagenes === 4) {
            desplazamientoInicial = 38; // La primera imagen arranca en 38%
        } else if (this._cantidadImagenes === 3) {
            desplazamientoInicial = 33; // La primera imagen arranca en 33%
        } else if (this._cantidadImagenes === 2) {
            desplazamientoInicial = 50; // La primera imagen arranca en 50%
        } else {
            desplazamientoInicial = 0; // Para otros casos, inicia en 0%
        }
    
        // Mueve el slider a la posición inicial ajustada
        slider.style.transform = `translateX(-${desplazamientoInicial}%)`;
        this.moverSlider(this.imagenActual); // Mueve el slider a la posición inicial
    }
    
    moverSlider(posicion) {
        let slider = this._seccionCarrousel.querySelector('.home-carousel-wrapper');

        // Ajusta el desplazamiento basado en la cantidad de imágenes
        let desplazamiento = 0;

        if (this._cantidadImagenes === 5) {
            desplazamiento = -(40 - (posicion * 20)); // De 40% a -40%
        } else if (this._cantidadImagenes === 4) {
            desplazamiento = -(38 - (posicion * 19)); // De 38% a -38%
        } else if (this._cantidadImagenes === 3) {
            desplazamiento = -(33 - (posicion * 16.5)); // De 33% a -33%
        } else if (this._cantidadImagenes === 2) {
            desplazamiento = -(50 - (posicion * 50)); // De 50% a -50%
        } else {
            desplazamiento = -(posicion * 100); // Para otras cantidades de imágenes
        }

        slider.style.transform = `translateX(${desplazamiento}%)`;

        this.pintarPunto(this._puntos, posicion); // Actualiza el punto activo
    }

    selectImage() {
        this._puntos.forEach((puntoParticular, i) => {
            puntoParticular.addEventListener('click', () => {
                this.imagenActual = i;  // Actualizamos el índice de la imagen actual
                this.moverSlider(this.imagenActual);  // Movemos el slider a la imagen seleccionada
            });
        });
    }

    pintarPunto(puntos, i) {
        puntos.forEach((puntoParticular) => {
            puntoParticular.classList.remove('activo');
        });
        puntos[i].classList.add('activo'); // Resalta el punto activo
    }

    startAutomaticSlider() {
        setInterval(() => {
            this.imagenActual = (this.imagenActual + 1) % this._cantidadImagenes; // Ciclo infinito
            this.moverSlider(this.imagenActual);
        }, 3000); // Cambia la imagen cada 3 segundos
    }
}

