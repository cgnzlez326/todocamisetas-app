<?php

declare(strict_types=1);

/**
 * Regla de negocio del precio final dinamico segun la categoria del cliente.
 *
 * - Cliente Preferencial: se aplica su `porcentaje_oferta` sobre el precio base
 *   (redondeado al CLP). Si el porcentaje es 0, se usa el precio base.
 * - Cliente Regular: siempre el precio base.
 */
final class PrecioService
{
    /**
     * Calcula el precio final de una camiseta para un cliente.
     *
     * @param array $camiseta Fila de camiseta (incluye precio).
     * @param array $cliente  Fila de cliente (incluye categoria y porcentaje_oferta).
     * @return int Precio final en CLP.
     */
    public static function calcular(array $camiseta, array $cliente): int
    {
        $precioBase = (int) $camiseta['precio'];

        if (($cliente['categoria'] ?? 'Regular') !== 'Preferencial') {
            return $precioBase;
        }

        $porcentaje = (float) ($cliente['porcentaje_oferta'] ?? 0);

        if ($porcentaje <= 0) {
            return $precioBase;
        }

        return (int) round($precioBase * (1 - $porcentaje / 100));
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
