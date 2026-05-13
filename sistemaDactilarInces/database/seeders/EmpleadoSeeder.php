<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Empleado;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $cargo = Cargo::firstOrCreate(
            ['cargo' => 'Administrador'],
            ['cargo' => 'Administrador']
        );

        $existeEmpleado = Empleado::where('correo', 'admin@test.com')->exists();

        if (! $existeEmpleado) {
            $empleado = Empleado::create([
                'id_cargo' => $cargo->id,
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'telefono' => '04120000000',
                'identificacion' => '00000000',
                'correo' => 'admin@test.com',
                'contraseña' => Hash::make('password123'),
                'foto' => '',
                'sexo' => 'M',
                'huella_pulgar' => '',
                'huella_indice' => '',
            ]);

            $adminRole = Role::where('role', 'Administrador')->first();
            $empleado->roles()->attach($adminRole->id);

            $this->command->info('Usuario de prueba creado:');
            $this->command->info('  Correo: admin@test.com');
            $this->command->info('  Contraseña: password123');
            $this->command->info('  Rol: Administrador');
        } else {
            $this->command->info('El usuario de prueba ya existe.');
        }
    }
}
