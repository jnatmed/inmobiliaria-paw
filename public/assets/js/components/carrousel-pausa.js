class CarrouselPausa {
    constructor(publicacionElement) {
        this.index = 0; // Índice de la imagen actual
        this.items = publicacionElement.querySelectorAll('.destacado-item'); // Todos los elementos de la imagen
        this.playButton = publicacionElement.querySelector('#boton-pausa'); // Botón de reproducción/pausa
        this.intervalId = null; // Para almacenar el ID del intervalo
        this.isPlaying = false; // Comienza en pausa

        this.init();
    }

    init() {
        // Mostrar la primera imagen
        this.showImage(this.index);

        // Agregar evento al botón de reproducción/pausa
        this.playButton.addEventListener('click', () => this.togglePlayPause());

        // Asegurarse de que el carrusel esté detenido al iniciar
        this.stopCarousel();

        // Establecer el icono del botón de inicio en "play"
        this.playButton.innerHTML = '&#9658;'; // Icono de "play"
    }

    showImage(index) {
        // Ocultar todas las imágenes
        this.items.forEach(item => {
            item.classList.remove('show'); // Quitar clase de animación
        });
        
        // Mostrar la imagen actual con la clase de animación
        this.items[index].classList.add('show');
    }

    startCarousel() {
        this.intervalId = setInterval(() => {
            this.nextImage();
        }, 3000); // Cambia cada 3 segundos
    }

    stopCarousel() {
        clearInterval(this.intervalId);
    }

    nextImage() {
        this.index = (this.index + 1) % this.items.length; // Ciclar al siguiente índice
        this.showImage(this.index);
    }

    togglePlayPause() {
        if (this.isPlaying) {
            this.stopCarousel();
            this.playButton.innerHTML = '&#9658;'; // Icono de "play"
        } else {
            this.startCarousel();
            this.playButton.innerHTML = '&#10074;&#10074;'; // Icono de "pausa"
        }
        this.isPlaying = !this.isPlaying; // Cambiar estado
    }
}