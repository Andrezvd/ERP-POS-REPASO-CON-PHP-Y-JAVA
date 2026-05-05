-- ============================================
-- SCRIPT DDL COMPLETO - ERP-POS MINI PROYECTO
-- BASE DE DATOS: erp_pos
-- Motor: MySQL 8.x
-- ============================================

CREATE DATABASE IF NOT EXISTS erp_pos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE erp_pos;

-- ============================================
-- TABLAS DEL SISTEMA (USUARIOS Y SEGURIDAD)
-- ============================================

-- TABLA: roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLA: usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- TABLA: sesiones (para control de sesiones activas)
CREATE TABLE sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    activa TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE CATÁLOGOS
-- ============================================

-- TABLA: categorias
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLA: unidades_medida
CREATE TABLE unidades_medida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLA: impuestos
CREATE TABLE impuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLA: formas_pago
CREATE TABLE formas_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE NEGOCIO (PRODUCTOS E INVENTARIO)
-- ============================================

-- TABLA: productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(100) NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INT,
    unidad_medida_id INT,
    impuesto_id INT,
    precio_compra DECIMAL(12,2) NOT NULL DEFAULT 0,
    precio_venta DECIMAL(12,2) NOT NULL DEFAULT 0,
    precio_mayorista DECIMAL(12,2) NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    stock_maximo INT DEFAULT 100,
    ubicacion VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    es_servicio TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (unidad_medida_id) REFERENCES unidades_medida(id) ON DELETE SET NULL,
    FOREIGN KEY (impuesto_id) REFERENCES impuestos(id) ON DELETE SET NULL,
    INDEX idx_productos_codigo (codigo),
    INDEX idx_productos_nombre (nombre),
    INDEX idx_productos_categoria (categoria_id),
    INDEX idx_productos_activo (activo)
) ENGINE=InnoDB;

-- TABLA: inventario_movimientos (control de movimientos de stock)
CREATE TABLE inventario_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste', 'inicial') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    motivo VARCHAR(255),
    referencia_tipo VARCHAR(50), -- 'compra', 'venta', 'ajuste'
    referencia_id INT,
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_inventario_producto (producto_id),
    INDEX idx_inventario_fecha (created_at)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE TERCEROS (CLIENTES Y PROVEEDORES)
-- ============================================

-- TABLA: tipos_documento
CREATE TABLE tipos_documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLA: clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento_id INT,
    documento VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    ciudad VARCHAR(100),
    departamento VARCHAR(100),
    cupo_credito DECIMAL(12,2) DEFAULT 0,
    saldo_pendiente DECIMAL(12,2) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento(id) ON DELETE SET NULL,
    INDEX idx_clientes_documento (documento),
    INDEX idx_clientes_nombre (nombre)
) ENGINE=InnoDB;

-- TABLA: proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento_id INT,
    documento VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    ciudad VARCHAR(100),
    departamento VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento(id) ON DELETE SET NULL,
    INDEX idx_proveedores_documento (documento),
    INDEX idx_proveedores_nombre (nombre)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE VENTAS (MÓDULO POS)
-- ============================================

-- TABLA: ventas (cabecera de venta)
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_factura VARCHAR(20) UNIQUE NOT NULL,
    cliente_id INT,
    usuario_id INT,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    descuento DECIMAL(12,2) DEFAULT 0,
    iva DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    forma_pago_id INT,
    estado ENUM('completada', 'anulada', 'pendiente') DEFAULT 'completada',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (forma_pago_id) REFERENCES formas_pago(id) ON DELETE SET NULL,
    INDEX idx_ventas_fecha (created_at),
    INDEX idx_ventas_cliente (cliente_id),
    INDEX idx_ventas_estado (estado),
    INDEX idx_ventas_factura (numero_factura)
) ENGINE=InnoDB;

-- TABLA: ventas_detalle (detalle de cada producto en la venta)
CREATE TABLE ventas_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    descuento DECIMAL(12,2) DEFAULT 0,
    impuesto DECIMAL(12,2) DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_ventas_detalle_venta (venta_id),
    INDEX idx_ventas_detalle_producto (producto_id)
) ENGINE=InnoDB;

-- TABLA: ventas_pagos (múltiples formas de pago por venta)
CREATE TABLE ventas_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    forma_pago_id INT NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    referencia VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (forma_pago_id) REFERENCES formas_pago(id)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE COMPRAS
-- ============================================

