class Calendario {

    constructor() {

        this.today = this.toKey(new Date());
        const todayDate = this.fromKey(this.today);

        this.currentMonth = todayDate.getMonth();
        this.currentYear = todayDate.getFullYear();

        this.months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        this.occupiedDates = new Set();
        this.startDate = null;
        this.endDate = null;
        this.intervalsLoaded = false;

        this.container = document.getElementById('calendarContainer');
        this.title = document.getElementById('calendarTitle');
        this.table = document.getElementById('calendarTable');
        this.prevButton = document.getElementById('prevMonthButton');
        this.nextButton = document.getElementById('nextMonthButton');

        this.inputDesde = document.getElementById('input-desde');
        this.inputHasta = document.getElementById('input-hasta');

        this.clearButton = document.getElementById('clearCalendarSelectionButton');

        this.message = document.getElementById('calendarSelectionMessage');

        this.form = this.inputDesde ? this.inputDesde.closest('form') : null;

        this.canReserve = Boolean(this.inputDesde && this.inputHasta && this.form);

        if (!this.container || !this.title || !this.table || !this.prevButton || !this.nextButton) {
            return;
        }

        if (this.canReserve) {
            this.inputDesde.min = this.today;
            this.inputHasta.min = this.addDays(this.today, 1);
        }

        this.renderCalendar();
        this.addEventListeners();

        this.setMessage('Cargando disponibilidad del calendario...');

    }

    async init() {

        if (!this.container) {
            return;
        }

        try {

            const urlParams = new URLSearchParams(window.location.search);

            const idPublicacion = urlParams.get('id_pub');

            if (!idPublicacion) {
                throw new Error('Falta el parámetro id_pub en la URL.');
            }

            const response = await fetch(
                `/reservas/intervalos?id_pub=${encodeURIComponent(idPublicacion)}`
            );

            if (!response.ok) {
                throw new Error('No se pudieron obtener los intervalos de reserva.');
            }

            const intervals = await response.json();

            if (!Array.isArray(intervals)) {
                throw new Error('La respuesta de intervalos no tiene el formato esperado.');
            }

            this.loadOccupiedDates(intervals);

            this.intervalsLoaded = true;

            this.paintCalendar();

            if (this.canReserve) {
                this.setMessage('Seleccioná una fecha de inicio y después una fecha de finalización.');
            } else {
                this.setMessage('Consultá la disponibilidad: rojo indica ocupado y gris indica una fecha pasada.');
            }

        } catch (error) {

            this.intervalsLoaded = false;

            this.paintCalendar();

            this.setMessage(
                'No se pudo cargar la disponibilidad. Recargá la página antes de intentar reservar.',
                'error'
            );

            console.error(
                'Error al cargar los intervalos de reserva:',
                error
            );

        }
    }

    renderCalendar() {

        const body = this.table.querySelector('tbody');

        const firstDay = new Date(
            this.currentYear,
            this.currentMonth,
            1
        ).getDay();

        const daysInMonth = new Date(
            this.currentYear,
            this.currentMonth + 1,
            0
        ).getDate();

        const firstColumn = firstDay === 0 ? 6 : firstDay - 1;

        const monthName = this.months[this.currentMonth];

        const capitalizedMonth = monthName.charAt(0).toUpperCase() + monthName.slice(1);

        this.title.textContent = `${capitalizedMonth} ${this.currentYear}`;

        body.innerHTML = '';

        let day = 1;

        for (let rowNumber = 0; rowNumber < 6; rowNumber++) {

            const row = document.createElement('tr');

            for (let column = 0; column < 7; column++) {

                const cell = document.createElement('td');

                const hasDay =
                    !(
                        rowNumber === 0
                        && column < firstColumn
                    )
                    && day <= daysInMonth;

                if (hasDay) {
                    const date = new Date(this.currentYear, this.currentMonth, day);

                    const dateKey = this.toKey(date);

                    cell.textContent = day;

                    /*Cada celda guarda su fecha completa. Ejemplo: data-date="2024-08-15"*/
                    cell.dataset.date = dateKey;

                    cell.setAttribute('aria-label', this.toDisplay(dateKey));

                    day++;

                } else {

                    cell.classList.add('calendar-empty-day');

                    cell.setAttribute('aria-hidden', 'true');

                }

                row.appendChild(cell);

            }

            body.appendChild(row);

        }

        this.paintCalendar();
        this.updateNavigationButtons();

    }

