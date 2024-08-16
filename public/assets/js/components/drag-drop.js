class DragDrop {
    constructor() {
        this.dropArea = document.querySelector("#drop-area");
        this.previewContainer = document.querySelector(".preview-container");
        this.error = document.querySelector(".error-drop");
        this.allowedImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        this.maxFileSize = 1 * 1024 * 1024; // 1MB en bytes
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

            if (file.size > this.maxFileSize) {
                this.createImagePreview(file, null, true);
                continue;
            }

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

    createImagePreview(file, src, exceeded = false) {
        let contenedorImagen = document.createElement("div");
        contenedorImagen.setAttribute('class', `image-container ${exceeded ? 'exceeded' : ''}`);

        if (src) {
            let image = new Image();
            image.src = src;
            contenedorImagen.appendChild(image);
        }

        let nombreImagen = document.createElement("p");
        nombreImagen.setAttribute('class', 'info');
        if (exceeded) {
            nombreImagen.innerHTML = `${file.name} - Tamaño máximo excedido (${this.formatFileSize(file.size)}), Máximo permitido = 1MB`;
        } else {
            nombreImagen.innerHTML = `${file.name} - ${this.formatFileSize(file.size)}`;
        }
        contenedorImagen.appendChild(nombreImagen);

        if (exceeded) {
            let errorMensaje = document.createElement("div");
            errorMensaje.setAttribute('class', 'error-message');
            errorMensaje.innerText = "Máximo excedido";
            contenedorImagen.appendChild(errorMensaje);
        }

        let botonEliminar = document.createElement("button");
        botonEliminar.setAttribute('class', 'remove-button');
        botonEliminar.innerText = "Eliminar imagen";
        botonEliminar.addEventListener('click', () => {
            this.removeImage(contenedorImagen);
        });
        contenedorImagen.appendChild(botonEliminar);

        this.previewContainer.appendChild(contenedorImagen);
    }

    formatFileSize(size) {
        const units = ["bytes", "KB", "MB", "GB", "TB"];
        let unitIndex = 0;
        let formattedSize = size;

        while (formattedSize >= 1024 && unitIndex < units.length - 1) {
            formattedSize /= 1024;
            unitIndex++;
        }

        return `${formattedSize.toFixed(2)} ${units[unitIndex]}`;
    }    

    removeImage(element) {
        element.remove();
    }
}
