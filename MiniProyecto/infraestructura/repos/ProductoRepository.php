<?php
/**
 * REPOSITORIO DE PRODUCTOS
 * Capa de acceso a datos para la entidad Producto
 */

require_once __DIR__ . '/../config/database.php';

class ProductoRepository {
    
    /**
     * Obtiene todos los productos activos
     */
    public function getAll(): array {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre, 
                       u.nombre AS unidad_nombre, i.nombre AS impuesto_nombre, i.porcentaje AS impuesto_porcentaje
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN unidades_medida u ON p.unidad_medida_id = u.id
                LEFT JOIN impuestos i ON p.impuesto_id = i.id
                WHERE p.activo = 1
                ORDER BY p.nombre ASC";
        return fetchAll($sql);
    }
    
    /**
     * Obtiene un producto por su ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre, 
                       u.nombre AS unidad_nombre, i.nombre AS impuesto_nombre, i.porcentaje AS impuesto_porcentaje
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN unidades_medida u ON p.unidad_medida_id = u.id
                LEFT JOIN impuestos i ON p.impuesto_id = i.id
                WHERE p.id = ?";
        return fetchOne($sql, [$id]);
    }
    
    /**
     * Obtiene un producto por su código
     */
    public function getByCodigo(string $codigo): ?array {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.codigo = ?";
        return fetchOne($sql, [$codigo]);
    }
    
    /**
     * Busca productos por nombre o código
     */
    public function search(string $query): array {
        $like = "%{$query}%";
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.activo = 1 AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.codigo_barras LIKE ?)
                ORDER BY p.nombre ASC
                LIMIT 20";
        return fetchAll($sql, [$like, $like, $like]);
    }
    
    /**
     * Obtiene productos con stock bajo
     */
    public function getStockBajo(): array {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre,
                       (p.stock_minimo - p.stock_actual) AS faltante
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.activo = 1 AND p.stock_actual <= p.stock_minimo
                ORDER BY faltante DESC";
        return fetchAll($sql);
    }
    
    /**
     * Crea un nuevo producto
     */
    public function create(array $data): int {
        $sql = "INSERT INTO productos (codigo, codigo_barras, nombre, descripcion, categoria_id, 
                        unidad_medida_id, impuesto_id, precio_compra, precio_venta, 
                        precio_mayorista, stock_actual, stock_minimo, stock_maximo, ubicacion, es_servicio)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return insert($sql, [
            $data['codigo'],
            $data['codigo_barras'] ?? null,
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['categoria_id'] ?? null,
            $data['unidad_medida_id'] ?? 1,
            $data['impuesto_id'] ?? 1,
            $data['precio_compra'] ?? 0,
            $data['precio_venta'],
            $data['precio_mayorista'] ?? null,
            $data['stock_actual'] ?? 0,
            $data['stock_minimo'] ?? 5,
            $data['stock_maximo'] ?? 100,
            $data['ubicacion'] ?? null,
            $data['es_servicio'] ?? 0
        ]);
    }
    
    /**
     * Actualiza un producto existente
     */
    public function update(int $id, array $data): int {
        $fields = [];
        $params = [];
        
        $allowedFields = ['codigo', 'codigo_barras', 'nombre', 'descripcion', 'categoria_id',
                         'unidad_medida_id', 'impuesto_id', 'precio_compra', 'precio_venta',
                         'precio_mayorista', 'stock_actual', 'stock_minimo', 'stock_maximo',
                         'ubicacion', 'activo', 'es_servicio'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return 0;
        }
        
        $params[] = $id;
        $sql = "UPDATE productos SET " . implode(', ', $fields) . " WHERE id = ?";
        return execute($sql, $params);
    }
    
    /**
     * Elimina un producto (borrado lógico)
     */
    public function delete(int $id): int {
        return execute("UPDATE productos SET activo = 0 WHERE id = ?", [$id]);
    }
    
    /**
     * Actualiza el stock de un producto
     */
    public function updateStock(int $id, int $cantidad): int {
        return execute("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?", [$cantidad, $id]);
    }
    
    /**
     * Obtiene el total de productos
     */
    public function count(): int {
        $result = fetchOne("SELECT COUNT(*) AS total FROM productos WHERE activo = 1");
        return $result['total'] ?? 0;
    }
}
