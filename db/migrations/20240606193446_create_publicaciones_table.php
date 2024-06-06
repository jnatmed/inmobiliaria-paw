<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePublicacionesTable extends AbstractMigration
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
        $table = $this->table('publicaciones');
        $table->addColumn('nombre', 'string')
              ->addColumn('apellido', 'string')
              ->addColumn('dni', 'string')
              ->addColumn('telefono', 'string')
              ->addColumn('email', 'string')
              ->addColumn('provincia', 'string')
              ->addColumn('localidad', 'string')
              ->addColumn('direccion', 'string')
              ->addColumn('nombre_alojamiento', 'string')
              ->addColumn('tipo_alojamiento', 'string')
              ->addColumn('capacidad_maxima', 'integer')
              ->addColumn('cant_banios', 'integer')
              ->addColumn('cantidad_dormitorios', 'integer')
              ->addColumn('cochera', 'boolean')
              ->addColumn('pileta', 'boolean')
              ->addColumn('aire_acondicionado', 'boolean')
              ->addColumn('wifi', 'boolean')
              ->addColumn('normas_alojamiento', 'text')
              ->addColumn('descripcion_alojamiento', 'text')
              ->create();
    }
}
