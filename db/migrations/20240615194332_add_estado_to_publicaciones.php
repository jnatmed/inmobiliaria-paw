<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEstadoToPublicaciones extends AbstractMigration
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
        // Create 'estado_publicaciones' table
        $table = $this->table('estado_publicaciones', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
              ->addColumn('estado', 'string', ['limit' => 255])
              ->create();

        // Insert the default states
        $this->table('estado_publicaciones')->insert([
            ['id' => 1, 'estado' => 'pendiente-de-aceptacion'],
            ['id' => 2, 'estado' => 'aceptado'],
            ['id' => 3, 'estado' => 'rechazado'],
        ])->saveData();

        // Add 'estado_id' column to 'publicaciones' table
        $table = $this->table('publicaciones');
        $table->addColumn('estado_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'id_usuario'])
              ->addForeignKey('estado_id', 'estado_publicaciones', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
              ->update();

        // Update existing publicaciones with default states
        $this->execute("UPDATE publicaciones SET estado_id = 1 WHERE id = 1");
        $this->execute("UPDATE publicaciones SET estado_id = 2 WHERE id = 2");
        $this->execute("UPDATE publicaciones SET estado_id = 3 WHERE id = 3");
    }
}
