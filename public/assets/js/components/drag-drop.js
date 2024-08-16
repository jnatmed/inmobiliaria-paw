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

    async previewFiles(files) {
        this.error.style.display = "none";
        for (let file of files) {
            const actualType = await this.getFileType(file);
            if (!this.allowedImageTypes.includes(actualType)) {
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
                this.createImagePreview(file, reader.result, false, actualType);
            };
        }
    }

    mostrarError(message) {
        this.error.style.display = "block";
        this.error.innerHTML = message;
    }

    createImagePreview(file, src, exceeded = false, actualType = "") {
        let contenedorImagen = document.createElement("div");
        contenedorImagen.setAttribute('class', `image-container ${exceeded ? 'exceeded' : ''}`);

        if (src) {
            let image = new Image();
            image.src = src;
            contenedorImagen.appendChild(image);
        }

        let nombreImagen = document.createElement("p");
        nombreImagen.setAttribute('class', 'info');
        const tipoArchivo = actualType || file.type;
        if (exceeded) {
            nombreImagen.innerHTML = `${file.name} (${tipoArchivo}) - Tamaño máximo excedido (${this.formatFileSize(file.size)}), Máximo permitido = 1MB`;
        } else {
            nombreImagen.innerHTML = `${file.name} (${tipoArchivo}) - ${this.formatFileSize(file.size)}`;
        }
        contenedorImagen.appendChild(nombreImagen);

        if (exceeded) {
            let errorMensaje = document.createElement("div");
            errorMensaje.setAttribute('class', 'error-message');
            errorMensaje.innerText = "Máximo excedido";
            contenedorImagen.appendChild(errorMensaje);
        }

        // Crear y añadir el botón de eliminar
        let botonEliminar = document.createElement("button");
        botonEliminar.setAttribute('class', 'remove-button');
        botonEliminar.innerText = "Eliminar imagen";
        botonEliminar.onclick = () => {
            this.removeImage(contenedorImagen);
        };
        contenedorImagen.appendChild(botonEliminar);

        // Añadir el contenedor de la imagen a la vista previa
        this.previewContainer.appendChild(contenedorImagen);
    }

    async getFileType(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => {
                const arr = (new Uint8Array(reader.result)).subarray(0, 4);
                let header = "";
                for (let i = 0; i < arr.length; i++) {
                    header += arr[i].toString(16);
                }
                // Verifica las cabeceras conocidas de los archivos
                let fileType = "";
                switch (header) {
                    case "89504e47":
                        fileType = "image/png";
                        break;
                    case "ffd8ffe0":
                    case "ffd8ffe1":
                    case "ffd8ffe2":
                    case "ffd8ffe3":
                    case "ffd8ffe8":
                        fileType = "image/jpeg";
                        break;
                    case "47494638":
                        fileType = "image/gif";
                        break;
                    default:
                        fileType = "unknown"; // Si no coincide, devolverá "unknown"
                        break;
                }
                resolve(fileType);
            };
            reader.readAsArrayBuffer(file.slice(0, 4));
        });
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
