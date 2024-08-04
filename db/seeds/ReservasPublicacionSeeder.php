<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class ReservasPublicacionSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'id_publicacion' => 1,
                'fecha_inicio' => '2024-07-01',
                'fecha_fin' => '2024-07-05',
                'precio_por_noche' => 100.00,
                'estado_reserva' => 'pendiente',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'notas' => 'Primera reserva.'
            ],
            [
                'id_publicacion' => 2,
                'fecha_inicio' => '2024-08-10',
                'fecha_fin' => '2024-08-15',
                'precio_por_noche' => 150.00,
                'estado_reserva' => 'confirmada',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'notas' => 'Segunda reserva.'
            ],
            [
                'id_publicacion' => 3,
                'fecha_inicio' => '2024-09-20',
                'fecha_fin' => '2024-09-25',
                'precio_por_noche' => 120.00,
                'estado_reserva' => 'cancelada',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'notas' => 'Tercera reserva.'
            ]
        ];

        $this->table('reservas_publicacion')->insert($data)->saveData();
    }
}
