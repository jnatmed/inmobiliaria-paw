<?php if(isset($resultadoReserva['exito'])) : ?>
    <?php if($resultadoReserva['exito']) : ?>
        <p class="msj_reserva msj_exito">
            <?= $resultadoReserva['mensaje']?>
        </p>
    <?php else: ?>
        <p class="msj_reserva msj_error">
            <?= $resultadoReserva['mensaje']?>
        </p>
    <?php endif; ?>
<?php endif; ?>

<section class="container-reserva">
    <article class="container-form">
        <img src="/imgs/image.png" alt="img-destacada" class="img-destacada">
        <form action="/publicacion/reservar" class="form-reserva" method="POST"> 
            <input type="text" name="id_publicacion" value="<?= $id_publicacion ?>" hidden>
            <label for="input-desde" class="lbl-desde">Desde</label>
            <input type="date" name="input-desde" id="input-desde" class="input-form-reserva">
            <label for="input-hasta" class="lbl-hasta">Hasta</label>
            <input type="date" name="input-hasta" id="input-hasta" class="input-form-reserva">
            <input type="submit" value="Reservar Periodo" class="btn-form reservar">
            <input type="submit" value="Cancelar" class="btn-form cancelar">
        </form>

    </article>
    <article id="calendarContainer" class="calendarContainer">
            <input type="text" id="periodos" value="<?= $periodos_json ?>" hidden>
            <input type="text" id="monthInput" placeholder="Introduce el mes (ej: marzo)" hidden>
            <button id="showCalendarButton" hidden>Mostrar Calendario</button>
            <div id="calendarNavigation">
                <button id="prevMonthButton" disabled>←</button>
                <button id="nextMonthButton">→</button>
            </div>
            <h3 id="calendarTitle" class="calendarTitle"></h3>
            <table id="calendarTable" class="greenTable">
                <thead>
                    <tr>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miércoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                        <th>Sábado</th>
                        <th>Domingo</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas del calendario generadas aquí -->
                    <?php
                    $currentYear = date("Y");
                    $currentMonth = date("m") - 1;
                    $firstDay = new DateTime("$currentYear-$currentMonth-01");
                    $firstDayOfWeek = (int)$firstDay->format("N") - 1;
                    $daysInMonth = (int)$firstDay->format("t");

                    $day = 1;
                    for ($i = 0; $i < 6; $i++) {
                        echo "<tr>";
                        for ($j = 0; $j < 7; $j++) {
                            if ($i === 0 && $j < $firstDayOfWeek) {
                                echo "<td></td>";
                            } else if ($day <= $daysInMonth) {
                                $highlight = "";
                                foreach ($reservas as $intervalo) {
                                    $start = DateTime::createFromFormat('d/m/Y', $intervalo[0]);
                                    $end = DateTime::createFromFormat('d/m/Y', $intervalo[1]);
                                    $current = new DateTime("$currentYear-$currentMonth-$day");
                                    if ($current >= $start && $current <= $end) {
                                        $highlight = "highlight";
                                    }
                                }
                                echo "<td class='$highlight'>$day</td>";
                                $day++;
                            } else {
                                echo "<td></td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
    </article>
</section>
