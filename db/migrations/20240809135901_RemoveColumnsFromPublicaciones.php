<?php
use Phinx\Migration\AbstractMigration;

class RemoveColumnsFromPublicaciones extends AbstractMigration
{
    public function change()
    {
        // Acceder a la tabla publicaciones
        $table = $this->table('publicaciones');

        // Eliminar las columnas especificadas
        $table->removeColumn('nombre')
              ->removeColumn('apellido')
              ->removeColumn('dni')
              ->removeColumn('telefono')
              ->removeColumn('email')
              ->update();
    }
}