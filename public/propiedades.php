<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$filters = propiedadSearchFiltersFromRequest();

$page = max(1, (int) get('page', 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$total = Propiedad::countSearch($filters);
$propiedades = Propiedad::search($filters, $perPage, $offset);
$totalPages = max(1, (int) ceil($total / $perPage));

renderHeader('Buscar propiedades', 'search');
?>
<aside class="filters-sidebar">
    <h3>Filtros</h3>
    <?php
    $action = url('/propiedades.php');
    $variant = 'sidebar';
    require BASE_PATH . '/app/views/partials/filtros.php';
    ?>
</aside>

<main class="results-content">
    <div class="results-header">
        <h1>Encontramos <?= $total ?> inmueble(s)</h1>
    </div>

    <?php if (!$propiedades): ?>
        <div class="no-results">
            <p>No hay propiedades que coincidan con tu búsqueda.</p>
        </div>
    <?php else: ?>
        <div class="properties-grid property-grid">
            <?php foreach ($propiedades as $prop): ?>
                <?php require BASE_PATH . '/app/views/partials/property-card.php'; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $query = array_merge($filters, ['page' => $i]);
                    $url = url('/propiedades.php') . '?' . http_build_query(array_filter($query, fn($v) => $v !== '' && $v !== null));
                    ?>
                    <a href="<?= e($url) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php renderFooter('search'); ?>
