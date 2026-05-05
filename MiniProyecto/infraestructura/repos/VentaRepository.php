<?php
/**
 * REPOSITORIO DE VENTAS
 */

require_once __DIR__ . '/../config/database.php';

class VentaRepository {
    
    public function getAll(): array {
        $sql = "SELECT v.*, c.nombre AS cliente_nombre, u.nombre AS usuario_nombre, fp.nombre AS forma_pago_nombre
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN formas_pago fp ON v.forma_pago_id = fp.id
                ORDER BY v.created_at DESC
                LIMIT 100";
        return fetchAll($sql);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT v.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                       u.nombre AS usuario_nombre, fp.nombre AS forma_pago_nombre
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN formas_pago fp ON v.forma_pago_id = fp.id
                WHERE v.id = ?";
        return fetchOne($sql, [$id]);
    }
    
    public function getDetalle(int $ventaId): array {
        $sql = "SELECT vd.*, p.nombre AS producto_nombre, p.codigo AS producto_codigo
                FROM ventas_detalle vd
                JOIN productos p ON vd.producto_id = p.id
                WHERE vd.venta_id = ?
                ORDER BY vd.id ASC";
        return fetchAll($sql, [$ventaId]);
    }
    
    public function getPagos(int $ventaId): array {
        $sql = "SELECT vp.*, fp.nombre AS forma_pago_nombre
                FROM ventas_pagos vp
                JOIN formas_pago fp ON vp.forma_pago_id = fp.id
                WHERE vp.venta_id = ?";
        return fetchAll($sql, [$ventaId]);
    }
    
    /**
     * Registra una venta completa con transacción
     */
    public function registrarVenta(array $data): array {
        try {
            beginTransaction();
            
            // Generar número de factura
            $numeroFactura = $this->generarNumeroFactura();
            
            // Calcular totales
            $subtotal = 0;
            $iva = 0;
            
            foreach ($data['productos'] as $item) {
                $producto = fetchOne("SELECT precio_venta, impuesto_id FROM productos WHERE id = ?", [$item['producto_id']]);
                $precio = $item['precio_unitario'] ?? $producto['precio_venta'];
                $subtotal += $precio * $item['cantidad'];
                
                // Calcular IVA
                if ($producto['impuesto_id']) {
                    $impuesto = fetchOne("SELECT porcentaje FROM impuestos WHERE id = ?", [$producto['impuesto_id']]);
                    $iva += ($precio * $item['cantidad']) * ($impuesto['porcentaje'] / 100);
                }
            }
            
            $total = $subtotal + $iva;
            
            // Insertar cabecera
            $ventaId = insert(
                "INSERT INTO ventas (numero_factura, cliente_id, usuario_id, subtotal, iva, total, forma_pago_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $numeroFactura,
                    $data['cliente_id'] ?? 1,
                    $data['usuario_id'] ?? 1,
                    $subtotal,
                    $iva,
                    $total,
                    $data['forma_pago_id'] ?? 1
                ]
            );
            
            // Insertar detalle y actualizar stock
            foreach ($data['productos'] as $item) {
                $producto = fetchOne("SELECT precio_venta, stock_actual FROM productos WHERE id = ?", [$item['producto_id']]);
                $precio = $item['precio_unitario'] ?? $producto['precio_venta'];
                $subtotalItem = $precio * $item['cantidad'];
                
                insert(
                    "INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                     VALUES (?, ?, ?, ?, ?)",
                    [$ventaId, $item['producto_id'], $item['cantidad'], $precio, $subtotalItem]
                );
                
                // Actualizar stock
                execute("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?", 
                       [$item['cantidad'], $item['producto_id']]);
                
                // Registrar movimiento
                insert(
                    "INSERT INTO inventario_movimientos (producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo, referencia_id)
                     VALUES (?, 'salida', ?, ?, ?, 'Venta', 'venta', ?)",
                    [$item['producto_id'], $item['cantidad'], $producto['stock_actual'], 
                     $producto['stock_actual'] - $item['cantidad'], $ventaId]
                );
            }
            
            // Registrar pago
            insert(
                "INSERT INTO ventas_pagos (venta_id, forma_pago_id, monto) VALUES (?, ?, ?)",
                [$ventaId, $data['forma_pago_id'] ?? 1, $total]
            );
            
            commit();
            
            return [
                'success' => true,
                'venta_id' => $ventaId,
                'numero_factura' => $numeroFactura,
                'total' => $total,
                'message' => "Venta registrada exitosamente. Factura: {$numeroFactura}"
            ];
            
        } catch (Exception $e) {
            rollback();
            return [
                'success' => false,
                'message' => 'Error al registrar venta: ' . $e->getMessage()
            ];
        }
    }
    
    private function generarNumeroFactura(): string {
        $result = fetchOne("SELECT COALESCE(MAX(CAST(SUBSTRING(numero_factura, 4) AS UNSIGNED)), 0) AS ultimo FROM ventas");
        $nuevoNumero = $result['ultimo'] + 1;
        return 'FAC' . str_pad($nuevoNumero, 8, '0', STR_PAD_LEFT);
    }
    
    public function anular(int $id): array {
        try {
            beginTransaction();
            
            $venta = $this->getById($id);
            if (!$venta) {
                throw new Exception("Venta no encontrada");
            }
            
            execute("UPDATE ventas SET estado = 'anulada' WHERE id = ?", [$id]);
            
            // Revertir stock
            $detalles = $this->getDetalle($id);
            foreach ($detalles as $detalle) {
                execute("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?", 
                       [$detalle['cantidad'], $detalle['producto_id']]);
            }
            
            commit();
            return ['success' => true, 'message' => 'Venta anulada exitosamente'];
        } catch (Exception $e) {
            rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getVentasDelDia(): array {
        $sql = "SELECT v.*, c.nombre AS cliente_nombre
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                WHERE DATE(v.created_at) = CURDATE() AND v.estado = 'completada'
                ORDER BY v.created_at DESC";
        return fetchAll($sql);
    }
    
    public function getResumenDiario(): ?array {
        return fetchOne("SELECT * FROM v_resumen_ventas_diario WHERE fecha = CURDATE()");
    }
    
    public function getDashboard(): ?array {
        return fetchOne("SELECT * FROM v_dashboard_resumen");
    }
    
    public function getTopProductos(int $limite = 10): array {
        $sql = "SELECT * FROM v_top_productos LIMIT ?";
        return fetchAll($sql, [$limite]);
    }
}
