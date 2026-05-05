<?php
/**
 * CONFIGURACIÓN DE BASE DE DATOS - ERP-POS
 * Conexión PDO a MySQL
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_NAME', 'erp_pos');
define('DB_USER', 'root');
define('DB_PASS', '07');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtiene una instancia de conexión PDO
 * @return PDO
 * @throws PDOException
 */
function getConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    return $pdo;
}

/**
 * Ejecuta una consulta preparada y retorna todos los resultados
 */
function fetchAll(string $sql, array $params = []): array {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Ejecuta una consulta preparada y retorna un solo resultado
 */
function fetchOne(string $sql, array $params = []): ?array {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Ejecuta una sentencia INSERT/UPDATE/DELETE
 * @return int Número de filas afectadas
 */
function execute(string $sql, array $params = []): int {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Ejecuta INSERT y retorna el ID generado
 */
function insert(string $sql, array $params = []): int {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$pdo->lastInsertId();
}

/**
 * Inicia una transacción
 */
function beginTransaction(): void {
    getConnection()->beginTransaction();
}

/**
 * Confirma una transacción
 */
function commit(): void {
    getConnection()->commit();
}

/**
 * Revierte una transacción
 */
function rollback(): void {
    getConnection()->rollback();
}
