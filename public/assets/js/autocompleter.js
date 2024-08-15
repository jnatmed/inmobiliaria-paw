document.addEventListener('DOMContentLoaded', () => {

    class Autocompleter {
        constructor(inputElement, options = {}) {
            this.inputElement = inputElement;
            this.options = {
                resultsContainer: options.resultsContainer || this.createResultsContainer(),
                resultList: options.resultList || this.createResultList(),
                listItemClass: options.listItemClass || 'autocomplete-item',
                filterFunction: options.filterFunction || this.defaultFilterFunction,
                filterData: options.filterData || [],
                ...options
            };
            this.currentIndex = -1; // Índice del elemento actualmente seleccionado

            // Desactivar el autocompletado del navegador
            this.inputElement.setAttribute('autocomplete', 'off');            

            this.initEvents();
        }

        createResultsContainer() {
            const container = document.createElement('div');
            container.classList.add('autocomplete-results');
            this.inputElement.parentNode.appendChild(container);
            return container;
        }

        createResultList() {
            const list = document.createElement('ul');
            list.classList.add('autocomplete-list');
            this.options.resultsContainer.appendChild(list);
            return list;
        }

        defaultFilterFunction(query) {
            return this.options.filterData.filter(item => item.toLowerCase().includes(query.toLowerCase()));
        }

        initEvents() {
            this.inputElement.addEventListener('input', (event) => this.onInput(event));
            this.options.resultList.addEventListener('click', (event) => {
                if (event.target.tagName === 'LI') {
                    this.onResultClick(event);
                }
            });
            this.inputElement.addEventListener('keydown', (event) => this.onKeyDown(event)); // Agrega la captura de eventos de teclado
            this.inputElement.addEventListener('blur', () => this.clearResults()); // Cierra la lista al perder foco
        }

        onInput(event) {
            const query = event.target.value;
            const results = this.options.filterFunction.call(this, query);
            this.updateResults(results);
        }

        updateResults(results) {
            this.options.resultList.innerHTML = '';
            this.currentIndex = -1; // Reinicia el índice al actualizar los resultados
            results.forEach(result => {
                const listItem = document.createElement('li');
                listItem.classList.add(this.options.listItemClass);
                listItem.textContent = result;
                this.options.resultList.appendChild(listItem);
            });
        }

        onKeyDown(event) {
            const items = this.options.resultList.querySelectorAll(`.${this.options.listItemClass}`);
            if (items.length === 0) return;

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault(); // Evita el comportamiento predeterminado del navegador
                    if (!this.options.resultList.innerHTML) {
                        // Mostrar la lista completa si no hay texto en el input
                        this.updateResults(this.options.filterData);
                    }
                    this.currentIndex = (this.currentIndex + 1) % items.length; // Mueve hacia abajo
                    this.highlightItem(items);
                    break;
                case 'ArrowUp':
                    event.preventDefault(); // Evita el comportamiento predeterminado del navegador
                    if (!this.options.resultList.innerHTML) {
                        // Mostrar la lista completa si no hay texto en el input
                        this.updateResults(this.options.filterData);
                    }
                    this.currentIndex = (this.currentIndex - 1 + items.length) % items.length; // Mueve hacia arriba
                    this.highlightItem(items);
                    break;
                case 'Enter':
                    if (this.currentIndex > -1) {
                        this.inputElement.value = items[this.currentIndex].textContent;
                        this.clearResults();
                    }
                    break;
                case 'Escape': // Oculta la lista de autocompletado al presionar "Escape"
                    this.clearResults();
                    break;                    
            }
        }

        highlightItem(items) {
            items.forEach((item, index) => {
                if (index === this.currentIndex) {
                    item.classList.add('highlighted');
                } else {
                    item.classList.remove('highlighted');
                }
            });
        }

        onResultClick(event) {
            this.inputElement.value = event.target.textContent;
            this.clearResults();
        }

        clearResults() {
            this.options.resultList.innerHTML = '';
        }
    }

    // Uso de la clase Autocompleter
    const tipos_propiedad = ["Casa", "Departamento", "Quinta"];

    new Autocompleter(document.getElementById('tipo-alojamiento'), {
        resultsContainer: document.querySelector('#tipo-alojamiento + .autocomplete-results-container'),
        resultList: document.querySelector('#tipo-alojamiento + .autocomplete-results-container .autocomplete-list'),
        filterData: tipos_propiedad
    });

});
