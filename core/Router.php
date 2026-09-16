<?php

declare(strict_types=1);

/**
 * Enrutador manual basado en expresiones regulares.
 *
 * Cada ruta se define como [metodo, patron, manejador], donde el patron es una
 * expresion regular completa sobre la ruta normalizada (sin query string).
 */
final class Router
{
    /** @var array<int,array{0:string,1:string,2:array}> */
    private array $routes = [];

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Carga las rutas definidas en un archivo que retorna un arreglo.
     *
     * @param string $file
     * @return void
     */
    public function loadRoutes(string $file): void
    {
        $routes = require $file;

        foreach ($routes as $route) {
            $this->add($route[0], $route[1], $route[2]);
        }
    }

    /**
     * Registra una ruta.
     *
     * @param string $method
     * @param string $pattern
     * @param array  $handler [Clase::class, 'metodo']
     * @return void
     */
    public function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = [strtoupper($method), $pattern, $handler];
    }

    /**
     * Resuelve la solicitud y ejecuta el controlador correspondiente.
     *
     * @return void
     */
    public function dispatch(): void
    {
        $method = $this->request->method();
        $path   = $this->request->path();

        if ($method === 'OPTIONS') {
            Response::success(null, 'Preflight OK', 200);
            return;
        }

        $pathMatched = false;

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $pathMatched = true;

            if ($routeMethod !== $method) {
                continue;
            }

            $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            [$class, $action] = $handler;

            if (!class_exists($class) || !method_exists($class, $action)) {
                Response::error(500, 'Controlador o metodo no encontrado.');
                return;
            }

            $class::$action($this->request, ...$params);
            return;
        }

        if ($pathMatched) {
            Response::error(405, 'Metodo HTTP no permitido para esta ruta.');
            return;
        }

        Response::error(404, 'Recurso no encontrado.');
    }
}
