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
			document.querySelectorAll('.publicacion-item').forEach(publicacion => {
				new CarrouselPausa(publicacion);
			});
			new SliderPrecio();
			new filtrarPublicaciones();
		})
		.catch(function (error) {
			console.error("Error loading one or more scripts:", error);
		});
});
