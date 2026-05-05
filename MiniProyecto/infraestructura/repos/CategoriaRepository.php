<?php
/**
 * REPOSITORIO DE CATEGORÍAS
 */

require_once __DIR__ . '/../config/database.php';

class CategoriaRepository {
    
    public function getAll(): array {
        return fetchAll("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre ASC");
    }
    
    public function getById(int $id): ?array {
        return fetchOne("SELECT * FROM categorias WHERE id = ?", [$id]);
    }
    
    public function create(array $data): int {
        return insert("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)",
                     [$data['nombre'], $data['descripcion'] ?? null]);
    }
    
    public function update(int $id, array $data): int {
        $fields = [];
        $params = [];
        foreach (['nombre', 'descripcion', 'activo'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($fields)) return 0;
        $params[] = $id;
        return execute("UPDATE categorias SET " . implode(', ', $fields) . " WHERE id = ?", $params);
    }
    
    public function delete(int $id): int {
        return execute("UPDATE categorias SET activo = 0 WHERE id = ?", [$id]);
    }
}
