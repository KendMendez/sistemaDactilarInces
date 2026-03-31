<?php

namespace App\Helpers;

class Message
{
    public static function stored()
    {
        return 'Registro almacenado con exito.';
    }

    public static function updated()
    {
        return 'Registro actualizado con exito.';
    }

    public static function deleted()
    {
        return 'Registro eliminado con exito.';
    }

    public static function duplicated()
    {
        return 'Registro duplicado';
    }

    public static function exception()
    {
        return 'Hubo un error durante la operación.';
    }
}
