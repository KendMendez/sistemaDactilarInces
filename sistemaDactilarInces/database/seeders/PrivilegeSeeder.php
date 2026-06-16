<?php

namespace Database\Seeders;

use App\Models\Privilegio;
use Illuminate\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    public function run(): void
    {
        $privilegios = [
            ['privilegio' => 'ver empleados',        'campo' => 'Empleados'],
            ['privilegio' => 'crear empleado',       'campo' => 'Empleados'],
            ['privilegio' => 'editar empleado',      'campo' => 'Empleados'],
            ['privilegio' => 'eliminar empleado',    'campo' => 'Empleados'],
            ['privilegio' => 'ver roles',            'campo' => 'Roles'],
            ['privilegio' => 'crear rol',            'campo' => 'Roles'],
            ['privilegio' => 'editar rol',           'campo' => 'Roles'],
            ['privilegio' => 'eliminar rol',         'campo' => 'Roles'],
            ['privilegio' => 'ver privilegios',      'campo' => 'Privilegios'],
            ['privilegio' => 'asignar privilegios',  'campo' => 'Privilegios'],
            ['privilegio' => 'ver cargos',           'campo' => 'Cargos'],
            ['privilegio' => 'crear cargo',          'campo' => 'Cargos'],
            ['privilegio' => 'editar cargo',         'campo' => 'Cargos'],
            ['privilegio' => 'eliminar cargo',       'campo' => 'Cargos'],
            ['privilegio' => 'ver feriados',         'campo' => 'Feriados'],
            ['privilegio' => 'crear feriado',        'campo' => 'Feriados'],
            ['privilegio' => 'editar feriado',       'campo' => 'Feriados'],
            ['privilegio' => 'eliminar feriado',     'campo' => 'Feriados'],
            ['privilegio' => 'ver asistencias',      'campo' => 'Asistencias'],
            ['privilegio' => 'registrar asistencia', 'campo' => 'Asistencias'],
            ['privilegio' => 'editar asistencia',    'campo' => 'Asistencias'],
            ['privilegio' => 'eliminar asistencia',  'campo' => 'Asistencias'],
            ['privilegio' => 'ver horarios',         'campo' => 'Horarios'],
            ['privilegio' => 'crear horario',        'campo' => 'Horarios'],
            ['privilegio' => 'editar horario',       'campo' => 'Horarios'],
            ['privilegio' => 'eliminar horario',     'campo' => 'Horarios'],
            ['privilegio' => 'ver inasistencias',    'campo' => 'Inasistencias'],
            ['privilegio' => 'registrar inasistencia','campo' => 'Inasistencias'],
            ['privilegio' => 'editar inasistencia',  'campo' => 'Inasistencias'],
            ['privilegio' => 'eliminar inasistencia','campo' => 'Inasistencias'],
        ];

        foreach ($privilegios as $p) {
            Privilegio::updateOrCreate(
                ['privilegio' => $p['privilegio']],
                ['campo' => $p['campo']]
            );
        }
    }
}
