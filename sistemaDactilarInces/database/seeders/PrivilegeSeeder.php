<?php

namespace Database\Seeders;

use App\Models\Privilegio;
use Illuminate\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    public function run(): void
    {
        $privilegios = [
            // Empleados
            'ver empleados',
            'crear empleado',
            'editar empleado',
            'eliminar empleado',
            // Roles
            'ver roles',
            'crear rol',
            'editar rol',
            'eliminar rol',
            // Privilegios
            'ver privilegios',
            // Asignación role-privilegio
            'asignar privilegios',
            // Cargos
            'ver cargos',
            'crear cargo',
            'editar cargo',
            'eliminar cargo',
            // Feriados
            'ver feriados',
            'crear feriado',
            'editar feriado',
            'eliminar feriado',
            // Asistencias
            'ver asistencias',
            'registrar asistencia',
            'editar asistencia',
            'eliminar asistencia',
            // Horarios
            'ver horarios',
            'crear horario',
            'editar horario',
            'eliminar horario',
            // Inasistencias
            'ver inasistencias',
            'registrar inasistencia',
            'editar inasistencia',
            'eliminar inasistencia',
        ];

        foreach ($privilegios as $privilegio) {
            Privilegio::firstOrCreate(['privilegio' => $privilegio]);
        }
    }
}
