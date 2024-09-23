<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTipoPropiedadTable extends AbstractMigration
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
        // Crear la tabla tipo_propiedad
        $table = $this->table('tipos_alojamiento');
        $table->addColumn('descripcion_tipo', 'string', ['limit' => 255])
              ->addTimestamps()
              ->create();

        // Inserción de datos iniciales
        $data = [
            ['descripcion_tipo' => 'quinta'],
            ['descripcion_tipo' => 'departamento'],
            ['descripcion_tipo' => 'casa'],
        ];

        // Insertar los valores en la tabla tipo_propiedad
        $this->table('tipos_alojamiento')->insert($data)->saveData();
    }
}
