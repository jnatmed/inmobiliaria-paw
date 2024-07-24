<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUsuarioReservaToReservasPublicacion extends AbstractMigration
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
        $table = $this->table('reservas_publicacion');
        
        // Añadir la columna id_usuario_reserva
        $table->addColumn('id_usuario_reserva', 'integer', ['null' => true, 'after' => 'id_publicacion', 'signed' => false])
              ->addForeignKey('id_usuario_reserva', 'usuarios', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->update();
    }

    public function down()
    {
        $table = $this->table('reservas_publicacion');
        
        // Eliminar la foreign key y la columna id_usuario_reserva
        $table->dropForeignKey('id_usuario_reserva')
              ->removeColumn('id_usuario_reserva')
              ->update();
    }
}
