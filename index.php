<?php

declare(strict_types=1);

/**
 * Front controller de TodoCamisetas.
 * Todas las solicitudes HTTP pasan por este archivo (ver .htaccess).
 */

require_once __DIR__ . '/bootstrap.php';

$request = new Request();
$router  = new Router($request);

$router->loadRoutes(__DIR__ . '/routes/api.php');
$router->dispatch();
