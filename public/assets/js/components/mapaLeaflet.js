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

                    /*Si el usuario cambia de busqueda antes de que termine la consulta inversa, la respuesta anterior se ignora*/
                    if (seleccionActual !== this.numeroSeleccion
                    ) {
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

                    const textoActualizado =
                        estado.direccionCompleta !== ''
                            ? estado.direccionCompleta
                            : 'No se pudo determinar la dirección.';

                    marcador.bindPopup(textoActualizado).openPopup();

                    document.dispatchEvent(
                        new CustomEvent(
                            'mapa:ubicacion-actualizada',
                            {detail: estado}
                        )
                    );
                }
            );

        } else {
            this.marcadorActivo.setLatLng([lat, lon]);
        }

        this.marcadorActivo.bindPopup(textoPopup).openPopup();
    }

    /*Cancela cualquier consulta pendiente perteneciente a una ubicacion anterior*/
    limpiarDatosUbicacion() {

        this.numeroSeleccion++;

        this.asignarValor('#direccion', '');
        this.asignarValor('#direccion_completa', '');
        this.asignarValor('#provincia', '');
        this.asignarValor('#codigo_postal', '');

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

    /*Metodo utilizado por el mapa del detalle de la publicacion*/
    async buscarPorLatitudyLongitud(lat, lon) {

        try {

            if (lat == null || lon == null) {
                throw new Error('Latitud o longitud no válidas');
            }

            const address = await this.obtenerDireccion(lat, lon);

            const textoDireccion = address ? this.obtenerTextoSeguro(address.display_name) : '';

            this.mapa.setView([lat, lon], 18);

            L.marker([lat, lon], {draggable: true}).addTo(this.mapa).bindPopup(textoDireccion || 'Dirección no disponible').openPopup();

        } catch (error) {

            console.error('Hubo un problema al mostrar la dirección:', error);

        }
    }

    /*Método utilizado por el mapa general*/
    agregarPublicacionesAlMapa(publicaciones) {

        publicaciones.forEach(publicacion => {

            const lat = publicacion.latitud;
            const lon = publicacion.longitud;
            const nombre = publicacion.nombre_alojamiento;
            const precio = publicacion.precio;

            const url_pub = `${window.location.origin}${publicacion.url_pub}`;

            const url_imagen = `${window.location.origin}${publicacion.img_principal}`;

            const direccion = publicacion.direccion;

            const contenido = `
                <div style="text-align: center;">
                    <a href="${url_pub}" target="_blank">
                        <img
                            src="${url_imagen}"
                            alt="${nombre}"
                            style="width: 100%; max-width: 300px; height: auto; margin-bottom: 10px;"
                        />
                    </a>

                    <h1 style="font-size: 1.5rem; font-weight: bold;">
                        USD ${precio} / noche
                    </h1>

                    <h2 style="font-size: 1.25rem;">
                        ${nombre}
                    </h2>

                    <h3 style="font-size: 1rem;">
                        ${direccion}
                    </h3>
                </div>
            `;

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
                    window.location.href = url_pub;
                }
            );
        });
    }
}