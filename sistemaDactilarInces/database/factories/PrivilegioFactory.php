<?php

namespace Database\Factories;

use App\Models\Privilegio;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivilegioFactory extends Factory
{
    protected $model = Privilegio::class;

    public function definition(): array
    {
        return [
            'privilegio' => fake()->unique()->word(),
        ];
    }
}
