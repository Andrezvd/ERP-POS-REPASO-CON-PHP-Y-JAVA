# 🎯 PLAN DE PREPARACIÓN - PRUEBA TÉCNICA TIRESIA SAS

**Empresa:** TIRESIA SAS  
**Cargo:** Desarrollador Junior  
**Ubicación:** Floridablanca, Santander  
**Salario:** $2.359.000/mes  
**Duración del plan:** 6 Días (6-8 horas/día)  
**Inicio:** Hoy  
**Entrevista:** Próximo martes  

---

## 📋 TECNOLOGÍAS A CUBRIR

| Tecnología | Nivel Requerido | Tu nivel actual |
|------------|-----------------|-----------------|
| PHP | Sólido | 🔴 Cero |
| HTML5 | Sólido | 🟢 Ya sabes |
| XML | Intermedio | 🟡 Básico |
| JavaScript | Sólido | 🟢 Ya sabes |
| JSON | Intermedio | 🟢 Ya sabes |
| API REST | Intermedio | 🟡 Básico (desde PHP) |
| Web Services (SOAP) | Intermedio | 🔴 Cero |
| SQL / MySQL | Excelente | 🟢 Ya sabes |
| ERP | Intermedio | 🟡 Básico (conceptos) |
| POS | Intermedio | 🟡 Básico (conceptos) |
| PERL | Deseable | 🔴 Cero |
| FLEX | Deseable | 🔴 Cero |

---

## 🏗️ ARQUITECTURA DEL PROYECTO ERP-POS

```
erp-pos/
│
├── index.php                    # Dashboard principal del ERP
├── config/
│   └── database.php             # Conexión MySQL con PDO
│
├── api/                         # API RESTful
│   ├── productos.php            # CRUD Productos (JSON + XML)
│   ├── clientes.php             # CRUD Clientes
│   ├── ventas.php               # Registro de ventas
│   └── reportes.php             # Reportes y estadísticas
│
├── pos/                         # Módulo Punto de Venta
│   ├── index.php                # Interfaz del POS
│   ├── carrito.php              # Lógica del carrito de compras
│   └── ticket.php               # Generación de ticket/factura
│
├── ws/                          # Web Services (SOAP)
│   ├── server.php               # Servidor SOAP
│   └── client.php               # Cliente de prueba SOAP
│
├── perl/                        # Scripts PERL
│   ├── ejemplo.pl               # Script PERL básico
│   └── crud.pl                  # CRUD en PERL + MySQL
│
├── flex/                        # Notas y ejemplos FLEX
│   └── notas_flex.md            # Conceptos clave de FLEX
│
├── js/
│   └── app.js                   # JavaScript frontend (Fetch API)
│
├── xml/
│   └── productos.xml            # Exportación de datos a XML
│
├── sql/
│   └── schema.sql               # Esquema completo de BD
│
└── CRONOGRAMA.md                # Este archivo
```

---

## 🗄️ ESQUEMA DE BASE DE DATOS (MySQL)

```sql
-- ============================================
-- BASE DE DATOS: erp_pos
-- ============================================

CREATE DATABASE IF NOT EXISTS erp_pos;
USE erp_pos;

-- TABLA: categorias
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    categoria_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- TABLA: clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    documento VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: ventas (cabecera)
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    total DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'credito') DEFAULT 'efectivo',
    estado ENUM('completada', 'anulada') DEFAULT 'completada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);

-- TABLA: ventas_detalle (detalle de venta)
CREATE TABLE ventas_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- TABLA: compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
);

-- TABLA: compras_detalle
CREATE TABLE compras_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- TABLA: usuarios (para login)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor', 'inventario') DEFAULT 'vendedor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- DATOS DE PRUEBA
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónicos', 'Productos electrónicos y tecnología'),
('Ropa', 'Prendas de vestir'),
('Alimentos', 'Productos alimenticios'),
('Hogar', 'Artículos para el hogar');

INSERT INTO productos (codigo, nombre, descripcion, precio_compra, precio_venta, stock, stock_minimo, categoria_id) VALUES
('PROD001', 'Laptop HP', 'Laptop HP 8GB RAM', 1500000, 2500000, 10, 3, 1),
('PROD002', 'Mouse Inalámbrico', 'Mouse USB inalámbrico', 15000, 35000, 50, 10, 1),
('PROD003', 'Camisa Algodón', 'Camisa manga larga', 20000, 45000, 30, 5, 2),
('PROD004', 'Arroz 1kg', 'Arroz blanco premium', 2000, 3500, 100, 20, 3);

INSERT INTO clientes (nombre, documento, telefono, email, direccion) VALUES
('Cliente General', '00000000', '0000000', 'cliente@email.com', 'General'),
('Juan Pérez', '12345678', '3001234567', 'juan@email.com', 'Calle 123 #45-67');

INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
('Admin', 'admin', MD5('admin123'), 'admin');
```

