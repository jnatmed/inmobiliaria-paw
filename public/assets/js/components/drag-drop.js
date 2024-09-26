class DragDrop {
    constructor() {
        this.dropArea = document.querySelector("#drop-area");
        this.previewContainer = document.querySelector(".preview-container");
        this.error = document.querySelector(".error-drop");
        this.allowedImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        this.maxFileSize = 1 * 1024 * 1024; // 1MB en bytes
        this.inputFile = document.querySelector("#drop-input");
        this.filesList = []; // Lista para almacenar los archivos
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
        this.addFilesToList(e.target.files);
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
            this.addFilesToList(e.dataTransfer.files);
            this.previewFiles(e.dataTransfer.files);
        }
    }

    addFilesToList(files) {
        for (let file of files) {
            this.filesList.push(file);
        }
        this.updateInputFiles();
    }

    updateInputFiles() {
        let dataTransfer = new DataTransfer();
        for (let file of this.filesList) {
            dataTransfer.items.add(file);
        }
        this.inputFile.files = dataTransfer.files; // Actualizar el input con la lista de archivos
    }

    async previewFiles(files) {
        this.error.style.display = "none"; // Ocultar errores anteriores
        let hasError = false;

        for (let file of files) {
            const actualType = await this.getFileType(file);

            // Verificar tipo de archivo
            if (!this.allowedImageTypes.includes(actualType)) {
                this.mostrarError(`Tipo no permitido: ${file.name} Tipo Archivo: ${actualType}`, file);
                this.removeImageFromList(file); // Eliminar archivo de la lista
                hasError = true;
                continue; // Saltar a la siguiente imagen
            }

            // Verificar tamaño de archivo
            if (file.size > this.maxFileSize) {
                this.mostrarError(`Tamaño máximo excedido: Nombre: ${file.name}`, file, true);
                this.removeImageFromList(file); // Eliminar archivo de la lista
                hasError = true;
                continue; // Saltar a la siguiente imagen
            }

            // Leer el archivo para vista previa
            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => {
                this.createImagePreview(file, reader.result, false, actualType);
            };
        }
    }

    mostrarError(message, file = null, exceeded = false) {
        let errorContainer = document.querySelector("#cartel-errores-paso-2");
        errorContainer.classList.add("visible");

        let errorItem = document.createElement("p");
        errorItem.classList.add("error-message");
        errorItem.classList.add("visible");
        errorItem.innerHTML = message;

        if (exceeded && file) {
            let msjError = `- Tamaño: ${this.formatFileSize(file.size)},  - Máximo permitido: 1MB`;
            errorItem.innerHTML += msjError;
        }

        let closeButton = document.createElement("span");
        closeButton.classList.add("close-button");
        closeButton.innerHTML = `X`;

        closeButton.onclick = () => {
            errorItem.remove();
        };

        errorItem.appendChild(closeButton);
        errorContainer.appendChild(errorItem);
        errorContainer.style.display = "flex";
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
        nombreImagen.innerHTML = `${file.name} (Tipo Archivo: ${tipoArchivo}) - ${this.formatFileSize(file.size)}`;
        contenedorImagen.appendChild(nombreImagen);

        let botonEliminar = document.createElement("button");
        botonEliminar.setAttribute('class', 'remove-button');
        botonEliminar.innerText = "Eliminar imagen";
        botonEliminar.onclick = () => {
            this.removeImage(contenedorImagen, file);
        };
        contenedorImagen.appendChild(botonEliminar);

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
                    case "52494646":
                        fileType = "image/webp";
                        break;
                    default:
                        fileType = "unknown";
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

    removeImage(element, file) {
        element.remove();
        this.removeImageFromList(file); // Asegurarse de eliminar el archivo de la lista
    }

    removeImageFromList(file) {
        this.filesList = this.filesList.filter((f) => f !== file);
        this.updateInputFiles(); // Actualizar el input con la lista de archivos actualizada
    }
}
