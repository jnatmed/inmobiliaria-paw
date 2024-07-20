<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRejectedStateToReservasPublicacion extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        // Agregar el nuevo estado 'rechazada' a la columna estado_reserva
        $this->execute("
            ALTER TABLE `reservas_publicacion`
            MODIFY COLUMN `estado_reserva` ENUM('pendiente', 'confirmada', 'cancelada', 'rechazada') 
            COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente';
        ");
    }

    public function down()
    {
        // Revertir el cambio al estado original
        $this->execute("
            ALTER TABLE `reservas_publicacion`
            MODIFY COLUMN `estado_reserva` ENUM('pendiente', 'confirmada', 'cancelada') 
            COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente';
        ");
    }
}