-- TABLA: compras (cabecera de compra)
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_orden VARCHAR(20) UNIQUE NOT NULL,
    proveedor_id INT,
    usuario_id INT,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    descuento DECIMAL(12,2) DEFAULT 0,
    iva DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    estado ENUM('recibida', 'pendiente', 'anulada') DEFAULT 'pendiente',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_compras_fecha (created_at),
    INDEX idx_compras_proveedor (proveedor_id)
) ENGINE=InnoDB;

-- TABLA: compras_detalle (detalle de cada producto en la compra)
CREATE TABLE compras_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    descuento DECIMAL(12,2) DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_compras_detalle_compra (compra_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE CAJA
-- ============================================

-- TABLA: cajas (apertura y cierre de caja)
CREATE TABLE cajas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    saldo_inicial DECIMAL(12,2) NOT NULL DEFAULT 0,
    saldo_final DECIMAL(12,2) NULL,
    total_ventas DECIMAL(12,2) DEFAULT 0,
    total_gastos DECIMAL(12,2) DEFAULT 0,
    observaciones TEXT,
    estado ENUM('abierta', 'cerrada') DEFAULT 'abierta',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_cajas_usuario (usuario_id),
    INDEX idx_cajas_estado (estado)
) ENGINE=InnoDB;

-- TABLA: gastos (gastos operativos)
CREATE TABLE gastos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caja_id INT,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    tipo_gasto VARCHAR(50),
    usuario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caja_id) REFERENCES cajas(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE AUDITORÍA
-- ============================================

-- TABLA: auditoria (log de cambios importantes)
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(50) NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_auditoria_tabla (tabla),
    INDEX idx_auditoria_fecha (created_at),
    INDEX idx_auditoria_usuario (usuario_id)
) ENGINE=InnoDB;

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista: productos con stock bajo
CREATE OR REPLACE VIEW v_productos_stock_bajo AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.stock_actual,
    p.stock_minimo,
    c.nombre AS categoria,
    (p.stock_minimo - p.stock_actual) AS faltante
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE p.activo = 1 AND p.stock_actual <= p.stock_minimo
ORDER BY (p.stock_minimo - p.stock_actual) DESC;

-- Vista: resumen de ventas del día
CREATE OR REPLACE VIEW v_resumen_ventas_diario AS
SELECT 
    DATE(v.created_at) AS fecha,
    COUNT(v.id) AS total_ventas,
    SUM(v.total) AS monto_total,
    SUM(v.iva) AS total_iva,
    AVG(v.total) AS ticket_promedio,
    COUNT(DISTINCT v.cliente_id) AS clientes_atendidos
FROM ventas v
WHERE v.estado = 'completada'
GROUP BY DATE(v.created_at)
ORDER BY fecha DESC;

-- Vista: top productos más vendidos
CREATE OR REPLACE VIEW v_top_productos AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    c.nombre AS categoria,
    SUM(vd.cantidad) AS total_vendido,
    SUM(vd.subtotal) AS total_ingresos,
    COUNT(DISTINCT vd.venta_id) AS veces_vendido
FROM ventas_detalle vd
JOIN productos p ON vd.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
JOIN ventas v ON vd.venta_id = v.id
WHERE v.estado = 'completada'
GROUP BY p.id, p.codigo, p.nombre, c.nombre
ORDER BY total_vendido DESC;

-- Vista: dashboard resumen ejecutivo
CREATE OR REPLACE VIEW v_dashboard_resumen AS
SELECT
    (SELECT COUNT(*) FROM productos WHERE activo = 1) AS total_productos,
    (SELECT COUNT(*) FROM clientes WHERE activo = 1) AS total_clientes,
    (SELECT COUNT(*) FROM proveedores WHERE activo = 1) AS total_proveedores,
    (SELECT COUNT(*) FROM ventas WHERE estado = 'completada' AND DATE(created_at) = CURDATE()) AS ventas_hoy,
    (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE estado = 'completada' AND DATE(created_at) = CURDATE()) AS ingresos_hoy,
    (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE estado = 'completada' AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())) AS ingresos_mes,
    (SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock_actual <= stock_minimo) AS productos_stock_bajo;

-- ============================================
-- FUNCIONES Y PROCEDIMIENTOS
-- ============================================