    addEventListeners() {

        this.prevButton.addEventListener(
            'click',
            () => {this.changeMonth(-1);}
        );

        this.nextButton.addEventListener(
            'click',
            () => {this.changeMonth(1);}
        );

        //Clics sobre las celdas del calendario
        this.table.addEventListener(
            'click',
            (event) => {
                const cell = event.target.closest('td[data-date]');
                if (cell) {
                    this.handleDayClick(cell);
                }
            }
        );

        //Se permite seleccionar con enter o espacio tambien
        this.table.addEventListener(
            'keydown',
            (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                const cell = event.target.closest('td[data-date]');

                if (cell) {
                    event.preventDefault();
                    this.handleDayClick(cell);
                }
            }
        );

        /*Estos listeners solamente se agregan cuando existe el formulario de reserva*/
        if (this.canReserve) {

            this.inputDesde.addEventListener(
                'change',
                () => {this.syncFromInputs();}
            );

            this.inputHasta.addEventListener(
                'change',
                () => {this.syncFromInputs();}
            );

            this.form.addEventListener(
                'submit',
                (event) => {this.validateBeforeSubmit(event);}
            );

        }

        if (this.clearButton) {

            this.clearButton.addEventListener(
                'click',
                () => {
                    this.clearSelection();
                    this.setMessage('Selección eliminada. Elegí una nueva fecha de inicio.');
                }
            );

        }
    }

    changeMonth(amount) {

        const candidate = new Date(
            this.currentYear,
            this.currentMonth + amount,
            1
        );

        const currentMonth = this.fromKey(this.today);

        currentMonth.setDate(1);

        /*No permite navegar hacia meses anteriores al mes actual*/
        if (candidate < currentMonth) {
            return;
        }

        this.currentMonth = candidate.getMonth();
        this.currentYear = candidate.getFullYear();

        this.renderCalendar();

    }

    updateNavigationButtons() {

        const displayedMonth = new Date(
            this.currentYear,
            this.currentMonth,
            1
        );

        const currentMonth = this.fromKey(this.today);

        currentMonth.setDate(1);

        this.prevButton.disabled = displayedMonth <= currentMonth;

    }

    loadOccupiedDates(intervals) {

        this.occupiedDates.clear();

        intervals.forEach((interval) => {

            if (!Array.isArray(interval) || interval.length < 2) {
                return;
            }

            const start = this.backendDateToKey(interval[0]);

            const end = this.backendDateToKey(interval[1]);

            if (!start || !end || end < start) {
                return;
            }

            /*Se agregan todas las fechas del intervalo, incluyendo fecha inicial y fecha final*/
            for (let current = start; current <= end; current = this.addDays(current, 1)) {
                this.occupiedDates.add(current);
            }
        });
    }

    paintCalendar() {

        const cells = this.container.querySelectorAll('td[data-date]');

        cells.forEach((cell) => {

            const date = cell.dataset.date;

            cell.classList.remove(
                'ocupado',
                'libre',
                'past-date',
                'calendar-loading-date',
                'calendar-day-selectable',
                'fecha-inicio',
                'fecha-rango',
                'fecha-fin'
            );

            cell.removeAttribute('role');
            cell.removeAttribute('tabindex');

            cell.setAttribute('aria-disabled', 'true');

            /*Primero se define el estado de disponibilidad*/
            if (date < this.today) {

                cell.classList.add('past-date');

            } else if (!this.intervalsLoaded) {

                cell.classList.add('calendar-loading-date');

            } else if (this.occupiedDates.has(date)) {

                cell.classList.add('ocupado');

            } else {

                cell.classList.add('libre');

                /*Solamente puede seleccionar quien tiene visible el formulario de reserva*/
                if (this.canReserve) {

                    cell.classList.add('calendar-day-selectable');
                    cell.setAttribute('role', 'button');
                    cell.setAttribute('tabindex', '0');
                    cell.setAttribute('aria-disabled', 'false');

                }
            }

            /*Despues se pinta la seleccion actual*/
            if (date === this.startDate) {

                cell.classList.add('fecha-inicio');

            } else if (date === this.endDate) {

                cell.classList.add('fecha-fin');

            } else if (this.startDate && this.endDate && date > this.startDate && date < this.endDate) {

                cell.classList.add('fecha-rango');

            }
        });
    }

