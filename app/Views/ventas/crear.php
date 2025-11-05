<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Crear Nueva Venta</h1>
<?php if (!empty($error)): ?>
    <div class="alert-error" style="margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>

<div class="form-wrapper-venta">
<form id="formVenta" action="<?php echo APP_URL; ?>/apiventa/crear" method="POST" autocomplete="off">
    <!-- Información Principal -->
    <div class="form-section">
        <h3>📋 Información de la Venta</h3>
        
        <div class="form-group">
            <label for="id_cliente">Cliente: <span class="required">*</span></label>
            <select id="id_cliente" name="id_cliente" required>
                <option value="">Seleccionar Cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo (int)$cliente['id']; ?>">
                        <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido'] . ' - DNI: ' . $cliente['dni']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="field-error" id="err_id_cliente"></div>
        </div>

        <div class="form-group">
            <label for="id_usuario">Usuario: <span class="required">*</span></label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Seleccionar Usuario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo (int)$usuario['id']; ?>">
                        <?php echo htmlspecialchars($usuario['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="field-error" id="err_id_usuario"></div>
        </div>
    </div>

    <!-- Detalles de la Venta -->
    <div class="form-section">
        <h3>🛍️ Productos/Servicios</h3>
        
        <div id="detalles-container">
            <div class="detalle-item">
                <div class="detalle-row">
                    <div class="form-group-inline">
                        <label for="producto_0">Producto/Servicio:</label>
                        <select name="detalles[0][id_producto]" class="select-producto" data-index="0" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($productosServicios as $ps): ?>
                                <option value="<?php echo (int)$ps['id']; ?>" 
                                        data-precio="<?php echo number_format($ps['precio'], 2, '.', ''); ?>"
                                        data-tipo="<?php echo htmlspecialchars($ps['tipo']); ?>"
                                        data-stock="<?php echo (int)$ps['stock']; ?>">
                                    <?php echo htmlspecialchars($ps['tipo'] . ' - ' . $ps['nombre'] . ' (S/ ' . number_format($ps['precio'], 2) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-inline">
                        <label for="cantidad_0">Cantidad:</label>
                        <input type="number" name="detalles[0][cantidad]" class="input-cantidad" data-index="0" min="1" step="1" value="1" required>
                    </div>
                    <div class="form-group-inline">
                        <label for="precio_0">Precio (S/):</label>
                        <input type="number" name="detalles[0][precio]" class="input-precio" data-index="0" min="0.01" step="0.01" required readonly>
                    </div>
                    <div class="form-group-inline subtotal-group">
                        <label>Subtotal:</label>
                        <span class="subtotal-display" data-index="0">S/ 0.00</span>
                    </div>
                    <button type="button" class="btn-remove-detalle" data-index="0" style="display:none;">🗑️</button>
                </div>
            </div>
        </div>
        
        <button type="button" id="btn-agregar-producto" class="btn-agregar">➕ Agregar Producto/Servicio</button>
        
        <div class="total-section">
            <div class="total-label">Total de la Venta:</div>
            <div class="total-amount" id="total-venta">S/ 0.00</div>
        </div>
        <input type="hidden" id="total" name="total" value="0">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-guardar">💾 Guardar Venta</button>
        <a href="<?php echo APP_URL; ?>/venta" class="btn-cancelar">🔙 Regresar</a>
    </div>
</form>
</div>

<script>
let detalleIndex = 0;

// Agregar nuevo producto
document.getElementById('btn-agregar-producto').addEventListener('click', function() {
    detalleIndex++;
    const container = document.getElementById('detalles-container');
    const newDetalle = container.firstElementChild.cloneNode(true);
    
    // Actualizar índices
    const inputs = newDetalle.querySelectorAll('[name]');
    inputs.forEach(input => {
        const name = input.name.replace('[0]', '[' + detalleIndex + ']');
        input.name = name;
        input.value = '';
        input.dataset.index = detalleIndex;
        if (input.classList.contains('input-precio')) {
            input.readOnly = true;
        }
    });
    
    // Actualizar labels y IDs
    const labels = newDetalle.querySelectorAll('label');
    labels.forEach(label => {
        if (label.htmlFor) {
            label.htmlFor = label.htmlFor.replace('_0', '_' + detalleIndex);
        }
    });
    
    // Mostrar botón eliminar y limpiar subtotal
    newDetalle.querySelector('.btn-remove-detalle').style.display = 'block';
    newDetalle.querySelector('.btn-remove-detalle').dataset.index = detalleIndex;
    newDetalle.querySelector('.subtotal-display').textContent = 'S/ 0.00';
    newDetalle.querySelector('.subtotal-display').dataset.index = detalleIndex;
    
    // Limpiar valores
    newDetalle.querySelector('.select-producto').value = '';
    newDetalle.querySelector('.input-cantidad').value = 1;
    newDetalle.querySelector('.input-precio').value = '';
    
    container.appendChild(newDetalle);
    actualizarTotal();
});

// Eliminar detalle
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-remove-detalle')) {
        const detalle = e.target.closest('.detalle-item');
        if (document.getElementById('detalles-container').children.length > 1) {
            detalle.remove();
            actualizarTotal();
        } else {
            alert('Debe tener al menos un producto/servicio');
        }
    }
});

