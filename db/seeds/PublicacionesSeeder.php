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
                'latitud' => -34.603722,
                'longitud' => -58.381592,
                'precio' => 7500,
                'provincia' => 'Buenos Aires',
                'codigo_postal' => '1001',
                'direccion' => 'Av. Libertador 2345',
                'nombre_alojamiento' => 'Palacio del Río',
                'tipo_alojamiento_id' => 1,
                'capacidad_maxima' => rand(4, 8),
                'cant_banios' => rand(2, 3),
                'cantidad_dormitorios' => rand(3, 5),
                'cochera' => rand(0, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permiten fiestas, Mascotas bajo petición.',
                'descripcion_alojamiento' => 'Exclusiva casa con vista al río, ubicada en el barrio más prestigioso de Buenos Aires. Ideal para familias o grupos grandes que buscan lujo y comodidad.',
                'id_usuario' => 1,
                'estado_id' => 1
            ],
            [
                'latitud' => -32.889458,
                'longitud' => -68.845839,
                'precio' => 5800,
                'provincia' => 'Mendoza',
                'codigo_postal' => '5500',
                'direccion' => 'Calle San Martín 1500',
                'nombre_alojamiento' => 'Cabañas El Aconcagua',
                'tipo_alojamiento_id' => 2,
                'capacidad_maxima' => rand(2, 6),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(1, 3),
                'cochera' => rand(1, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(0, 1),
                'wifi' => rand(0, 1),
                'normas_alojamiento' => 'Prohibido fumar en interiores, No se permiten mascotas.',
                'descripcion_alojamiento' => 'Cabañas rústicas con vistas impresionantes a la cordillera de los Andes. Perfectas para disfrutar de la tranquilidad y los vinos mendocinos.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -31.420083,
                'longitud' => -64.188776,
                'precio' => 4100,
                'provincia' => 'Córdoba',
                'codigo_postal' => '5000',
                'direccion' => 'Bv. Chacabuco 899',
                'nombre_alojamiento' => 'La Estancia Serrana',
                'tipo_alojamiento_id' => 3,
                'capacidad_maxima' => rand(3, 7),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(2, 4),
                'cochera' => rand(1, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'Silencio después de las 22:00, No se permiten eventos.',
                'descripcion_alojamiento' => 'Hermosa estancia en las sierras cordobesas. Perfecta para escapar del ruido de la ciudad y disfrutar de la naturaleza en familia.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -34.921452,
                'longitud' => -57.954464,
                'precio' => 2900,
                'provincia' => 'La Plata',
                'codigo_postal' => '1900',
                'direccion' => 'Calle 50 N°123',
                'nombre_alojamiento' => 'Apartamento Universitario',
                'tipo_alojamiento_id' => 1,
                'capacidad_maxima' => rand(1, 3),
                'cant_banios' => rand(1, 1),
                'cantidad_dormitorios' => rand(1, 2),
                'cochera' => rand(0, 0),
                'pileta' => rand(0, 0),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permiten mascotas, Solo estudiantes.',
                'descripcion_alojamiento' => 'Cómodo apartamento ideal para estudiantes, ubicado cerca de las principales universidades de La Plata. Cuenta con todas las comodidades necesarias para una estancia agradable.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -38.939200,
                'longitud' => -62.232500,
                'precio' => 6700,
                'provincia' => 'Bahía Blanca',
                'codigo_postal' => '8000',
                'direccion' => 'Av. Colón 1234',
                'nombre_alojamiento' => 'Residencia Bahía Dreams',
                'tipo_alojamiento_id' => 2,
                'capacidad_maxima' => rand(2, 5),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(1, 3),
                'cochera' => rand(1, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permiten eventos ni fiestas.',
                'descripcion_alojamiento' => 'Residencia moderna y luminosa, equipada con pileta y cochera. Ideal para vacaciones familiares en Bahía Blanca.',
                'id_usuario' => 1,
                'estado_id' => 1
            ],
            [
                'latitud' => -34.522300,
                'longitud' => -58.700900,
                'precio' => 5100,
                'provincia' => 'Tigre',
                'codigo_postal' => '1648',
                'direccion' => 'Rio Luján 4321',
                'nombre_alojamiento' => 'Isla del Delta',
                'tipo_alojamiento_id' => 3,
                'capacidad_maxima' => rand(2, 8),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(2, 4),
                'cochera' => rand(0, 0),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'Solo acceso por lancha, No se permiten fiestas.',
                'descripcion_alojamiento' => 'Ubicada en una exclusiva isla del Delta del Paraná, esta propiedad ofrece una experiencia única para los amantes de la naturaleza y la tranquilidad. Perfecto para escapadas de fin de semana.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -26.827067,
                'longitud' => -65.203662,
                'precio' => 4200,
                'provincia' => 'Tucumán',
                'codigo_postal' => '4000',
                'direccion' => 'Av. Sarmiento 567',
                'nombre_alojamiento' => 'El Jardín de Tucumán',
                'tipo_alojamiento_id' => 1,
                'capacidad_maxima' => rand(2, 6),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(1, 3),
                'cochera' => rand(0, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permiten mascotas, Respeto por el horario de descanso.',
                'descripcion_alojamiento' => 'Casa acogedora con un amplio jardín, ubicada en el corazón de San Miguel de Tucumán. Perfecta para familias que buscan tranquilidad y comodidad.',
                'id_usuario' => 1,
                'estado_id' => 1
            ],
            [
                'latitud' => -32.950742,
                'longitud' => -60.647345,
                'precio' => 3300,
                'provincia' => 'Santa Fe',
                'codigo_postal' => '2000',
                'direccion' => 'Bv. Oroño 1001',
                'nombre_alojamiento' => 'Rosario al Río',
                'tipo_alojamiento_id' => 2,
                'capacidad_maxima' => rand(3, 6),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(1, 3),
                'cochera' => rand(0, 1),
                'pileta' => rand(0, 0),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permiten fiestas, No fumar en el interior.',
                'descripcion_alojamiento' => 'Departamento moderno en el centro de Rosario, a metros del río Paraná. Ideal para parejas o grupos pequeños que desean explorar la ciudad.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -27.468930,
                'longitud' => -58.834096,
                'precio' => 2800,
                'provincia' => 'Corrientes',
                'codigo_postal' => '3400',
                'direccion' => 'Calle Junín 789',
                'nombre_alojamiento' => 'Refugio del Paraná',
                'tipo_alojamiento_id' => 1,
                'capacidad_maxima' => rand(2, 5),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(1, 3),
                'cochera' => rand(1, 1),
                'pileta' => rand(0, 0),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'Mascotas permitidas bajo petición, Silencio después de las 23:00.',
                'descripcion_alojamiento' => 'Casa familiar situada a orillas del río Paraná, con vistas increíbles y ambientes relajados para disfrutar de Corrientes en su máxima expresión.',
                'id_usuario' => 1,
                'estado_id' => 1
            ],
            [
                'latitud' => -24.786450,
                'longitud' => -65.410292,
                'precio' => 4700,
                'provincia' => 'Salta',
                'codigo_postal' => '4400',
                'direccion' => 'Calle Balcarce 245',
                'nombre_alojamiento' => 'Casa Colonial Salteña',
                'tipo_alojamiento_id' => 3,
                'capacidad_maxima' => rand(3, 8),
                'cant_banios' => rand(2, 3),
                'cantidad_dormitorios' => rand(2, 4),
                'cochera' => rand(1, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'Prohibido fumar, Respetar las normas de convivencia.',
                'descripcion_alojamiento' => 'Imponente casa colonial con vistas a los cerros salteños. Un espacio ideal para disfrutar de la arquitectura tradicional y el encanto del norte argentino.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -36.623204,
                'longitud' => -64.290047,
                'precio' => 3700,
                'provincia' => 'La Pampa',
                'codigo_postal' => '6300',
                'direccion' => 'Av. San Martín 234',
                'nombre_alojamiento' => 'La Estancia Pampeana',
                'tipo_alojamiento_id' => 2,
                'capacidad_maxima' => rand(4, 10),
                'cant_banios' => rand(2, 3),
                'cantidad_dormitorios' => rand(3, 5),
                'cochera' => rand(1, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'Se permite fumar solo en áreas exteriores.',
                'descripcion_alojamiento' => 'Amplia estancia rodeada de campos pampeanos, ideal para quienes buscan desconectar de la rutina y disfrutar de un ambiente rural con todas las comodidades.',
                'id_usuario' => 1,
                'estado_id' => 1
            ],
            [
                'latitud' => -34.920292,
                'longitud' => -57.953565,
                'precio' => 6800,
                'provincia' => 'Buenos Aires',
                'codigo_postal' => '1900',
                'direccion' => 'Calle 51 N°1020',
                'nombre_alojamiento' => 'Hotel Boutique La Plata',
                'tipo_alojamiento_id' => 1,
                'capacidad_maxima' => rand(2, 4),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(1, 2),
                'cochera' => rand(1, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permiten eventos, Desayuno incluido.',
                'descripcion_alojamiento' => 'Pequeño hotel boutique en el centro de La Plata, con servicios de primera calidad y ambiente exclusivo. Perfecto para escapadas románticas o viajes de negocios.',
                'id_usuario' => 2,
                'estado_id' => 1
            ],
            [
                'latitud' => -31.741319,
                'longitud' => -60.511547,
                'precio' => 6200,
                'provincia' => 'Entre Ríos',
                'codigo_postal' => '3100',
                'direccion' => 'Ruta 12, Km 450',
                'nombre_alojamiento' => 'Termas del Paraíso',
                'tipo_alojamiento_id' => 3,
                'capacidad_maxima' => rand(4, 8),
                'cant_banios' => rand(2, 3),
                'cantidad_dormitorios' => rand(2, 4),
                'cochera' => rand(1, 1),
                'pileta' => rand(1, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'Acceso a las termas hasta las 22:00, Prohibido hacer ruido excesivo.',
                'descripcion_alojamiento' => 'Complejo termal en el corazón de Entre Ríos, ideal para relajarse y disfrutar de aguas termales naturales. Perfecto para familias o grupos grandes.',
                'id_usuario' => 3,
                'estado_id' => 1
            ],
            [
                'latitud' => -39.031778,
                'longitud' => -67.591215,
                'precio' => 4900,
                'provincia' => 'Río Negro',
                'codigo_postal' => '8332',
                'direccion' => 'Camino a Valle Grande 455',
                'nombre_alojamiento' => 'Cabañas del Limay',
                'tipo_alojamiento_id' => 2,
                'capacidad_maxima' => rand(4, 6),
                'cant_banios' => rand(1, 2),
                'cantidad_dormitorios' => rand(2, 3),
                'cochera' => rand(1, 1),
                'pileta' => rand(0, 1),
                'aire_acondicionado' => rand(1, 1),
                'wifi' => rand(1, 1),
                'normas_alojamiento' => 'No se permite acampar en los alrededores.',
                'descripcion_alojamiento' => 'Cabañas rústicas y acogedoras a orillas del río Limay. Un entorno natural perfecto para escapadas en familia o con amigos.',
                'id_usuario' => 2,
                'estado_id' => 1
            ]
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
        for ($i = 1; $i <= count($publicacionesData); $i++) {
            $id_usuario = rand(1, 3);
            for ($j = 1; $j <= 3; $j++) { // Bucle que va del 1 al 3
                $img = rand(0, 7); // Mantenemos la generación aleatoria de imágenes
        
                $imagenesPublicacionData[] = [
                    'id_publicacion' => $i,
                    'path_imagen' => $imagenes[$img]['path_imagen'],
                    'nombre_imagen' => $imagenes[$img]['nombre_imagen'],
                    'id_usuario' => $id_usuario 
                ];
            }
        }        $this->table('imagenes_publicacion')->insert($imagenesPublicacionData)->saveData();
    }
}
