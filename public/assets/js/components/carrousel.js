class Carrousel{

    constructor()
    {
        const carousels = document.querySelectorAll('.carousel');
          
        // Iterar sobre cada elemento .carousel
        carousels.forEach(carousel => {
            let currentIndex = 0;
    
            function nextSlide() {
                currentIndex++;
                if (currentIndex >= carousel.children.length) {
                    currentIndex = 0;
                }
                showSlide(carousel, currentIndex);
            }
    
            function prevSlide() {
                currentIndex--;
                if (currentIndex < 0) {
                    currentIndex = carousel.children.length - 1;
                }
                showSlide(carousel, currentIndex);
            }
    
            function showSlide(carousel, index) {
                const offset = index * carousel.clientWidth;
                carousel.scrollLeft = offset;
            }
    
            // Agregar listeners para los botones de navegación
            carousel.parentElement.querySelector('.prevButton').addEventListener('click', prevSlide);
            carousel.parentElement.querySelector('.nextButton').addEventListener('click', nextSlide);
        });

    }

}