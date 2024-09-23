document.addEventListener("DOMContentLoaded", () => {
	// const promiseCarrousel = PAW.cargarScriptPromise(
	// 	"Carrousel",
	// 	"/assets/js/components/carrousel.js"
	// );

	const promiseFiltro = PAW.cargarScriptPromise(
		"filtrarPublicaciones",
		"/assets/js/components/filtrarPublicaciones.js"
	);

	Promise.all([promiseFiltro])
		.then(function () {
			new filtrarPublicaciones(); 
		})
		.catch(function (error) {
			console.error("Error loading one or more scripts:", error);
		});
});
