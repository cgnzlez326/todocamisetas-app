<?php

declare(strict_types=1);

/**
 * Regla de negocio del precio final dinamico segun la categoria del cliente.
 *
 * - Cliente Preferencial: si la camiseta tiene precio_oferta definido, se usa
 *   ese valor; en caso contrario, el precio base.
 * - Cliente Regular: siempre el precio base.
 */
final class PrecioService
{
    /**
     * Calcula el precio final de una camiseta para un cliente.
     *
     * @param array $camiseta Fila de camiseta (incluye precio y precio_oferta).
     * @param array $cliente  Fila de cliente (incluye categoria).
     * @return int Precio final en CLP.
     */
    public static function calcular(array $camiseta, array $cliente): int
    {
        $precioBase = (int) $camiseta['precio'];

        if (($cliente['categoria'] ?? 'Regular') !== 'Preferencial') {
            return $precioBase;
        }

        $oferta = $camiseta['precio_oferta'] ?? null;

        if ($oferta === null || $oferta === '' || (int) $oferta <= 0) {
            return $precioBase;
        }

        return (int) $oferta;
    }

    /**
     * Anexa el campo precio_final a una camiseta segun el cliente.
     *
     * @param array $camiseta
     * @param array $cliente
     * @return array
     */
    public static function decorar(array $camiseta, array $cliente): array
    {
        $camiseta['precio_final'] = self::calcular($camiseta, $cliente);

        return $camiseta;
    }

    /**
     * Aplica el calculo a un conjunto de camisetas.
     *
     * @param array $camisetas
     * @param array $cliente
     * @return array
     */
    public static function decorarColeccion(array $camisetas, array $cliente): array
    {
        return array_map(
            static fn (array $camiseta): array => self::decorar($camiseta, $cliente),
            $camisetas
        );
    }
}