// Cuando se selecciona un producto, actualizar precio
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('select-producto')) {
        const index = e.target.dataset.index;
        const precio = parseFloat(e.target.selectedOptions[0]?.dataset.precio || 0);
        const precioInput = document.querySelector(`.input-precio[data-index="${index}"]`);
        if (precioInput) {
            precioInput.value = precio.toFixed(2);
            calcularSubtotal(index);
            actualizarTotal();
        }
    }
});

// Cuando cambia la cantidad, actualizar subtotal
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('input-cantidad')) {
        const index = e.target.dataset.index;
        calcularSubtotal(index);
        actualizarTotal();
    }
});

function calcularSubtotal(index) {
    const cantidad = parseFloat(document.querySelector(`.input-cantidad[data-index="${index}"]`)?.value || 0);
    const precio = parseFloat(document.querySelector(`.input-precio[data-index="${index}"]`)?.value || 0);
    const subtotal = cantidad * precio;
    const subtotalDisplay = document.querySelector(`.subtotal-display[data-index="${index}"]`);
    if (subtotalDisplay) {
        subtotalDisplay.textContent = 'S/ ' + subtotal.toFixed(2);
    }
}

function actualizarTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-display').forEach(display => {
        const text = display.textContent.replace('S/ ', '').replace(',', '');
        total += parseFloat(text || 0);
    });
    document.getElementById('total-venta').textContent = 'S/ ' + total.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
}

// Envío del formulario por AJAX
const form = document.getElementById('formVenta');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    document.getElementById('alertResult').textContent = '';
    
    // Validar que haya al menos un detalle
    const detalles = Array.from(document.querySelectorAll('.select-producto')).filter(s => s.value !== '');
    if (detalles.length === 0) {
        document.getElementById('alertResult').innerHTML = '<div class="alert-error">Debe agregar al menos un producto/servicio</div>';
        return;
    }
    
    // Construir payload
    const formData = new FormData(form);
    const payload = {
        id_usuario: parseInt(formData.get('id_usuario')),
        id_cliente: parseInt(formData.get('id_cliente')),
        total: parseFloat(document.getElementById('total').value),
        detalles: []
    };
    
    // Agregar detalles
    document.querySelectorAll('.detalle-item').forEach(item => {
        const productoSelect = item.querySelector('.select-producto');
        const cantidadInput = item.querySelector('.input-cantidad');
        const precioInput = item.querySelector('.input-precio');
        
        if (productoSelect.value && cantidadInput.value && precioInput.value) {
            payload.detalles.push({
                id_producto: parseInt(productoSelect.value),
                cantidad: parseInt(cantidadInput.value),
                precio: parseFloat(precioInput.value)
            });
        }
    });
    
    // Validaciones
    if (payload.id_usuario <= 0) {
        document.getElementById('err_id_usuario').textContent = 'Seleccione un usuario';
        return;
    }
    if (payload.id_cliente <= 0) {
        document.getElementById('err_id_cliente').textContent = 'Seleccione un cliente';
        return;
    }
    if (payload.total <= 0) {
        document.getElementById('alertResult').innerHTML = '<div class="alert-error">El total debe ser mayor a 0</div>';
        return;
    }
    
    // Enviar
    fetch(form.action, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r => r.json().then(data => ({status: r.status, body: data})))
    .then(res => {
        if (res.body.error) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">' + res.body.error + '</div>';
        } else if (res.body.message) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-success">' + res.body.message + '</div>';
            setTimeout(() => {
                window.location = '<?php echo APP_URL; ?>/venta?success=1';
            }, 1500);
        } else {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">Error desconocido</div>';
        }
    })
    .catch(() => {
        document.getElementById('alertResult').innerHTML = '<div class="alert-error">No se pudo conectar al servidor</div>';
    });
});

