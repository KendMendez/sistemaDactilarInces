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

        $adminRole = Role::where('role', 'Administrador')->first();

        // Usuario admin original
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

            $empleado->roles()->attach($adminRole->id);

            $this->command->info('Usuario de prueba creado:');
            $this->command->info('  Correo: admin@test.com');
            $this->command->info('  Contraseña: password123');
            $this->command->info('  Rol: Administrador');
        } else {
            $this->command->info('El usuario admin@test.com ya existe.');
        }

        // Super admin de respaldo (siempre se crea si no existe)
        $existeSuper = Empleado::where('correo', 'superadmin@test.com')->exists();

        if (! $existeSuper) {
            $super = Empleado::create([
                'id_cargo' => $cargo->id,
                'nombre' => 'Super',
                'apellido' => 'Admin',
                'telefono' => '04121111111',
                'identificacion' => '99999999',
                'correo' => 'superadmin@test.com',
                'contraseña' => Hash::make('SuperAdmin2025'),
                'foto' => '',
                'sexo' => 'M',
                'huella_pulgar' => '',
                'huella_indice' => '',
            ]);

            $super->roles()->attach($adminRole->id);

            $this->command->info('Super admin de respaldo creado:');
            $this->command->info('  Correo: superadmin@test.com');
            $this->command->info('  Contraseña: SuperAdmin2025');
            $this->command->info('  Rol: Administrador');
        } else {
            $this->command->info('El super admin superadmin@test.com ya existe.');
        }
    }
}