-- Función: generar número de factura automático
DELIMITER //
CREATE FUNCTION generar_numero_factura() 
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE ultimo_numero INT;
    DECLARE nuevo_numero VARCHAR(20);
    SET ultimo_numero = (SELECT COALESCE(MAX(CAST(SUBSTRING(numero_factura, 4) AS UNSIGNED)), 0) FROM ventas);
    SET nuevo_numero = CONCAT('FAC', LPAD(ultimo_numero + 1, 8, '0'));
    RETURN nuevo_numero;
END//
DELIMITER ;

-- Procedimiento: registrar venta completa (transaccional)
DELIMITER //
CREATE PROCEDURE sp_registrar_venta(
    IN p_cliente_id INT,
    IN p_usuario_id INT,
    IN p_forma_pago_id INT,
    IN p_productos_json JSON,
    OUT p_venta_id INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_subtotal DECIMAL(12,2) DEFAULT 0;
    DECLARE v_iva DECIMAL(12,2) DEFAULT 0;
    DECLARE v_total DECIMAL(12,2) DEFAULT 0;
    DECLARE v_numero_factura VARCHAR(20);
    DECLARE v_idx INT DEFAULT 0;
    DECLARE v_productos_count INT;
    DECLARE v_producto_id INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio DECIMAL(12,2);
    DECLARE v_stock_actual INT;
    DECLARE v_error INT DEFAULT 0;
    DECLARE v_impuesto DECIMAL(5,2) DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_mensaje = 'Error al procesar la venta. Transacción revertida.';
        SET p_venta_id = 0;
    END;
    
    START TRANSACTION;
    
    -- Generar número de factura
    SET v_numero_factura = generar_numero_factura();
    
    -- Obtener cantidad de productos en el JSON
    SET v_productos_count = JSON_LENGTH(p_productos_json);
    
    -- Calcular subtotal e IVA
    SET v_idx = 0;
    WHILE v_idx < v_productos_count DO
        SET v_producto_id = JSON_UNQUOTE(JSON_EXTRACT(p_productos_json, CONCAT('$[', v_idx, '].producto_id')));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_productos_json, CONCAT('$[', v_idx, '].cantidad')));
        SET v_precio = JSON_UNQUOTE(JSON_EXTRACT(p_productos_json, CONCAT('$[', v_idx, '].precio_unitario')));
        
        -- Verificar stock
        SELECT stock_actual INTO v_stock_actual FROM productos WHERE id = v_producto_id FOR UPDATE;
        IF v_stock_actual < v_cantidad THEN
            SET v_error = 1;
            SET p_mensaje = CONCAT('Stock insuficiente para producto ID: ', v_producto_id);
            LEAVE;
        END IF;
        
        -- Obtener impuesto del producto
        SELECT COALESCE(i.porcentaje, 0) INTO v_impuesto
        FROM productos p
        LEFT JOIN impuestos i ON p.impuesto_id = i.id
        WHERE p.id = v_producto_id;
        
        SET v_subtotal = v_subtotal + (v_precio * v_cantidad);
        SET v_iva = v_iva + ((v_precio * v_cantidad) * (v_impuesto / 100));
        
        SET v_idx = v_idx + 1;
    END WHILE;
    
    IF v_error = 0 THEN
        SET v_total = v_subtotal + v_iva;
        
        -- Insertar cabecera de venta
        INSERT INTO ventas (numero_factura, cliente_id, usuario_id, subtotal, iva, total, forma_pago_id)
        VALUES (v_numero_factura, p_cliente_id, p_usuario_id, v_subtotal, v_iva, v_total, p_forma_pago_id);
        
        SET p_venta_id = LAST_INSERT_ID();
        
        -- Insertar detalle y actualizar stock
        SET v_idx = 0;
        WHILE v_idx < v_productos_count DO
            SET v_producto_id = JSON_UNQUOTE(JSON_EXTRACT(p_productos_json, CONCAT('$[', v_idx, '].producto_id')));
            SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_productos_json, CONCAT('$[', v_idx, '].cantidad')));
            SET v_precio = JSON_UNQUOTE(JSON_EXTRACT(p_productos_json, CONCAT('$[', v_idx, '].precio_unitario')));
            
            INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal)
            VALUES (p_venta_id, v_producto_id, v_cantidad, v_precio, (v_precio * v_cantidad));
            
            -- Actualizar stock
            UPDATE productos SET stock_actual = stock_actual - v_cantidad WHERE id = v_producto_id;
            
            -- Registrar movimiento de inventario
            INSERT INTO inventario_movimientos (producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo, referencia_id)
            VALUES (v_producto_id, 'salida', v_cantidad, v_stock_actual, v_stock_actual - v_cantidad, 'Venta', 'venta', p_venta_id);
            
            SET v_idx = v_idx + 1;
        END WHILE;
        
        -- Registrar pago
        INSERT INTO ventas_pagos (venta_id, forma_pago_id, monto)
        VALUES (p_venta_id, p_forma_pago_id, v_total);
        
        SET p_mensaje = CONCAT('Venta registrada exitosamente. Factura: ', v_numero_factura);
    END IF;
    
    IF v_error = 1 THEN
        ROLLBACK;
        SET p_venta_id = 0;
    ELSE
        COMMIT;
    END IF;
