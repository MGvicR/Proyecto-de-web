<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$filters = propiedadSearchFiltersFromRequest();

$propiedades = Propiedad::search($filters, 100, 0);

echo json_encode($propiedades, JSON_UNESCAPED_UNICODE);
