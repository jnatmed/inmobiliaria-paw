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

        $usuariosData = [
            [
                'nombre' => 'Usuario 1',
                'apellido' => 'Apellido 1',
                'contrasenia' => password_hash('password1', PASSWORD_DEFAULT),
                'email' => 'usuario1@example.com',
                'telefono' => '541134387233',
                'tipo_usuario_id' => 1
            ],
            [
                'nombre' => 'Usuario 2',
                'apellido' => 'Apellido 2',
                'contrasenia' => password_hash('password2', PASSWORD_DEFAULT),
                'email' => 'usuario2@example.com',
                'telefono' => '541134387233',
                'tipo_usuario_id' => 2
            ],
            [
                'nombre' => 'Usuario 3',
                'apellido' => 'Apellido 3',
                'contrasenia' => password_hash('password3', PASSWORD_DEFAULT),
                'email' => 'usuario3@example.com',
                'telefono' => '541134387233',
                'tipo_usuario_id' => 3
            ],
        ];

        $this->table('usuarios')->insert($usuariosData)->save();

        // Datos de las publicaciones
        $publicacionesData = [
            [
                'latitud' => -34.645782106051634,
                'longitud' => -58.39849262386727,
                'precio' => 2500,
                'provincia' => 'Provincia 1',
                'codigo_postal' => '6712',
                'direccion' => 'Direccion 1',
                'nombre_alojamiento' => 'Alojamiento 1',
                'tipo_alojamiento_id' => 1,
                'capacidad_maxima' => rand(1, 10),
                'cant_banios' => rand(1, 3),
                'cantidad_dormitorios' => rand(1, 5),
                'cochera' => rand(0, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(0, 1),
                'wifi' => rand(0, 1),
                'normas_alojamiento' => 'Normas 1',
                'descripcion_alojamiento' => 'Ubicado en el vibrante y culturalmente rico barrio de San Telmo, Rincón del Tango ofrece una experiencia auténtica y acogedora en uno de los lugares más emblemáticos de Buenos Aires. Ideal para quienes buscan sumergirse en la vida local y disfrutar de la combinación perfecta entre comodidad moderna y encanto histórico.',
                'id_usuario' => 1,
                'estado_id' => 1
            ],
            [
                'latitud' => -34.6461351622715,
                'longitud' => -58.4003547989017,
                'precio' => 1500,
                'provincia' => 'Provincia 2',
                'codigo_postal' => '1437',
                'direccion' => 'Direccion 2',
                'nombre_alojamiento' => 'Rincón del Tango en San Telmo',
                'tipo_alojamiento_id' => 2,
                'capacidad_maxima' => rand(1, 10),
                'cant_banios' => rand(1, 3),
                'cantidad_dormitorios' => rand(1, 5),
                'cochera' => rand(0, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(0, 1),
                'wifi' => rand(0, 1),
                'normas_alojamiento' => 'Uso de Instalaciones Se debe usar las instalaciones del alojamiento de manera responsable. No se debe causar daño a los equipos, muebles y electrodomésticos. Cualquier daño causado deberá ser reportado de inmediato y el costo de reparación será a cargo del inquilino.',
                'descripcion_alojamiento' => 'Ubicado en el vibrante y culturalmente rico barrio de San Telmo, Rincón del Tango ofrece una experiencia auténtica y acogedora en uno de los lugares más emblemáticos de Buenos Aires. Ideal para quienes buscan sumergirse en la vida local y disfrutar de la combinación perfecta entre comodidad moderna y encanto histórico.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -34.63695079829552,
                'longitud' => -58.402421370639885,
                'precio' => 500,
                'provincia' => 'Provincia 3',
                'codigo_postal' => '1190',
                'direccion' => 'Direccion 3',
                'nombre_alojamiento' => 'Alojamiento 3',
                'tipo_alojamiento_id' => 3,
                'capacidad_maxima' => rand(1, 10),
                'cant_banios' => rand(1, 3),
                'cantidad_dormitorios' => rand(1, 5),
                'cochera' => rand(0, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(0, 1),
                'wifi' => rand(0, 1),
                'normas_alojamiento' => 'Normas 3',
                'descripcion_alojamiento' => 'Descripcion 3',
                'id_usuario' => 3,
                'estado_id' => 1
            ],
        ];

        $this->table('publicaciones')->insert($publicacionesData)->save();

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
                    'id_usuario' => $i
                ];
            }
        }
        $this->table('imagenes_publicacion')->insert($imagenesPublicacionData)->saveData();
      

    }
}
