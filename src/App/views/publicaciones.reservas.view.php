<!DOCTYPE html>
<html lang="es">

<head>

<?php require __DIR__ . '/parts/head.view.php' ?>
<link rel="stylesheet" href="/assets/css/tabla-reservas.css">

</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>

    <main>

    <section class="reservas-container">
        <h1 class="reservas-titulo">Lista de Reservas</h1>
        <table class="reservas-tabla">
            <thead>
                <tr>
                    <th>ID Reserva</th>
                    <th>ID Publicacion</th>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th>Estado</th>
                    <th>Nota</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?= htmlspecialchars($reserva['id_reserva']); ?></td>
                        <td><a href="/publicacion/ver?id_pub=<?= $reserva['id_pub'] ?>"><?= $reserva['id_pub'] ?></a></td>
                        <td><?= htmlspecialchars($reserva['desde']); ?></td>
                        <td><?= htmlspecialchars($reserva['hasta']); ?></td>
                        <td><?= htmlspecialchars($reserva['estado_reserva']); ?></td>
                        <td><?= htmlspecialchars($reserva['nota']); ?></td>
                        <td>
                            <a class="reservas-accion" href="/mis_publicaciones/reserva/aceptar?id_pub=<?= $reserva['id_pub']; ?>&id_reserva=<?= $reserva['id_reserva']; ?>">Aceptar</a> |
                            <a class="reservas-accion" href="/mis_publicaciones/reserva/cancelar?id_pub=<?= $reserva['id_pub']; ?>&id_reserva=<?= $reserva['id_reserva']; ?>">Cancelar</a> |
                            <a class="reservas-accion" href="/mis_publicaciones/reserva/rechazar?id_pub=<?= $reserva['id_pub']; ?>&id_reserva=<?= $reserva['id_reserva']; ?>">Rechazar</a>
                            <a class="reservas-accion" href="/publicacion/ver?id_pub=<?= $reserva['id_pub'] ?>">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    </main>

    <?php require __DIR__.'/parts/footer.view.php' ?>
</body>

</html>    