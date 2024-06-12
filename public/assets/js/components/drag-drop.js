class Drag_Drop {
    constructor() {
        this.dropArea = document.querySelector(".input-dad");
        this.output = document.querySelector(".output-dad");
        this.inputFile = document.querySelector("#imagen_plato")
        this.dropAreaText = this.dropArea.querySelector('p')

        this.inicializar();
    }

    inicializar() {
        this.dropArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        this.dropArea.addEventListener("drop", (e) => {
            e.preventDefault();
            const imagen = e.dataTransfer.files[0];
            if (!imagen || !imagen.type.match("image")) 
                return;
            this.mostrar(imagen);
            this.eliminarDropArea()
            this.inputFile.files = e.dataTransfer.files //Agrego la imagen al input para usar en el form
        });

        this.dropArea.addEventListener("dragenter", (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log("El archivo está sobre la zona de drop");
            this.dropArea.classList.add("drag-over");
        });

        this.dropArea.addEventListener("dragleave", (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log("El archivo ha salido de la zona de drop");
            this.dropArea.classList.remove("drag-over"); // Remover clase al salir del área
        });

    }

    mostrar(imagen) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const imagenHTML = `<div class="image-dad">
                                    <img src="${e.target.result}" alt="image">
                               </div>`;
            this.output.innerHTML = imagenHTML;
        };
        reader.readAsDataURL(imagen);
    }

    eliminarDropArea() {
        if (this.dropArea) {
            this.dropArea.parentNode.removeChild(this.dropArea);
        }
    }
}

