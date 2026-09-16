<?php

declare(strict_types=1);

/**
 * Respuestas JSON consistentes para el Frontend.
 */
final class Response
{
    /**
     * Emite una respuesta exitosa.
     *
     * @param mixed       $data
     * @param string|null $message
     * @param int         $status
     * @param array|null  $meta
     * @return void
     */
    public static function success($data = null, ?string $message = null, int $status = 200, ?array $meta = null): void
    {
        $payload = ['success' => true, 'data' => $data];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        self::emit($payload, $status);
    }

    /**
     * Emite una respuesta de error.
     *
     * @param int         $status
     * @param string      $message
     * @param array|null  $errors
     * @return void
     */
    public static function error(int $status, string $message, ?array $errors = null): void
    {
        $payload = [
            'success' => false,
            'error'   => [
                'code'    => $status,
                'message' => $message,
            ],
        ];

        if ($errors !== null) {
            $payload['error']['details'] = $errors;
        }

        self::emit($payload, $status);
    }

    /**
     * Serializa el payload y fija cabeceras/codigo HTTP.
     */
    private static function emit(array $payload, int $status): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
