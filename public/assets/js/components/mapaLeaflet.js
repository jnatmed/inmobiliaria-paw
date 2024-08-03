
class MapaLeaflet {
    
    constructor() {        

        // coordenadas de ciudad autonoma de buenos aires
        this.mapa = L.map('mapid').setView([-34.6037, -58.3816], 11);


        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png?',{}).addTo(this.mapa);

    }

    init()    
    {
        
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
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
    
                // Centrar el mapa en las coordenadas encontradas
                this.mapa.setView([lat, lon], 13);
    
                // Agregar un marcador en las coordenadas encontradas y hacerlo arrastrable
                const marker = L.marker([lat, lon], { draggable: true }).addTo(this.mapa)
                    .bindPopup(address)
                    .openPopup();
                const position = marker.getLatLng(); // Obtener la nueva posición del marcador              
                // Convertir las coordenadas a JSON
                const coordenadasJSON = JSON.stringify(position);

                // Colocar las coordenadas en el input #direccion en formato JSON
                document.querySelector('#direccion').value = coordenadasJSON;
                    
                // Evento 'dragend' para actualizar las coordenadas después de arrastrar el marcador
                marker.on('dragend', function(event) {
                    const marker = event.target; // Obtener el marcador que fue arrastrado
                    const position = marker.getLatLng(); // Obtener la nueva posición del marcador
                    console.log(position); // Mostrar la nueva posición en la consola
                    
                    // Convertir las coordenadas a JSON
                    const coordenadasJSON = JSON.stringify(position);
    
                    // Colocar las coordenadas en el input #direccion en formato JSON
                    document.querySelector('#direccion').value = coordenadasJSON;
                });
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
            await new Promise(resolve => setTimeout(resolve, 1000)); // Simula un retraso en la solicitud
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            return data.display_name; // Esto te da una dirección legible
        } catch (error) {
            console.error('Hubo un problema al obtener la dirección: ', error);
            return 'Dirección no disponible'; // Valor por defecto en caso de error
        }
    }
    

    async buscarPorLatitudyLongitud(lat, lon){
    
        try {
                // Si no se proporciona una dirección, obtenerla usando una función de geocodificación inversa

                address = await this.obtenerDireccion(lat, lon);

                // Centrar el mapa en las coordenadas encontradas
                this.mapa.setView([lat, lon], 13);
    
                // Agregar un marcador en las coordenadas encontradas y hacerlo arrastrable
                L.marker([lat, lon], { draggable: true }).addTo(this.mapa)
                    .bindPopup(address)
                    .openPopup();

        } catch (error) {
            console.error('Hubo un problema al mostrar la direccion: ', error);
        }
    }
    

    

}