class Carrousel {
    constructor() {
        this.currentIndex = 0;
    }

    init() {
        const prevButton = document.querySelector('.prev');
        const nextButton = document.querySelector('.next');

        // Asegúrate de que los botones existen antes de añadir los eventos
        if (prevButton) {
            prevButton.addEventListener('click', () => this.moveCarousel(-1));
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => this.moveCarousel(1));
        }
    }

    moveCarousel(step) {
        const images = document.querySelectorAll('.li-imagen-publicacion');
        const totalImages = images.length;

        this.currentIndex += step;

        if (this.currentIndex < 0) {
            this.currentIndex = totalImages - 1; // Mover al final si retrocede desde la primera imagen
        } else if (this.currentIndex >= totalImages) {
            this.currentIndex = 0; // Mover al inicio si avanza desde la última imagen
        }

        const ulElement = document.querySelector('.ul-imagenes-publicacion');
        ulElement.style.transform = `translateX(-${this.currentIndex * 100}%)`;
    }
}