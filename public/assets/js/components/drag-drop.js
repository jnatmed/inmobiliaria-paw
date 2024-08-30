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
        this.error.style.display = "none"; // Ocultar errores anteriores
        let hasError = false;

        for (let file of files) {
            const actualType = await this.getFileType(file);

            // Verificar tipo de archivo
            if (!this.allowedImageTypes.includes(actualType)) {
                this.mostrarError(`Tipo no permitido: ${file.name} Tipo Archivo: ${actualType}`, file);
                hasError = true;
                continue; // Saltar a la siguiente imagen
            }

            // Verificar tamaño de archivo
            if (file.size > this.maxFileSize) {
                this.mostrarError(`Tamaño máximo excedido: Nombre: ${file.name}`, file, true);
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

        // // Mostrar mensaje si hubo errores
        // if (hasError) {
        //     this.error.style.display = "flex";
        // }
    }

    mostrarError(message, file = null, exceeded = false) {
        let errorContainer = document.querySelector("#cartel-errores-paso-2");
        errorContainer.classList.add("visible");

        // Crear un error item
        let errorItem = document.createElement("p");
        errorItem.classList.add("error-message");
        errorItem.classList.add("visible");
        errorItem.innerHTML = message;

        // Mostrar el tamaño si el error es por tamaño excedido
        if (exceeded && file) {
            let msjError = `- Tamaño: ${this.formatFileSize(file.size)},  - Máximo permitido: 1MB`;
            errorItem.innerHTML += msjError;
        }

        // Crear el botón de cerrar
        let closeButton = document.createElement("span");
        closeButton.classList.add("close-button");
        closeButton.innerHTML = `X`;

        // Asignar el evento para eliminar el errorItem
        closeButton.onclick = () => {
            errorItem.remove();
        };

        // Agregar el botón al errorItem y el errorItem al errorContainer
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
                    case "25504446":
                        fileType = "application/pdf";
                        break;
                    case "504b0304":
                        fileType = "application/zip"; // Para archivos zip y jar
                        break;
                    case "4d5a9000":
                        fileType = "application/x-msdownload"; // Para archivos .exe
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

    removeImage(element) {
        element.remove();
    }
}