    handleDayClick(cell) {

        /*Sin formulario o sin intervalos cargados, el calendario es solamente informativo*/
        if (!this.canReserve || !this.intervalsLoaded) {
            return;
        }

        if (!cell.classList.contains('calendar-day-selectable')) {
            return;
        }

        const selectedDate = cell.dataset.date;

        this.clearInputErrors();

        //Primer clic o clic posterior a un rango completo: comienza una nueva seleccion
        if (!this.startDate || this.endDate) {

            this.selectStartDate(selectedDate);
            return;

        }

        //Si vuelve a elegir el mismo dia, se informa que el final debe ser posterior
        if (selectedDate === this.startDate) {

            this.setMessage(
                `La fecha de finalización debe ser posterior al ${this.toDisplay(this.startDate)}.`,
                'error'
            );

            return;

        }

        //Si elige un dia anterior, ese dia pasa a ser el nuevo inicio
        if (selectedDate < this.startDate) {

            this.selectStartDate(selectedDate);

            this.setMessage(
                `Cambiaste el inicio al ${this.toDisplay(selectedDate)}. Elegí una fecha posterior.`
            );

            return;

        }

        const error = this.getRangeError(this.startDate, selectedDate);

        if (error) {

            this.endDate = null;
            this.inputHasta.value = '';
            this.paintCalendar();
            this.setMessage(error, 'error');
            return;

        }

        this.endDate = selectedDate;

        this.inputHasta.value = selectedDate;

        this.paintCalendar();

        this.setMessage(
            `Rango seleccionado: ${this.toDisplay(this.startDate)} al ${this.toDisplay(this.endDate)}.`,
            'success'
        );

    }

    selectStartDate(date) {

        this.startDate = date;
        this.endDate = null;

        this.inputDesde.value = date;
        this.inputHasta.value = '';

        /*La fecha final debe ser al menos el día siguiente*/
        this.inputHasta.min = this.addDays(date, 1);

        this.paintCalendar();

        this.setMessage(`Fecha de inicio: ${this.toDisplay(date)}. Ahora elegí la fecha de finalización.`);

    }

    syncFromInputs() {

        this.clearInputErrors();

        const start = this.inputDesde.value;
        const end = this.inputHasta.value;

        this.startDate = null;
        this.endDate = null;

        if (!start && !end) {

            this.paintCalendar();
            this.setMessage('Seleccioná una fecha de inicio y después una fecha de finalización.');
            return true;

        }

        if (!start) {
            return this.showInputError(this.inputDesde, 'Elegí primero una fecha de inicio.');
        }

        const startError = this.getStartDateError(start);

        if (startError) {

            return this.showInputError(
                this.inputDesde,
                startError
            );

        }

        this.startDate = start;

        this.inputHasta.min = this.addDays(start, 1);

        if (!end) {

            this.paintCalendar();
            this.setMessage(`Fecha de inicio: ${this.toDisplay(start)}. Ahora elegí la fecha de finalización.`);
            return true;

        }

        const rangeError = this.getRangeError(start, end);

        if (rangeError) {

            this.paintCalendar();
            return this.showInputError(this.inputHasta, rangeError);

        }

        this.endDate = end;

        this.paintCalendar();

        this.setMessage(
            `Rango seleccionado: ${this.toDisplay(start)} al ${this.toDisplay(end)}.`,
            'success'
        );

        return true;

    }

