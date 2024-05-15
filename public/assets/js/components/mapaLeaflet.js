
class MapaLeaflet {
    
    constructor() {        

        // coordenadas de ciudad autonoma de buenos aires
        this.mapa = L.map('mapid').setView([-34.6037, -58.3816], 11);


        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png?',{}).addTo(this.mapa);

    }

    async buscar(address){
        // Función para obtener coordenadas usando fetch y Nominatim
         
            // Construir la URL de la solicitud
            const encodedAddress = encodeURIComponent(address);
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedAddress}`;

            try {
                // Añadir un retraso de 1 segundo (1000 milisegundos) entre solicitudes
                await new Promise(resolve => setTimeout(resolve, 1000));

                // Realizar la solicitud con fetch
                const response = await fetch(url);
                // Verificar si la respuesta fue exitosa
                if (!response.ok) {
                    throw new Error(`Error en la solicitud: ${response.statusText}`);
                }

                // Convertir la respuesta a JSON
                const data = await response.json();

                // Procesar la respuesta JSON
                if (data.length > 0) {
                    const lat = data[0].lat;
                    const lon = data[0].lon;

                    // Centrar el mapa en las coordenadas encontradas
                    this.mapa.setView([lat, lon], 13);

                    // Agregar un marcador en las coordenadas encontradas
                    L.marker([lat, lon]).addTo(this.mapa)
                        .bindPopup(address)
                        .openPopup();
                } else {
                    alert('No se encontraron resultados para la dirección especificada.');
                }
            } catch (error) {
                console.error('Hubo un problema con la solicitud de fetch:', error);
            }
        
    }

}