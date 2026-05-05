<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP-POS Mini Proyecto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div id="app">
        <!-- Login -->
        <div id="login-view" class="view active">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <div class="logo">📊</div>
                        <h1>ERP-POS</h1>
                        <p>Sistema de Gestión Empresarial</p>
                    </div>
                    <form id="login-form" onsubmit="return handleLogin(event)">
                        <div class="form-group">
                            <label for="usuario">Usuario</label>
                            <input type="text" id="usuario" name="usuario" required placeholder="admin" autocomplete="username">
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" required placeholder="admin123" autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                        <p class="login-hint">Demo: admin / admin123</p>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dashboard -->
        <div id="dashboard-view" class="view">
            <nav class="navbar">
                <div class="nav-brand">📊 ERP-POS</div>
                <div class="nav-menu">
                    <button class="nav-btn active" onclick="showView('dashboard-content')" title="Dashboard">📊</button>
                    <button class="nav-btn" onclick="showView('pos-view')" title="Punto de Venta">🛒</button>
                    <button class="nav-btn" onclick="showView('productos-view')" title="Productos">📦</button>
                    <button class="nav-btn" onclick="showView('clientes-view')" title="Clientes">👥</button>
                    <button class="nav-btn" onclick="showView('proveedores-view')" title="Proveedores">🏢</button>
                    <button class="nav-btn" onclick="showView('ventas-view')" title="Ventas">💰</button>
                    <button class="nav-btn" onclick="showView('compras-view')" title="Compras">📥</button>
                    <button class="nav-btn" onclick="showView('reportes-view')" title="Reportes">📈</button>
                </div>
                <div class="nav-user">
                    <span id="user-name">Usuario</span>
                    <button class="btn btn-sm btn-danger" onclick="handleLogout()">Salir</button>
                </div>
            </nav>

            <div class="main-content">
                <!-- Dashboard Content -->
                <div id="dashboard-content" class="view-content active">
                    <h2>Dashboard</h2>
                    <div class="cards-grid" id="dashboard-cards">
                        <div class="card"><div class="card-icon">📦</div><div class="card-info"><h3>Productos</h3><p id="total-productos">0</p></div></div>
                        <div class="card"><div class="card-icon">👥</div><div class="card-info"><h3>Clientes</h3><p id="total-clientes">0</p></div></div>
                        <div class="card"><div class="card-icon">🏢</div><div class="card-info"><h3>Proveedores</h3><p id="total-proveedores">0</p></div></div>
                        <div class="card"><div class="card-icon">💰</div><div class="card-info"><h3>Ventas Hoy</h3><p id="ventas-hoy">0</p></div></div>
                        <div class="card highlight"><div class="card-icon">💵</div><div class="card-info"><h3>Ingresos Hoy</h3><p id="ingresos-hoy">$0</p></div></div>
                        <div class="card highlight"><div class="card-icon">📈</div><div class="card-info"><h3>Ingresos del Mes</h3><p id="ingresos-mes">$0</p></div></div>
                        <div class="card warning"><div class="card-icon">⚠️</div><div class="card-info"><h3>Stock Bajo</h3><p id="stock-bajo">0</p></div></div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="panel">
                                <h3>🛒 Ventas del Día</h3>
                                <div class="table-responsive">
                                    <table id="tabla-ventas-hoy">
                                        <thead><tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Hora</th></tr></thead>
                                        <tbody id="ventas-hoy-body"><tr><td colspan="4">Cargando...</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="panel">
                                <h3>🏆 Top Productos</h3>
                                <div class="table-responsive">
                                    <table id="tabla-top-productos">
                                        <thead><tr><th>Producto</th><th>Vendido</th><th>Ingresos</th></tr></thead>
                                        <tbody id="top-productos-body"><tr><td colspan="3">Cargando...</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <h3>⚠️ Productos con Stock Bajo</h3>
                        <div class="table-responsive">
                            <table id="tabla-stock-bajo">
                                <thead><tr><th>Código</th><th>Producto</th><th>Stock</th><th>Mínimo</th><th>Faltante</th></tr></thead>
                                <tbody id="stock-bajo-body"><tr><td colspan="5">Cargando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- POS View -->
                <div id="pos-view" class="view-content">
                    <h2>🛒 Punto de Venta</h2>
                    <div class="pos-container">
                        <div class="pos-left">
                            <div class="pos-search">
                                <input type="text" id="pos-search" placeholder="Buscar producto por nombre o código..." onkeyup="buscarProductosPOS(event)">
                            </div>
                            <div class="pos-productos" id="pos-productos-list">
                                <p>Cargando productos...</p>
                            </div>
                        </div>
                        <div class="pos-right">
                            <div class="pos-cliente">
                                <select id="pos-cliente" class="form-control"></select>
                            </div>
                            <div class="pos-pago">
                                <select id="pos-forma-pago" class="form-control">
                                    <option value="1">Efectivo</option>
                                    <option value="2">Tarjeta Débito</option>
                                    <option value="3">Tarjeta Crédito</option>
                                    <option value="4">Transferencia</option>
                                </select>
                            </div>
                            <div class="table-responsive">
                                <table id="pos-carrito">
                                    <thead><tr><th>Producto</th><th>Precio</th><th>Cant</th><th>Subtotal</th><th></th></tr></thead>
                                    <tbody id="pos-carrito-body"></tbody>
                                </table>
                            </div>
                            <div class="pos-totales">
                                <div class="total-line"><span>Subtotal:</span><span id="pos-subtotal">$0</span></div>
                                <div class="total-line"><span>IVA (19%):</span><span id="pos-iva">$0</span></div>
                                <div class="total-line total-final"><span>TOTAL:</span><span id="pos-total">$0</span></div>
                            </div>
                            <button class="btn btn-success btn-block btn-lg" onclick="finalizarVenta()">💵 Cobrar Venta</button>
                        </div>
                    </div>
                </div>

                <!-- Productos View -->
                <div id="productos-view" class="view-content">
                    <div class="view-header">
                        <h2>📦 Productos</h2>
                        <button class="btn btn-primary" onclick="mostrarFormProducto()">+ Nuevo Producto</button>
                    </div>
                    <div class="search-bar">
                        <input type="text" id="search-productos" placeholder="Buscar productos..." onkeyup="cargarProductos()">
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-productos">
                            <thead>
                                <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>P. Venta</th><th>Stock</th><th>Mín</th><th>Acciones</th></tr>
                            </thead>
                            <tbody id="productos-body"><tr><td colspan="7">Cargando...</td></tr></tbody>
                        </table>
                    </div>
                </div>

                <!-- Clientes View -->
                <div id="clientes-view" class="view-content">
                    <div class="view-header">
                        <h2>👥 Clientes</h2>
                        <button class="btn btn-primary" onclick="mostrarFormCliente()">+ Nuevo Cliente</button>
                    </div>
                    <div class="search-bar">
                        <input type="text" id="search-clientes" placeholder="Buscar clientes..." onkeyup="cargarClientes()">
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-clientes">
                            <thead><tr><th>Documento</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Ciudad</th><th>Acciones</th></tr></thead>
                            <tbody id="clientes-body"><tr><td colspan="6">Cargando...</td></tr></tbody>
                        </table>
                    </div>
                </div>

                <!-- Proveedores View -->
                <div id="proveedores-view" class="view-content">
                    <div class="view-header">
                        <h2>🏢 Proveedores</h2>
                        <button class="btn btn-primary" onclick="mostrarFormProveedor()">+ Nuevo Proveedor</button>
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-proveedores">
                            <thead><tr><th>Documento</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th>Ciudad</th><th>Acciones</th></tr></thead>
                            <tbody id="proveedores-body"><tr><td colspan="7">Cargando...</td></tr></tbody>
                        </table>
                    </div>
                </div>

                <!-- Ventas View -->
                <div id="ventas-view" class="view-content">
                    <div class="view-header">
                        <h2>💰 Historial de Ventas</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-ventas">
                            <thead><tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
                            <tbody id="ventas-body"><tr><td colspan="6">Cargando...</td></tr></tbody>
                        </table>
                    </div>
                </div>

                <!-- Compras View -->
                <div id="compras-view" class="view-content">
                    <div class="view-header">
                        <h2>📥 Compras</h2>
                        <button class="btn btn-primary" onclick="mostrarFormCompra()">+ Nueva Compra</button>
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-compras">
                            <thead><tr><th>Orden</th><th>Proveedor</th><th>Total</th><th>Estado</th><th>Fecha</th></tr></thead>
                            <tbody id="compras-body"><tr><td colspan="5">Cargando...</td></tr></tbody>
                        </table>
                    </div>
                </div>

                <!-- Reportes View -->
                <div id="reportes-view" class="view-content">
                    <h2>📈 Reportes</h2>
                    <div class="row">
                        <div class="col">
                            <div class="panel">
                                <h3>Exportar Datos</h3>
                                <button class="btn btn-info" onclick="exportarXML()">📄 Exportar Productos a XML</button>
                                <button class="btn btn-info" onclick="exportarJSON()">📋 Exportar Productos a JSON</button>
                            </div>
                        </div>
                        <div class="col">
                            <div class="panel">
                                <h3>API REST</h3>
                                <p>Usa <code>?format=xml</code> o <code>?format=json</code> en los endpoints</p>
                                <p>Ej: <code>api_productos.php?format=xml</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="cerrarModal()">&times;</span>
            <div id="modal-body"></div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
