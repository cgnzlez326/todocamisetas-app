<?php

declare(strict_types=1);

/**
 * Acceso a datos de tallas (tabla tallas).
 *
 * Atributos de la entidad:
 *  - id:     int     Identificador interno (PK, autoincremental).
 *  - nombre: string  Nombre de la talla, p. ej. "S", "M", "L", "XL" (UNIQUE).
 *
 * Relaciones:
 *  - camisetas: muchos-a-muchos mediante la tabla pivote camiseta_talla.
 */
final class Talla
{
    /**
     * Lista todas las tallas.
     *
     * @return array<int,array>
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT id, nombre FROM tallas ORDER BY id');

        return $stmt->fetchAll();
    }

    /**
     * Busca una talla por su id.
     *
     * @param int $id
     * @return array|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, nombre FROM tallas WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $talla = $stmt->fetch();

        return $talla === false ? null : $talla;
    }

    /**
     * Verifica si un nombre de talla ya existe.
     *
     * @param string $nombre
     * @return bool
     */
    public static function existsNombre(string $nombre): bool
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM tallas WHERE nombre = :nombre');
        $stmt->execute([':nombre' => $nombre]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Crea una talla y devuelve su id.
     *
     * @param string $nombre
     * @return int
     */
    public static function create(string $nombre): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO tallas (nombre) VALUES (:nombre)');
        $stmt->execute([':nombre' => $nombre]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Elimina una talla.
     *
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM tallas WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Cuenta cuantas tallas existen de una lista de ids.
     *
     * @param array<int,int> $ids
     * @return int
     */
    public static function countByIds(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt         = Database::connection()->prepare("SELECT COUNT(*) FROM tallas WHERE id IN ($placeholders)");
        $stmt->execute(array_values($ids));

        return (int) $stmt->fetchColumn();
    }

    /**
     * Verifica que todas las tallas indicadas existan.
     *
     * @param array<int,int> $ids
     * @return bool
     */
    public static function existenTodas(array $ids): bool
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        return $ids !== [] && self::countByIds($ids) === count($ids);
    }
}
