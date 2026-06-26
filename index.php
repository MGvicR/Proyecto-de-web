<?php

declare(strict_types=1);

$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$destino = ($base === '' ? '' : $base) . '/public/index.php';

header('Location: ' . $destino, true, 302);
exit;
