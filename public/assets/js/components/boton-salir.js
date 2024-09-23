document.addEventListener('DOMContentLoaded', () => {


    document.querySelectorAll(".elemento__botao_saida").forEach((button) => {
        button.state = "default";
      
        // function to transition a button from one state to the next
        let updateButtonState = (button, state) => {
          if (logoutButtonStates[state]) {
            button.state = state;
            for (let key in logoutButtonStates[state]) {
              button.style.setProperty(key, logoutButtonStates[state][key]);
            }
          }
        };
      
        // mouse hover listeners on button
        button.addEventListener("mouseenter", () => {
          if (button.state === "default") {
            updateButtonState(button, "hover");
            button.classList.add("elemento__botao--underline");
          }
        });
        button.addEventListener("mouseleave", () => {
          if (button.state === "hover") {
            updateButtonState(button, "default");
            button.classList.remove("elemento__botao--underline");
          }
        });
      
        // click listener on button
        button.addEventListener("click", () => {
          if (button.state === "default" || button.state === "hover") {
            button.classList.add("clicked");
            button.classList.remove("elemento__botao--underline");
            updateButtonState(button, "walking1");
            setTimeout(() => {
              button.classList.add("elemento__botao--porta-slammed");
              updateButtonState(button, "walking2");
              setTimeout(() => {
                button.classList.add("falling");
                updateButtonState(button, "falling1");
                setTimeout(() => {
                  updateButtonState(button, "falling2");
                  setTimeout(() => {
                    updateButtonState(button, "falling3");
                    setTimeout(() => {
                      button.classList.remove("clicked");
                      button.classList.remove("elemento__botao--porta-slammed");
                      button.classList.remove("falling");
                      updateButtonState(button, "default");

                      // Realizar la solicitud GET al finalizar la animación
                      fetch("/cerrar-sesion")
                        .then(response => {
                          if (!response.ok) {
                            throw new Error('Network response was not ok ' + response.statusText);
                          }
                          return response.json();
                        })
                        .then(data => {
                          console.log("Request successful", data);
                          // Recargar la página
                          window.location.reload();
                        })
                        .catch(error => {
                          console.error("There was a problem with the fetch operation:", error);
                          // Recargar la página incluso si hay un error
                          window.location.reload();
                        });                          


                    }, 50);
                  }, logoutButtonStates["falling2"]["--duracao--caminhada"]);
                }, logoutButtonStates["falling1"]["--duracao--caminhada"]);
              }, logoutButtonStates["walking2"]["--elemento__botao--conjunto-duration"]);
            }, logoutButtonStates["walking1"]["--elemento__botao--conjunto-duration"]);
          }
        });
      });
      
      const logoutButtonStates = {
        default: {
          "--elemento__botao--conjunto-duration": "10",
          "--transform-elemento__botao--conjunto": "none",
          "--duracao--caminhada": "10",
          "--transform-elemento__botao--braco1": "none",
          "--transform-pulso1": "none",
          "--transform-elemento__botao--braco2": "none",
          "--transform-pulso2": "none",
          "--transform-elemento__botao--perna1": "none",
          "--transform-parturilha1": "none",
          "--transform-elemento__botao--perna2": "none",
          "--transform-parturilha2": "none",
        },
        hover: {
          "--elemento__botao--conjunto-duration": "10",
          "--transform-elemento__botao--conjunto": "translateX(1.5px)",
          "--duracao--caminhada": "10",
          "--transform-elemento__botao--braco1": "rotate(-5deg)",
          "--transform-pulso1": "rotate(-15deg)",
          "--transform-elemento__botao--braco2": "rotate(5deg)",
          "--transform-pulso2": "rotate(6deg)",
          "--transform-elemento__botao--perna1": "rotate(-10deg)",
          "--transform-parturilha1": "rotate(5deg)",
          "--transform-elemento__botao--perna2": "rotate(20deg)",
          "--transform-parturilha2": "rotate(-20deg)",
        },
        walking1: {
          "--elemento__botao--conjunto-duration": "30",
          "--transform-elemento__botao--conjunto": "translateX(11px)",
          "--duracao--caminhada": "30",
          "--transform-elemento__botao--braco1":
            "translateX(-4px) translateY(-2px) rotate(120deg)",
          "--transform-pulso1": "rotate(-5deg)",
          "--transform-elemento__botao--braco2": "translateX(4px) rotate(-110deg)",
          "--transform-pulso2": "rotate(-5deg)",
          "--transform-elemento__botao--perna1": "translateX(-3px) rotate(80deg)",
          "--transform-parturilha1": "rotate(-30deg)",
          "--transform-elemento__botao--perna2": "translateX(4px) rotate(-60deg)",
          "--transform-parturilha2": "rotate(20deg)",
        },
        walking2: {
          "--elemento__botao--conjunto-duration": "40",
          "--transform-elemento__botao--conjunto": "translateX(17px)",
          "--duracao--caminhada": "30",
          "--transform-elemento__botao--braco1": "rotate(60deg)",
          "--transform-pulso1": "rotate(-15deg)",
          "--transform-elemento__botao--braco2": "rotate(-45deg)",
          "--transform-pulso2": "rotate(6deg)",
          "--transform-elemento__botao--perna1": "rotate(-5deg)",
          "--transform-parturilha1": "rotate(10deg)",
          "--transform-elemento__botao--perna2": "rotate(10deg)",
          "--transform-parturilha2": "rotate(-20deg)",
        },
        falling1: {
          "--elemento__botao--conjunto-duration": "160",
          "--duracao--caminhada": "40",
          "--transform-elemento__botao--braco1": "rotate(-60deg)",
          "--transform-pulso1": "none",
          "--transform-elemento__botao--braco2": "rotate(30deg)",
          "--transform-pulso2": "rotate(120deg)",
          "--transform-elemento__botao--perna1": "rotate(-30deg)",
          "--transform-parturilha1": "rotate(-20deg)",
          "--transform-elemento__botao--perna2": "rotate(20deg)",
        },
        falling2: {
          "--duracao--caminhada": "30",
          "--transform-elemento__botao--braco1": "rotate(-100deg)",
          "--transform-elemento__botao--braco2": "rotate(-60deg)",
          "--transform-pulso2": "rotate(60deg)",
          "--transform-elemento__botao--perna1": "rotate(80deg)",
          "--transform-parturilha1": "rotate(20deg)",
          "--transform-elemento__botao--perna2": "rotate(-60deg)",
        },
        falling3: {
          "--duracao--caminhada": "50",
          "--transform-elemento__botao--braco1": "rotate(-30deg)",
          "--transform-pulso1": "rotate(40deg)",
          "--transform-elemento__botao--braco2": "rotate(50deg)",
          "--transform-pulso2": "none",
          "--transform-elemento__botao--perna1": "rotate(-30deg)",
          "--transform-elemento__botao--perna2": "rotate(20deg)",
          "--transform-parturilha2": "none",
        },
      };


})