<?php

declare(strict_types=1);

/**
 * Arranque de la aplicacion: autoload, configuracion y manejo de errores.
 */

require_once __DIR__ . '/config/config.php';

/**
 * Autoload simple por convencion de nombres: busca la clase en los
 * directorios del nucleo (core), servicios (services) y modelos/controladores.
 */
spl_autoload_register(static function (string $class): void {
    $directories = ['core', 'services', 'models', 'controllers'];

    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $class . '.php';

        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

/**
 * Convierte excepciones no capturadas en respuestas JSON controladas.
 */
set_exception_handler(static function (Throwable $exception): void {
    $status  = ($exception instanceof RuntimeException && $exception->getCode() >= 400 && $exception->getCode() < 600)
        ? $exception->getCode()
        : 500;

    $message = $status === 500 && !APP_DEBUG
        ? 'Error interno del servidor.'
        : $exception->getMessage();

    Response::error($status, $message);
});

/**
 * Convierte errores PHP en excepciones para no devolver salida ambigua.
 */
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});
