<?php

declare(strict_types=1);

/**
 * Controlador de informacion general del servicio.
 */
final class HomeController
{
    /**
     * GET /
     * Respuesta de bienvenida / salud.
     *
     * @param Request $request
     * @return void
     */
    public static function index(Request $request): void
    {
        Response::success([
            'servicio' => 'TodoCamisetas API',
            'version'  => '1.0.0',
            'docs'     => '/api',
        ], 'API operativa.');
    }

    /**
     * GET /api
     * Indice de recursos disponibles.
     *
     * @param Request $request
     * @return void
     */
    public static function info(Request $request): void
    {
        Response::success([
            'servicio' => 'TodoCamisetas API',
            'version'  => '1.0.0',
            'recursos' => [
                'camisetas' => '/api/camisetas',
                'clientes'  => '/api/clientes',
                'tallas'    => '/api/tallas',
            ],
        ], 'API operativa.');
    }
}
