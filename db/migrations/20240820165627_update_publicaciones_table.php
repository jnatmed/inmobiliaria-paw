<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdatePublicacionesTable extends AbstractMigration
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
        // Eliminar la columna tipo_alojamiento
        $this->table('publicaciones')
            ->removeColumn('tipo_alojamiento')
            ->update();

        // Agregar la columna tipo_alojamiento_id como foreign key
        $this->table('publicaciones')
            ->addColumn('tipo_alojamiento_id', 'integer', ['null' => true, 'signed' => false])
            ->addForeignKey('tipo_alojamiento_id', 'tipos_alojamiento', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
            ->update();
    }
}
