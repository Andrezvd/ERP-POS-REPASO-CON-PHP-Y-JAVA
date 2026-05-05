<?php
/**
 * REPOSITORIO DE COMPRAS
 */

require_once __DIR__ . '/../config/database.php';

class CompraRepository {
    
    public function getAll(): array {
        $sql = "SELECT co.*, p.nombre AS proveedor_nombre, u.nombre AS usuario_nombre
                FROM compras co
                LEFT JOIN proveedores p ON co.proveedor_id = p.id
                LEFT JOIN usuarios u ON co.usuario_id = u.id
                ORDER BY co.created_at DESC
                LIMIT 100";
        return fetchAll($sql);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT co.*, p.nombre AS proveedor_nombre, p.documento AS proveedor_documento,
                       u.nombre AS usuario_nombre
                FROM compras co
                LEFT JOIN proveedores p ON co.proveedor_id = p.id
                LEFT JOIN usuarios u ON co.usuario_id = u.id
                WHERE co.id = ?";
        return fetchOne($sql, [$id]);
    }
    
    public function getDetalle(int $compraId): array {
        $sql = "SELECT cd.*, pr.nombre AS producto_nombre, pr.codigo AS producto_codigo
                FROM compras_detalle cd
                JOIN productos pr ON cd.producto_id = pr.id
                WHERE cd.compra_id = ?";
        return fetchAll($sql, [$compraId]);
    }
    
    public function registrarCompra(array $data): array {
        try {
            beginTransaction();
            
            $numeroOrden = 'ORD' . str_pad(($this->getUltimoNumero() + 1), 8, '0', STR_PAD_LEFT);
            
            $subtotal = 0;
            foreach ($data['productos'] as $item) {
                $subtotal += $item['precio_unitario'] * $item['cantidad'];
            }
            
            $compraId = insert(
                "INSERT INTO compras (numero_orden, proveedor_id, usuario_id, subtotal, total, estado)
                 VALUES (?, ?, ?, ?, ?, 'recibida')",
                [$numeroOrden, $data['proveedor_id'], $data['usuario_id'] ?? 1, $subtotal, $subtotal]
            );
            
            foreach ($data['productos'] as $item) {
                $subtotalItem = $item['precio_unitario'] * $item['cantidad'];
                insert(
                    "INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_unitario, subtotal)
                     VALUES (?, ?, ?, ?, ?)",
                    [$compraId, $item['producto_id'], $item['cantidad'], $item['precio_unitario'], $subtotalItem]
                );
                
                // Actualizar stock
                $producto = fetchOne("SELECT stock_actual FROM productos WHERE id = ?", [$item['producto_id']]);
                execute("UPDATE productos SET stock_actual = stock_actual + ?, precio_compra = ? WHERE id = ?",
                       [$item['cantidad'], $item['precio_unitario'], $item['producto_id']]);
                
                // Registrar movimiento
                insert(
                    "INSERT INTO inventario_movimientos (producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo, referencia_id)
                     VALUES (?, 'entrada', ?, ?, ?, 'Compra', 'compra', ?)",
                    [$item['producto_id'], $item['cantidad'], $producto['stock_actual'],
                     $producto['stock_actual'] + $item['cantidad'], $compraId]
                );
            }
            
            commit();
            return ['success' => true, 'compra_id' => $compraId, 'numero_orden' => $numeroOrden];
        } catch (Exception $e) {
            rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function getUltimoNumero(): int {
        $result = fetchOne("SELECT COALESCE(MAX(CAST(SUBSTRING(numero_orden, 4) AS UNSIGNED)), 0) AS ultimo FROM compras");
        return $result['ultimo'] ?? 0;
    }
}
