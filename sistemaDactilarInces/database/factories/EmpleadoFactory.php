<?php

namespace Database\Factories;

use App\Models\Cargo;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'id_cargo' => Cargo::factory(),
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'telefono' => fake()->numerify('0412########'),
            'identificacion' => fake()->numerify('########'),
            'correo' => fake()->unique()->safeEmail(),
            'contraseña' => static::$password ??= Hash::make('password123'),
            'foto' => '',
            'sexo' => fake()->randomElement(['M', 'F']),
            'huella_pulgar' => '',
            'huella_indice' => '',
        ];
    }
}
