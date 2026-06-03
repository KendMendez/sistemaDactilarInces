<?php

namespace Database\Seeders;

use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FeriadoSeeder extends Seeder
{
    private array $fijos = [
        ['mes' => 1,  'dia' => 1,  'desc' => 'Año Nuevo'],
        ['mes' => 4,  'dia' => 19, 'desc' => 'Declaración de la Independencia'],
        ['mes' => 5,  'dia' => 1,  'desc' => 'Día del Trabajador'],
        ['mes' => 6,  'dia' => 24, 'desc' => 'Batalla de Carabobo'],
        ['mes' => 7,  'dia' => 5,  'desc' => 'Día de la Independencia'],
        ['mes' => 7,  'dia' => 24, 'desc' => 'Natalicio de Simón Bolívar'],
        ['mes' => 10, 'dia' => 12, 'desc' => 'Día de la Resistencia Indígena'],
        ['mes' => 12, 'dia' => 24, 'desc' => 'Nochebuena'],
        ['mes' => 12, 'dia' => 25, 'desc' => 'Navidad'],
        ['mes' => 12, 'dia' => 31, 'desc' => 'Fin de Año'],
    ];

    public function run(): void
    {
        foreach (range(2025, 2030) as $year) {
            foreach ($this->fijos as $fijo) {
                Feriado::firstOrCreate([
                    'fecha' => Carbon::create($year, $fijo['mes'], $fijo['dia'])->toDateString(),
                ], [
                    'descripcion' => $fijo['desc'],
                ]);
            }

            $easter = Carbon::create($year, 3, 21)->addDays(easter_days($year));

            $variables = [
                [$easter->copy()->subDays(48), 'Carnaval'],
                [$easter->copy()->subDays(47), 'Carnaval'],
                [$easter->copy()->subDays(3),  'Jueves Santo'],
                [$easter->copy()->subDays(2),  'Viernes Santo'],
            ];

            foreach ($variables as [$fecha, $desc]) {
                Feriado::firstOrCreate([
                    'fecha' => $fecha->toDateString(),
                ], [
                    'descripcion' => $desc,
                ]);
            }
        }

        $this->command->info('Feriados 2025-2030 sembrados correctamente.');
    }
}
