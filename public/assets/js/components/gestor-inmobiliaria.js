class GestorInmobiliaria {

    constructor(){
        this.inmuebles = [
            {
              precio: 50000,
              comodidad: {
                wifi: true,
                aptoMascotas: false,
                cercaPlaya: true
              },
              direccion: "Calle Rivadavia 100, CABA"
            },
            {
              precio: 65000,
              comodidad: {
                wifi: false,
                aptoMascotas: true,
                cercaPlaya: false
              },
              direccion: "Av. Corrientes 600, CABA"
            },
            {
              precio: 75000,
              comodidad: {
                wifi: true,
                aptoMascotas: true,
                cercaPlaya: false
              },
              direccion: "Calle Reconquista 900, CABA"
            },
            {
              precio: 85000,
              comodidad: {
                wifi: true,
                aptoMascotas: false,
                cercaPlaya: true
              },
              direccion: "Calle Maipú 800, CABA"
            },
            {
              precio: 95000,
              comodidad: {
                wifi: false,
                aptoMascotas: true,
                cercaPlaya: false
              },
              direccion: "Calle San Martín 700, CABA"
            },
            {
              precio: 105000,
              comodidad: {
                wifi: true,
                aptoMascotas: false,
                cercaPlaya: true
              },
              direccion: "Calle Viamonte 600, CABA"
            },
            {
              precio: 115000,
              comodidad: {
                wifi: false,
                aptoMascotas: true,
                cercaPlaya: false
              },
              direccion: "Calle Lavalle 500, CABA"
            },
            {
              precio: 125000,
              comodidad: {
                wifi: true,
                aptoMascotas: true,
                cercaPlaya: true
              },
              direccion: "Calle Sarmiento 400, CABA"
            },
            {
              precio: 135000,
              comodidad: {
                wifi: false,
                aptoMascotas: false,
                cercaPlaya: true
              },
              direccion: "Calle Tucumán 300, CABA"
            },
            {
              precio: 145000,
              comodidad: {
                wifi: true,
                aptoMascotas: true,
                cercaPlaya: false
              },
              direccion: "Calle Bartolomé Mitre 200, CABA"
            }
          ];
          
          console.log(this.inmuebles);
                  
    }
}