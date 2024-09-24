document.addEventListener("DOMContentLoaded", () => {
	// const promiseCarrousel = PAW.cargarScriptPromise(
	// 	"Carrousel",
	// 	"/assets/js/components/carrousel.js"
	// );

	const promiseFiltro = PAW.cargarScriptPromise(
		"filtrarPublicaciones",
		"/assets/js/components/filtrarPublicaciones.js"
	);

	const promiseSliderPrecio = PAW.cargarScriptPromise(
		"SliderPrecio",
		"/assets/js/components/sliderPrecio.js"
	);

	Promise.all([promiseFiltro, promiseSliderPrecio])
		.then(function () {
			new SliderPrecio();
			new filtrarPublicaciones(); 
		})
		.catch(function (error) {
			console.error("Error loading one or more scripts:", error);
		});
});