---

## 📅 DÍA 1 — FUNDACIONES: PHP + HTML5 + MySQL

**Duración:** 8 horas  
**Objetivo:** Aprender PHP desde cero y conectarlo con MySQL.

### 🕐 Hora 1-2: Instalación y configuración
- [ ] Descargar e instalar PHP 8.x desde [windows.php.net](https://windows.php.net/download)
- [ ] Agregar PHP al PATH del sistema
- [ ] Verificar instalación: `php -v`
- [ ] Usar servidor integrado: `php -S localhost:8000`
- [ ] Crear carpeta del proyecto `erp-pos/`

### 🕐 Hora 2-4: PHP básico
- [ ] Sintaxis básica: `<?php ?>`, echo, variables, tipos de datos
- [ ] Arrays indexados y asociativos
- [ ] Estructuras de control: if/else, foreach, while
- [ ] Funciones: definición, parámetros, return
- [ ] Superglobales: `$_GET`, `$_POST`, `$_SESSION`
- [ ] Manejo de formularios HTML + PHP
- [ ] Sesiones: `session_start()`, `$_SESSION`

### 🕐 Hora 4-6: PHP + MySQL (PDO)
- [ ] Conexión a MySQL con PDO
- [ ] Consultas preparadas (prepared statements) - SEGURIDAD
- [ ] CRUD: INSERT, SELECT, UPDATE, DELETE
- [ ] Manejo de errores con try/catch
- [ ] Transacciones

### 🕐 Hora 6-8: Construir módulo PRODUCTOS
- [ ] Crear `config/database.php` (conexión PDO)
- [ ] Crear `productos.php` (listar productos en tabla HTML5)
- [ ] Crear `productos_crear.php` (formulario + INSERT)
- [ ] Crear `productos_editar.php` (formulario + UPDATE)
- [ ] Crear `productos_eliminar.php` (DELETE con confirmación)

**📦 Entregable del Día 1:** Módulo CRUD de Productos funcional.

---

## 📅 DÍA 2 — API REST + JSON + XML

**Duración:** 8 horas  
**Objetivo:** Crear y consumir APIs REST con PHP.

### 🕐 Hora 1-3: API REST en PHP
- [ ] Conceptos: REST, endpoints, métodos HTTP (GET, POST, PUT, DELETE)
- [ ] Headers HTTP: Content-Type, Authorization, CORS
- [ ] Router simple en PHP: `$_SERVER['REQUEST_METHOD']` y `$_GET['action']`
- [ ] Respuestas HTTP: `http_response_code()`
- [ ] Estructura de una API RESTful

### 🕐 Hora 3-5: JSON
- [ ] `json_encode()` - convertir array/objeto a JSON
- [ ] `json_decode()` - convertir JSON a array/objeto PHP
- [ ] API que devuelve JSON: header('Content-Type: application/json')
- [ ] Recibir JSON en POST: `file_get_contents('php://input')`
- [ ] Construir API REST para Productos que devuelve JSON

### 🕐 Hora 5-6: XML
- [ ] `SimpleXMLElement` - crear XML desde PHP
- [ ] `simplexml_load_string()` - parsear XML
- [ ] API que devuelve XML: header('Content-Type: application/xml')
- [ ] Parámetro `?format=xml` o `?format=json` en la API

### 🕐 Hora 6-8: Construir API completa
- [ ] Crear `api/productos.php` con:
  - `GET /api/productos.php` → listar todos (JSON o XML)
  - `GET /api/productos.php?id=1` → un producto
  - `POST /api/productos.php` → crear producto (body JSON)
  - `PUT /api/productos.php?id=1` → actualizar
  - `DELETE /api/productos.php?id=1` → eliminar
- [ ] Probar con Postman/curl o desde el navegador
- [ ] Crear `api/clientes.php` (misma estructura)

**📦 Entregable del Día 2:** API REST completa con soporte JSON y XML.

---

## 📅 DÍA 3 — ERP + POS + WEB SERVICES (SOAP)

**Duración:** 8 horas  
**Objetivo:** Construir la lógica de negocio de un ERP-POS.

### 🕐 Hora 1-2: Lógica de ERP
- [ ] Conceptos ERP: módulos, integración, inventario, ventas, compras
- [ ] Dashboard: tarjetas con totales (productos, clientes, ventas del día)
- [ ] Reportes básicos: productos con stock bajo, ventas por período
- [ ] Implementar dashboard en `index.php`

### 🕐 Hora 2-4: Lógica de POS (Punto de Venta)
- [ ] Interfaz del POS: buscador de productos, tabla de carrito
- [ ] Agregar productos al carrito (sesión)
- [ ] Calcular subtotal, IVA (19%), total
- [ ] Seleccionar cliente, forma de pago
- [ ] Finalizar venta: INSERT en ventas + ventas_detalle
- [ ] Actualizar stock de productos

### 🕐 Hora 4-6: Web Services (SOAP)
- [ ] ¿Qué es SOAP? XML-based protocol, WSDL
- [ ] Habilitar extensión SOAP en PHP (`extension=soap` en php.ini)
- [ ] Crear clase con métodos: `consultarProducto()`, `validarStock()`
- [ ] Crear servidor SOAP: `ws/server.php`
- [ ] Crear cliente SOAP: `ws/client.php`
- [ ] Probar comunicación cliente-servidor

### 🕐 Hora 6-8: Construir POS completo
- [ ] Finalizar `pos/index.php` (interfaz completa)
- [ ] Finalizar `pos/carrito.php` (lógica backend)
- [ ] Finalizar `pos/ticket.php` (generar ticket HTML)
- [ ] Integrar WS de validación de stock en el POS

**📦 Entregable del Día 3:** POS funcional + Web Service SOAP.

---

## 📅 DÍA 4 — JAVASCRIPT + FRONTEND INTEGRADO

**Duración:** 8 horas  
**Objetivo:** Frontend dinámico que consuma tu propia API.

### 🕐 Hora 1-3: JavaScript moderno
- [ ] Fetch API: `fetch(url)` para consumir APIs
- [ ] async/await: manejo de promesas
- [ ] Manipulación del DOM: createElement, innerHTML, appendChild
- [ ] Eventos: click, change, submit
- [ ] JSON.parse() / JSON.stringify()

### 🕐 Hora 3-5: Consumir tu propia API
- [ ] JS → fetch → `api/productos.php` → JSON → renderizar tabla
- [ ] Formulario JS que envía POST a la API
- [ ] Eliminar producto con confirmación y fetch DELETE
- [ ] Actualizar producto con fetch PUT

### 🕐 Hora 5-6: HTML5 avanzado
- [ ] Formularios HTML5: type="number", type="email", required, pattern
- [ ] Data attributes: `data-id`, `data-precio`
- [ ] Templates HTML: `<template>` tag
- [ ] Validación del lado del cliente

### 🕐 Hora 6-8: Construir Dashboard interactivo
- [ ] Crear `js/app.js` con funciones:
  - `cargarProductos()` → fetch GET → renderizar
  - `crearProducto(data)` → fetch POST
  - `actualizarProducto(id, data)` → fetch PUT
  - `eliminarProducto(id)` → fetch DELETE
- [ ] Dashboard con cards actualizables en tiempo real
- [ ] POS con búsqueda en vivo de productos (JS + API)

**📦 Entregable del Día 4:** Frontend SPA (Single Page Application) conectado a tu API.

---

## 📅 DÍA 5 — PERL + FLEX (INTRODUCCIÓN)

**Duración:** 8 horas  
**Objetivo:** Tener conceptos claros para la evaluación técnica.

### 🕐 Hora 1-3: PERL básico
- [ ] Instalar Perl (Strawberry Perl para Windows: [strawberryperl.com](https://strawberryperl.com/))
- [ ] Sintaxis básica: `print`, variables (`$scalar`, `@array`, `%hash`)
- [ ] Estructuras de control: if/else, for, foreach, while
- [ ] Operaciones con strings y arrays
- [ ] Funciones en Perl: `sub nombre { }`
- [ ] Expresiones regulares en Perl: `=~ m/patron/`, `s/patron/reemplazo/`

### 🕐 Hora 3-5: PERL + MySQL
- [ ] Instalar módulo DBI: `cpan install DBI DBD::mysql`
- [ ] Conectar a MySQL desde Perl
- [ ] Consultas: SELECT, INSERT, UPDATE, DELETE
- [ ] Manejo de resultados: fetchrow_array, fetchrow_hashref
- [ ] Crear `perl/crud.pl` (CRUD completo en Perl)

### 🕐 Hora 5-6: PERL como CGI
- [ ] ¿Qué es CGI? Common Gateway Interface
- [ ] Script Perl CGI que genera HTML
- [ ] Recibir parámetros GET/POST en Perl
- [ ] Diferencias entre Perl CGI vs PHP moderno

### 🕐 Hora 6-8: FLEX / Adobe Flex
- [ ] ¿Qué es Adobe Flex? Framework para RIA (Rich Internet Applications)
- [ ] Arquitectura: MXML (markup) + ActionScript (lógica)
- [ ] Componentes básicos: Button, TextInput, DataGrid, Form
- [ ] Data binding: conectar UI con datos
- [ ] HTTPService: consumir APIs REST desde Flex
- [ ] ¿Por qué es relevante? Empresas legacy aún lo usan
- [ ] Crear `flex/notas_flex.md` con resumen de conceptos

**📦 Entregable del Día 5:** Scripts PERL funcionales + Notas de FLEX.

---

## 📅 DÍA 6 — REPASO + SIMULACRO DE PRUEBA TÉCNICA

**Duración:** 6-8 horas  
**Objetivo:** Integrar todo y practicar bajo presión.

### 🕐 Hora 1-2: Integración final
- [ ] Verificar que todo el proyecto funcione correctamente
- [ ] Probar todos los endpoints de la API
- [ ] Probar el POS completo (agregar productos, vender, generar ticket)
- [ ] Probar el Web Service SOAP
- [ ] Probar scripts PERL
- [ ] Corregir errores

### 🕐 Hora 2-4: Simulacro 1 - Construir módulo desde cero
- [ ] **Ejercicio:** Construir módulo "Proveedores" completo en 1 hora:
  - CRUD en PHP + MySQL
  - API REST (JSON)
  - Frontend con JS
- [ ] **Ejercicio 2:** Explicar en voz alta cómo funciona:
  - La conexión PDO
  - Una consulta preparada
  - El flujo de una venta en el POS
  - Cómo se consume la API desde JS

### 🕐 Hora 4-5: Simulacro 2 - Preguntas técnicas
- [ ] ¿Cómo evitas SQL Injection? → Prepared Statements
- [ ] ¿Diferencia entre GET y POST? → Idempotencia, seguridad
- [ ] ¿Qué es REST? → Stateless, recursos, métodos HTTP
- [ ] ¿Qué es SOAP? → Protocolo XML, WSDL, más estricto que REST
- [ ] ¿Cómo funciona una sesión en PHP? → session_start(), cookies
- [ ] ¿Qué es PDO? → Capa de abstracción de BD
- [ ] ¿Diferencia entre JSON y XML? → Sintaxis, peso, legibilidad
- [ ] ¿Qué es un ERP? → Sistema de planificación de recursos
- [ ] ¿Qué es un POS? → Punto de venta, transacciones comerciales

### 🕐 Hora 5-6: Preparación para PERL y FLEX
- [ ] Frase clave para PERL: *"Tengo conocimientos básicos, hice un script CRUD y entiendo la sintaxis. Estoy en proceso de aprendizaje activo."*
- [ ] Frase clave para FLEX: *"Conozco la arquitectura MXML + ActionScript. Entiendo que es para aplicaciones RIA. No tengo experiencia práctica pero comprendo los conceptos fundamentales."*
- [ ] Prepárate para decir: *"Estoy dispuesto a aprender y capacitarme en las tecnologías que la empresa necesite."*

### 🕐 Hora 6-8: Repaso final y mentalidad
- [ ] Repasar puntos débiles identificados durante la semana
- [ ] Verificar que el proyecto esté completo y funcional
- [ ] Preparar el discurso de presentación personal
- [ ] Mentalidad: "Voy a mostrar lo que construí, no solo lo que sé"
- [ ] Descansar bien la noche anterior

---

## 🧠 PREGUNTAS FRECUENTES DE PRUEBA TÉCNICA

### PHP
```php
// ¿Cómo conectas a MySQL con PDO?
$pdo = new PDO("mysql:host=localhost;dbname=erp_pos", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ¿Cómo haces una consulta preparada?
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

// ¿Cómo manejas sesiones?
session_start();
$_SESSION['usuario'] = 'admin';
```

### API REST
```php
// Devolver JSON
header('Content-Type: application/json');
echo json_encode($productos);

// Recibir JSON
$data = json_decode(file_get_contents('php://input'), true);
```

### JavaScript
```javascript
// Consumir API con Fetch
async function cargarProductos() {
    const res = await fetch('api/productos.php');
    const data = await res.json();
    // renderizar...
}

// Enviar datos POST
fetch('api/productos.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(producto)
});
```

### SQL
```sql
-- Productos con stock bajo
SELECT * FROM productos WHERE stock <= stock_minimo;

-- Ventas del día
SELECT SUM(total) FROM ventas WHERE DATE(created_at) = CURDATE();

-- Top 5 productos más vendidos
SELECT p.nombre, SUM(vd.cantidad) as total_vendido
FROM ventas_detalle vd
JOIN productos p ON vd.producto_id = p.id
GROUP BY vd.producto_id
ORDER BY total_vendido DESC
LIMIT 5;
```

---

## 💡 ESTRATEGIA PARA LA ENTREVISTA

### Qué decir cuando no sabes algo:
> *"No tengo experiencia práctica en esa tecnología específica, pero en mi proyecto ERP-POS trabajé con tecnologías similares como [PHP/API REST/JSON]. Entiendo los conceptos fundamentales y estoy seguro de que puedo aprenderlo rápidamente."*

### Cómo mostrar tu proyecto:
1. Ten el proyecto corriendo en `localhost:8000`
2. Muestra el dashboard del ERP
3. Demuestra el POS (agrega un producto, haz una venta)
4. Muestra la API (abre `api/productos.php` en el navegador)
5. Muestra el código fuente organizado

### Lo que más valoran:
- **Lógica de programación** → La tienes de Spring Boot/FastAPI
- **SQL** → Ya lo dominas
- **Actitud de aprendizaje** → Muestra entusiasmo
- **Resolución de problemas** → Piensa en voz alta durante la prueba

---

## ✅ CHECKLIST FINAL

- [ ] PHP instalado y funcionando
- [ ] MySQL corriendo (local o Docker)
- [ ] Base de datos `erp_pos` creada con todas las tablas
- [ ] Módulo CRUD de Productos funcional
- [ ] API REST con JSON y XML
- [ ] POS con carrito de compras
- [ ] Web Service SOAP
- [ ] Frontend JavaScript conectado a la API
- [ ] Scripts PERL funcionales
- [ ] Notas de FLEX preparadas
- [ ] Proyecto completo funcionando en localhost
- [ ] Discurso de presentación listo

---

## 🚀 ¡TÚ PUEDES!

Recuerda: **Ya tienes la base sólida** (Spring Boot, FastAPI, Django, SQL, frontend).  
Solo estás aprendiendo **el mismo concepto con diferente sintaxis**.

**¡A darlo todo! 💪**
