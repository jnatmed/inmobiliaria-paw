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
        }
  
        onInput(event) {
            const query = event.target.value;
            const results = this.options.filterFunction.call(this, query);
            this.updateResults(results);
        }
  
        updateResults(results) {
            this.options.resultList.innerHTML = '';
            results.forEach(result => {
                const listItem = document.createElement('li');
                listItem.classList.add(this.options.listItemClass);
                listItem.textContent = result;
                this.options.resultList.appendChild(listItem);
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
    
    const tipos_propiedad = ["Casa", "Departamento", "Quinta" ]
  
    new Autocompleter(document.getElementById('tipo-alojamiento'), {
        resultsContainer: document.querySelector('#tipo-alojamiento + .autocomplete-results-container'),
        resultList: document.querySelector('#tipo-alojamiento + .autocomplete-results-container .autocomplete-list'),
        filterData: tipos_propiedad
    });
    
  });
  