END//
DELIMITER ;

-- ============================================
-- DATOS DE PRUEBA (SEEDERS)
-- ============================================

-- Roles
INSERT INTO roles (nombre, descripcion) VALUES
('admin', 'Acceso total al sistema'),
('vendedor', 'Puede realizar ventas y consultar inventario'),
('inventario', 'Puede gestionar productos y proveedores'),
('supervisor', 'Puede ver reportes y anular ventas');

-- Tipos de documento
INSERT INTO tipos_documento (codigo, nombre) VALUES
('CC', 'Cédula de Ciudadanía'),
('NIT', 'Número de Identificación Tributaria'),
('CE', 'Cédula de Extranjería'),
('PP', 'Pasaporte');

-- Formas de pago
INSERT INTO formas_pago (codigo, nombre) VALUES
('EFECTIVO', 'Efectivo'),
('TARJETA_DEBITO', 'Tarjeta Débito'),
('TARJETA_CREDITO', 'Tarjeta Crédito'),
('TRANSFERENCIA', 'Transferencia Bancaria'),
('CREDITO', 'Crédito');

-- Unidades de medida
INSERT INTO unidades_medida (codigo, nombre) VALUES
('UND', 'Unidad'),
('KG', 'Kilogramo'),
('GR', 'Gramo'),
('LT', 'Litro'),
('ML', 'Mililitro'),
('M', 'Metro'),
('CM', 'Centímetro'),
('CAJA', 'Caja'),
('PAQ', 'Paquete');

-- Impuestos
INSERT INTO impuestos (nombre, porcentaje) VALUES
('Exento', 0),
('IVA 5%', 5),
('IVA 19%', 19);

-- Categorías
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónicos', 'Productos electrónicos y tecnología'),
('Ropa y Accesorios', 'Prendas de vestir y accesorios'),
('Alimentos y Bebidas', 'Productos alimenticios y bebidas'),
('Hogar', 'Artículos para el hogar'),
('Oficina', 'Útiles y muebles de oficina'),
('Salud y Belleza', 'Productos de cuidado personal'),
('Deportes', 'Artículos deportivos'),
('Juguetería', 'Juguetes y entretenimiento');

-- Usuario administrador por defecto
INSERT INTO usuarios (nombre, usuario, email, password, rol_id) VALUES
('Administrador', 'admin', 'admin@erppos.com', MD5('admin123'), 1),
('Vendedor Demo', 'vendedor', 'vendedor@erppos.com', MD5('vendedor123'), 2);

-- Clientes
INSERT INTO clientes (tipo_documento_id, documento, nombre, telefono, email, direccion, ciudad) VALUES
(1, '00000000', 'Cliente General', '0000000', 'cliente@email.com', 'General', 'Bucaramanga'),
(1, '12345678', 'Juan Pérez', '3001234567', 'juan@email.com', 'Calle 123 #45-67', 'Bucaramanga'),
(1, '87654321', 'María García', '3109876543', 'maria@email.com', 'Carrera 89 #12-34', 'Floridablanca'),
(2, '900123456', 'Comercial XYZ SAS', '6071234567', 'comercial@email.com', 'Av. Principal #100-200', 'Bucaramanga');

-- Proveedores
INSERT INTO proveedores (tipo_documento_id, documento, nombre, contacto, telefono, email, ciudad) VALUES
(2, '800123456', 'Distribuidora Tecnológica SAS', 'Carlos López', '3001112233', 'carlos@distecno.com', 'Bogotá'),
(2, '800654321', 'Alimentos del Campo SAS', 'Ana Martínez', '3104445566', 'ana@alimentos.com', 'Bucaramanga'),
(1, '11122334', 'Moda Express', 'Pedro Ramírez', '3157778899', 'pedro@moda.com', 'Medellín');

