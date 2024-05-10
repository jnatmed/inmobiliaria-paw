
class MapaLeaflet {
    
    constructor() {

        // coordenadas de ciudad autonoma de buenos aires
        let mapa = L.map('mapid').setView([-34.6037, -58.3816], 11);


        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png?',{}).addTo(mapa);


       
    }


}