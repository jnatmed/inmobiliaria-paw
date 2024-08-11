<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTiposUsuariosTable extends AbstractMigration
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
        // Crear la tabla tipos_usuarios
        $table = $this->table('tipos_usuarios');
        $table->addColumn('tipo', 'string', ['limit' => 50])
              ->create();

        // Insertar los tipos de usuario
        $this->table('tipos_usuarios')->insert([
            ['tipo' => 'propietario'],
            ['tipo' => 'empleado'],
            ['tipo' => 'inquilino'],
        ])->saveData();

        // Modificar la tabla usuarios
        $this->table('usuarios')
            ->removeColumn('tipo_usuario')
            ->addColumn('tipo_usuario_id', 'integer', ['signed' => false, 'null' => true])
            ->addForeignKey('tipo_usuario_id', 'tipos_usuarios', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->update();
    }
}
