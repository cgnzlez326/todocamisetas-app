<?php

declare(strict_types=1);

/**
 * Acceso a datos de camisetas (tabla camisetas).
 *
 * Atributos de la entidad:
 *  - id:            int          Identificador interno (PK, autoincremental).
 *  - titulo:        string       Nombre descriptivo de la camiseta.
 *  - club:          string       Club al que pertenece.
 *  - pais:          string       Procedencia del diseño.
 *  - tipo:          string       Clasificación: "Local" | "Visita" | "Femenino".
 *  - color:         string       Combinación principal de colores.
 *  - precio:        int          Valor base en pesos chilenos (CLP).
 *  - precio_oferta: int|null     Precio de oferta en CLP (null = sin oferta).
 *  - detalles:      string|null  Texto adicional o descripción técnica.
 *  - sku:           string       Código de producto único (UNIQUE).
 *  - created_at:    string       Fecha/hora de creación.
 *  - updated_at:    string       Fecha/hora de última actualización.
 *
 * Relaciones:
 *  - tallas: muchos-a-muchos mediante la tabla pivote camiseta_talla.
 *  - clientes: muchos-a-muchos mediante la tabla pivote cliente_camiseta.
 */
final class Camiseta
{
    /**
     * Lista todas las camisetas.
     *
     * @return array<int,array>
     */
    public static function all(): array
    {
        $sql  = 'SELECT id, titulo, club, pais, tipo, color, precio, precio_oferta, detalles, sku, created_at, updated_at
                 FROM camisetas ORDER BY id';
        $stmt = Database::connection()->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Busca una camiseta por su id.
     *
     * @param int $id
     * @return array|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, titulo, club, pais, tipo, color, precio, precio_oferta, detalles, sku, created_at, updated_at
             FROM camisetas WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $camiseta = $stmt->fetch();

        return $camiseta === false ? null : $camiseta;
    }

    /**
     * Busca una camiseta por su SKU.
     *
     * @param string $sku
     * @return array|null
     */
    public static function findBySku(string $sku): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, titulo, club, pais, tipo, color, precio, precio_oferta, detalles, sku, created_at, updated_at
             FROM camisetas WHERE sku = :sku LIMIT 1'
        );
        $stmt->execute([':sku' => $sku]);
        $camiseta = $stmt->fetch();

        return $camiseta === false ? null : $camiseta;
    }

    /**
     * Verifica si un SKU ya existe (excluyendo opcionalmente una camiseta).
     *
     * @param string   $sku
     * @param int|null $exceptId
     * @return bool
     */
    public static function existsSku(string $sku, ?int $exceptId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM camisetas WHERE sku = :sku';
        $params = [':sku' => $sku];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Crea una camiseta y devuelve su id.
     *
     * @param array $data
     * @return int
     * @throws RuntimeException 409 si el SKU ya existe (clave duplicada).
     */
    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO camisetas (titulo, club, pais, tipo, color, precio, precio_oferta, detalles, sku)
             VALUES (:titulo, :club, :pais, :tipo, :color, :precio, :precio_oferta, :detalles, :sku)'
        );

        try {
            $stmt->execute([
                ':titulo'        => $data['titulo'],
                ':club'          => $data['club'],
                ':pais'          => $data['pais'],
                ':tipo'          => $data['tipo'],
                ':color'         => $data['color'],
                ':precio'        => $data['precio'],
                ':precio_oferta' => $data['precio_oferta'],
                ':detalles'      => $data['detalles'],
                ':sku'           => $data['sku'],
            ]);
        } catch (PDOException $e) {
            if (Database::isDuplicateKey($e)) {
                throw new RuntimeException('El SKU ya existe.', 409);
            }

            throw $e;
        }

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza los datos de una camiseta.
     *
     * @param int   $id
     * @param array $data
     * @return bool
     * @throws RuntimeException 409 si el SKU ya existe (clave duplicada).
     */
    public static function update(int $id, array $data): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE camisetas
             SET titulo = :titulo, club = :club, pais = :pais, tipo = :tipo, color = :color,
                 precio = :precio, precio_oferta = :precio_oferta, detalles = :detalles, sku = :sku
             WHERE id = :id'
        );

        try {
            return $stmt->execute([
                ':titulo'        => $data['titulo'],
                ':club'          => $data['club'],
                ':pais'          => $data['pais'],
                ':tipo'          => $data['tipo'],
                ':color'         => $data['color'],
                ':precio'        => $data['precio'],
                ':precio_oferta' => $data['precio_oferta'],
                ':detalles'      => $data['detalles'],
                ':sku'           => $data['sku'],
                ':id'            => $id,
            ]);
        } catch (PDOException $e) {
            if (Database::isDuplicateKey($e)) {
                throw new RuntimeException('El SKU ya existe.', 409);
            }

            throw $e;
        }
    }

    /**
     * Elimina una camiseta.
     *
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM camisetas WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Indica si una camiseta esta asociada a algun cliente.
     *
     * @param int $id
     * @return bool
     */
    public static function tieneClientes(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM cliente_camiseta WHERE camiseta_id = :id'
        );
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
