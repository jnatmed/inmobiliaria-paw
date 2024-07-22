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
            const urlParams = new URLSearchParams(window.location.search);
            const id_pub = urlParams.get('id_pub');
            
            if (!id_pub) {
                throw new Error('El parámetro id_pub no está presente en la URL');
            }
    
            const response = await fetch(`/reservas/intervalos?id_pub=${id_pub}`);
            if (!response.ok) {
                throw new Error('Error al obtener los intervalos de reserva');
            }
            const periodos = await response.json();
            console.log(periodos);
            this.marcarIntervalos(periodos);
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
        const startDay = (firstDay === 0) ? 6 : firstDay - 1;
        
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');
            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');
                if (i === 0 && j < startDay) {
                    cell.innerText = '';
                } else if (day <= daysInMonth) {
                    cell.innerText = day;
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
        const cells = document.querySelectorAll('#calendarContainer td');
        cells.forEach(cell => {
            cell.classList.remove('ocupado', 'libre');
        });

        const today = new Date();
        const firstDayCurrentMonth = new Date(this.currentYear, this.currentMonth, 1);
        const lastDayCurrentMonth = new Date(this.currentYear, this.currentMonth + 1, 0);

        // Unir todos los intervalos en un solo conjunto de fechas ocupadas
        const occupiedDates = new Set();
        
        this.markedIntervals.forEach(interval => {
            const [fromDay, fromMonth, fromYear] = interval[0].split('/').map(Number);
            const [toDay, toMonth, toYear] = interval[1].split('/').map(Number);

            const startDate = new Date(fromYear, fromMonth - 1, fromDay);
            const endDate = new Date(toYear, toMonth - 1, toDay);

            if ((fromYear === this.currentYear && fromMonth - 1 === this.currentMonth) ||
                (toYear === this.currentYear && toMonth - 1 === this.currentMonth) ||
                (startDate < firstDayCurrentMonth && endDate > lastDayCurrentMonth)) {

                let currentDate = startDate;
                while (currentDate <= endDate) {
                    if (currentDate.getFullYear() === this.currentYear && currentDate.getMonth() === this.currentMonth) {
                        occupiedDates.add(currentDate.getDate());
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
            }
        });

        cells.forEach(cell => {
            const cellDay = Number(cell.innerText);
            if (cellDay) {
                const cellDate = new Date(this.currentYear, this.currentMonth, cellDay);
                if (cellDate < today) {
                    cell.classList.add('past-date');
                } else if (occupiedDates.has(cellDay)) {
                    cell.classList.add('ocupado');
                } else {
                    cell.classList.add('libre');
                }
            }
        });
    }
    
}
