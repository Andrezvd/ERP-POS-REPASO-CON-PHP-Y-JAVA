const API_URL = 'http://localhost:8000/MiniProyecto2/api/CategoriasEp.php';

/**
 * Obtiene todas las categorías
 * @returns {Promise<Array>} Array de categorías
 */
async function apiGetCategorias() {
    const response = await fetch(API_URL);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

/**
 * Obtiene una categoría por su ID
 * @param {number} id
 * @returns {Promise<Object>} Categoría
 */
async function apiGetCategoriaPorId(id) {
    const response = await fetch(`${API_URL}?id=${id}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

/**
 * Crea una nueva categoría
 * @param {Object} categoria - { nombre, descripcion, activo }
 * @returns {Promise<Object>} { id, message }
 */
async function apiCrearCategoria(categoria) {
    const response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(categoria)
    });
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data;
}

/**
 * Actualiza una categoría existente
 * @param {number} id
 * @param {Object} categoria - { nombre, descripcion, activo }
 * @returns {Promise<Object>} { message }
 */
async function apiActualizarCategoria(id, categoria) {
    const response = await fetch(`${API_URL}?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(categoria)
    });
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data;
}

/**
 * Elimina una categoría
 * @param {number} id
 * @returns {Promise<Object>} { message }
 */
async function apiEliminarCategoria(id) {
    const response = await fetch(`${API_URL}?id=${id}`, {
        method: 'DELETE'
    });
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data;
}

// ============================================
// FUNCIONES DE RENDERIZADO (solo pintan HTML)
// ============================================

/**
 * Renderiza la tabla de categorías en el tbody
 * @param {Array} categorias
 */
function renderizarTabla(categorias) {
    const tbody = document.getElementById('tabla-categorias');
    const mensaje = document.getElementById('mensaje');

    if (categorias.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="loading">No hay categorías registradas</td></tr>';
        return;
    }

    let html = '';
    categorias.forEach(cat => {
        const estado = cat.activo == 1 || cat.activo === true
            ? '<span class="badge badge-activo">Activo</span>'
            : '<span class="badge badge-inactivo">Inactivo</span>';

        html += `
            <tr>
                <td>${cat.id}</td>
                <td>${cat.nombre}</td>
                <td>${cat.descripcion || '-'}</td>
                <td>${estado}</td>
                <td>${cat.created_at || '-'}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    mensaje.innerHTML = '';
}

/**
 * Muestra un mensaje de error en el HTML
 * @param {string} mensajeTexto
 */
function mostrarError(mensajeTexto) {
    document.getElementById('mensaje').innerHTML =
        `<div class="error">${mensajeTexto}</div>`;
}

// ============================================
// CARGA INICIAL
// ============================================

(async function cargarCategorias() {
    try {
        const categorias = await apiGetCategorias();
        renderizarTabla(categorias);
    } catch (error) {
        mostrarError('Error al cargar categorías: ' + error.message);
        document.getElementById('tabla-categorias').innerHTML = '';
    }
})();
