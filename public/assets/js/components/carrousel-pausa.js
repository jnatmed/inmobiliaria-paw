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
        this.showImage(this.index);
        this.playButton.addEventListener('click', () => this.togglePlayPause());
        this.stopCarousel();
        this.playButton.innerHTML = '&#9658;';
    }

    showImage(index) {
        this.items.forEach(item => {
            item.classList.remove('show');
        });

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
        this.isPlaying = !this.isPlaying;
    }
}