<?php

declare(strict_types=1);

/**
 * Conexion unica (singleton) a MySQL mediante PDO.
 */
final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    /**
     * Devuelve la conexion PDO reutilizable a la base de datos.
     *
     * @return PDO
     * @throws RuntimeException si la conexion falla.
     */
    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        try {
            self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('No se pudo conectar a la base de datos: ' . $e->getMessage(), 500);
        }

        return self::$connection;
    }

    /**
     * Ejecuta un callback dentro de una transaccion, con commit/rollback.
     *
     * @param callable $callback Recibe la conexion PDO y puede devolver un valor.
     * @return mixed El valor devuelto por el callback.
     * @throws Throwable Si el callback falla (se hace rollback y se propaga).
     */
    public static function transaction(callable $callback)
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Indica si una excepcion PDO corresponde a una clave duplicada (UNIQUE).
     *
     * @param PDOException $e
     * @return bool
     */
    public static function isDuplicateKey(PDOException $e): bool
    {
        // SQLSTATE 23000 = violacion de integridad; 1062 = entrada duplicada (MySQL/MariaDB).
        return ($e->errorInfo[0] ?? '') === '23000'
            && (int) ($e->errorInfo[1] ?? 0) === 1062;
    }
}
