<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$slug = (string) get('slug', '');
$prop = $slug ? Propiedad::findBySlug($slug) : null;

if (!$prop || $prop['estado'] !== 'activa') {
    flash('error', 'Propiedad no encontrada o no disponible.');
    redirect('/propiedades.php');
}

$fotos = Propiedad::fotos((int) $prop['id']);
$sessionUser = currentUser();
$perfilComprador = isComprador() && $sessionUser ? Usuario::findById($sessionUser['id']) : null;
$contactNombre = $perfilComprador['nombre'] ?? ($sessionUser !== null ? $sessionUser['nombre'] : '');
$contactApellido = $perfilComprador['apellido'] ?? '';
$contactEmail = $perfilComprador['email'] ?? ($sessionUser !== null ? $sessionUser['email'] : '');
$contactTelefono = $perfilComprador['telefono'] ?? '';

$contactoComprador = null;
$calificacionVendedor = null;
if (isComprador() && $sessionUser) {
    $contactoComprador = Contacto::findByCompradorPropiedad($sessionUser['id'], (int) $prop['id']);
    if ($contactoComprador) {
        $calificacionVendedor = CalificacionVendedor::findByContacto((int) $contactoComprador['id']);
    }
}

renderHeader($prop['titulo']);
?>
<div class="property-detail-page container">
    <a href="<?= e(url('/propiedades.php')) ?>" class="btn-clear-filters">← Volver al catálogo</a>

    <article class="property-detail property-detail-layout">
        <div>
            <div class="property-gallery">
                <?php if ($fotos): ?>
                    <?php foreach ($fotos as $foto): ?>
                        <figure class="gallery-item">
                            <img src="<?= e(url('/uploads/propiedades/' . $foto['ruta_archivo'])) ?>"
                                 alt="<?= e(fotoEspacioLabel($foto['tipo'] ?? 'recamara', (int) ($foto['numero'] ?? 1))) ?>">
                            <figcaption><?= e(fotoEspacioLabel($foto['tipo'] ?? 'recamara', (int) ($foto['numero'] ?? 1))) ?></figcaption>
                        </figure>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-photo large">Sin fotos</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="property-info card">
            <div class="dashboard-header" style="margin-bottom: 1rem; padding: 0; box-shadow: none; background: transparent;">
                <h1><?= e($prop['titulo']) ?></h1>
                <span class="card-badge-inline">Renta</span>
            </div>
            <p class="price large"><?= formatMoney($prop['precio_mensual']) ?> / mes</p>

            <div class="features">
                <span>🛏 <?= (int) $prop['recamaras'] ?> recámaras</span>
                <span>🛁 <?= (int) $prop['banos'] ?> baños</span>
                <span>🍳 <?= (int) $prop['cocinas'] ?> cocinas</span>
            </div>
            <p class="location">📍 <?= e($prop['colonia']) ?>, <?= e($prop['alcaldia']) ?>, CDMX</p>

            <ul class="features-list">
                <li><strong>Tipo:</strong> <?= e(ucfirst($prop['tipo_inmueble'])) ?></li>
                <?php if ($prop['calle_referencia']): ?>
                    <li><strong>Referencia:</strong> <?= e($prop['calle_referencia']) ?></li>
                <?php endif; ?>
                <li><strong>Estacionamiento:</strong> <?= boolLabel($prop['tiene_estacionamiento']) ?></li>
                <li><strong>Mascotas:</strong> <?= boolLabel($prop['permite_mascotas']) ?></li>
                <li><strong>Amueblado:</strong> <?= boolLabel($prop['amueblado']) ?></li>
            </ul>

            <h2>Descripción</h2>
            <p class="description"><?= nl2br(e($prop['descripcion'])) ?></p>

            <div class="contact-box">
                <h2>Contacto del vendedor</h2>
                <ul class="contact-vendor-list">
                    <li><strong>Nombre:</strong> <?= e($prop['vendedor_nombre']) ?></li>
                    <?php if ($prop['vendedor_telefono']): ?>
                        <li><strong>Teléfono:</strong> <?= e($prop['vendedor_telefono']) ?></li>
                    <?php else: ?>
                        <li><strong>Teléfono:</strong> No registrado</li>
                    <?php endif; ?>
                    <?php if ($prop['vendedor_email']): ?>
                        <li><strong>Correo:</strong> <?= e($prop['vendedor_email']) ?></li>
                    <?php else: ?>
                        <li><strong>Correo:</strong> No registrado</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="contact-box contact-form-box">
                <?php if (isLoggedIn() && !isComprador()): ?>
                    <h2>Consultas de compradores</h2>
                    <p class="meta">Inicia sesión con una cuenta de comprador para enviar consultas sobre esta propiedad.</p>
                <?php elseif (isComprador() && $contactoComprador && (int) $contactoComprador['leido'] === 1): ?>
                    <?php if ($calificacionVendedor): ?>
                        <h2>Calificación enviada</h2>
                        <p class="meta">Ya calificaste al vendedor de esta propiedad.</p>
                        <p class="rating-display" aria-label="Valoración <?= (int) $calificacionVendedor['estrellas'] ?> de 5">
                            <?= e(estrellasLabel((int) $calificacionVendedor['estrellas'])) ?>
                        </p>
                        <?php if ($calificacionVendedor['comentario']): ?>
                            <p class="rating-comment"><strong>Tu comentario:</strong> <?= nl2br(e($calificacionVendedor['comentario'])) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <h2>Calificar vendedor</h2>
                        <p class="meta">El vendedor contestó tu consulta. Comparte tu experiencia con una valoración.</p>
                        <form method="post" action="<?= e(url('/comprador/calificar.php')) ?>" class="form-stack">
                            <?php renderCsrfField(); ?>
                            <input type="hidden" name="contacto_id" value="<?= (int) $contactoComprador['id'] ?>">
                            <input type="hidden" name="slug" value="<?= e($prop['slug']) ?>">
                            <div class="form-group">
                                <span class="label-block">Valoración *</span>
                                <div class="star-rating" role="radiogroup" aria-label="Valoración de 1 a 5 estrellas">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="estrella_<?= $i ?>" name="estrellas" value="<?= $i ?>" required>
                                        <label for="estrella_<?= $i ?>" title="<?= $i ?> estrellas">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comentario">Comentario</label>
                                <textarea id="comentario" name="comentario" rows="4"
                                          placeholder="Cuéntanos cómo fue la atención del vendedor…"></textarea>
                            </div>
                            <button type="submit" class="btn full-width">Enviar calificación</button>
                        </form>
                    <?php endif; ?>
                <?php elseif (isComprador() && $contactoComprador): ?>
                    <h2>Tu consulta</h2>
                    <p class="meta">Tu consulta está <strong>pendiente</strong>. El vendedor aún no la ha contestado.</p>
                <?php else: ?>
                    <h2>Enviar consulta</h2>
                    <?php if (!isLoggedIn()): ?>
                        <p class="meta">¿Tienes cuenta de comprador? <a href="<?= e(url('/login.php')) ?>">Inicia sesión</a> para guardar tus consultas.</p>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/contacto.php')) ?>" class="form-stack">
                        <?php renderCsrfField(); ?>
                        <input type="hidden" name="propiedad_id" value="<?= (int) $prop['id'] ?>">
                        <div class="form-group">
                            <label for="nombre_visitante">Nombre *</label>
                            <input type="text" id="nombre_visitante" name="nombre_visitante" required maxlength="100"
                                   value="<?= e(trim($contactNombre . ' ' . $contactApellido)) ?>"
                                   <?= isComprador() ? 'readonly class="input-readonly"' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" maxlength="150"
                                   value="<?= e($contactEmail) ?>"
                                   <?= isComprador() ? 'readonly class="input-readonly"' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono"
                                   class="js-solo-numeros<?= isComprador() ? ' input-readonly' : '' ?>" required
                                   inputmode="numeric" pattern="[0-9]*" maxlength="20"
                                   title="Solo números" value="<?= e($contactTelefono) ?>"
                                   <?= isComprador() ? 'readonly' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label for="mensaje">Mensaje</label>
                            <textarea id="mensaje" name="mensaje" rows="4" placeholder="Me interesa agendar una visita…"></textarea>
                        </div>
                        <button type="submit" class="btn full-width">Enviar mensaje al vendedor</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
</div>

<?php renderFooter(); ?>