-- Productos
INSERT INTO productos (codigo, codigo_barras, nombre, descripcion, categoria_id, unidad_medida_id, impuesto_id, precio_compra, precio_venta, stock_actual, stock_minimo, stock_maximo) VALUES
('PROD001', '7701001001001', 'Laptop HP Pavilion', 'Laptop HP Pavilion 15.6" 8GB RAM 256GB SSD', 1, 1, 3, 1500000, 2500000, 10, 3, 50),
('PROD002', '7701001001002', 'Mouse Inalámbrico', 'Mouse USB inalámbrico óptico', 1, 1, 3, 15000, 35000, 50, 10, 200),
('PROD003', '7701001001003', 'Teclado Mecánico', 'Teclado mecánico RGB retroiluminado', 1, 1, 3, 45000, 85000, 25, 5, 100),
('PROD004', '7701001001004', 'Monitor 24"', 'Monitor LED 24 pulgadas Full HD', 1, 1, 3, 350000, 580000, 8, 3, 30),
('PROD005', '7701001001005', 'Camisa Algodón', 'Camisa manga larga algodón premium', 2, 1, 3, 20000, 45000, 30, 5, 100),
('PROD006', '7701001001006', 'Pantalón Jean', 'Pantalón jean clásico tela stretch', 2, 1, 3, 35000, 75000, 20, 5, 80),
('PROD007', '7701001001007', 'Zapatos Deportivos', 'Zapatos deportivos running talla 40-44', 2, 1, 3, 55000, 120000, 15, 5, 60),
('PROD008', '7701001001008', 'Arroz 1kg', 'Arroz blanco premium 1 kilogramo', 3, 2, 2, 2000, 3500, 200, 50, 500),
('PROD009', '7701001001009', 'Aceite Vegetal 1L', 'Aceite vegetal 1 litro', 3, 4, 2, 4000, 7500, 100, 20, 300),
('PROD010', '7701001001010', 'Gaseosa 350ml', 'Gaseosa sabor cola 350ml', 3, 5, 2, 1500, 3000, 300, 50, 600),
('PROD011', '7701001001011', 'Jabón Líquido', 'Jabón líquido antibacterial 250ml', 6, 5, 3, 3000, 6500, 40, 10, 150),
('PROD012', '7701001001012', 'Shampoo 400ml', 'Shampoo para todo tipo de cabello', 6, 5, 3, 8000, 16500, 25, 5, 100),
('PROD013', '7701001001013', 'Cuaderno 100 hojas', 'Cuaderno universitario 100 hojas cuadriculado', 5, 1, 1, 2000, 4500, 80, 20, 200),
('PROD014', '7701001001014', 'Bolígrafo Caja x12', 'Caja de bolígrafos tinta negra x12 unidades', 5, 9, 1, 5000, 12000, 30, 10, 100),
('PROD015', '7701001001015', 'Pelota Fútbol', 'Pelota de fútbol profesional talla 5', 7, 1, 3, 25000, 55000, 12, 5, 50);

-- Ventas de ejemplo
INSERT INTO ventas (numero_factura, cliente_id, usuario_id, subtotal, iva, total, forma_pago_id, estado) VALUES
('FAC00000001', 1, 1, 35000, 6650, 41650, 1, 'completada'),
('FAC00000002', 2, 1, 120000, 22800, 142800, 2, 'completada'),
('FAC00000003', 3, 2, 45000, 8550, 53550, 1, 'completada');

INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
(1, 2, 1, 35000, 35000),
(2, 4, 1, 580000, 580000),
(2, 2, 2, 35000, 70000),
(3, 5, 1, 45000, 45000);

INSERT INTO ventas_pagos (venta_id, forma_pago_id, monto) VALUES
(1, 1, 41650),
(2, 2, 142800),
(3, 1, 53550);

-- Movimientos de inventario iniciales
INSERT INTO inventario_movimientos (producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo, referencia_id) VALUES
(2, 'salida', 1, 50, 49, 'Venta FAC00000001', 'venta', 1),
(4, 'salida', 1, 8, 7, 'Venta FAC00000002', 'venta', 2),
(2, 'salida', 2, 49, 47, 'Venta FAC00000002', 'venta', 2),
(5, 'salida', 1, 30,