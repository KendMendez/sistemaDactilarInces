<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Empleado;
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

    public function matchFingerprint(string $huella): ?array
    {
        $scannedImg = @imagecreatefromstring(base64_decode($huella));
        if (! $scannedImg) {
            return null;
        }

        $w = 32;
        $h = 32;
        $scannedNorm = $this->normalizeFingerprint($scannedImg, $w, $h);
        imagedestroy($scannedImg);

        $mean = array_sum(array_merge(...$scannedNorm)) / ($w * $h);
        $variance = 0.0;
        foreach ($scannedNorm as $row) foreach ($row as $v) $variance += ($v - $mean) ** 2;
        $variance /= ($w * $h);
        if ($variance < 0.01) return null;

        $employees = Empleado::whereNotNull('huella_pulgar')
            ->orWhereNotNull('huella_indice')
            ->get();

        $bestScore = 1.0;
        $bestEmployee = null;

        foreach ($employees as $emp) {
            foreach (['huella_pulgar', 'huella_indice'] as $field) {
                if (empty($emp->$field)) {
                    continue;
                }

                $storedImg = @imagecreatefromstring(base64_decode($emp->$field));
                if (! $storedImg) {
                    continue;
                }

                $storedNorm = $this->normalizeFingerprint($storedImg, $w, $h);
                imagedestroy($storedImg);

                $score = $this->compareNormalized($scannedNorm, $storedNorm);

                if ($score < $bestScore) {
                    $bestScore = $score;
                    $bestEmployee = $emp;
                }
            }
        }

        $threshold = 0.18;
        if ($bestEmployee && $bestScore < $threshold) {
            return [
                'id_empleado' => Crypt::encrypt($bestEmployee->id),
                'nombre' => $bestEmployee->nombre . ' ' . $bestEmployee->apellido,
                'score' => round($bestScore, 4),
            ];
        }

        return null;
    }

    private function normalizeFingerprint($img, int $w, int $h): array
    {
        $thumb = imagecreatetruecolor($w, $h);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $w, $h, imagesx($img), imagesy($img));

        $gray = [];
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgb = imagecolorsforindex($thumb, imagecolorat($thumb, $x, $y));
                $gray[$x][$y] = ($rgb['red'] + $rgb['green'] + $rgb['blue']) / 3;
            }
        }
        imagedestroy($thumb);

        $min = 255;
        $max = 0;
        foreach ($gray as $row) {
            foreach ($row as $v) {
                if ($v < $min) {
                    $min = $v;
                }
                if ($v > $max) {
                    $max = $v;
                }
            }
        }

        $range = $max - $min;
        if ($range > 0) {
            for ($x = 0; $x < $w; $x++) {
                for ($y = 0; $y < $h; $y++) {
                    $gray[$x][$y] = ($gray[$x][$y] - $min) / $range;
                }
            }
        } else {
            for ($x = 0; $x < $w; $x++) {
                for ($y = 0; $y < $h; $y++) {
                    $gray[$x][$y] = 0.0;
                }
            }
        }

        return $gray;
    }

    private function compareNormalized(array $a, array $b): float
    {
        $totalDiff = 0.0;
        $count = 0;
        $w = count($a);
        for ($x = 0; $x < $w; $x++) {
            $h = count($a[$x]);
            for ($y = 0; $y < $h; $y++) {
                $totalDiff += abs($a[$x][$y] - $b[$x][$y]);
                $count++;
            }
        }
        return $count > 0 ? $totalDiff / $count : 1.0;
    }
}
