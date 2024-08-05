<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetsTable extends AbstractMigration
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
        // Crear la tabla password_resets
        $table = $this->table('password_resets', ['id' => false, 'primary_key' => ['id']]);
        
        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true])
              ->addColumn('user_id', 'integer', ['signed' => false])
              ->addColumn('token', 'string', ['limit' => 255])
              ->addColumn('created_at', 'datetime')
              ->addForeignKey('user_id', 'usuarios', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->create();
    }
}
