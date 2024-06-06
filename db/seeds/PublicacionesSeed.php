<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PublicacionesSeed extends AbstractSeed
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
        $faker = Faker\Factory::create();

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'nombre' => $faker->firstName,
                'apellido' => $faker->lastName,
                'dni' => $faker->numerify('########'),
                'telefono' => $faker->phoneNumber,
                'email' => $faker->email,
                'provincia' => $faker->state,
                'localidad' => $faker->city,
                'direccion' => $faker->streetAddress,
                'nombre_alojamiento' => $faker->company,
                'tipo_alojamiento' => $faker->randomElement(['Casa', 'Departamento', 'Cabaña']),
                'capacidad_maxima' => $faker->numberBetween(1, 10),
                'cant_banios' => $faker->numberBetween(1, 3),
                'cantidad_dormitorios' => $faker->numberBetween(1, 5),
                'cochera' => $faker->boolean,
                'pileta' => $faker->boolean,
                'aire_acondicionado' => $faker->boolean,
                'wifi' => $faker->boolean,
                'normas_alojamiento' => $faker->text(200),
                'descripcion_alojamiento' => $faker->paragraphs(3, true),
            ];
        }

        $this->table('publicaciones')->insert($data)->save();
    }
}
