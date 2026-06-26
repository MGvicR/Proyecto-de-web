<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$alcaldiaId = (int) get('alcaldia_id', 0);

if ($alcaldiaId <= 0) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(Colonia::byAlcaldia($alcaldiaId), JSON_UNESCAPED_UNICODE);
