<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddArchivadaStateToPublicaciones extends AbstractMigration {

    public function up(): void{
        
        $this->table('estado_publicaciones')->insert([
            [
                'id' => 4,
                'estado' => 'archivada'
            ]
        ])->saveData();

    }

    public function down(): void {

        $this->execute('UPDATE publicaciones SET estado_id = 1 WHERE estado_id = 4');

        $this->execute("DELETE FROM estado_publicaciones WHERE id = 4 AND estado = 'archivada'");

    }
    
}