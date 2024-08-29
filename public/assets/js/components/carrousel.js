class Carrousel {

    /*
    Recibe un argumento (clase) ->
        clase         => contenedor de tipo class donde está el carrousel
    */
    
    constructor(clase) {
        this._clase = clase;
        this._seccionCarrousel = document.querySelector(this._clase);
        this._imagenes = this._seccionCarrousel.querySelectorAll('.carousel-img');
        this._cantidadImagenes = this._imagenes.length;
        
        this.inicializarCarrousel();
        this.startCarrousel();
        this.selectImage();      
    }

    inicializarCarrousel() { 
        let porcentajeSlider = 100 * this._cantidadImagenes;

        let slider = this._seccionCarrousel.querySelector('.slider');
        slider.style.width = `${porcentajeSlider}%`;

        this._imagenes.forEach((imagen) => {
            imagen.style.width = `calc(100% / ${this._cantidadImagenes})`;
        });

        this.agregarPuntos(this._seccionCarrousel);
    }

    agregarPuntos(contenedor) {
        let ul = document.createElement('ul');
        ul.classList.add('puntos');
        let li;
        
        for (let i = 0; i < this._cantidadImagenes; i++) {
            li = document.createElement('li');
            li.classList.add('punto');
            if (i === 0) {
                li.classList.add('activo');
            }
            ul.appendChild(li);
        }    

        contenedor.appendChild(ul);
    }

    startCarrousel() {
        let time = 3000;
        let imagenActual = 0;
        let punto = document.querySelectorAll('.punto');
        let slider = document.querySelector('.slider');
        let divisor = 100 / this._cantidadImagenes;
        
        setInterval(() => {
            let operacion = imagenActual * -divisor;
            slider.style.transform = `translateX(${operacion}%)`;
            this.pintarPunto(punto, imagenActual);

            if (imagenActual < this._cantidadImagenes - 1) {
                imagenActual++;
            } else {
                imagenActual = 0;
            }
        }, time);
    }

    selectImage() {
        let punto = document.querySelectorAll('.punto');
        let slider = document.querySelector('.slider');
        let divisor = 100 / punto.length;

        punto.forEach((puntoParticular, i) => {
            punto[i].addEventListener('click', () => {
                let posicion = i;
                let operacion = posicion * -divisor;
                slider.style.transform = `translateX(${operacion}%)`;
                this.pintarPunto(punto, i);
            });    
        });  
    }

    pintarPunto(punto, i) {
        punto.forEach((puntoParticular) => {
            puntoParticular.classList.remove('activo');
        });
        punto[i].classList.add('activo');
    }
}

