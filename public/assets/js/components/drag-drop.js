class DragDrop {
    constructor() {
        this.dropArea = document.querySelector("#drop-area");
        this.previewContainer = document.querySelector(".preview-container");
        this.error = document.querySelector(".error-drop");
        this.allowedImageTypes = ["image/jpeg", "image/png"];
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

        const archivosSeleccionados = Array.from(e.target.files);

        /*addFilesToList() devuelve únicamente los archivos que realmente fueron agregados y que no estaban duplicados*/
        const archivosNuevos = this.addFilesToList(archivosSeleccionados);

        /*Crea previews solamente para los archivos nuevos*/
        this.previewFiles(archivosNuevos);

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

        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        
            const archivosSoltados = Array.from(e.dataTransfer.files);

            /*Se agregan solamente los que todavia no estaban seleccionados*/
            const archivosNuevos = this.addFilesToList(archivosSoltados);

            /*Se muestran solamente las previews nuevas*/
            this.previewFiles(archivosNuevos);
        }

    }

    addFilesToList(files) {

        /*Esta lista contendra solamente los archivos aceptados durante la seleccion actual*/
        const archivosNuevos = [];

        for (const file of files) {
            
            /*Comprueba si el archivo ya estaba en la lista de selecciones anteriores y si ya apareció dentro de la seleccion actual*/
            const yaFueSeleccionado =
                this.filesList.some(archivoGuardado => this.isSameFile(archivoGuardado, file))
                ||
                archivosNuevos.some(archivoNuevo => this.isSameFile(archivoNuevo, file));

            if (yaFueSeleccionado) {
                this.mostrarError(`La imagen "${file.name}" ya fue seleccionada y no se agregó nuevamente.`);
                continue;
            }

            archivosNuevos.push(file);
        }

        /*Agrega a la lista general solamente los archivos no repetidos*/
        this.filesList.push(...archivosNuevos);

        /*Actualiza el verdadero input type="file" que se envia al servidor*/
        this.updateInputFiles();

        /*Devuelve solamente los archivos nuevos para que handleFileSelect() o handleDrop() creen sus previews*/
        return archivosNuevos;
    }

    /*Dos archivos se consideran iguales cuando coinciden el nomobre, el tamaño en bytes y la ultima fecha de modificiacion*/
    isSameFile(fileA, fileB) {
        return fileA.name === fileB.name &&
            fileA.size === fileB.size &&
            fileA.lastModified === fileB.lastModified;
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

        botonEliminar.type = "button";

        botonEliminar.setAttribute('class', 'remove-button');

        botonEliminar.setAttribute('aria-label', `Quitar ${file.name}`);

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

            reader.onload = () => {
        
                if (!(reader.result instanceof ArrayBuffer)) {
                    resolve("unknown");
                    return;
                }

                const bytes = new Uint8Array(reader.result);

                /*Comprueba si los primeros bytes coinciden con una firma*/
                const comienzaCon = (...firma) => {
                    return firma.every((byte, indice) => bytes[indice] === byte);
                };

                /*PNG: 89 50 4E 47*/
                if (comienzaCon(0x89, 0x50, 0x4E, 0x47)) {
                    resolve("image/png");
                    return;
                }

                /*Todos los JPEG válidos comienzan con: FF D8 FF*/
                if (comienzaCon(0xFF, 0xD8, 0xFF)) {
                    resolve("image/jpeg");
                    return;
                }

                /*GIF: 47 49 46 38*/
                if (comienzaCon(0x47, 0x49, 0x46, 0x38)) {
                    resolve("image/gif");
                    return;
                }

                /*WEBP comienza con RIFF y contiene WEBP entre los bytes 8 y 11*/
                const esWebp =
                    comienzaCon(0x52, 0x49, 0x46, 0x46) &&
                    bytes[8] === 0x57 &&
                    bytes[9] === 0x45 &&
                    bytes[10] === 0x42 &&
                    bytes[11] === 0x50;

                if (esWebp) {
                    resolve("image/webp");
                    return;
                }

                resolve("unknown");
            };

            reader.onerror = () => {
                resolve("unknown");
            };

            /*Se leen 12 bytes porque alcanzan para reconocer las firmas utilizadas anteriormente*/
            reader.readAsArrayBuffer(file.slice(0, 12));
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
