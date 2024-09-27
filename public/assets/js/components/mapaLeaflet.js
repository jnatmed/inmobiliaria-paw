class MapaLeaflet {

    constructor() {
        // Coordenadas de Ciudad Autónoma de Buenos Aires
        this.mapa = L.map('mapid').setView([-34.6037, -58.3816], 11);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png?', {}).addTo(this.mapa);
    }

    async buscar(address, marcar = true) {
        const encodedAddress = encodeURIComponent(address);
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedAddress}`;

        try {
            await new Promise(resolve => setTimeout(resolve, 1000));
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);

                this.mapa.setView([lat, lon], 13);

                if (marcar) {
                    const marker = L.marker([lat, lon], { draggable: true }).addTo(this.mapa)
                        .bindPopup(address)
                        .openPopup();
                    const position = marker.getLatLng();
                    const coordenadasJSON = JSON.stringify(position);

                    document.querySelector('#direccion').value = coordenadasJSON;
                    document.querySelector('#direccion_completa').value = data[0].display_name;

                    marker.on('dragend', async function (event) {
                        const marker = event.target;
                        const position = marker.getLatLng();
                        const coordenadasJSON = JSON.stringify(position);

                        document.querySelector('#direccion').value = coordenadasJSON;
                        document.querySelector('#direccion_completa').value = position.display_name;

                        // Actualizar Codigo Postal y provincia después de arrastrar el marcador
                        await this.actualizarCodigoPostalProvincia(position.lat, position.lng);
                    }.bind(this)); // Bind 'this' to maintain context
                }

                // Actualizar localidad y provincia después de buscar
                await this.actualizarCodigoPostalProvincia(lat, lon);
            } else {
                console.log('No se encontraron resultados para la dirección especificada.');
            }
        } catch (error) {
            console.error('Hubo un problema con la solicitud de fetch:', error);
        }
    }

    async obtenerDireccion(lat, lon) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`;
        try {
            await new Promise(resolve => setTimeout(resolve, 1000));
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            return data; // Devuelve todo el objeto de datos
        } catch (error) {
            console.error('Hubo un problema al obtener la dirección: ', error);
            return { display_name: 'Dirección no disponible' }; // Valor por defecto
        }
    }

    async actualizarCodigoPostalProvincia(lat, lon) {
        const data = await this.obtenerDireccion(lat, lon);
        const displayName = data.display_name || '';
        const addressParts = displayName.split(',').map(part => part.trim());

        console.log(data.display_name)

        const codigo_postal = addressParts.length > 1 ? addressParts[addressParts.length - 2] : '';
        const provincia = addressParts.length > 2 ? addressParts[addressParts.length - 3] : '';

        document.querySelector('#codigo_postal').value = codigo_postal || '';
        document.querySelector('#provincia').value = provincia || '';
    }

    async buscarPorLatitudyLongitud(lat, lon) {
        try {
            if (lat == null || lon == null) {
                throw new Error('Latitud o longitud no válidas');
            }

            let address = await this.obtenerDireccion(lat, lon);
            this.mapa.setView([lat, lon], 18);

            L.marker([lat, lon], { draggable: true }).addTo(this.mapa)
                .bindPopup(address.display_name || 'Dirección no disponible')
                .openPopup();

        } catch (error) {
            console.error('Hubo un problema al mostrar la dirección: ', error);
        }
    }

    agregarPublicacionesAlMapa(publicaciones) {
        publicaciones.forEach(publicacion => {
            const lat = publicacion.latitud;
            const lon = publicacion.longitud;
            const nombre = publicacion.nombre_alojamiento;
            const precio = publicacion.precio;
            const url_pub = `${window.location.origin}${publicacion.url_pub}`;
            const url_imagen = `${window.location.origin}${publicacion.img_principal}`;
            const direccion = publicacion.direccion;

            // console.log(`<img src="${url}" alt="${nombre}" style="width: 100%; max-width: 300px; height: auto; margin-bottom: 10px;" />`);
            const contenido = `
                <div style="text-align: center;">
                    <a href="${url_pub}" target="_blank">
                        <img src="${url_imagen}" alt="${nombre}" style="width: 100%; max-width: 300px; height: auto; margin-bottom: 10px;" />
                    </a>
                    <h1 style="font-size: 1.5rem; font-weight: bold;">USD ${precio} / noche</h1>
                    <h2 style="font-size: 1.25rem;">${nombre}</h2>
                    <h3 style="font-size: 1rem;">${direccion}</h3>
                </div>
                `;


            const marcador = L.marker([lat, lon])
                .addTo(this.mapa)
                .bindPopup(contenido);

            marcador.on('mouseover', function () {
                this.openPopup();
            });

            marcador.on('mouseout', function () {
                this.closePopup();
            });

            marcador.on('click', function () {
                window.location.href = url_pub;
            });
        });
    }
}
