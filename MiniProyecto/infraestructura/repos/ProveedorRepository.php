<?php
/**
 * REPOSITORIO DE PROVEEDORES
 */

require_once __DIR__ . '/../config/database.php';

class ProveedorRepository {
    
    public function getAll(): array {
        $sql = "SELECT p.*, td.nombre AS tipo_documento_nombre
                FROM proveedores p
                LEFT JOIN tipos_documento td ON p.tipo_documento_id = td.id
                WHERE p.activo = 1
                ORDER BY p.nombre ASC";
        return fetchAll($sql);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT p.*, td.nombre AS tipo_documento_nombre
                FROM proveedores p
                LEFT JOIN tipos_documento td ON p.tipo_documento_id = td.id
                WHERE p.id = ?";
        return fetchOne($sql, [$id]);
    }
    
    public function create(array $data): int {
        $sql = "INSERT INTO proveedores (tipo_documento_id, documento, nombre, contacto, telefono, email, direccion, ciudad, departamento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return insert($sql, [
            $data['tipo_documento_id'] ?? 1,
            $data['documento'],
            $data['nombre'],
            $data['contacto'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['departamento'] ?? null
        ]);
    }
    
    public function update(int $id, array $data): int {
        $fields = [];
        $params = [];
        $allowedFields = ['tipo_documento_id', 'documento', 'nombre', 'contacto', 'telefono',
                         'email', 'direccion', 'ciudad', 'departamento', 'activo'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($fields)) return 0;
        $params[] = $id;
        return execute("UPDATE proveedores SET " . implode(', ', $fields) . " WHERE id = ?", $params);
    }
    
    public function delete(int $id): int {
        return execute("UPDATE proveedores SET activo = 0 WHERE id = ?", [$id]);
    }
    
    public function count(): int {
        $result = fetchOne("SELECT COUNT(*) AS total FROM proveedores WHERE activo = 1");
        return $result['total'] ?? 0;
    }
}
