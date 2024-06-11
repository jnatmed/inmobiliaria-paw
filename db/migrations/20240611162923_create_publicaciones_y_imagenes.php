<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePublicacionesYImagenes extends AbstractMigration
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
        // Create 'publicaciones' table
        $table = $this->table('publicaciones', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
              ->addColumn('nombre', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('apellido', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('dni', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('telefono', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('email', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('provincia', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('localidad', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('direccion', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('nombre_alojamiento', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('tipo_alojamiento', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('capacidad_maxima', 'integer', ['null' => true])
              ->addColumn('cant_banios', 'integer', ['null' => true])
              ->addColumn('cantidad_dormitorios', 'integer', ['null' => true])
              ->addColumn('cochera', 'boolean', ['null' => true])
              ->addColumn('pileta', 'boolean', ['null' => true])
              ->addColumn('aire_acondicionado', 'boolean', ['null' => true])
              ->addColumn('wifi', 'boolean', ['null' => true])
              ->addColumn('normas_alojamiento', 'text', ['null' => true])
              ->addColumn('descripcion_alojamiento', 'text', ['null' => true])
              ->create();

        // Create 'imagenes_publicacion' table with composite primary key
        $table = $this->table('imagenes_publicacion', ['id' => false]);
        $table->addColumn('id_imagen', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('id_publicacion', 'integer', ['signed' => false])
            ->addColumn('path_imagen', 'string', ['limit' => 255])
            ->addColumn('nombre_imagen', 'string', ['limit' => 255, 'null' => true])
            ->addForeignKey('id_publicacion', 'publicaciones', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['id_imagen', 'id_publicacion'], ['unique' => true])
            ->create();
    }
}
