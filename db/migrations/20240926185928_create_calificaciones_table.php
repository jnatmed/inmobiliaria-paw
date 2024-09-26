<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCalificacionesTable extends AbstractMigration
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
        $table = $this->table('calificaciones', ['id' => false, 'primary_key' => ['id_publicacion', 'id_usuario']]);
        $table->addColumn('id_publicacion', 'integer', ['signed' => false, 'null' => false]) // Clave foránea, NO NULL
              ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false]) // Clave foránea, NO NULL
              ->addColumn('calificacion', 'integer', ['limit' => 1, 'null' => false]) // Calificación entre 1 y 5, NO NULL
              ->addColumn('comentario', 'text', ['null' => true]) // Comentario opcional
              ->addForeignKey('id_publicacion', 'publicaciones', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->addForeignKey('id_usuario', 'usuarios', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->create();
    }
}
