<?php
/**
 * REPOSITORIO DE CLIENTES
 */

require_once __DIR__ . '/../config/database.php';

class ClienteRepository {
    
    public function getAll(): array {
        $sql = "SELECT c.*, td.nombre AS tipo_documento_nombre, td.codigo AS tipo_documento_codigo
                FROM clientes c
                LEFT JOIN tipos_documento td ON c.tipo_documento_id = td.id
                WHERE c.activo = 1
                ORDER BY c.nombre ASC";
        return fetchAll($sql);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT c.*, td.nombre AS tipo_documento_nombre, td.codigo AS tipo_documento_codigo
                FROM clientes c
                LEFT JOIN tipos_documento td ON c.tipo_documento_id = td.id
                WHERE c.id = ?";
        return fetchOne($sql, [$id]);
    }
    
    public function search(string $query): array {
        $like = "%{$query}%";
        $sql = "SELECT c.*, td.nombre AS tipo_documento_nombre
                FROM clientes c
                LEFT JOIN tipos_documento td ON c.tipo_documento_id = td.id
                WHERE c.activo = 1 AND (c.nombre LIKE ? OR c.documento LIKE ? OR c.telefono LIKE ?)
                ORDER BY c.nombre ASC
                LIMIT 20";
        return fetchAll($sql, [$like, $like, $like]);
    }
    
    public function create(array $data): int {
        $sql = "INSERT INTO clientes (tipo_documento_id, documento, nombre, telefono, email, direccion, ciudad, departamento, cupo_credito)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return insert($sql, [
            $data['tipo_documento_id'] ?? 1,
            $data['documento'],
            $data['nombre'],
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['departamento'] ?? null,
            $data['cupo_credito'] ?? 0
        ]);
    }
    
    public function update(int $id, array $data): int {
        $fields = [];
        $params = [];
        $allowedFields = ['tipo_documento_id', 'documento', 'nombre', 'telefono', 'email',
                         'direccion', 'ciudad', 'departamento', 'cupo_credito', 'activo'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return 0;
        $params[] = $id;
        return execute("UPDATE clientes SET " . implode(', ', $fields) . " WHERE id = ?", $params);
    }
    
    public function delete(int $id): int {
        return execute("UPDATE clientes SET activo = 0 WHERE id = ?", [$id]);
    }
    
    public function count(): int {
        $result = fetchOne("SELECT COUNT(*) AS total FROM clientes WHERE activo = 1");
        return $result['total'] ?? 0;
    }
}
