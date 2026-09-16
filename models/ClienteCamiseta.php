<?php

declare(strict_types=1);

/**
 * Gestion de la relacion entre clientes y camisetas (tabla cliente_camiseta).
 *
 * Tabla pivote cliente_camiseta (clave primaria compuesta):
 *  - cliente_id:  int  FK -> clientes.id  (ON DELETE RESTRICT).
 *  - camiseta_id: int  FK -> camisetas.id (ON DELETE RESTRICT).
 *  - cantidad:    int  Unidades de la camiseta asociadas al cliente (default 1).
 */
final class ClienteCamiseta
{
    /**
     * Asocia una camiseta a un cliente.
     *
     * @param int $clienteId
     * @param int $camisetaId
     * @param int $cantidad
     * @return bool
     */
    public static function attach(int $clienteId, int $camisetaId, int $cantidad = 1): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO cliente_camiseta (cliente_id, camiseta_id, cantidad)
             VALUES (:cliente, :camiseta, :cantidad)
             ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)'
        );

        return $stmt->execute([
            ':cliente'  => $clienteId,
            ':camiseta' => $camisetaId,
            ':cantidad' => $cantidad,
        ]);
    }

    /**
     * Quita la asociacion entre un cliente y una camiseta.
     *
     * @param int $clienteId
     * @param int $camisetaId
     * @return bool
     */
    public static function detach(int $clienteId, int $camisetaId): bool
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM cliente_camiseta WHERE cliente_id = :cliente AND camiseta_id = :camiseta'
        );

        return $stmt->execute([':cliente' => $clienteId, ':camiseta' => $camisetaId]);
    }

    /**
     * Verifica si existe la asociacion cliente-camiseta.
     *
     * @param int $clienteId
     * @param int $camisetaId
     * @return bool
     */
    public static function exists(int $clienteId, int $camisetaId): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM cliente_camiseta WHERE cliente_id = :cliente AND camiseta_id = :camiseta'
        );
        $stmt->execute([':cliente' => $clienteId, ':camiseta' => $camisetaId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Camisetas asociadas a un cliente junto con la cantidad contratada.
     *
     * @param int $clienteId
     * @return array<int,array>
     */
    public static function camisetasDe(int $clienteId): array
    {
        $sql = 'SELECT c.id, c.titulo, c.club, c.pais, c.tipo, c.color,
                       c.precio, c.detalles, c.sku, cc.cantidad
                FROM camisetas c
                INNER JOIN cliente_camiseta cc ON cc.camiseta_id = c.id
                WHERE cc.cliente_id = :id
                ORDER BY c.id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([':id' => $clienteId]);

        return $stmt->fetchAll();
    }
}
