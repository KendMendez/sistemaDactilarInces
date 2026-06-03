<?php

namespace App\Console\Commands;

use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\Feriado;
use App\Models\Inasistencia;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerarInasistencias extends Command
{
    protected $signature = 'inasistencias:generar';
    protected $description = 'Genera registros de inasistencia para empleados sin marcación hoy';

    public function handle(): int
    {
        $hoy = Carbon::today();
        $dayName = $hoy->locale('es')->dayName;

        if (in_array($dayName, ['sábado', 'domingo'])) {
            $this->info('Hoy es fin de semana, se omite.');
            return Command::SUCCESS;
        }

        $esFeriado = Feriado::where('fecha', $hoy->toDateString())->exists();
        if ($esFeriado) {
            $this->info('Hoy es feriado, se omite.');
            return Command::SUCCESS;
        }

        $empleadosActivos = Empleado::all();
        $generadas = 0;

        foreach ($empleadosActivos as $empleado) {
            $tieneAsistencia = Asistencia::where('id_empleado', $empleado->id)
                ->where('fecha', $hoy)
                ->exists();

            if (! $tieneAsistencia) {
                $yaTieneInasistencia = Inasistencia::where('id_empleado', $empleado->id)
                    ->where('fecha', $hoy)
                    ->exists();

                if (! $yaTieneInasistencia) {
                    Inasistencia::create([
                        'id_empleado' => $empleado->id,
                        'fecha' => $hoy,
                        'justificacion' => 'Automática por inasistencia',
                    ]);
                    $generadas++;
                }
            }
        }

        $this->info("Se generaron {$generadas} inasistencias para hoy.");

        return Command::SUCCESS;
    }
}
