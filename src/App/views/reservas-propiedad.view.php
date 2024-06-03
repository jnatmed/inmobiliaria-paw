<!DOCTYPE html>
<html lang="es">

<head>

    <?php require __DIR__.'/parts/head.view.php' ?>

</head>

<body class="home">

    <?php require __DIR__.'/parts/header.view.php' ?>


    <main>

    <section id="calendarContainer" class="calendarContainer">
            <input type="text" id="periodos" value="<?= $periodos_json ?>" hidden>
            <input type="text" id="monthInput" placeholder="Introduce el mes (ej: marzo)">
            <button id="showCalendarButton">Mostrar Calendario</button>
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
                                foreach ($periodos as $intervalo) {
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
        </section>



    </main>
        