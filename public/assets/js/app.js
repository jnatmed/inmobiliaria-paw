class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */

      // if (['/'].includes(window.location.pathname)) 
      //   {
      //     document.querySelectorAll(".elemento__botao_saida").forEach((button) => {
      //       button.state = "default";
          
      //       // function to transition a button from one state to the next
      //       let updateButtonState = (button, state) => {
      //         if (logoutButtonStates[state]) {
      //           button.state = state;
      //           for (let key in logoutButtonStates[state]) {
      //             button.style.setProperty(key, logoutButtonStates[state][key]);
      //           }
      //         }
      //       };
          
      //       // mouse hover listeners on button
      //       button.addEventListener("mouseenter", () => {
      //         if (button.state === "default") {
      //           updateButtonState(button, "hover");
      //           button.classList.add("elemento__botao--underline");
      //         }
      //       });
      //       button.addEventListener("mouseleave", () => {
      //         if (button.state === "hover") {
      //           updateButtonState(button, "default");
      //           button.classList.remove("elemento__botao--underline");
      //         }
      //       });
          
      //       // click listener on button
      //       button.addEventListener("click", () => {
      //         if (button.state === "default" || button.state === "hover") {
      //           button.classList.add("clicked");
      //           button.classList.remove("elemento__botao--underline");
      //           updateButtonState(button, "walking1");
      //           setTimeout(() => {
      //             button.classList.add("elemento__botao--porta-slammed");
      //             updateButtonState(button, "walking2");
      //             setTimeout(() => {
      //               button.classList.add("falling");
      //               updateButtonState(button, "falling1");
      //               setTimeout(() => {
      //                 updateButtonState(button, "falling2");
      //                 setTimeout(() => {
      //                   updateButtonState(button, "falling3");
      //                   setTimeout(() => {
      //                     button.classList.remove("clicked");
      //                     button.classList.remove("elemento__botao--porta-slammed");
      //                     button.classList.remove("falling");
      //                     updateButtonState(button, "default");

      //                     // Realizar la solicitud GET al finalizar la animación
      //                     fetch("/cerrar-sesion")
      //                       .then(response => {
      //                         if (!response.ok) {
      //                           throw new Error('Network response was not ok ' + response.statusText);
      //                         }
      //                         return response.json();
      //                       })
      //                       .then(data => {
      //                         console.log("Request successful", data);
      //                         // Recargar la página
      //                         window.location.reload();
      //                       })
      //                       .catch(error => {
      //                         console.error("There was a problem with the fetch operation:", error);
      //                         // Recargar la página incluso si hay un error
      //                         window.location.reload();
      //                       });                          


      //                   }, 1000);
      //                 }, logoutButtonStates["falling2"]["--duracao--caminhada"]);
      //               }, logoutButtonStates["falling1"]["--duracao--caminhada"]);
      //             }, logoutButtonStates["walking2"]["--elemento__botao--conjunto-duration"]);
      //           }, logoutButtonStates["walking1"]["--elemento__botao--conjunto-duration"]);
      //         }
      //       });
      //     });
          
      //     const logoutButtonStates = {
      //       default: {
      //         "--elemento__botao--conjunto-duration": "100",
      //         "--transform-elemento__botao--conjunto": "none",
      //         "--duracao--caminhada": "100",
      //         "--transform-elemento__botao--braco1": "none",
      //         "--transform-pulso1": "none",
      //         "--transform-elemento__botao--braco2": "none",
      //         "--transform-pulso2": "none",
      //         "--transform-elemento__botao--perna1": "none",
      //         "--transform-parturilha1": "none",
      //         "--transform-elemento__botao--perna2": "none",
      //         "--transform-parturilha2": "none",
      //       },
      //       hover: {
      //         "--elemento__botao--conjunto-duration": "100",
      //         "--transform-elemento__botao--conjunto": "translateX(1.5px)",
      //         "--duracao--caminhada": "100",
      //         "--transform-elemento__botao--braco1": "rotate(-5deg)",
      //         "--transform-pulso1": "rotate(-15deg)",
      //         "--transform-elemento__botao--braco2": "rotate(5deg)",
      //         "--transform-pulso2": "rotate(6deg)",
      //         "--transform-elemento__botao--perna1": "rotate(-10deg)",
      //         "--transform-parturilha1": "rotate(5deg)",
      //         "--transform-elemento__botao--perna2": "rotate(20deg)",
      //         "--transform-parturilha2": "rotate(-20deg)",
      //       },
      //       walking1: {
      //         "--elemento__botao--conjunto-duration": "300",
      //         "--transform-elemento__botao--conjunto": "translateX(11px)",
      //         "--duracao--caminhada": "300",
      //         "--transform-elemento__botao--braco1":
      //           "translateX(-4px) translateY(-2px) rotate(120deg)",
      //         "--transform-pulso1": "rotate(-5deg)",
      //         "--transform-elemento__botao--braco2": "translateX(4px) rotate(-110deg)",
      //         "--transform-pulso2": "rotate(-5deg)",
      //         "--transform-elemento__botao--perna1": "translateX(-3px) rotate(80deg)",
      //         "--transform-parturilha1": "rotate(-30deg)",
      //         "--transform-elemento__botao--perna2": "translateX(4px) rotate(-60deg)",
      //         "--transform-parturilha2": "rotate(20deg)",
      //       },
      //       walking2: {
      //         "--elemento__botao--conjunto-duration": "400",
      //         "--transform-elemento__botao--conjunto": "translateX(17px)",
      //         "--duracao--caminhada": "300",
      //         "--transform-elemento__botao--braco1": "rotate(60deg)",
      //         "--transform-pulso1": "rotate(-15deg)",
      //         "--transform-elemento__botao--braco2": "rotate(-45deg)",
      //         "--transform-pulso2": "rotate(6deg)",
      //         "--transform-elemento__botao--perna1": "rotate(-5deg)",
      //         "--transform-parturilha1": "rotate(10deg)",
      //         "--transform-elemento__botao--perna2": "rotate(10deg)",
      //         "--transform-parturilha2": "rotate(-20deg)",
      //       },
      //       falling1: {
      //         "--elemento__botao--conjunto-duration": "1600",
      //         "--duracao--caminhada": "400",
      //         "--transform-elemento__botao--braco1": "rotate(-60deg)",
      //         "--transform-pulso1": "none",
      //         "--transform-elemento__botao--braco2": "rotate(30deg)",
      //         "--transform-pulso2": "rotate(120deg)",
      //         "--transform-elemento__botao--perna1": "rotate(-30deg)",
      //         "--transform-parturilha1": "rotate(-20deg)",
      //         "--transform-elemento__botao--perna2": "rotate(20deg)",
      //       },
      //       falling2: {
      //         "--duracao--caminhada": "300",
      //         "--transform-elemento__botao--braco1": "rotate(-100deg)",
      //         "--transform-elemento__botao--braco2": "rotate(-60deg)",
      //         "--transform-pulso2": "rotate(60deg)",
      //         "--transform-elemento__botao--perna1": "rotate(80deg)",
      //         "--transform-parturilha1": "rotate(20deg)",
      //         "--transform-elemento__botao--perna2": "rotate(-60deg)",
      //       },
      //       falling3: {
      //         "--duracao--caminhada": "500",
      //         "--transform-elemento__botao--braco1": "rotate(-30deg)",
      //         "--transform-pulso1": "rotate(40deg)",
      //         "--transform-elemento__botao--braco2": "rotate(50deg)",
      //         "--transform-pulso2": "none",
      //         "--transform-elemento__botao--perna1": "rotate(-30deg)",
      //         "--transform-elemento__botao--perna2": "rotate(20deg)",
      //         "--transform-parturilha2": "none",
      //       },
      //     };
          
      //   }


      
      if (['/publicacion/new'].includes(window.location.pathname))
        {
            // Cargar scripts individualmente usando Promesas
            const promiseFormularioMultiStep = PAW.cargarScriptPromise('FormularioMultistep', '/assets/js/components/formularioMultiStep.js');
            const promiseDrag_Drop = PAW.cargarScriptPromise("Drag_Drop", "/assets/js/components/drag-drop.js");
            const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');

            // Usar Promise.all para esperar a que todos los scripts se carguen
            Promise.all([promiseFormularioMultiStep, promiseDrag_Drop, promiseMapaLeafLet]).then(function() {
                // Una vez que todos los scripts se han cargado, ejecutar el código que depende de esos scripts


                const mapaLeaf = new MapaLeaflet()

                const locationDiv = document.querySelector('#location');
                
                // Agregar un event listener al botón de búsqueda
                document.querySelector('#buscarUbicacion').addEventListener('click', (event) => {
                  event.preventDefault(); // Evitar comportamiento predeterminado del botón
                  const address = document.querySelector('#ubicacion').value;
                  // console.log(address);
                  mapaLeaf.buscar(address);
                });

                document.querySelectorAll('.input-dad').forEach(dropArea => {
                    const inputId = dropArea.dataset.input;
                    const inputFile = document.querySelector(`#${inputId}`);
                    const output = dropArea.nextElementSibling;
                    
                    // Inicializar la funcionalidad Drag and Drop
                    new Drag_Drop(dropArea, inputFile, output);

                    // Inicializar el formulario multistep
                    new FormularioMultistep();
                });
            }).catch(function(error) {
                // Manejar cualquier error en la carga de scripts
                console.error('Error loading one or more scripts:', error);
            });
      
        }


      if (['/buscar', '/publicacion/new'].includes(window.location.pathname))
      {

        PAW.cargarScript('GestorInmobiliaria', '/assets/js/components/gestor-inmobiliaria.js')
        PAW.cargarScript('Cookier', '/assets/js/components/cookier.js')
        PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{         

          let gestor = new GestorInmobiliaria()


        

            
        })
      }        
      
      if (['/', '/publicaciones/list', '/mis_publicaciones'].includes(window.location.pathname)) {
        // Seleccionar todos los elementos con la clase .carousel
        const carousels = document.querySelectorAll('.carousel');
        
        // Iterar sobre cada elemento .carousel
        carousels.forEach(carousel => {
            let currentIndex = 0;
    
            function nextSlide() {
                currentIndex++;
                if (currentIndex >= carousel.children.length) {
                    currentIndex = 0;
                }
                showSlide(carousel, currentIndex);
            }
    
            function prevSlide() {
                currentIndex--;
                if (currentIndex < 0) {
                    currentIndex = carousel.children.length - 1;
                }
                showSlide(carousel, currentIndex);
            }
    
            function showSlide(carousel, index) {
                const offset = index * carousel.clientWidth;
                carousel.scrollLeft = offset;
            }
    
            // Agregar listeners para los botones de navegación
            carousel.parentElement.querySelector('.prevButton').addEventListener('click', prevSlide);
            carousel.parentElement.querySelector('.nextButton').addEventListener('click', nextSlide);
        });
    }

    })
  }
}

const app = new appPAW()
