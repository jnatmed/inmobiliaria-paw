class publicacionNew {

  constructor() {

    document.addEventListener('DOMContentLoaded', () => {

      const promiseFormularioMultiStep = PAW.cargarScriptPromise(
        'FormularioMultistep',
        '/assets/js/components/formularioMultiStep.js'
      );

      const promiseDragDrop = PAW.cargarScriptPromise(
        'DragDrop',
        '/assets/js/components/drag-drop.js'
      );

      const promiseMapaLeafLet = PAW.cargarScriptPromise(
        'MapaLeaflet',
        '/assets/js/components/mapaLeaflet.js'
      );

      const promiseFormatterNumberInputs = PAW.cargarScriptPromise(
        'FormatterNumberInputs',
        '/assets/js/components/FormatterNumberInputs.js'
      );

      Promise.all([
        promiseFormatterNumberInputs,
        promiseFormularioMultiStep,
        promiseDragDrop,
        promiseMapaLeafLet
      ]).then(function () {
        new FormularioMultistep();
        new FormatterNumberInputs('#precio');
        new DragDrop();

        const mapaLeaf = new MapaLeaflet();
        const ubicacionInput = document.querySelector('#ubicacion');
        const buscarButton = document.querySelector('#buscarUbicacion');
        const loading = document.querySelector('.loader');
        const resultadosContainer = document.querySelector('#resultados-ubicacion');
        const resultadosLista = document.querySelector('#lista-resultados-ubicacion');
        const mensajeBusqueda = document.querySelector('#mensaje-busqueda-ubicacion');

        /*Vacía y oculta la lista de resultados*/
        const limpiarResultados = () => {
          resultadosLista.innerHTML = '';
          resultadosContainer.hidden = true;
        };

        /*Muestra mensajes de informacion, exito o error*/
        const mostrarMensaje = (mensaje, tipo = 'informacion') => {

          mensajeBusqueda.textContent = mensaje;
          mensajeBusqueda.classList.remove('exito', 'error', 'informacion');

          if (mensaje !== '') {
            mensajeBusqueda.classList.add(tipo);
          }

        };

        /*Convierte el arreglo de datos faltantes en texto*/
        const describirFaltantes = faltantes => {

          if (!Array.isArray(faltantes) || faltantes.length === 0) {
            return '';
          }

          return faltantes.join(', ');

        };

        /*Informa si la direccion seleccionada está completa*/
        const informarEstadoSeleccion = estado => {

          if (estado.exito) {
            mostrarMensaje('Ubicación seleccionada. Podés mover el marcador para ajustar el punto exacto.', 'exito');
            return;
          }

          mostrarMensaje(
            `La ubicación fue encontrada, pero faltan estos datos: ${describirFaltantes(estado.faltantes)}. Elegí otra opción o mové el marcador para intentar completar la dirección.`,
            'error'
          );
        };

        /*Se ejecuta cuando el usuario elige una opcion*/
        const seleccionarResultado = resultado => {

          try {

            const estado = mapaLeaf.seleccionarResultado(resultado);

            /*Muestra en el buscador la opcion que realmente eligio el usuario*/
            ubicacionInput.value = estado.direccionCompleta;

            limpiarResultados();

            informarEstadoSeleccion(estado);

          } catch (error) {

            console.error('No se pudo seleccionar la ubicación:', error);

            mostrarMensaje('No se pudo utilizar esa ubicación. Elegí otra opción.', 'error');

          }
        };

        /*Construye la lista de resultados sin usar innerHTML con datos obtenidos desde Nominatim*/
        const mostrarResultados = resultados => {

          limpiarResultados();

          resultados.forEach((resultado, indice) => {

            const item = document.createElement('li');

            item.classList.add('autocomplete-item');

            const opcion = document.createElement('button');

            opcion.type = 'button';
            opcion.classList.add('autocomplete-option');

            const titulo = document.createElement('span');
            titulo.classList.add('autocomplete-option-titulo');
            titulo.textContent = resultado.name || `Resultado ${indice + 1}`;

            const detalle = document.createElement('span');
            detalle.classList.add('autocomplete-option-detalle');
            detalle.textContent = resultado.display_name || 'Dirección sin descripción';

            opcion.appendChild(titulo);
            opcion.appendChild(detalle);

            opcion.addEventListener('click', () => {

              seleccionarResultado(resultado);

            });

            item.appendChild(opcion);
            resultadosLista.appendChild(item);

          });

          resultadosContainer.hidden = false;

        };

        /*Realiza una búsqueda explícita. Se ejecuta al presionar el boton*/
        const ejecutarBusqueda = async () => {

          if (buscarButton.disabled) { /*Evita lanzar dos búsquedas mientras todavíaestá pendiente la primera*/
            return;
          }

          const address = ubicacionInput.value.trim();

          limpiarResultados();

          if (address === '') {

            mapaLeaf.limpiarDatosUbicacion();
            mostrarMensaje('Escribí una dirección, localidad o provincia antes de buscar.', 'error');
            ubicacionInput.focus();

            return;

          }

          /*Se limpian los valores anteriores. Para evitar luego enviar accidentalmente coordenadas anteriores*/
          mapaLeaf.limpiarDatosUbicacion();

          mostrarMensaje('Buscando ubicaciones...', 'informacion');

          loading.classList.add('activo');
          buscarButton.disabled = true;

          try {

            const resultados = await mapaLeaf.buscarResultados(address, 5);

            if (resultados.length === 0) {
              mostrarMensaje('No se encontraron coincidencias. Probá agregando localidad y provincia.', 'error');
              return;
            }

            mostrarResultados(resultados);

            mostrarMensaje(`Se encontraron ${resultados.length} opciones. Elegí la ubicación correcta.`, 'informacion');

          } catch (error) {

            console.error('Error al buscar la ubicación:', error);

            mostrarMensaje('No se pudo consultar el servicio de ubicaciones. Intentá nuevamente.', 'error');

          } finally {

            loading.classList.remove('activo');
            buscarButton.disabled = false;

          }
        };

        /*Buscar con el boton*/
        buscarButton.addEventListener('click', ejecutarBusqueda);

        /*Buscar con Enter*/
        ubicacionInput.addEventListener('keydown', event => {

          if (event.key === 'Enter') {
            event.preventDefault();
            ejecutarBusqueda();
          }

        });

        /*Si cambia el texto, la selección anterior deja de ser valida*/
        ubicacionInput.addEventListener('input', () => {

          mapaLeaf.limpiarDatosUbicacion();
          limpiarResultados();
          mostrarMensaje('');

        });

        /*MapaLeaflet dispara este evento cuando termina de actualizar la dirección despues de arrastrar*/
        document.addEventListener('mapa:ubicacion-actualizada', event => {

          const estado = event.detail;

          if (!estado || typeof estado !== 'object') {
            return;
          }

          if (estado.direccionCompleta !== '') {
            ubicacionInput.value = estado.direccionCompleta;
          }

          informarEstadoSeleccion(estado);

        });

        /*Cierra la lista al hacer click fuera del buscador*/
        document.addEventListener('click', event => {

          const sectorBusqueda = event.target.closest('.input-busqueda');

          if (!sectorBusqueda) {
            limpiarResultados();
          }

        });

      }).catch(function (error) {

        console.error('Error loading one or more scripts:', error);
        
      });
    });
  }
}

const appPublicacion = new publicacionNew();