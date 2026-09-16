<?php

declare(strict_types=1);

/**
 * Router para el servidor embebido de PHP: `php -S 127.0.0.1:8090 router.php`.
 *
 * Sirve los archivos estaticos del proyecto (Swagger UI y su openapi.yaml) y
 * delega el resto al front controller. Bajo Apache esta misma funcion la
 * cumple .htaccess.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// Bloquear dotfiles (.git, archivos ocultos)
if (preg_match('#(^|/)\.#', $path) === 1) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => ['code' => 403, 'message' => 'Acceso denegado.']]);
    return true;
}

// Servir archivos y directorios reales (el indice lo resuelve el servidor)
$real = realpath(__DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $path));
if ($path !== '/' && $real !== false && strpos($real, __DIR__) === 0) {
    return false;
}

// Delegar al front controller
$_SERVER['SCRIPT_NAME'] = '/index.php';

require __DIR__ . '/index.php';
