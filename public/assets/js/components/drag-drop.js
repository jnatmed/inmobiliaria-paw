class Drag_Drop {
    constructor(dropArea, inputFile, output) {
        this.dropArea = dropArea;
        this.output = output;
        this.inputFile = inputFile;
        this.dropAreaText = this.dropArea.querySelector('p');

        this.inicializar();
    }

    inicializar() {
        // Handle drag over event
        this.dropArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.dropArea.classList.add("drag-over");
        });

        // Handle drop event
        this.dropArea.addEventListener("drop", (e) => {
            e.preventDefault();
            this.dropArea.classList.remove("drag-over");
            const imagen = e.dataTransfer.files[0];
            if (!imagen || !imagen.type.match("image")) 
                return;
            this.mostrar(imagen);
            this.eliminarDropArea();
            this.inputFile.files = e.dataTransfer.files; // Set the file to the input
        });

        // Handle drag enter event
        this.dropArea.addEventListener("dragenter", (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log("El archivo está sobre la zona de drop");
            this.dropArea.classList.add("drag-over");
        });

        // Handle drag leave event
        this.dropArea.addEventListener("dragleave", (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log("El archivo ha salido de la zona de drop");
            this.dropArea.classList.remove("drag-over");
        });

        // Handle click event to open file dialog
        this.dropArea.addEventListener("click", () => {
            this.inputFile.click();
        });

        // Handle change event for file input
        this.inputFile.addEventListener("change", (e) => {
            const imagen = e.target.files[0];
            if (!imagen || !imagen.type.match("image")) 
                return;
            this.mostrar(imagen);
            this.eliminarDropArea();
        });
    }

    mostrar(imagen) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const imagenHTML = `<div class="image-dad">
                                    <img src="${e.target.result}" alt="image">
                                    <button class="remove-icon">&times;</button>
                               </div>`;
            this.output.innerHTML = imagenHTML;

            const removeButton = this.output.querySelector('.remove-icon');
            removeButton.addEventListener('click', () => {
                this.removeImage();
            });
        };
        reader.readAsDataURL(imagen);
    }

    eliminarDropArea() {
        if (this.dropArea) {
            this.dropArea.style.display = 'none';
        }
    }

    removeImage() {
        this.output.innerHTML = '';
        this.inputFile.value = ''; // Clear the input value
        if (this.dropArea) {
            this.dropArea.style.display = 'flex'; // Show the drop area again
        }
    }
}