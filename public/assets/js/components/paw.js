class PAW {

    //nuevoElemento("script", "", {scr: URL, name: "nombreDelScript"})  
   static nuevoElemento(tag, contenido, atributos = {}) {
       let elemento = document.createElement(tag);
      
       for (const atributo in atributos) {
           elemento.setAttribute(atributo, atributos[atributo])
       }
      if (contenido.tagName)
          elemento.appendChild(contenido); 
      else
          elemento.appendChild(document.createTextNode(contenido));
  
      return elemento;
   }   
  
   static cargarScript (nombre, url, fnCallback = null) {
       let elemento = document.querySelector("script#" + nombre);
       if (!elemento) {
          //Creo el tag script
          elemento = this.nuevoElemento("script","",{src: url, id: nombre});
          
          //Funcion de Callback
          if (fnCallback)
              elemento.addEventListener("load", fnCallback);
  
          document.head.appendChild(elemento);
       }
  
      return elemento;
   }

   static cargarScriptPromise(nombre, url) {
    return new Promise((resolve, reject) => {
        let elemento = document.querySelector("script#" + nombre);
        if (!elemento) {
            // Creo el tag script
            elemento = this.nuevoElemento("script", "", { src: url, id: nombre });
            
            // Evento de carga
            elemento.addEventListener("load", () => {
                resolve(elemento);
            });

            // Evento de error
            elemento.addEventListener("error", () => {
                reject(new Error(`Error loading script: ${url}`));
            });

            document.head.appendChild(elemento);
        } else {
            resolve(elemento);
        }
    });
    }


  }