<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$usuarios = Usuario::all();

renderHeader('Usuarios');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Administración de usuarios</h1>
        <a href="<?= e(url('/admin/estadisticas.php')) ?>" class="btn btn-small">Estadísticas</a>
    </div>

    <p class="meta">Gestiona cuentas, roles y acceso al sistema.</p>

    <?php if (!$usuarios): ?>
        <p class="empty-state">No hay usuarios registrados.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                        $nombreCompleto = trim($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));
                        $esYo = (int) $usuario['id'] === currentUser()['id'];
                        ?>
                        <tr>
                            <td><?= e($nombreCompleto) ?><?= $esYo ? ' (tú)' : '' ?></td>
                            <td><?= e($usuario['email']) ?></td>
                            <td><?= e($usuario['telefono'] ?: '—') ?></td>
                            <td>
                                <form method="post" action="<?= e(url('/admin/usuarios-acciones.php')) ?>" class="inline-form inline-form-compact">
                                    <?php renderCsrfField(); ?>
                                    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                    <input type="hidden" name="accion" value="cambiar_rol">
                                    <select name="rol" class="filter-select filter-select-inline js-cambiar-rol"
                                            data-rol-actual="<?= e($usuario['rol']) ?>"
                                            <?= $esYo ? 'disabled' : '' ?>>
                                        <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                        <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                        <option value="comprador" <?= $usuario['rol'] === 'comprador' ? 'selected' : '' ?>>Comprador</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span class="badge badge-<?= (int) $usuario['activo'] === 1 ? 'activa' : 'inactiva' ?>">
                                    <?= (int) $usuario['activo'] === 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y', strtotime($usuario['creado_en']))) ?></td>
                            <td class="actions-cell">
                                <?php if (!$esYo): ?>
                                    <form method="post" action="<?= e(url('/admin/usuarios-acciones.php')) ?>" class="inline-form inline-form-link"
                                          onsubmit="return confirm('¿<?= (int) $usuario['activo'] === 1 ? 'Desactivar' : 'Activar' ?> esta cuenta?');">
                                        <?php renderCsrfField(); ?>
                                        <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                        <input type="hidden" name="accion" value="toggle_activo">
                                        <button type="submit" class="link-button <?= (int) $usuario['activo'] === 1 ? 'action-danger' : '' ?>">
                                            <?= (int) $usuario['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="meta">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<script>
document.querySelectorAll('.js-cambiar-rol').forEach(function (select) {
    select.addEventListener('change', function () {
        var nuevo = select.value;
        var anterior = select.dataset.rolActual || nuevo;
        if (nuevo === anterior) {
            return;
        }

        var etiquetas = {
            admin: 'Administrador',
            vendedor: 'Vendedor',
            comprador: 'Comprador'
        };
        var mensaje = '¿Cambiar el rol de este usuario a ' + (etiquetas[nuevo] || nuevo) + '?';

        if (!window.confirm(mensaje)) {
            select.value = anterior;
            return;
        }

        select.form.submit();
    });
});
</script>
<?php renderFooter(); ?>
