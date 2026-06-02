<?php
/**
 * Configuración de conexión a la base de datos MySQL (XAMPP)
 * 
 * Credenciales por defecto de XAMPP:
 * - Host: localhost
 * - Usuario: root
 * - Contraseña: (vacía)
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'task_manager');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Obtiene una conexión PDO a la base de datos.
 * Usa patrón singleton para reutilizar la conexión.
 *
 * @return PDO
 * @throws PDOException si la conexión falla
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}
