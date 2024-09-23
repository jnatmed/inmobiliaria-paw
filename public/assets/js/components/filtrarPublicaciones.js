class filtrarPublicaciones {
    constructor() {
        const formFiltros = document.getElementById('formFiltros');

        formFiltros.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(formFiltros);
            const queryString = new URLSearchParams(formData).toString();
            const url = `/publicaciones/list?${queryString}`;

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Indica que es una solicitud AJAX
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error en la solicitud: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    document.querySelector('.publicaciones-list').innerHTML = html; // Actualiza la lista de publicaciones
                })
                .catch(error => {
                    console.error('Error en la solicitud AJAX', error);
                });
        });
    }
}
