<?php

declare(strict_types=1);

/**
 * Configuracion general de TodoCamisetas.
 *
 * Los valores de conexion pueden sobreescribirse con variables de entorno
 * para no fijar credenciales en el codigo.
 */

function env(string $key, string $default = ''): string
{
    $value = getenv($key);

    return ($value === false || $value === '') ? $default : $value;
}

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'todocamisetas'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

/** Debug: muestra detalles de error en la respuesta. Desactivar en produccion. */
define('APP_DEBUG', env('APP_DEBUG', '1') === '1');
