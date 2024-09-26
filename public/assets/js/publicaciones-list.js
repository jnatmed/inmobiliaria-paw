document.addEventListener("DOMContentLoaded", () => {

	const promiseFiltro = PAW.cargarScriptPromise(
		"filtrarPublicaciones",
		"/assets/js/components/filtrarPublicaciones.js"
	);

	const promiseSliderPrecio = PAW.cargarScriptPromise(
		"SliderPrecio",
		"/assets/js/components/sliderPrecio.js"
	);

	const promiseCarrouselPausa = PAW.cargarScriptPromise(
		"CarrouselPausa",
		"/assets/js/components/carrousel-pausa.js"
	);

	Promise.all([promiseFiltro, promiseSliderPrecio, promiseCarrouselPausa])
		.then(function () {
			new SliderPrecio();
			new filtrarPublicaciones();
			document.querySelectorAll('.publicacion-item').forEach(publicacion => {
				new CarrouselPausa(publicacion);
			});
		})
		.catch(function (error) {
			console.error("Error loading one or more scripts:", error);
		});
});