// Inicializar total
actualizarTotal();
</script>

<style>
.form-wrapper-venta {
    max-width: 900px;
    margin: 45px auto 0;
    background: #20263B;
    padding: 38px 31px 34px 31px;
    border-radius: 12px;
    box-shadow: 0 2px 13px rgba(0,0,0,0.2);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #353950;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    color: #fff;
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
}

.form-group {
    margin-bottom: 18px;
}

.form-group-inline {
    flex: 1;
    min-width: 150px;
    margin-right: 10px;
}

.detalle-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    flex-wrap: wrap;
    margin-bottom: 15px;
    padding: 15px;
    background: #141828;
    border-radius: 8px;
    border: 1px solid #28304A;
}

.form-group-inline label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #fff;
    font-size: 14px;
}

.required {
    color: #ff6b6b;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #fff;
}

input[type="text"], 
input[type="number"], 
select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #353950;
    background: #141828;
    color: #eee;
    font-size: 16px;
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus {
    outline: none;
    border-color: #208cff;
    box-shadow: 0 0 6px rgba(32, 140, 255, 0.33);
}

select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23fff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
}

.subtotal-group {
    min-width: 120px;
}

.subtotal-display {
    display: block;
    font-weight: bold;
    color: #28a745;
    font-size: 16px;
    margin-top: 5px;
}

.btn-agregar {
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 20px;
    transition: all 0.2s;
}

.btn-agregar:hover {
    background: #218838;
    transform: translateY(-1px);
}

.btn-remove-detalle {
    background: #dc3545;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
    height: fit-content;
}

.btn-remove-detalle:hover {
    background: #b02a37;
    transform: translateY(-1px);
}

.total-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #141828;
    border-radius: 8px;
    margin-top: 20px;
    border: 2px solid #28a745;
}

.total-label {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
}

.total-amount {
    font-size: 28px;
    font-weight: 800;
    color: #28a745;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    align-items: center;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-guardar {
    background-color: #007bff;
    color: white;
    padding: 13px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 17px;
    font-weight: bold;
    transition: all 0.12s;
    min-width: 200px;
}

.btn-guardar:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.btn-cancelar {
    background-color: #6c757d;
    color: white;
    padding: 13px 24px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.12s;
    min-width: 200px;
    text-align: center;
}

.btn-cancelar:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

.field-error {
    color: #ff6b6b;
    font-size: 15px;
    margin-top: 3px;
    min-height: 17px;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
    border-radius: 4px;
    padding: 7px 14px;
    margin-bottom: 13px;
    display: inline-block;
}

.alert-success {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
    border-radius: 4px;
    padding: 7px 14px;
    margin-bottom: 13px;
    display: inline-block;
}

@media (max-width: 768px) {
    .form-wrapper-venta {
        padding: 16px;
    }
    
    .detalle-row {
        flex-direction: column;
    }
    
    .form-group-inline {
        width: 100%;
        margin-right: 0;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-guardar,
    .btn-cancelar {
        width: 100%;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>

