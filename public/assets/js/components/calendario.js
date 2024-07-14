class Calendario {
    constructor() {
        this.currentDate = new Date();
        this.currentMonth = this.currentDate.getMonth();
        this.currentYear = this.currentDate.getFullYear();
        this.months = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        this.markedIntervals = [];
        this.renderCalendar();
        this.addEventListeners();
    }

    async init() {
        try {
            const response = await fetch('/reservas/intervalos');
            if (!response.ok) {
                throw new Error('Error al obtener los intervalos de reserva');
            }
            const periodos = await response.json();
            console.log(periodos);
            this.marcarIntervalos(periodos);
            // Aquí puedes utilizar los intervalos de reserva como desees
        } catch (error) {
            console.error('Error al cargar los intervalos de reserva:', error);
        }
    }

    getMonthIndex(monthName) {
        return this.months.indexOf(monthName.toLowerCase());
    }

    showCalendar(monthName) {
        const monthIndex = this.getMonthIndex(monthName);
        if (monthIndex !== -1) {
            this.currentMonth = monthIndex;
            this.renderCalendar();
        } else {
            alert("Mes no válido");
        }
    }

    renderCalendar() {
        const title = document.getElementById('calendarTitle');
        const table = document.getElementById('calendarTable').getElementsByTagName('tbody')[0];
        table.innerHTML = '';

        const monthName = this.months[this.currentMonth];
        const year = this.currentYear;
        const firstDay = new Date(year, this.currentMonth, 1).getDay();
        const daysInMonth = new Date(year, this.currentMonth + 1, 0).getDate();

        title.innerText = `${monthName} ${year}`;

        let day = 1;
        const startDay = (firstDay === 0) ? 6 : firstDay - 1; // Adjust for starting day (Monday as first day of week)
        
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');
            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');
                if (i === 0 && j < startDay) {
                    cell.innerText = '';
                } else if (day <= daysInMonth) {
                    cell.innerText = day;
                    cell.classList.add('disabled-highlight');
                    console.log(cell.classList)
                    day++;
                }
                row.appendChild(cell);
            }
            table.appendChild(row);
        }

        this.applyMarkedIntervals();
        this.updateNavigationButtons();
    }

    addEventListeners() {
        document.getElementById('showCalendarButton').addEventListener('click', () => {
            const monthName = document.getElementById('monthInput').value;
            this.showCalendar(monthName);
        });

        document.getElementById('prevMonthButton').addEventListener('click', () => {
            this.changeMonth(-1);
        });

        document.getElementById('nextMonthButton').addEventListener('click', () => {
            this.changeMonth(1);
        });
    }

    changeMonth(change) {
        this.currentMonth += change;
        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear -= 1;
        } else if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear += 1;
        }
        this.renderCalendar();
    }

    updateNavigationButtons() {
        const today = new Date();
        const isCurrentMonth = this.currentYear === today.getFullYear() && this.currentMonth === today.getMonth();
        document.getElementById('prevMonthButton').disabled = isCurrentMonth;
        document.getElementById('nextMonthButton').disabled = false;
    }


    marcarIntervalos(intervalos) {
        this.markedIntervals = intervalos;
        this.applyMarkedIntervals();
    }

    applyMarkedIntervals() {
        // tomo todas las celdas de la tabla calendario
        const cells = document.querySelectorAll('#calendarContainer td');

        // a cada celda les  remuevo las clases highlight y disabled-highlight
        cells.forEach(cell => {
            cell.classList.remove('highlight', 'disabled-highlight');
        });

        var unIntervaloFueMarcado = false

        this.markedIntervals.forEach(interval => {
            const [fromDay, fromMonth, fromYear] = interval[0].split('/').map(Number);
            const [toDay, toMonth, toYear] = interval[1].split('/').map(Number);

            console.log(fromDay, fromMonth, fromYear)
            console.log(toDay, toMonth, toYear)
            console.log("Marcando Intervalos..")

            console.log(`fromYear === this.currentYear // ${fromYear} === ${this.currentYear} // ${fromYear === this.currentYear}`)
            console.log(`fromMonth === this.currentMonth + 1 // ${fromMonth} === ${this.currentMonth + 1} // ${fromMonth === this.currentMonth + 1}`)

            if (fromYear === this.currentYear && Number(fromMonth) === Number(this.currentMonth + 1)) {

                unIntervaloFueMarcado = true

                console.log("Las fechas coinciden")


                const startDate = new Date(fromYear, fromMonth - 1, fromDay);
                const endDate = new Date(toYear, toMonth - 1, toDay);

                cells.forEach(cell => {
                    const cellDay = Number(cell.innerText);
                    if (cellDay) {
                        const cellDate = new Date(this.currentYear, this.currentMonth, cellDay);
                        if (cellDate >= startDate && cellDate <= endDate) {
                            cell.classList.add('highlight');
                        } else {
                            cell.classList.add('disabled-highlight');
                        }
                    }
                });
            }
        });

        if(!unIntervaloFueMarcado){
                cells.forEach(cell => {
                    const cellDay = Number(cell.innerText);
                    if (cellDay) {
                            cell.classList.add('disabled-highlight');
                    }
                });
        }
    }
}

