class DragDrop {
    constructor() {
        this.dropArea = document.querySelector("#drop-area");
        this.previewContainer = document.querySelector(".preview-container");
        this.error = document.querySelector(".error-drop");
        this.allowedImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        this.inputFile = document.querySelector("#drop-input"); 
        this.inicializar();
    }

    inicializar() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.dropArea.addEventListener('dragenter', this.highlightDropArea.bind(this));
        this.dropArea.addEventListener('dragover', this.highlightDropArea.bind(this));
        this.dropArea.addEventListener('dragleave', this.unhighlightDropArea.bind(this));
        this.dropArea.addEventListener('drop', this.handleDrop.bind(this));
        this.inputFile.addEventListener('change', this.handleFileSelect.bind(this)); 
    }

    handleFileSelect(e) {
        this.previewFiles(e.target.files);
    }

    highlightDropArea(e) {
        this.dropArea.classList.add("highlight");
        e.preventDefault();
    }

    unhighlightDropArea(e) {
        this.dropArea.classList.remove("highlight");
        e.preventDefault();
    }                                                                      

    handleDrop(e) {
        this.dropArea.classList.remove("highlight");
        e.preventDefault();

        if (e.dataTransfer.files) {
            this.previewFiles(e.dataTransfer.files);
        }
    }

    previewFiles(files) {
        this.error.style.display = "none";
        for (let file of files) {
            if (!this.allowedImageTypes.includes(file.type)) {
                this.mostrarError("Solo se permiten archivos .jpg, .png, .jpeg");
                return;
            }
        }
        for (let file of files) {
            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => {
                this.createImagePreview(file, reader.result);
            };
        }
    }

    mostrarError(message) {
        this.error.style.display = "block";
        this.error.innerHTML = message;
    }

    createImagePreview(file, src) {
        let image = new Image();
        image.src = src;

        let contenedorImagen = document.createElement("div");
        contenedorImagen.setAttribute('class', 'image-container');
        contenedorImagen.appendChild(image);
        this.previewContainer.appendChild(contenedorImagen);

        let nombreImagen = document.createElement("p");
        nombreImagen.setAttribute('class', 'info');
        nombreImagen.innerHTML = file.name;
        contenedorImagen.appendChild(nombreImagen);

        let botonEliminar = document.createElement("button");
        botonEliminar.setAttribute('class', 'remove-button');
        botonEliminar.innerText = "Eliminar imagen";
        botonEliminar.addEventListener('click', () => {
            this.removeImage(contenedorImagen);
        });
        contenedorImagen.appendChild(botonEliminar);
    }

    removeImage(element) {
        element.remove();
    }
}
