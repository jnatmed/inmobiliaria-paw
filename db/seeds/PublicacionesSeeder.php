<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PublicacionesSeeder extends AbstractSeed
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
        // Datos de las publicaciones
        $publicacionesData = [];
        for ($i = 1; $i <= 3; $i++) {
            $publicacionesData[] = [
                'nombre' => 'Nombre ' . $i,
                'apellido' => 'Apellido ' . $i,
                'dni' => 'DNI ' . $i,
                'telefono' => 'Telefono ' . $i,
                'email' => 'email' . $i . '@example.com',
                'provincia' => 'Provincia ' . $i,
                'localidad' => 'Localidad ' . $i,
                'direccion' => 'Direccion ' . $i,
                'nombre_alojamiento' => 'Alojamiento ' . $i,
                'tipo_alojamiento' => 'Tipo ' . $i,
                'capacidad_maxima' => rand(1, 10),
                'cant_banios' => rand(1, 3),
                'cantidad_dormitorios' => rand(1, 5),
                'cochera' => rand(0, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(0, 1),
                'wifi' => rand(0, 1),
                'normas_alojamiento' => 'Normas ' . $i,
                'descripcion_alojamiento' => 'Descripcion ' . $i,
            ];
        }
        $this->table('publicaciones')->insert($publicacionesData)->saveData();

        // Datos de las imágenes
        $imagenes = [
            [
                'path_imagen' => 'casa-foto-1.png',
                'nombre_imagen' => 'vista de frente casa blanca grande'
            ],
            [
                'path_imagen' => 'casa-foto-2.png',
                'nombre_imagen' => 'vista costado casa blanca grande'
            ],
            [
                'path_imagen' => 'casa-foto-3.png',
                'nombre_imagen' => 'puerta entrada casa vista del fondo'
            ],
            [
                'path_imagen' => 'casa-foto-4.png',
                'nombre_imagen' => 'vista puerta cerrada madera roble'
            ],
            [
                'path_imagen' => 'casa-foto-5.png',
                'nombre_imagen' => 'vista escalera metal negro vista al fondo pieza balcon'
            ],
            [
                'path_imagen' => 'casa-foto-6.png',
                'nombre_imagen' => 'vista fondo casa blanca grande con pileta pasto verde'
            ],
            [
                'path_imagen' => 'casa-foto-7.png',
                'nombre_imagen' => 'vista interior sector cocina con horno pizzero aluminio acero inoxidable'
            ],
            [
                'path_imagen' => 'casa-foto-8.png',
                'nombre_imagen' => 'vista costado cocina horno pizero y mesada isla marmol blanco piso porcelana y acceso a primero piso por escalera mas aire acondicionado'
            ],
        ];

        // Crear imágenes para cada publicación
        $imagenesPublicacionData = [];
        for ($i = 1; $i <= 3; $i++) {
            foreach ($imagenes as $imagen) {
                $imagenesPublicacionData[] = [
                    'id_publicacion' => $i,
                    'path_imagen' => $imagen['path_imagen'],
                    'nombre_imagen' => $imagen['nombre_imagen'],
                ];
            }
        }
        $this->table('imagenes_publicacion')->insert($imagenesPublicacionData)->saveData();
      

    }
}
