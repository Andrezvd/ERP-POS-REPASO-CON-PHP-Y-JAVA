/**
 * ERP-POS Mini Proyecto - JavaScript Frontend
 * Aplicación SPA con Fetch API
 */

const API_BASE = '../aplicacion/';
let carrito = [];
let usuarioActual = null;

// ============================================
// AUTENTICACIÓN
// ============================================

async function handleLogin(event) {
    event.preventDefault();
    const usuario = document.getElementById('usuario').value;
    const password = document.getElementById('password').value;
    
    try {
        const res = await fetch(`${API_BASE}api_usuarios.php?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario, password })
        });
        const data = await res.json();
        
        if (data.success) {
            usuarioActual = data.user;
            document.getElementById('user-name').textContent = `👤 ${data.user.nombre}`;
            document.getElementById('login-view').classList.remove('active');
            document.getElementById('dashboard-view').classList.add('active');
            cargarDashboard();
            cargarClientesSelect();
        } else {
            alert('Credenciales inválidas');
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
    return false;
}

function handleLogout() {
    usuarioActual = null;
    carrito = [];
    document.getElementById('dashboard-view').classList.remove('active');
    document.getElementById('login-view').classList.add('active');
    document.getElementById('login-form').reset();
}

// ============================================
// NAVEGACIÓN
// ============================================

function showView(viewId) {
    document.querySelectorAll('.view-content').forEach(v => v.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(viewId).classList.add('active');
    event.target.classList.add('active');
    
    // Cargar datos según la vista
    switch(viewId) {
        case 'dashboard-content': cargarDashboard(); break;
        case 'pos-view': cargarPOS(); break;
        case 'productos-view': cargarProductos(); break;
        case 'clientes-view': cargarClientes(); break;
        case 'proveedores-view': cargarProveedores(); break;
        case 'ventas-view': cargarVentas(); break;
        case 'compras-view': cargarCompras(); break;
    }
}

// ============================================
// DASHBOARD
// ============================================

async function cargarDashboard() {
    try {
        const res = await fetch(`${API_BASE}api_ventas.php?dashboard`);
        const data = await res.json();
        
        if (data.resumen) {
            document.getElementById('total-productos').textContent = data.resumen.total_productos || 0;
            document.getElementById('total-clientes').textContent = data.resumen.total_clientes || 0;
            document.getElementById('total-proveedores').textContent = data.resumen.total_proveedores || 0;
            document.getElementById('ventas-hoy').textContent = data.resumen.ventas_hoy || 0;
            document.getElementById('ingresos-hoy').textContent = '$' + formatearMoneda(data.resumen.ingresos_hoy || 0);
            document.getElementById('ingresos-mes').textContent = '$' + formatearMoneda(data.resumen.ingresos_mes || 0);
            document.getElementById('stock-bajo').textContent = data.resumen.productos_stock_bajo || 0;
        }
        
        // Ventas del día
        const ventasBody = document.getElementById('ventas-hoy-body');
        if (data.ventas_hoy && data.ventas_hoy.length > 0) {
            ventasBody.innerHTML = data.ventas_hoy.map(v => `
                <tr>
                    <td>${v.numero_factura}</td>
                    <td>${v.cliente_nombre || 'General'}</td>
                    <td>$${formatearMoneda(v.total)}</td>
                    <td>${new Date(v.created_at).toLocaleTimeString()}</td>
                </tr>
            `).join('');
        } else {
            ventasBody.innerHTML = '<tr><td colspan="4">No hay ventas hoy</td></tr>';
        }
        
        // Top productos
        const topBody = document.getElementById('top-productos-body');
        if (data.top_productos && data.top_productos.length > 0) {
            topBody.innerHTML = data.top_productos.slice(0, 5).map(p => `
                <tr>
                    <td>${p.nombre}</td>
                    <td>${p.total_vendido}</td>
                    <td>$${formatearMoneda(p.total_ingresos)}</td>
                </tr>
            `).join('');
        } else {
            topBody.innerHTML = '<tr><td colspan="3">Sin datos</td></tr>';
        }
        
        // Stock bajo
        await cargarStockBajo();
        
    } catch (error) {
        console.error('Error cargando dashboard:', error);
    }
}

async function cargarStockBajo() {
    try {
        const res = await fetch(`${API_BASE}api_productos.php?stock_bajo`);
        const data = await res.json();
        const body = document.getElementById('stock-bajo-body');
        
        if (data.length > 0) {
            body.innerHTML = data.map(p => `
                <tr>
                    <td>${p.codigo}</td>
                    <td>${p.nombre}</td>
                    <td class="danger">${p.stock_actual}</td>
                    <td>${p.stock_minimo}</td>
                    <td>${p.faltante}</td>
                </tr>
            `).join('');
        } else {
            body.innerHTML = '<tr><td colspan="5">✅ Todos los productos tienen stock suficiente</td></tr>';
        }
    } catch (error) {
        console.error('Error cargando stock bajo:', error);
    }
}

// ============================================
// PRODUCTOS (CRUD)
// ============================================

async function cargarProductos() {
    const search = document.getElementById('search-productos')?.value || '';
    let url = `${API_BASE}api_productos.php`;
    if (search) url += `?search=${encodeURIComponent(search)}`;
    
    try {
        const res = await fetch(url);
        const data = await res.json();
        const body = document.getElementById('productos-body');
        
        if (data.length > 0) {
            body.innerHTML = data.map(p => `
                <tr>
                    <td>${p.codigo}</td>
                    <td><strong>${p.nombre}</strong></td>
                    <td>${p.categoria_nombre || '-'}</td>
                    <td>$${formatearMoneda(p.precio_venta)}</td>
                    <td class="${p.stock_actual <= p.stock_minimo ? 'danger' : ''}">${p.stock_actual}</td>
                    <td>${p.stock_minimo}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editarProducto(${p.id})">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${p.id})">🗑️</button>
                    </td>
                </tr>
            `).join('');
        } else {
            body.innerHTML = '<tr><td colspan="7">No hay productos registrados</td></tr>';
        }
    } catch (error) {
        console.error('Error cargando productos:', error);
    }
}

function mostrarFormProducto(id = null) {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    
    if (id) {
        // Editar - cargar datos
        fetch(`${API_BASE}api_productos.php?id=${id}`)
            .then(r => r.json())
            .then(p => {
                modalBody.innerHTML = generarFormProducto(p);
                modal.classList.add('show');
            });
    } else {
        modalBody.innerHTML = generarFormProducto(null);
        modal.classList.add('show');
    }
}

function generarFormProducto(producto = null) {
    const isEdit = producto !== null;
    const p = producto || {};
    
    return `
        <h3>${isEdit ? '✏️ Editar' : '➕ Nuevo'} Producto</h3>
        <form onsubmit="return guardarProducto(event, ${isEdit ? p.id : 'null'})">
            <div class="form-row">
                <div class="form-group">
                    <label>Código *</label>
                    <input type="text" name="codigo" value="${p.codigo || ''}" required>
                </div>
                <div class="form-group">
                    <label>Código Barras</label>
                    <input type="text" name="codigo_barras" value="${p.codigo_barras || ''}">
                </div>
            </div>
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" value="${p.nombre || ''}" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="2">${p.descripcion || ''}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id" id="sel-categoria"></select>
                </div>
                <div class="form-group">
                    <label>Impuesto</label>
                    <select name="impuesto_id">
                        <option value="1" ${p.impuesto_id == 1 ? 'selected' : ''}>Exento</option>
                        <option value="2" ${p.impuesto_id == 2 ? 'selected' : ''}>IVA 5%</option>
                        <option value="3" ${p.impuesto_id == 3 ? 'selected' : ''}>IVA 19%</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Precio Compra</label>
                    <input type="number" step="0.01" name="precio_compra" value="${p.precio_compra || 0}">
                </div>
                <div class="form-group">
                    <label>Precio Venta *</label>
                    <input type="number" step="0.01" name="precio_venta" value="${p.precio_venta || ''}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Stock Actual</label>
                    <input type="number" name="stock_actual" value="${p.stock_actual || 0}">
                </div>
                <div class="form-group">
                    <label>Stock Mínimo</label>
                    <input type="number" name="stock_minimo" value="${p.stock_minimo || 5}">
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-block">${isEdit ? 'Actualizar' : 'Crear'} Producto</button>
        </form>
    `;
    
    // Cargar categorías en el select después de renderizar
    setTimeout(cargarCategoriasSelect, 100);
}

async function cargarCategoriasSelect() {
    const sel = document.getElementById('sel-categoria');
    if (!sel) return;
    
    try {
        const res = await fetch(`${API_BASE}api_categorias.php`);
        const cats = await res.json();
        sel.innerHTML = '<option value="">Sin categoría</option>' +
            cats.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
    } catch (e) {
        console.error('Error cargando categorías:', e);
    }
}

async function guardarProducto(event, id) {
    event.preventDefault();
    const form = event.target;
    const data = Object.fromEntries(new FormData(form));
    
    try {
        let url = `${API_BASE}api_productos.php`;
        let method = 'POST';
        
        if (id) {
            url += `?id=${id}`;
            method = 'PUT';
        }
        
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        if (result.success || result.id) {
            alert(result.message || 'Operación exitosa');
            cerrarModal();
            cargarProductos();
            cargarDashboard();
        } else {
            alert('Error: ' + (result.error || 'Error desconocido'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function editarProducto(id) {
    mostrarFormProducto(id);
}

async function eliminarProducto(id) {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return;
    
    try {
        const res = await fetch(`${API_BASE}api_productos.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            alert('Producto eliminado');
            cargarProductos();
            cargarDashboard();
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// ============================================
// CLIENTES (CRUD)
// ============================================

async function cargarClientes() {
    const search = document.getElementById('search-clientes')?.value || '';
    let url = `${API_BASE}api_clientes.php`;
    if (search) url += `?search=${encodeURIComponent(search)}`;
    
    try {
        const res = await fetch(url);
        const data = await res.json();
        const body = document.getElementById('clientes-body');
        
        if (data.length > 0) {
            body.innerHTML = data.map(c => `
                <tr>
                    <td>${c.tipo_documento_codigo || ''} ${c.documento}</td>
                    <td><strong>${c.nombre}</strong></td>
                    <td>${c.telefono || '-'}</td>
                    <td>${c.email || '-'}</td>
                    <td>${c.ciudad || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editarCliente(${c.id})">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarCliente(${c.id})">🗑️</button>
                    </td>
                </tr>
            `).join('');
        } else {
            body.innerHTML = '<tr><td colspan="6">No hay clientes registrados</td></tr>';
        }
    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
}

function mostrarFormCliente(id = null) {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    
    if (id) {
        fetch(`${API_BASE}api_clientes.php?id=${id}`)
            .then(r => r.json())
            .then(c => {
                modalBody.innerHTML = generarFormCliente(c);
                modal.classList.add('show');
            });
    } else {
        modalBody.innerHTML = generarFormCliente(null);
        modal.classList.add('show');
    }
}

function generarFormCliente(cliente = null) {
    const isEdit = cliente !== null;
    const c = cliente || {};
    
    return `
        <h3>${isEdit ? '✏️ Editar' : '➕ Nuevo'} Cliente</h3>
        <form onsubmit="return guardarCliente(event, ${isEdit ? c.id : 'null'})">
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo Documento</label>
                    <select name="tipo_documento_id">
                        <option value="1" ${c.tipo_documento_id == 1 ? 'selected' : ''}>CC</option>
                        <option value="2" ${c.tipo_documento_id == 2 ? 'selected' : ''}>NIT</option>
                        <option value="3" ${c.tipo_documento_id == 3 ? 'selected' : ''}>CE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Documento *</label>
                    <input type="text" name="documento" value="${c.documento || ''}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" value="${c.nombre || ''}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="${c.telefono || ''}">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="${c.email || ''}">
                </div>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="${c.direccion || ''}">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" value="${c.ciudad || ''}">
                </div>
                <div class="form-group">
                    <label>Cupo Crédito</label>
                    <input type="number" step="0.01" name="cupo_credito" value="${c.cupo_credito || 0}">
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-block">${isEdit ? 'Actualizar' : 'Crear'} Cliente</button>
        </form>
    `;
}

async function guardarCliente(event, id) {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target));
    
    try {
        let url = `${API_BASE}api_clientes.php`;
        let method = 'POST';
        if (id) { url += `?id=${id}`; method = 'PUT'; }
        
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        });
        const result = await res.json();
        
        if (result.success || result.id) {
            alert('Cliente guardado');
            cerrarModal();
            cargarClientes();
            cargarClientesSelect();
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function editarCliente(id) { mostrarFormCliente(id); }

async function eliminarCliente(id) {
    if (!confirm('¿Eliminar cliente?')) return;
    await fetch(`${API_BASE}api_clientes.php?id=${id}`, { method: 'DELETE' });
    cargarClientes();
}

async function cargarClientesSelect() {
    try {
        const res = await fetch(`${API_BASE}api_clientes.php`);
        const clientes = await res.json();
        const sel = document.getElementById('pos-cliente');
        if (sel) {
            sel.innerHTML = clientes.map(c => 
                `<option value="${c.id}">${c.nombre} (${c.documento})</option>`
            ).join('');
        }
    } catch (e) {
        console.error('Error cargando clientes select:', e);
    }
}

// ============================================
// PROVEEDORES (CRUD)
// ============================================

async function cargarProveedores() {
    try {
        const res = await fetch(`${API_BASE}api_proveedores.php`);
        const data = await res.json();
        const body = document.getElementById('proveedores-body');
        
        if (data.length > 0) {
            body.innerHTML = data.map(p => `
                <tr>
                    <td>${p.documento}</td>
                    <td><strong>${p.nombre}</strong></td>
                    <td>${p.contacto || '-'}</td>
                    <td>${p.telefono || '-'}</td>
                    <td>${p.email || '-'}</td>
                    <td>${p.ciudad || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editarProveedor(${p.id})">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarProveedor(${p.id})">🗑️</button>
                    </td>
                </tr>
            `).join('');
        } else {
            body.innerHTML = '<tr><td colspan="7">No hay proveedores</td></tr>';
        }
    } catch (error) {
        console.error('Error cargando proveedores:', error);
    }
}

function mostrarFormProveedor(id = null) {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    
    if (id) {
        fetch(`${API_BASE}api_proveedores.php?id=${id}`)
            .then(r => r.json())
            .then(p => {
                modalBody.innerHTML = generarFormProveedor(p);
                modal.classList.add('show');
            });
    } else {
        modalBody.innerHTML = generarFormProveedor(null);
        modal.classList.add('show');
    }
}

function generarFormProveedor(proveedor = null) {
    const isEdit = proveedor !== null;
    const p = proveedor || {};
    
    return `
        <h3>${isEdit ? '✏️ Editar' : '➕ Nuevo'} Proveedor</h3>
        <form onsubmit="return guardarProveedor(event, ${isEdit ? p.id : 'null'})">
            <div class="form-row">
                <div class="form-group">
                    <label>Documento *</label>
                    <input type="text" name="documento" value="${p.documento || ''}" required>
                </div>
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="${p.nombre || ''}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contacto</label>
                    <input type="text" name="contacto" value="${p.contacto || ''}">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="${p.telefono || ''}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="${p.email || ''}">
                </div>
                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" value="${p.ciudad || ''}">
                </div>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="${p.direccion || ''}">
            </div>
            <button type="submit" class="btn btn-success btn-block">${isEdit ? 'Actualizar' : 'Crear'} Proveedor</button>
        </form>
    `;
}

async function guardarProveedor(event, id) {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target));
    
    try {
        let url = `${API_BASE}api_proveedores.php`;
        let method = 'POST';
        if (id) { url += `?id=${id}`; method = 'PUT'; }
        
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        });
        const result = await res.json();
        
        if (result.success || result.id) {
            alert('Proveedor guardado');
            cerrarModal();
            cargarProveedores();
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function editarProveedor(id) { mostrarFormProveedor(id); }

async function eliminarProveedor(id) {
    if (!confirm('¿Eliminar proveedor?')) return;
    await fetch(`${API_BASE}api_proveedores.php?id=${id}`, { method: 'DELETE' });
    cargarProveedores();
}

// ============================================
// VENTAS
// ============================================

async function cargarVentas() {
    try {
        const res = await fetch(`${API_BASE}api_ventas.php`);
        const data = await res.json();
        const body = document.getElementById('ventas-body');
        
        if (data.length > 0) {
            body.innerHTML = data.map(v => `
                <tr>
                    <td><strong>${v.numero_factura}</strong></td>
                    <td>${v.cliente_nombre || 'General'}</td>
                    <td>$${formatearMoneda(v.total)}</td>
                    <td><span class="badge ${v.estado === 'completada' ? 'badge-success' : 'badge-danger'}">${v.estado}</span></td>
                    <td>${new Date(v.created_at).toLocaleString()}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verVenta(${v.id})">👁️</button>
                        ${v.estado === 'completada' ? `<button class="btn btn-sm btn-danger" onclick="anularVenta(${v.id})">🚫</button>` : ''}
                    </td>
                </tr>
            `).join('');
        } else {
            body.innerHTML = '<tr><td colspan="6">No hay ventas registradas</td></tr>';
        }
    } catch (error) {
        console.error('Error cargando ventas:', error);
    }
}

async function verVenta(id) {
    try {
        const res = await fetch(`${API_BASE}api_ventas.php?id=${id}`);
        const venta = await res.json();
        
        const modal = document.getElementById('modal');
        const modalBody = document.getElementById('modal-body');
        
        let detalleHtml = '';
        if (venta.detalle) {
            detalleHtml = venta.detalle.map(d => `
                <tr>
                    <td>${d.producto_codigo}</td>
                    <td>${d.producto_nombre}</td>
                    <td>${d.cantidad}</td>
                    <td>$${formatearMoneda(d.precio_unitario)}</td>
                    <td>$${formatearMoneda(d.subtotal)}</td>
                </tr>
            `).join('');
        }
        
        modalBody.innerHTML = `
            <h3>🧾 Venta ${venta.numero_factura}</h3>
            <p><strong>Cliente:</strong> ${venta.cliente_nombre || 'General'}</p>
            <p><strong>Fecha:</strong> ${new Date(venta.created_at).toLocaleString()}</p>
            <p><strong>Estado:</strong> ${venta.estado}</p>
            <hr>
            <table>
                <thead><tr><th>Código</th><th>Producto</th><th>Cant</th><th>Precio</th><th>Subtotal</th></tr></thead>
                <tbody>${detalleHtml}</tbody>
                <tfoot>
                    <tr><td colspan="4"><strong>Subtotal</strong></td><td>$${formatearMoneda(venta.subtotal)}</td></tr>
                    <tr><td colspan="4"><strong>IVA</strong></td><td>$${formatearMoneda(venta.iva)}</td></tr>
                    <tr><td colspan="4"><strong>TOTAL</strong></td><td><strong>$${formatearMoneda(venta.total)}</strong></td></tr>
                </tfoot>
            </table>
        `;
        modal.classList.add('show');
    } catch (error) {
        console.error('Error:', error);
    }
}

async function anularVenta(id) {
    if (!confirm('¿Estás seguro de anular esta venta? Se revertirá el stock.')) return;
    
    try {
        const res = await fetch(`${API_BASE}api_ventas.php?id=${id}&action=anular`, { method: 'PUT' });
        const data = await res.json();
        alert(data.message);
        cargarVentas();
        cargarDashboard();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// ============================================
// COMPRAS
// ============================================

async function cargarCompras() {
    try {
        const res = await fetch(`${API_BASE}api_compras.php`);
        const data = await res.json();
        const body = document.getElementById('compras-body');
        
        if (data.length > 0) {
            body.innerHTML = data.map(c => `
                <tr>
                    <td><strong>${c.numero_orden}</strong></td>
                    <td>${c.proveedor_nombre || '-'}</td>
                    <td>$${formatearMoneda(c.total)}</td>
                    <td><span class="badge ${c.estado === 'recibida' ? 'badge-success' : 'badge-warning'}">${c.estado}</span></td>
                    <td>${new Date(c.created_at).toLocaleString()}</td>
                </tr>
            `).join('');
        } else {
            body.innerHTML = '<tr><td colspan="5">No hay compras registradas</td></tr>';
        }
    } catch (error) {
        console.error('Error cargando compras:', error);
    }
}

function mostrarFormCompra() {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    
    modalBody.innerHTML = `
        <h3>📥 Nueva Compra</h3>
        <form onsubmit="return guardarCompra(event)">
            <div class="form-group">
                <label>Proveedor *</label>
                <select name="proveedor_id" id="compra-proveedor" required></select>
            </div>
            <div class="form-group">
                <label>Producto ID *</label>
                <input type="number" name="producto_id" placeholder="ID del producto" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Cantidad *</label>
                    <input type="number" name="cantidad" required min="1">
                </div>
                <div class="form-group">
                    <label>Precio Unitario *</label>
                    <input type="number" step="0.01" name="precio_unitario" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-block">Registrar Compra</button>
        </form>
    `;
    modal.classList.add('show');
    
    // Cargar proveedores
    fetch(`${API_BASE}api_proveedores.php`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('compra-proveedor');
            sel.innerHTML = data.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
        });
}

async function guardarCompra(event) {
    event.preventDefault();
    const form = event.target;
    const data = {
        proveedor_id: parseInt(form.proveedor_id.value),
        usuario_id: 1,
        productos: [{
            producto_id: parseInt(form.producto_id.value),
            cantidad: parseInt(form.cantidad.value),
            precio_unitario: parseFloat(form.precio_unitario.value)
        }]
    };
    
    try {
        const res = await fetch(`${API_BASE}api_compras.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        
        if (result.success) {
            alert(`Compra registrada: ${result.numero_orden}`);
            cerrarModal();
            cargarCompras();
            cargarDashboard();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// ============================================
// MÓDULO POS (PUNTO DE VENTA)
// ============================================

async function cargarPOS() {
    await cargarProductosPOS();
    actualizarCarrito();
}

async function cargarProductosPOS(search = '') {
    let url = `${API_BASE}api_productos.php`;
    if (search) url += `?search=${encodeURIComponent(search)}`;
    
    try {
        const res = await fetch(url);
        const productos = await res.json();
        const container = document.getElementById('pos-productos-list');
        
        if (productos.length > 0) {
            container.innerHTML = productos.map(p => `
                <div class="pos-producto-item" onclick="agregarAlCarrito(${p.id}, '${p.nombre}', ${p.precio_venta}, ${p.stock_actual})">
                    <div class="nombre">${p.nombre}</div>
                    <div class="precio">$${formatearMoneda(p.precio_venta)}</div>
                    <div class="stock">Stock: ${p.stock_actual}</div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p>No se encontraron productos</p>';
        }
    } catch (error) {
        console.error('Error cargando POS:', error);
    }
}

function buscarProductosPOS(event) {
    const search = document.getElementById('pos-search').value;
    cargarProductosPOS(search);
}

function agregarAlCarrito(id, nombre, precio, stock) {
    const existente = carrito.find(item => item.id === id);
    
    if (existente) {
        if (existente.cantidad >= stock) {
            alert('Stock insuficiente');
            return;
        }
        existente.cantidad++;
    } else {
        if (stock <= 0) {
            alert('Producto sin stock');
            return;
        }
        carrito.push({ id, nombre, precio, cantidad: 1, stock });
    }
    
    actualizarCarrito();
}

function quitarDelCarrito(id) {
    carrito = carrito.filter(item => item.id !== id);
    actualizarCarrito();
}

function cambiarCantidad(id, nuevaCantidad) {
    const item = carrito.find(i => i.id === id);
    if (!item) return;
    
    if (nuevaCantidad <= 0) {
        quitarDelCarrito(id);
        return;
    }
    
    if (nuevaCantidad > item.stock) {
        alert('Stock insuficiente. Stock disponible: ' + item.stock);
        return;
    }
    
    item.cantidad = nuevaCantidad;
    actualizarCarrito();
}

function actualizarCarrito() {
    const body = document.getElementById('pos-carrito-body');
    let subtotal = 0;
    
    if (carrito.length === 0) {
        body.innerHTML = '<tr><td colspan="5">Carrito vacío. Selecciona productos.</td></tr>';
        document.getElementById('pos-subtotal').textContent = '$0';
        document.getElementById('pos-iva').textContent = '$0';
        document.getElementById('pos-total').textContent = '$0';
        return;
    }
    
    body.innerHTML = carrito.map(item => {
        const itemSubtotal = item.precio * item.cantidad;
        subtotal += itemSubtotal;
        
        return `
            <tr>
                <td>${item.nombre}</td>
                <td>$${formatearMoneda(item.precio)}</td>
                <td>
                    <button class="btn btn-sm" onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})">-</button>
                    ${item.cantidad}
                    <button class="btn btn-sm" onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})">+</button>
                </td>
                <td>$${formatearMoneda(itemSubtotal)}</td>
                <td><button class="btn btn-sm btn-danger" onclick="quitarDelCarrito(${item.id})">✕</button></td>
            </tr>
        `;
    }).join('');
    
    const iva = subtotal * 0.19;
    const total = subtotal + iva;
    
    document.getElementById('pos-subtotal').textContent = '$' + formatearMoneda(subtotal);
    document.getElementById('pos-iva').textContent = '$' + formatearMoneda(iva);
    document.getElementById('pos-total').textContent = '$' + formatearMoneda(total);
}

async function finalizarVenta() {
    if (carrito.length === 0) {
        alert('El carrito está vacío');
        return;
    }
    
    if (!confirm('¿Confirmar venta por $' + formatearMoneda(calcularTotal()) + '?')) return;
    
    const productos = carrito.map(item => ({
        producto_id: item.id,
        cantidad: item.cantidad,
        precio_unitario: item.precio
    }));
    
    const data = {
        cliente_id: parseInt(document.getElementById('pos-cliente').value) || 1,
        usuario_id: usuarioActual?.id || 1,
        forma_pago_id: parseInt(document.getElementById('pos-forma-pago').value) || 1,
        productos: productos
    };
    
    try {
        const res = await fetch(`${API_BASE}api_ventas.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        
        if (result.success) {
            alert(`✅ Venta exitosa!\nFactura: ${result.numero_factura}\nTotal: $${formatearMoneda(result.total)}`);
            carrito = [];
            actualizarCarrito();
            cargarProductosPOS();
            cargarDashboard();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar venta: ' + error.message);
    }
}

function calcularTotal() {
    let subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    return subtotal + (subtotal * 0.19);
}

// ============================================
// EXPORTAR DATOS
// ============================================

async function exportarXML() {
    try {
        const res = await fetch(`${API_BASE}api_productos.php?format=xml`);
        const xml = await res.text();
        const blob = new Blob([xml], { type: 'application/xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'productos.xml';
        a.click();
        URL.revokeObjectURL(url);
    } catch (error) {
        alert('Error al exportar: ' + error.message);
    }
}

async function exportarJSON() {
    try {
        const res = await fetch(`${API_BASE}api_productos.php?format=json`);
        const json = await res.json();
        const blob = new Blob([JSON.stringify(json, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'productos.json';
        a.click();
        URL.revokeObjectURL(url);
    } catch (error) {
        alert('Error al exportar: ' + error.message);
    }
}

// ============================================
// UTILIDADES
// ============================================

function formatearMoneda(valor) {
    return Number(valor || 0).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

function cerrarModal() {
    document.getElementById('modal').classList.remove('show');
}

// Cerrar modal al hacer clic fuera
document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
