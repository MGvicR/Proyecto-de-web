<?php

declare(strict_types=1);

/** @var array $prop */
?>
<article class="property-card card-listing">
    <a class="card-listing-link" href="<?= e(url('/propiedad.php?slug=' . urlencode($prop['slug']))) ?>">
        <div class="card-img">
            <?php if (!empty($prop['foto'])): ?>
                <img src="<?= e(url('/uploads/propiedades/' . $prop['foto'])) ?>" alt="<?= e($prop['titulo']) ?>">
            <?php else: ?>
                <div class="no-photo">Sin foto</div>
            <?php endif; ?>
            <span class="card-badge">Renta</span>
        </div>
        <div class="property-card-body card-body">
            <h3><?= e($prop['titulo']) ?></h3>
            <?php if (!empty($prop['valoracion_vendedor'])): ?>
                <p class="property-rating" aria-label="Valoración del vendedor: <?= e((string) $prop['valoracion_vendedor']) ?> de 5">
                    <?= e(estrellasLabel((int) round((float) $prop['valoracion_vendedor']))) ?>
                    <span class="rating-value">(<?= e(number_format((float) $prop['valoracion_vendedor'], 1)) ?>)</span>
                </p>
            <?php endif; ?>
            <p class="price"><?= formatMoney($prop['precio_mensual']) ?> / mes</p>
            <div class="features">
                <span>🛏 <?= (int) $prop['recamaras'] ?></span>
                <span>🛁 <?= (int) $prop['banos'] ?></span>
                <span>🍳 <?= (int) $prop['cocinas'] ?></span>
            </div>
            <p class="location">📍 <?= e($prop['colonia']) ?>, <?= e($prop['alcaldia']) ?></p>
            <?php if (!empty($prop['vendedor_nombre'])): ?>
                <p class="property-agent">Agente: <?= e(vendedorDisplayName($prop)) ?></p>
            <?php endif; ?>
            <?php if (!empty($prop['publicado_en'])): ?>
                <p class="property-published">Publicado: <?= e(date('d/m/Y', strtotime($prop['publicado_en']))) ?></p>
            <?php endif; ?>
            <span class="btn-detail">Ver detalles</span>
        </div>
    </a>
</article>