    validateBeforeSubmit(event) {

        /*No se permite enviar si no se pudo consultar la disponibilidad*/
        if (!this.intervalsLoaded) {

            event.preventDefault();
            this.showInputError(this.inputDesde, 'No se pudo verificar la disponibilidad. Recargá la página.');
            this.inputDesde.reportValidity();

            return;

        }

        /*Vuelve a comprobar los inputs antes del POST*/
        if (!this.syncFromInputs()) {

            event.preventDefault();

            if (!this.inputDesde.checkValidity()) {
                this.inputDesde.reportValidity();
            } else {
                this.inputHasta.reportValidity();
            }

        }
    }

    getStartDateError(date) {

        if (!this.isValidKey(date)) {
            return 'La fecha de inicio no es válida.';
        }

        if (date < this.today) {
            return 'La fecha de inicio no puede estar en el pasado.';
        }

        if (this.occupiedDates.has(date)) {
            return 'La fecha de inicio está ocupada.';
        }

        return null;

    }

    getRangeError(start, end) {

        if (!this.isValidKey(end)) {
            return 'La fecha de finalización no es válida.';
        }

        if (end <= start) {
            return 'La fecha de finalización debe ser posterior a la fecha de inicio.';
        }

        /*Recorre el intervalo completo, aunque atraviese semanas, meses o años*/
        for (let current = start; current <= end; current = this.addDays(current, 1)) {

            if (this.occupiedDates.has(current)) {
                return 'El rango elegido atraviesa al menos una fecha ocupada.';
            }

        }

        return null;
    
    }

    clearSelection() {

        this.startDate = null;
        this.endDate = null;

        if (this.canReserve) {

            this.inputDesde.value = '';
            this.inputHasta.value = '';

            this.inputHasta.min = this.addDays(this.today, 1);

            this.clearInputErrors();

        }

        this.paintCalendar();

    }

    clearInputErrors() {

        if (!this.canReserve) {
            return;
        }

        this.inputDesde.setCustomValidity('');
        this.inputHasta.setCustomValidity('');

    }

    showInputError(input, message) {

        input.setCustomValidity(message);

        this.setMessage(message, 'error');

        this.paintCalendar();

        return false;

    }

    setMessage(message, type = 'info') {

        if (!this.message) {
            return;
        }

        this.message.textContent = message;

        this.message.classList.remove(
            'calendar-selection-message--error',
            'calendar-selection-message--success'
        );

        if (type === 'error') {

            this.message.classList.add('calendar-selection-message--error');

        } else if (type === 'success') {

            this.message.classList.add('calendar-selection-message--success');

        }

    }

    /*Convierte "15/08/2026" a "2026-08-15"*/
    backendDateToKey(value) {

        if (typeof value !== 'string') {
            return null;
        }

        const parts = value
            .split('/')
            .map(Number);

        const day = parts[0];
        const month = parts[1];
        const year = parts[2];

        if (!Number.isInteger(day) || !Number.isInteger(month) || !Number.isInteger(year)) {
            return null;
        }

        const key = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        return this.isValidKey(key) ? key : null;

    }

    isValidKey(value) {

        if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return false;
        }

        const parts = value
            .split('-')
            .map(Number);

        const year = parts[0];
        const month = parts[1];
        const day = parts[2];

        const date = new Date(year, month - 1, day);

        return (
            date.getFullYear() === year
            && date.getMonth() === month - 1
            && date.getDate() === day
        );

    }

    fromKey(value) {

        const parts = value
            .split('-')
            .map(Number);

        const year = parts[0];
        const month = parts[1];
        const day = parts[2];

        return new Date(
            year,
            month - 1,
            day
        );

    }

    toKey(date) {

        const year = date.getFullYear();

        const month = String(date.getMonth() + 1).padStart(2, '0');

        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;

    }

    addDays(value, amount) {

        const date = this.fromKey(value);

        date.setDate(date.getDate() + amount);

        return this.toKey(date);

    }

    toDisplay(value) {

        const parts = value.split('-');

        const year = parts[0];
        const month = parts[1];
        const day = parts[2];

        return `${day}/${month}/${year}`;
        
    }
}