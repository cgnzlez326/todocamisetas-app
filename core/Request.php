<?php

declare(strict_types=1);

/**
 * Encapsula la solicitud HTTP entrante.
 */
final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path   = $this->resolvePath();
        $this->query  = $_GET;
        $this->body   = $this->resolveBody();
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Parametro de query string.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Cuerpo JSON decodificado.
     *
     * @return array
     */
    public function body(): array
    {
        return $this->body;
    }

    /**
     * Campo del cuerpo JSON.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Normaliza la URI quitando el path base de la aplicacion y el query string.
     */
    private function resolvePath(): string
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . ltrim($uri, '/');

        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    /**
     * Decodifica el cuerpo de la solicitud si viene en JSON.
     */
    private function resolveBody(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
