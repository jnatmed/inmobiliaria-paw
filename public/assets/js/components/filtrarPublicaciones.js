class filtrarPublicaciones {
    constructor() {
        const formularios = document.querySelectorAll('#formFiltros, #formFiltrosMobile');

        formularios.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault(); 

                const formData = new FormData(form);
                const queryString = new URLSearchParams(formData).toString();
                const url = `/publicaciones/list?${queryString}`;
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Indicamos que es una solicitud AJAX
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.publicaciones-list').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error en la solicitud AJAX', error);
                    });
            });

            // Escuchar el evento de reset
            form.addEventListener('reset', (e) => {
                e.preventDefault(); 
                window.location.href = '/publicaciones/list';
            });
        });
    }
}
