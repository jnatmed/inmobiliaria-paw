class MapaLeaflet {

    constructor() {
        //Coordenadas iniciales de CABA
        this.mapa = L.map('mapid').setView([-34.6037, -58.3816], 11);

        L.tileLayer(
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png?',
            {}
        ).addTo(this.mapa);

        //Guarda el unico marcador utilizado en el formulario de alta
        this.marcadorActivo = null;

        //Permite ignorar respuestas viejas si el usuario cambia de ubicación
        this.numeroSeleccion = 0;

        /*Recalcular el tamaño del mapa cuando cambia el ancho*/
        this.temporizadorAjusteMapa = null;

        window.addEventListener(
            'resize',
            () => {
                window.clearTimeout(this.temporizadorAjusteMapa);
                this.temporizadorAjusteMapa = window.setTimeout(
                    () => this.ajustarTamanio(),
                    100
                );
            }
        );

        window.setTimeout(
            () => this.ajustarTamanio(),
            0
        );

    }

    ajustarTamanio() {
        if (!this.mapa) {
            return;
        }

        this.mapa.invalidateSize({pan: false});
    }

    restaurarUbicacion(lat, lng, textoPopup = '') {
        const latitud = Number(lat);
        const longitud = Number(lng);

        if (!Number.isFinite(latitud) || !Number.isFinite(longitud)) {
            return false;
        }

        const textoSeguro = this.obtenerTextoSeguro(textoPopup) || 'Ubicación seleccionada';

        this.mapa.setView(
            [latitud, longitud],
            16
        );

        this.actualizarMarcadorActivo(latitud, longitud, textoSeguro);

        this.actualizarCoordenadas(latitud, longitud);

        return true;
    }

    /*Busca varias coincidencias en Nominatim. Solamente obtiene resultados. No coloca un marcador*/
    async buscarResultados(address, limite = 5) {

        const direccionBuscada =
            typeof address === 'string'
                ? address.trim()
                : '';

        if (direccionBuscada === '') {
            return [];
        }

        const limiteSeguro = Math.min(
            Math.max(Number(limite) || 5, 1),
            10
        );

        const parametros = new URLSearchParams({
            format: 'jsonv2',
            q: direccionBuscada,
            addressdetails: '1',
            limit: String(limiteSeguro),
            countrycodes: 'ar',
            'accept-language': 'es'
        });

        const url =
            `https://nominatim.openstreetmap.org/search?${parametros.toString()}`;

        await new Promise(resolve => setTimeout(resolve, 1000));

        const response = await fetch(url, {headers: {Accept: 'application/json'}});

        if (!response.ok) {
            throw new Error(`Error en la solicitud: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();

        return Array.isArray(data) ? data : [];
    }

    /*busca y centra el mapa en la primera coincidencia. Si marcar es true, tambien selecciona esa coincidencia*/
    async buscar(address, marcar = true) {

        try {

            const resultados = await this.buscarResultados(address, 1);

            if (resultados.length === 0) {
                console.log('No se encontraron resultados para la dirección especificada.');
                return [];
            }

            const primerResultado = resultados[0];

            const lat = Number.parseFloat(primerResultado.lat);
            const lon = Number.parseFloat(primerResultado.lon);

            if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
                throw new Error('Nominatim devolvió coordenadas no válidas.');
            }

            this.mapa.setView([lat, lon], 13);

            if (marcar) {
                await this.seleccionarResultado(primerResultado);
            }

            return resultados;

        } catch (error) {

            console.error('Hubo un problema con la solicitud de búsqueda:', error);
            return [];

        }
    }

    /*Recibe la opción elegida por el usuario. Actualiza el mapa, el marcador y los campos del formulario*/
    async seleccionarResultado(resultado) {

        if (!resultado || typeof resultado !== 'object') {
            throw new Error('El resultado seleccionado no es válido.');
        }

        const lat = Number.parseFloat(resultado.lat);
        const lon = Number.parseFloat(resultado.lon);

        if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
            throw new Error('El resultado seleccionado no tiene coordenadas válidas.');
        }

        const direccionCompleta = this.obtenerTextoSeguro(resultado.display_name);

        if (direccionCompleta === '') {
            throw new Error('El resultado seleccionado no tiene una dirección válida.');
        }

        /*Cada selección recibe un número propio. Si el usuario cambia de búsqueda antes de que termine la consulta inversa, la respuesta anterior se ignora*/
        const seleccionActual = ++this.numeroSeleccion;

        this.mapa.setView([lat, lon], 16);

        this.actualizarMarcadorActivo(lat, lon, direccionCompleta);

        this.actualizarCoordenadas(lat, lon);

        /*Primero se usan los datos que ya vinieron en el resultado seleccionado*/
        let estado = this.construirEstadoDesdeResultado(resultado);

        this.aplicarEstadoUbicacion(estado);

        /*Si dirección, provincia y código postal ya están completos, no se necesita realizar otra consulta*/
        if (estado.exito) {
            return {
                ...estado,
                obsoleto: false
            };
        }

        /*Algunas ciudades o localidades no traen codigo postal en el resultado de busqueda. 
        Por lo que se repute la consulta en la misma posicion para intentar obtener los datos faltantes*/
        const resultadoInverso = await this.obtenerDireccion(lat, lon);

        /*Mientras esperamos, el usuario puede haber cambiado de ubicación. En ese caso se descarta esta respuesta*/
        if (seleccionActual !== this.numeroSeleccion) {
            return {
                ...estado,
                obsoleto: true
            };
        }

        if (resultadoInverso !== null) {

            const resultadoCompletado = this.combinarResultados(resultado, resultadoInverso);

            estado = this.construirEstadoDesdeResultado(resultadoCompletado);

        }

        /*DespuEs de intentar completar los datos mediante la busqueda inversa, se acepta que el codigo postal pueda no estar dispo*/
        estado = this.normalizarEstadoFinal(estado);

        this.aplicarEstadoUbicacion(estado);

        return {
            ...estado,
            obsoleto: false
        };

    }

    /*Crea el marcador solamente la primera vez. En las selecciones siguientes mueve el mismo marcador*/
    actualizarMarcadorActivo(lat, lon, textoPopup) {

        if (this.marcadorActivo === null) {

            this.marcadorActivo = L.marker(
                [lat, lon],
                {draggable: true}
            ).addTo(this.mapa);

            this.marcadorActivo.on(

                'dragend',

                async event => {

                    const marcador = event.target;
                    const posicion = marcador.getLatLng();

                    const seleccionActual = ++this.numeroSeleccion;

                    this.actualizarCoordenadas(posicion.lat, posicion.lng);

                    marcador.bindPopup('Actualizando dirección...').openPopup();

                    const resultadoInverso = await this.obtenerDireccion(posicion.lat, posicion.lng);

                    /*Si el usuario cambió de ubicación mientras se espera, se ignora esta respuesta*/
                    if (seleccionActual !== this.numeroSeleccion) {

                        marcador.closePopup();
                        return;

                    }

                    let estado =
                        resultadoInverso !== null
                            ? this.construirEstadoDesdeResultado(
                                resultadoInverso
                            )
                            : this.crearEstadoUbicacionVacio();

                    estado = this.normalizarEstadoFinal(estado);

                    this.aplicarEstadoUbicacion(estado);

                    

                    marcador.closePopup();

                    document.dispatchEvent(
                        new CustomEvent(
                            'mapa:ubicacion-actualizada',
                            {detail: estado}
                        )
                    );
                }
            );

        } else {

            /*En una nueva selección no se crea otro marcador se mueve el que ya existe*/
            this.marcadorActivo.setLatLng([lat, lon]);
        }


        if (this.marcadorActivo.getPopup()) {

            this.marcadorActivo.setPopupContent(textoPopup);

        } else {

            this.marcadorActivo.bindPopup(textoPopup);
        }

        this.marcadorActivo.closePopup();
    }

    /*Cancela cualquier consulta pendiente perteneciente a una ubicacion anterior*/
    limpiarDatosUbicacion() {

        this.numeroSeleccion++;

        this.asignarValor('#direccion', '');
        this.asignarValor('#direccion_completa', '');
        this.asignarValor('#provincia', '');
        this.asignarValor('#codigo_postal', '');

        this.asignarPlaceholder('#codigo_postal', '');

        this.limpiarPreviewAlta();

        if (this.marcadorActivo !== null) {
            this.marcadorActivo.closePopup();
        }
    }

    /*Guarda las coordenadas en el formato esperado por PHP: {"lat":-39.03,"lng":-67.58}*/
    actualizarCoordenadas(lat, lon) {

        if (!Number.isFinite(Number(lat)) || !Number.isFinite(Number(lon))) {
            this.asignarValor('#direccion', '');
            return;
        }

        const coordenadasJSON = JSON.stringify({lat: Number(lat), lng: Number(lon)});
        this.asignarValor('#direccion', coordenadasJSON);

    }

    /*Extrae direccion, provincia y codigo postal sin modificar el formulario*/
    construirEstadoDesdeResultado(resultado) {

        const direccionCompleta = this.obtenerTextoSeguro(resultado.display_name);

        const address =
            resultado.address &&
            typeof resultado.address === 'object'
                ? resultado.address
                : {};

        const provincia =
            this.obtenerPrimerTextoValido([
                address.state,
                address.province,
                address.region,
                address.state_district
            ]);

        const codigoPostal = this.obtenerTextoSeguro(address.postcode);

        const faltantes = [];

        if (direccionCompleta === '') {
            faltantes.push('dirección completa');
        }

        if (provincia === '') {
            faltantes.push('provincia');
        }

        if (codigoPostal === '') {
            faltantes.push('código postal');
        }

        return {
            exito: faltantes.length === 0,
            direccionCompleta,
            provincia,
            codigoPostal,
            faltantes,
            advertencias: []
        };
    }

    /*El codigo postal no siempre esta disponible en OpenStreetMap. Solamente la direccion completa y la provincia se consideran datos obligatorios*/
    normalizarEstadoFinal(estado) {

        const codigoPostalFaltante = estado.codigoPostal === '';

        const faltantesCriticos = estado.faltantes.filter(campo => campo !== 'código postal');

        return {
            ...estado,

            exito:
                faltantesCriticos.length === 0,

            faltantes:
                faltantesCriticos,

            advertencias:
                codigoPostalFaltante
                    ? ['código postal no disponible']
                    : []
        };
    }

    /*Escribe en el formulario un estado de ubicación que ya fue revisado*/
    aplicarEstadoUbicacion(estado) {

        this.asignarValor('#direccion_completa', estado.direccionCompleta);
        this.asignarValor('#provincia', estado.provincia);

        this.asignarValor('#codigo_postal', estado.codigoPostal);

        /*El placeholder se muestra solamente despues de terminar la busqueda directa e inversa. Antes de seleccionar una ubicacion queda vacio*/
        const codigoPostalNoDisponible = Array.isArray(estado.advertencias) && estado.advertencias.includes('código postal no disponible');

        this.asignarPlaceholder(
            '#codigo_postal',

            codigoPostalNoDisponible
                ? 'No se pudo encontrar el código postal'
                : ''
        );

        return estado;
    }


    /*Mantengo este metodo anterior para otros lugares que lo utilizan y evitar problemas*/
    actualizarCamposDesdeResultado(resultado) {
        const estado = this.construirEstadoDesdeResultado(resultado);
        return this.aplicarEstadoUbicacion(estado);
    }

    /*Conserva los datos de la opción elegida*/
    combinarResultados(resultadoBusqueda, resultadoInverso) {

        const addressBusqueda =
            resultadoBusqueda.address &&
            typeof resultadoBusqueda.address === 'object'
                ? resultadoBusqueda.address
                : {};

        const addressInverso =
            resultadoInverso.address &&
            typeof resultadoInverso.address === 'object'
                ? resultadoInverso.address
                : {};

        return {
            ...resultadoInverso,
            ...resultadoBusqueda,

            display_name:
                this.obtenerTextoSeguro(
                    resultadoBusqueda.display_name
                ) ||
                this.obtenerTextoSeguro(
                    resultadoInverso.display_name
                ),

            address: {
                ...addressInverso,
                ...addressBusqueda
            }
        };
    }

    /*Estado utilizado cuando no se pudo obtener ningún dato de dirección*/
    crearEstadoUbicacionVacio() {

        return {
            exito: false,
            direccionCompleta: '',
            provincia: '',
            codigoPostal: '',
            faltantes: [
                'dirección completa',
                'provincia',
                'código postal'
            ],
            advertencias: []
        };
    }

    /*Hace búsqueda inversa.  Recibe coordenadas y obtiene la dirección correspondiente*/
    async obtenerDireccion(lat, lon) {

        const parametros = new URLSearchParams({
            format: 'jsonv2',
            lat: String(lat),
            lon: String(lon),
            addressdetails: '1',
            'accept-language': 'es'
        });

        const url = `https://nominatim.openstreetmap.org/reverse?${parametros.toString()}`;

        try {

            await new Promise(resolve => setTimeout(resolve, 1000));

            const response = await fetch(url, {headers: {Accept: 'application/json'}});

            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();

            return data && typeof data === 'object'
                ? data
                : null;

        } catch (error) {

            console.error('Hubo un problema al obtener la dirección:', error);
            return null;

        }
    }

    /*Actualiza dirección completa, provincia y código postal*/
    async actualizarCodigoPostalProvincia(lat, lon) {

        const data = await this.obtenerDireccion(lat, lon);

        let estado =
            data === null
                ? this.crearEstadoUbicacionVacio()
                : this.construirEstadoDesdeResultado(
                    data
                );

        estado = this.normalizarEstadoFinal(estado);

        return this.aplicarEstadoUbicacion(estado);

    }

    /*Convierte un valor en texto válido. Rechaza valores invalidos*/
    obtenerTextoSeguro(valor) {

        if (typeof valor !== 'string') {
            return '';
        }

        const texto = valor.trim();
        const textoNormalizado = texto.toLowerCase();

        if (texto === '' || textoNormalizado === 'undefined' || textoNormalizado === 'null') {
            return '';
        }

        return texto;

    }

    /*Devuelve el primer valor válido de un conjunto de opciones*/
    obtenerPrimerTextoValido(valores) {

        for (const valor of valores) {
            const texto = this.obtenerTextoSeguro(valor);
            if (texto !== '') {
                return texto;
            }
        }

        return '';

    }

    /*Asigna un valor solamente si el elemento existe en la página. Sirve para seguir usando MapaLeaflet en el mapa general y en el detalle de una publicación*/
    asignarValor(selector, valor) {

        const elemento = document.querySelector(selector);
        if (elemento) {
            elemento.value = valor;
        }

    }

    /*Cambia el placeholder solamente si el campo existe en la pagina actual*/
    asignarPlaceholder(selector, valor) {
        const elemento = document.querySelector(selector);

        if (elemento) {
            elemento.placeholder = valor;
        }
    }

    /*Devuelve el texto que se muestra como precio*/
    obtenerPrecioPreview(valor) {

        if (valor === null || valor === undefined) {
            return 'Precio pendiente';
        }

        const texto = String(valor).trim();

        /*Se quitan los puntos de miles y se cambia la coma decimal solamente para comprobar si representa un precio mayor que cero*/
        const numero = Number(texto.replace(/\./g, '').replace(',', '.'));

        if (texto === '' || !Number.isFinite(numero) || numero <= 0) {
            return 'Precio pendiente';
        }

        return `USD ${texto} / noche`;
    }

    /*Construye la tarjeta compartida por el mapa general, el popup del alta y la preview visible del alta*/
    crearContenidoPreview(datos = {}) {

        const nombre = this.obtenerTextoSeguro(datos.nombre) || 'Nombre pendiente';

        const direccion = this.obtenerTextoSeguro(datos.direccion) || 'Seleccioná una ubicación para completar la dirección.';

        const urlPublicacion = this.obtenerTextoSeguro(datos.urlPublicacion);

        const urlImagen = this.obtenerTextoSeguro(datos.urlImagen);

        const cantidadImagenes = Math.max(
            0,
            Number.parseInt(datos.cantidadImagenes, 10) || 0
        );

        const tarjeta = document.createElement('article');

        tarjeta.classList.add('mapa-preview-card');

        if (datos.modoAlta === true) {
            tarjeta.classList.add(
                'mapa-preview-card--alta'
            );
        }

        /*En el mapa general la imagen funciona como enlace. En el alta todavia no existe una publicacion por lo que se utiliza un div comun*/
        const contenedorImagen =
            document.createElement(
                urlPublicacion !== ''
                    ? 'a'
                    : 'div'
            );

        contenedorImagen.classList.add('mapa-preview-imagen-contenedor');

        if (urlPublicacion !== '') {
            contenedorImagen.href = urlPublicacion;

            contenedorImagen.target = '_blank';

            contenedorImagen.rel = 'noopener noreferrer';
        }

        /*Imagen del mapa general*/
        if (urlImagen !== '') {

            const imagen = document.createElement('img');

            imagen.classList.add('mapa-preview-imagen');

            imagen.src = urlImagen;

            imagen.alt = `Imagen de ${nombre}`;

            imagen.loading = 'lazy';

            contenedorImagen.appendChild(imagen);

            if (cantidadImagenes > 1) {

                const contadorImagenes = document.createElement('span');

                contadorImagenes.classList.add('mapa-preview-imagen-contador');
                contadorImagenes.textContent = `Vista previa: imagen 1 de ${cantidadImagenes}`;
                contenedorImagen.appendChild(contadorImagenes);

            }

        } else {

            /*En el alta las imágenes se agregan  en el paso 2*/
            const imagenPendiente = document.createElement('div');

            imagenPendiente.classList.add('mapa-preview-imagen-pendiente');

            imagenPendiente.textContent =
                datos.imagenPendiente === true
                    ? 'Imagen pendiente: se agrega en el paso 2.'
                    : 'Imagen no disponible.';

            contenedorImagen.appendChild(imagenPendiente);
        }

        const informacion = document.createElement('div');

        informacion.classList.add('mapa-preview-informacion');

        const precio = document.createElement('p');

        precio.classList.add('mapa-preview-precio');

        precio.textContent = this.obtenerPrecioPreview(datos.precio);

        const titulo = document.createElement('h3');

        titulo.classList.add('mapa-preview-nombre');

        titulo.textContent = nombre;

        const textoDireccion = document.createElement('p');

        textoDireccion.classList.add('mapa-preview-direccion');

        textoDireccion.textContent = direccion;

        informacion.appendChild(precio);
        informacion.appendChild(titulo);
        informacion.appendChild(textoDireccion);

        tarjeta.appendChild(contenedorImagen);

        tarjeta.appendChild(informacion);

        return tarjeta;
    }

    /*Actualiza la tarjeta del popup del marcador*/
    actualizarPreviewAlta(datos) {

        if (this.marcadorActivo === null) {
            return;
        }

        const tieneImagen = this.obtenerTextoSeguro(datos.urlImagen) !== '';

        const tarjetaPopup = this.crearContenidoPreview({
            ...datos,
            modoAlta: true,
            imagenPendiente: !tieneImagen
        });

        /*Si el marcador ya tiene un popup se reemplaza su contenido*/
        if (this.marcadorActivo.getPopup()) {

            this.marcadorActivo.setPopupContent(tarjetaPopup);

        } else {

            /*Si todavía no tenía popup se lo asocia*/
            this.marcadorActivo.bindPopup(tarjetaPopup);
        }
    }

    /*Cierra y elimina la preview anterior cuandola ubicación deja de ser válida*/
    limpiarPreviewAlta() {

        if (this.marcadorActivo === null) {
            return;
        }

        this.marcadorActivo.closePopup();
        this.marcadorActivo.unbindPopup();
    }

    /*Metodo utilizado por el mapa del detalle de la publicacion*/
    async buscarPorLatitudyLongitud(lat, lon) {

        try {

            if (lat == null || lon == null) {
                throw new Error('Latitud o longitud no válidas');
            }

            const address = await this.obtenerDireccion(lat, lon);

            const textoDireccion = address ? this.obtenerTextoSeguro(address.display_name) : '';

            this.mapa.setView([lat, lon], 18);

            const textoPopup = textoDireccion || 'Dirección no disponible';

            L.marker([lat, lon], {draggable: true}).addTo(this.mapa).bindPopup(textoPopup, {minWidth: 170, maxWidth: 200, className: 'popup-direccion-detalle'}).openPopup();

        } catch (error) {

            console.error('Hubo un problema al mostrar la dirección:', error);

        }
    }

    /*Método utilizado por el mapa general*/
    agregarPublicacionesAlMapa(publicaciones) {

        publicaciones.forEach(

            publicacion => {

                const lat = publicacion.latitud;
                const lon = publicacion.longitud;
                const urlPublicacion = `${window.location.origin}${publicacion.url_pub}`;
                const urlImagen = `${window.location.origin}${publicacion.img_principal}`;

                /*Se utiliza el mismo componente que utilizará el formulario de alta*/
                const contenido =
                    this.crearContenidoPreview({
                        nombre: publicacion.nombre_alojamiento,
                        precio: publicacion.precio,
                        direccion: publicacion.direccion,
                        urlPublicacion,
                        urlImagen
                    });

                const marcador = L.marker([lat, lon]).addTo(this.mapa).bindPopup(contenido);

                marcador.on(
                    'mouseover',

                    function () {
                        this.openPopup();
                    }
                );

                marcador.on(
                    'mouseout',

                    function () {
                        this.closePopup();
                    }
                );

                marcador.on(
                    'click',

                    function () {
                        window.location.href = urlPublicacion;
                    }
                );
            }
        );
    }
}