<?php

declare(strict_types=1);

function dbEnv(string $key, string $default = ''): string
{
    if (isset($_ENV[$key]) && (string) $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && (string) $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    static $fileConfig = null;
    if ($fileConfig === null) {
        $localConfig = __DIR__ . '/config.local.php';
        $fileConfig = file_exists($localConfig) ? require $localConfig : [];
    }

    return (string) ($fileConfig[$key] ?? $default);
}

function createMysqlPdo(string $host, string $port, string $name, string $user, string $pass): PDO
{
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $baseOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $useSsl = in_array(strtolower(dbEnv('DB_SSL', '')), ['1', 'true', 'yes'], true)
        || str_contains($host, 'aivencloud.com');

    if (!$useSsl) {
        return new PDO($dsn, $user, $pass, $baseOptions);
    }

    $sslAttempts = [
        [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false],
    ];

    $caCandidates = array_unique(array_filter([
        dbEnv('DB_SSL_CA', ''),
        '/etc/ssl/certs/ca-certificates.crt',
        '/etc/ssl/cert.pem',
    ]));

    foreach ($caCandidates as $caFile) {
        if (!is_readable($caFile)) {
            continue;
        }

        $sslAttempts[] = [
            PDO::MYSQL_ATTR_SSL_CA => $caFile,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        $sslAttempts[] = [
            PDO::MYSQL_ATTR_SSL_CA => $caFile,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
        ];
    }

    $lastError = null;
    foreach ($sslAttempts as $sslOptions) {
        try {
            return new PDO($dsn, $user, $pass, $baseOptions + $sslOptions);
        } catch (PDOException $e) {
            $lastError = $e;
        }
    }

    throw $lastError ?? new PDOException('No se pudo conectar a MySQL con SSL');
}

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = dbEnv('DB_HOST', 'localhost');
        $name = dbEnv('DB_NAME', 'rentas_cdmx');
        $user = dbEnv('DB_USER', 'root');
        $pass = dbEnv('DB_PASS', '');
        $port = dbEnv('DB_PORT', '3306');

        try {
            $pdo = createMysqlPdo($host, $port, $name, $user, $pass);
        } catch (PDOException $e) {
            if (PHP_SAPI !== 'cli' && !headers_sent()) {
                http_response_code(503);
                header('Content-Type: text/html; charset=UTF-8');
                $enRender = dbEnv('RENDER', '') !== '' || dbEnv('RENDER_SERVICE_ID', '') !== '';
                echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
                echo '<title>Base de datos no disponible</title></head><body>';
                echo '<h1>No se pudo conectar a la base de datos</h1>';
                if ($enRender) {
                    echo '<p>En el panel de <strong>Render</strong>, ve a <em>Environment</em> y define:</p>';
                    echo '<ul><li>DB_HOST</li><li>DB_NAME</li><li>DB_USER</li><li>DB_PASS</li><li>DB_PORT (3306)</li></ul>';
                    echo '<p>Render no incluye MySQL. Necesitas una base MySQL externa (Aiven, Railway, etc.) e importar <code>sql/schema.sql</code>.</p>';
                    echo '<p>La base del profesor (tecweb) solo funciona en ese servidor, no desde Render.</p>';
                } else {
                    echo '<p>Revisa <code>app/config/config.local.php</code> o las variables DB_* del entorno.</p>';
                }
                echo '</body></html>';
                exit;
            }

            throw $e;
        }

        ensurePropiedadFotosSchema($pdo);
        ensureAdminEmail($pdo);
        ensureCompradorSchema($pdo);
        ensureCalificacionesSchema($pdo);
        ensureSitioConfigSchema($pdo);
    }

    return $pdo;
}

function ensureCompradorSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $rolCol = $db->query("SHOW COLUMNS FROM usuarios LIKE 'rol'")->fetch();
        if ($rolCol && !str_contains((string) $rolCol['Type'], 'comprador')) {
            $db->exec(
                "ALTER TABLE usuarios
                 MODIFY rol ENUM('vendedor', 'admin', 'comprador') NOT NULL DEFAULT 'vendedor'"
            );
        }

        $hasCompradorId = (bool) $db->query("SHOW COLUMNS FROM contactos LIKE 'comprador_id'")->fetch();
        if (!$hasCompradorId) {
            $db->exec(
                'ALTER TABLE contactos
                 ADD COLUMN comprador_id INT UNSIGNED NULL AFTER vendedor_id'
            );
        }

        $hasFk = (bool) $db->query(
            "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = 'contactos'
               AND CONSTRAINT_NAME = 'fk_contacto_comprador'"
        )->fetch();

        if (!$hasFk) {
            try {
                $db->exec(
                    'ALTER TABLE contactos
                     ADD CONSTRAINT fk_contacto_comprador
                     FOREIGN KEY (comprador_id) REFERENCES usuarios(id)'
                );
            } catch (PDOException) {
                // Puede existir con otro nombre en instalaciones previas.
            }
        }

        $hasIndex = (bool) $db->query(
            "SHOW INDEX FROM contactos WHERE Key_name = 'idx_contacto_comprador'"
        )->fetch();

        if (!$hasIndex) {
            try {
                $db->exec('ALTER TABLE contactos ADD INDEX idx_contacto_comprador (comprador_id)');
            } catch (PDOException) {
                // Índice duplicado en reintentos.
            }
        }

        $hasUnique = (bool) $db->query(
            "SHOW INDEX FROM contactos WHERE Key_name = 'uk_contacto_comprador_propiedad'"
        )->fetch();

        if (!$hasUnique) {
            try {
                $db->exec(
                    'ALTER TABLE contactos
                     ADD UNIQUE INDEX uk_contacto_comprador_propiedad (comprador_id, propiedad_id)'
                );
            } catch (PDOException) {
                // Puede fallar si ya hay duplicados; la validación en aplicación sigue activa.
            }
        }
    } catch (PDOException) {
        // Tablas aún no creadas durante instalación inicial.
    }
}

