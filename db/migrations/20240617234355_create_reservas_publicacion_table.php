<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateReservasPublicacionTable extends AbstractMigration
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
    public function change(): void
    {
        // Create the reservas_publicacion table
        $table = $this->table('reservas_publicacion');
        
        // Add columns
        $table->addColumn('id_publicacion', 'integer', ['signed' => false])
              ->addColumn('fecha_inicio', 'date')
              ->addColumn('fecha_fin', 'date')
              ->addColumn('precio_por_noche', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('estado_reserva', 'enum', ['values' => ['pendiente', 'confirmada', 'cancelada'], 'default' => 'pendiente'])
              ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('fecha_actualizacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('notas', 'text', ['null' => true])
              
              // Add foreign key constraint
              ->addForeignKey('id_publicacion', 'publicaciones', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
              
              // Create the table
              ->create();
    }
}
