<?php
/**
 * WEB SERVICE SOAP - ERP-POS
 * Servidor SOAP para consulta de productos y validación de stock
 */

require_once __DIR__ . '/../infraestructura/repos/ProductoRepository.php';

class ErpPosService {
    
    private $productoRepo;
    
    public function __construct() {
        $this->productoRepo = new ProductoRepository();
    }
    
    /**
     * Consulta un producto por código
     * @param string $codigo
     * @return array
     */
    public function consultarProducto($codigo) {
        $producto = $this->productoRepo->getByCodigo($codigo);
        
        if ($producto) {
            return [
                'success' => true,
                'id' => $producto['id'],
                'codigo' => $producto['codigo'],
                'nombre' => $producto['nombre'],
                'precio_venta' => (float)$producto['precio_venta'],
                'stock_actual' => (int)$producto['stock_actual'],
                'stock_minimo' => (int)$producto['stock_minimo']
            ];
        }
        
        return [
            'success' => false,
            'message' => "Producto con código '{$codigo}' no encontrado"
        ];
    }
    
    /**
     * Valida si hay stock suficiente para un producto
     * @param int $productoId
     * @param int $cantidad
     * @return array
     */
    public function validarStock($productoId, $cantidad) {
        $producto = $this->productoRepo->getById((int)$productoId);
        
        if (!$producto) {
            return [
                'success' => false,
                'disponible' => false,
                'message' => 'Producto no encontrado'
            ];
        }
        
        $disponible = $producto['stock_actual'] >= $cantidad;
        
        return [
            'success' => true,
            'disponible' => $disponible,
            'producto' => $producto['nombre'],
            'stock_actual' => (int)$producto['stock_actual'],
            'cantidad_solicitada' => (int)$cantidad
        ];
    }
    
    /**
     * Lista todos los productos activos
     * @return array
     */
    public function listarProductos() {
        return $this->productoRepo->getAll();
    }
    
    /**
     * Obtiene productos con stock bajo
     * @return array
     */
    public function productosStockBajo() {
        return $this->productoRepo->getStockBajo();
    }
}

// ============================================
// INICIALIZAR SERVIDOR SOAP
// ============================================

$options = [
    'uri' => 'http://localhost:8000/aplicacion/ws_server.php',
    'soap_version' => SOAP_1_2,
    'encoding' => 'UTF-8'
];

try {
    $server = new SoapServer(null, $options);
    $server->setClass('ErpPosService');
    $server->handle();
} catch (SoapFault $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
