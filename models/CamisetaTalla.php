<?php

declare(strict_types=1);

/**
 * Gestion de la relacion muchos-a-muchos entre camisetas y tallas.
 *
 * Tabla pivote camiseta_talla (clave primaria compuesta):
 *  - camiseta_id: int  FK -> camisetas.id (ON DELETE CASCADE).
 *  - talla_id:    int  FK -> tallas.id    (ON DELETE CASCADE).
 */
final class CamisetaTalla
{
    /**
     * Devuelve las tallas asociadas a una camiseta.
     *
     * @param int $camisetaId
     * @return array<int,array>
     */
    public static function tallasDe(int $camisetaId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.id, t.nombre
             FROM tallas t
             INNER JOIN camiseta_talla ct ON ct.talla_id = t.id
             WHERE ct.camiseta_id = :id
             ORDER BY t.id'
        );
        $stmt->execute([':id' => $camisetaId]);

        return $stmt->fetchAll();
    }

    /**
     * Devuelve las tallas de todas las camisetas, agrupadas por camiseta.
     *
     * Evita el patron N+1 al listar: una sola consulta con JOIN.
     *
     * @return array<int,array<int,array>> Mapa camiseta_id => lista de tallas.
     */
    public static function tallasPorCamiseta(): array
    {
        $sql  = 'SELECT ct.camiseta_id, t.id, t.nombre
                 FROM camiseta_talla ct
                 INNER JOIN tallas t ON t.id = ct.talla_id
                 ORDER BY ct.camiseta_id, t.id';
        $stmt = Database::connection()->query($sql);

        $mapa = [];

        foreach ($stmt->fetchAll() as $fila) {
            $mapa[(int) $fila['camiseta_id']][] = [
                'id'     => (int) $fila['id'],
                'nombre' => $fila['nombre'],
            ];
        }

        return $mapa;
    }

    /**
     * Reemplaza las tallas de una camiseta por el conjunto indicado.
     *
     * No administra transacciones: el llamador debe envolver la operacion
     * (por ejemplo con Database::transaction) para que sea atomica.
     *
     * @param int            $camisetaId
     * @param array<int,int> $tallaIds
     * @return void
     */
    public static function sync(int $camisetaId, array $tallaIds): void
    {
        $pdo = Database::connection();

        $delete = $pdo->prepare('DELETE FROM camiseta_talla WHERE camiseta_id = :id');
        $delete->execute([':id' => $camisetaId]);

        $insert = $pdo->prepare('INSERT INTO camiseta_talla (camiseta_id, talla_id) VALUES (:camiseta, :talla)');

        foreach (array_unique(array_map('intval', $tallaIds)) as $tallaId) {
            $insert->execute([':camiseta' => $camisetaId, ':talla' => $tallaId]);
        }
    }
}
