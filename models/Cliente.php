<?php

declare(strict_types=1);

/**
 * Acceso a datos de clientes B2B (tabla clientes).
 *
 * Atributos de la entidad:
 *  - id:                int     Identificador interno (PK, autoincremental).
 *  - nombre_comercial:  string  Identidad de la empresa cliente.
 *  - rut:               string  RUT o ID comercial oficial (UNIQUE).
 *  - ciudad:            string  Ciudad de la dirección.
 *  - region:            string  Región de la dirección.
 *  - categoria:         string  "Regular" | "Preferencial".
 *  - contacto_nombre:   string  Nombre del encargado.
 *  - contacto_email:    string  Correo electrónico del encargado.
 *  - porcentaje_oferta: string  Porcentaje de descuento personalizado (0-100).
 *  - created_at:        string  Fecha/hora de creación.
 *  - updated_at:        string  Fecha/hora de última actualización.
 *
 * Relaciones:
 *  - camisetas: muchos-a-muchos mediante la tabla pivote cliente_camiseta.
 */
final class Cliente
{
    /**
     * Lista todos los clientes.
     *
     * @return array<int,array>
     */
    public static function all(): array
    {
        $sql = 'SELECT id, nombre_comercial, rut, ciudad, region, categoria,
                       contacto_nombre, contacto_email, porcentaje_oferta, created_at, updated_at
                FROM clientes ORDER BY id';
        $stmt = Database::connection()->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Busca un cliente por su id.
     *
     * @param int $id
     * @return array|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, nombre_comercial, rut, ciudad, region, categoria,
                    contacto_nombre, contacto_email, porcentaje_oferta, created_at, updated_at
             FROM clientes WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $cliente = $stmt->fetch();

        return $cliente === false ? null : $cliente;
    }

    /**
     * Verifica si un RUT ya existe (excluyendo opcionalmente un cliente).
     *
     * @param string   $rut
     * @param int|null $exceptId
     * @return bool
     */
    public static function existsRut(string $rut, ?int $exceptId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM clientes WHERE rut = :rut';
        $params = [':rut' => $rut];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Crea un cliente y devuelve su id.
     *
     * @param array $data
     * @return int
     * @throws RuntimeException 409 si el RUT ya existe (clave duplicada).
     */
    public static function create(array $data): int
    {
        $pdo  = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO clientes (nombre_comercial, rut, ciudad, region, categoria,
                                   contacto_nombre, contacto_email, porcentaje_oferta)
             VALUES (:nombre_comercial, :rut, :ciudad, :region, :categoria,
                     :contacto_nombre, :contacto_email, :porcentaje_oferta)'
        );

        try {
            $stmt->execute([
                ':nombre_comercial'  => $data['nombre_comercial'],
                ':rut'               => $data['rut'],
                ':ciudad'            => $data['ciudad'],
                ':region'            => $data['region'],
                ':categoria'         => $data['categoria'],
                ':contacto_nombre'   => $data['contacto_nombre'],
                ':contacto_email'    => $data['contacto_email'],
                ':porcentaje_oferta' => $data['porcentaje_oferta'],
            ]);
        } catch (PDOException $e) {
            if (Database::isDuplicateKey($e)) {
                throw new RuntimeException('El RUT del cliente ya existe.', 409);
            }

            throw $e;
        }

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza los datos de un cliente.
     *
     * @param int   $id
     * @param array $data
     * @return bool
     * @throws RuntimeException 409 si el RUT ya existe (clave duplicada).
     */
    public static function update(int $id, array $data): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE clientes
             SET nombre_comercial = :nombre_comercial, rut = :rut, ciudad = :ciudad, region = :region,
                 categoria = :categoria, contacto_nombre = :contacto_nombre,
                 contacto_email = :contacto_email, porcentaje_oferta = :porcentaje_oferta
             WHERE id = :id'
        );

        try {
            return $stmt->execute([
                ':nombre_comercial'  => $data['nombre_comercial'],
                ':rut'               => $data['rut'],
                ':ciudad'            => $data['ciudad'],
                ':region'            => $data['region'],
                ':categoria'         => $data['categoria'],
                ':contacto_nombre'   => $data['contacto_nombre'],
                ':contacto_email'    => $data['contacto_email'],
                ':porcentaje_oferta' => $data['porcentaje_oferta'],
                ':id'                => $id,
            ]);
        } catch (PDOException $e) {
            if (Database::isDuplicateKey($e)) {
                throw new RuntimeException('El RUT del cliente ya existe.', 409);
            }

            throw $e;
        }
    }

    /**
     * Elimina un cliente.
     *
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM clientes WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Indica si un cliente tiene camisetas asociadas.
     *
     * @param int $id
     * @return bool
     */
    public static function tieneCamisetas(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM cliente_camiseta WHERE cliente_id = :id'
        );
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