function ensureAdminEmail(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $db->exec(
            "UPDATE usuarios
             SET email = 'Administrador@gmail.com'
             WHERE rol = 'admin'
               AND email = 'admin@rentascdmx.mx'"
        );
    } catch (PDOException) {
        // La tabla puede no existir aún durante la instalación inicial.
    }
}

function ensureCalificacionesSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $exists = (bool) $db->query(
            "SHOW TABLES LIKE 'calificaciones_vendedor'"
        )->fetch();

        if ($exists) {
            return;
        }

        $db->exec(
            'CREATE TABLE calificaciones_vendedor (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                contacto_id INT UNSIGNED NOT NULL,
                vendedor_id INT UNSIGNED NOT NULL,
                comprador_id INT UNSIGNED NOT NULL,
                propiedad_id INT UNSIGNED NOT NULL,
                estrellas TINYINT UNSIGNED NOT NULL,
                comentario TEXT NULL,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_calif_contacto FOREIGN KEY (contacto_id) REFERENCES contactos(id),
                CONSTRAINT fk_calif_vendedor FOREIGN KEY (vendedor_id) REFERENCES usuarios(id),
                CONSTRAINT fk_calif_comprador FOREIGN KEY (comprador_id) REFERENCES usuarios(id),
                CONSTRAINT fk_calif_propiedad FOREIGN KEY (propiedad_id) REFERENCES propiedades(id),
                CONSTRAINT chk_calif_estrellas CHECK (estrellas BETWEEN 1 AND 5),
                UNIQUE KEY uk_calif_contacto (contacto_id),
                INDEX idx_calif_vendedor (vendedor_id)
            )'
        );
    } catch (PDOException) {
        // Instalación inicial o permisos insuficientes.
    }
}

function ensureSitioConfigSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $exists = (bool) $db->query("SHOW TABLES LIKE 'configuracion_sitio'")->fetch();

        if ($exists) {
            return;
        }

        $db->exec(
            'CREATE TABLE configuracion_sitio (
                clave VARCHAR(80) NOT NULL PRIMARY KEY,
                valor TEXT NOT NULL,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )'
        );

        $defaults = [
            'hero_titulo' => 'Encuentra tu lugar ideal',
            'hero_subtitulo' => 'Propiedades exclusivas en renta en las mejores zonas de Ciudad de México.',
        ];

        $stmt = $db->prepare('INSERT INTO configuracion_sitio (clave, valor) VALUES (?, ?)');
        foreach ($defaults as $clave => $valor) {
            $stmt->execute([$clave, $valor]);
        }
    } catch (PDOException) {
        // Instalación inicial.
    }
}

function ensurePropiedadFotosSchema(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $hasTipo = (bool) $db->query("SHOW COLUMNS FROM propiedad_fotos LIKE 'tipo'")->fetch();
        if (!$hasTipo) {
            $db->exec(
                "ALTER TABLE propiedad_fotos
                 ADD COLUMN tipo ENUM('recamara', 'bano', 'cocina', 'estacionamiento')
                     NOT NULL DEFAULT 'recamara' AFTER nombre_original"
            );
            $db->exec(
                'ALTER TABLE propiedad_fotos
                 ADD COLUMN numero TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER tipo'
            );
        }

        $hasUnique = (bool) $db->query(
            "SHOW INDEX FROM propiedad_fotos WHERE Key_name = 'uk_foto_espacio'"
        )->fetch();

        if (!$hasUnique) {
            try {
                $db->exec(
                    'ALTER TABLE propiedad_fotos
                     ADD UNIQUE KEY uk_foto_espacio (propiedad_id, tipo, numero)'
                );
            } catch (PDOException) {
                // Puede fallar si hay fotos legacy duplicadas; la app sigue funcionando.
            }
        }

        $ordenCol = $db->query("SHOW COLUMNS FROM propiedad_fotos LIKE 'orden'")->fetch();
        if ($ordenCol && stripos((string) $ordenCol['Type'], 'tinyint') !== false) {
            $db->exec(
                'ALTER TABLE propiedad_fotos MODIFY orden SMALLINT UNSIGNED NOT NULL DEFAULT 0'
            );
        }
    } catch (PDOException) {
        // La tabla puede no existir aún durante la instalación inicial.
    }
}
