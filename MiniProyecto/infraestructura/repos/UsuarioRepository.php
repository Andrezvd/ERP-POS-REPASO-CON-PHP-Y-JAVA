<?php
/**
 * REPOSITORIO DE USUARIOS
 */

require_once __DIR__ . '/../config/database.php';

class UsuarioRepository {
    
    public function getAll(): array {
        $sql = "SELECT u.id, u.nombre, u.usuario, u.email, u.rol_id, u.activo, u.ultimo_acceso, u.created_at,
                       r.nombre AS rol_nombre
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                ORDER BY u.nombre ASC";
        return fetchAll($sql);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.id = ?";
        return fetchOne($sql, [$id]);
    }
    
    public function login(string $usuario, string $password): ?array {
        $sql = "SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.usuario = ? AND u.password = MD5(?) AND u.activo = 1";
        $user = fetchOne($sql, [$usuario, $password]);
        
        if ($user) {
            execute("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?", [$user['id']]);
        }
        
        return $user;
    }
    
    public function create(array $data): int {
        $sql = "INSERT INTO usuarios (nombre, usuario, email, password, rol_id)
                VALUES (?, ?, ?, MD5(?), ?)";
        return insert($sql, [
            $data['nombre'],
            $data['usuario'],
            $data['email'] ?? null,
            $data['password'],
            $data['rol_id'] ?? 2
        ]);
    }
    
    public function update(int $id, array $data): int {
        $fields = [];
        $params = [];
        $allowedFields = ['nombre', 'usuario', 'email', 'rol_id', 'activo'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (array_key_exists('password', $data) && !empty($data['password'])) {
            $fields[] = "password = MD5(?)";
            $params[] = $data['password'];
        }
        if (empty($fields)) return 0;
        $params[] = $id;
        return execute("UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?", $params);
    }
}
