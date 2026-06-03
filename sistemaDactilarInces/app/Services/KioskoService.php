<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class KioskoService
{
    public function verificar(string $idEmpleadoEncrypted): array
    {
        $decryptedId = Crypt::decrypt($idEmpleadoEncrypted);

        $lockKey = 'kiosko_spam_' . $decryptedId;
        if (! Cache::add($lockKey, true, 3)) {
            return [
                'error' => 1,
                'msg' => 'Ya registró su asistencia recientemente. Espere 3 minutos.',
            ];
        }

        $hoy = Carbon::today()->toDateString();
        $ahora = Carbon::now();

        $asistenciaHoy = Asistencia::where('id_empleado', $decryptedId)
            ->where('fecha', $hoy)
            ->first();

        $horario = Horario::where('id_empleado', $decryptedId)
            ->whereJsonContains('dia', $ahora->locale('es')->dayName)
            ->first();

        if ($asistenciaHoy && $asistenciaHoy->hora_salida) {
            Cache::forget($lockKey);
            return [
                'error' => 1,
                'msg' => 'Ya completó su jornada hoy.',
            ];
        }

        if (! $asistenciaHoy) {
            $status = 'presente';
            $horaEntradaEsperada = $horario ? Carbon::parse($horario->hora_entrada_tolerada) : null;

            if ($horaEntradaEsperada && $ahora->diffInMinutes($horaEntradaEsperada, false) > 15) {
                $status = 'pending_approval';
            }

            Asistencia::create([
                'id_empleado' => $decryptedId,
                'fecha' => $hoy,
                'hora_entrada' => $ahora->format('H:i:s'),
                'status' => $status,
                'tipo_marcacion' => 'kiosko',
            ]);

            return [
                'error' => 0,
                'msg' => $status === 'presente'
                    ? 'Entrada registrada correctamente.'
                    : 'Entrada registrada con retraso. Pendiente de aprobación.',
                'tipo' => 'entrada',
                'status' => $status,
            ];
        }

        $status = 'presente';
        $horaSalidaEsperada = $horario ? Carbon::parse($horario->hora_salida_tolerada) : null;

        if ($horaSalidaEsperada && $horaSalidaEsperada->diffInMinutes($ahora, false) > 15) {
            $status = 'pending_approval';
        }

        $asistenciaHoy->update([
            'hora_salida' => $ahora->format('H:i:s'),
            'status' => $status,
        ]);

        return [
            'error' => 0,
            'msg' => $status === 'presente'
                ? 'Salida registrada correctamente.'
                : 'Salida registrada antes de tiempo. Pendiente de aprobación.',
            'tipo' => 'salida',
            'status' => $status,
        ];
    }

    public function getTemplates(): array
    {
        return \App\Models\Empleado::whereNotNull('huella_pulgar')
            ->orWhereNotNull('huella_indice')
            ->get()
            ->map(function ($empleado) {
                return [
                    'id_empleado' => Crypt::encrypt($empleado->id),
                    'nombre' => $empleado->nombre . ' ' . $empleado->apellido,
                    'huella_pulgar' => $empleado->huella_pulgar,
                    'huella_indice' => $empleado->huella_indice,
                ];
            })
            ->toArray();
    }
}
