<?php

declare(strict_types=1);

/**
 * Rutas de la API. Cada entrada es [metodo, patron_regex, [Controlador, accion]].
 *
 * El patron es una expresion regular completa sobre la ruta normalizada.
 */

return [
    // Documentacion / salud del servicio
    ['GET', '#^/$#', [HomeController::class, 'index']],
    ['GET', '#^/api$#', [HomeController::class, 'info']],

    // Tallas
    ['GET',    '#^/api/tallas$#',                 [TallaController::class, 'index']],
    ['POST',   '#^/api/tallas$#',                 [TallaController::class, 'store']],
    ['GET',    '#^/api/tallas/(?P<id>\d+)$#',     [TallaController::class, 'show']],
    ['DELETE', '#^/api/tallas/(?P<id>\d+)$#',     [TallaController::class, 'destroy']],

    // Camisetas
    ['GET',    '#^/api/camisetas$#',              [CamisetaController::class, 'index']],
    ['POST',   '#^/api/camisetas$#',              [CamisetaController::class, 'store']],
    ['GET',    '#^/api/camisetas/sku/(?P<sku>[^/]+)$#', [CamisetaController::class, 'showBySku']],
    ['GET',    '#^/api/camisetas/(?P<id>\d+)$#',  [CamisetaController::class, 'show']],
    ['PUT',    '#^/api/camisetas/(?P<id>\d+)$#',  [CamisetaController::class, 'update']],
    ['DELETE', '#^/api/camisetas/(?P<id>\d+)$#',  [CamisetaController::class, 'destroy']],

    // Clientes
    ['GET',    '#^/api/clientes$#',               [ClienteController::class, 'index']],
    ['POST',   '#^/api/clientes$#',               [ClienteController::class, 'store']],
    ['GET',    '#^/api/clientes/(?P<id>\d+)$#',   [ClienteController::class, 'show']],
    ['PUT',    '#^/api/clientes/(?P<id>\d+)$#',   [ClienteController::class, 'update']],
    ['DELETE', '#^/api/clientes/(?P<id>\d+)$#',   [ClienteController::class, 'destroy']],

    // Relacion cliente <-> camisetas y precio final dinamico
    ['GET',    '#^/api/clientes/(?P<id>\d+)/camisetas$#',                            [ClienteController::class, 'camisetas']],
    ['POST',   '#^/api/clientes/(?P<id>\d+)/camisetas$#',                            [ClienteController::class, 'asociar']],
    ['GET',    '#^/api/clientes/(?P<id>\d+)/camisetas/(?P<camisetaId>\d+)/precio$#', [ClienteController::class, 'precio']],
    ['DELETE', '#^/api/clientes/(?P<id>\d+)/camisetas/(?P<camisetaId>\d+)$#',        [ClienteController::class, 'desasociar']],
];
