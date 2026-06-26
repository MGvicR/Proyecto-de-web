<?php

declare(strict_types=1);

class Propiedad
{
    private static function applySearchFilters(string &$sql, array $filters, array &$params): void
    {
        if (!empty($filters['alcaldia_id'])) {
            $sql .= ' AND a.id = ?';
            $params[] = (int) $filters['alcaldia_id'];
        }
        if (!empty($filters['colonia_id'])) {
            $sql .= ' AND c.id = ?';
            $params[] = (int) $filters['colonia_id'];
        }
        if (!empty($filters['tipo_inmueble'])) {
            $sql .= ' AND p.tipo_inmueble = ?';
            $params[] = $filters['tipo_inmueble'];
        }
        if (!empty($filters['categoria'])) {
            match ($filters['categoria']) {
                'amueblado' => $sql .= ' AND p.amueblado = 1',
                'estacionamiento' => $sql .= ' AND p.tiene_estacionamiento = 1',
                'mascotas' => $sql .= ' AND p.permite_mascotas = 1',
                'premium' => $sql .= ' AND p.precio_mensual >= 20000',
                'economico' => $sql .= ' AND p.precio_mensual <= 12000',
                default => null,
            };
        }
        if (!empty($filters['vendedor_id'])) {
            $sql .= ' AND p.vendedor_id = ?';
            $params[] = (int) $filters['vendedor_id'];
        }
        if (!empty($filters['publicado_desde']) && isValidDateFilter((string) $filters['publicado_desde'])) {
            $sql .= ' AND DATE(p.publicado_en) >= ?';
            $params[] = $filters['publicado_desde'];
        }
        if (!empty($filters['publicado_hasta']) && isValidDateFilter((string) $filters['publicado_hasta'])) {
            $sql .= ' AND DATE(p.publicado_en) <= ?';
            $params[] = $filters['publicado_hasta'];
        }
        if (!empty($filters['recamaras'])) {
            $sql .= ' AND p.recamaras >= ?';
            $params[] = max(1, (int) $filters['recamaras']);
        }
        if (!empty($filters['banos'])) {
            $sql .= ' AND p.banos >= ?';
            $params[] = max(1, (int) $filters['banos']);
        }
        if (!empty($filters['cocinas'])) {
            $sql .= ' AND p.cocinas >= ?';
            $params[] = max(1, (int) $filters['cocinas']);
        }
        if (isset($filters['tiene_estacionamiento']) && $filters['tiene_estacionamiento'] !== '') {
            $sql .= ' AND p.tiene_estacionamiento = ?';
            $params[] = (int) $filters['tiene_estacionamiento'];
        }
        if (isset($filters['permite_mascotas']) && $filters['permite_mascotas'] !== '') {
            $sql .= ' AND p.permite_mascotas = ?';
            $params[] = (int) $filters['permite_mascotas'];
        }
        if (isset($filters['amueblado']) && $filters['amueblado'] !== '') {
            $sql .= ' AND p.amueblado = ?';
            $params[] = (int) $filters['amueblado'];
        }
        if (!empty($filters['valoracion_min'])) {
            $sql .= ' AND (SELECT AVG(cv.estrellas)
                           FROM calificaciones_vendedor cv
                           WHERE cv.vendedor_id = p.vendedor_id) >= ?';
            $params[] = max(1, min(5, (int) $filters['valoracion_min']));
        }
    }

    public static function search(array $filters, int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT p.*, c.nombre AS colonia, a.nombre AS alcaldia,
                       u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido,
                       (SELECT pf.ruta_archivo FROM propiedad_fotos pf
                        WHERE pf.propiedad_id = p.id
                        ORDER BY
                            CASE pf.tipo
                                WHEN \'recamara\' THEN 1
                                WHEN \'bano\' THEN 2
                                WHEN \'cocina\' THEN 3
                                ELSE 4
                            END,
                            pf.numero ASC
                        LIMIT 1) AS foto,
                       (SELECT ROUND(AVG(cv.estrellas), 1)
                        FROM calificaciones_vendedor cv
                        WHERE cv.vendedor_id = p.vendedor_id) AS valoracion_vendedor
                FROM propiedades p
                INNER JOIN colonias c ON c.id = p.colonia_id
                INNER JOIN alcaldias a ON a.id = c.alcaldia_id
                INNER JOIN usuarios u ON u.id = p.vendedor_id
                WHERE p.estado = \'activa\'';
        $params = [];

        self::applySearchFilters($sql, $filters, $params);

        $sql .= ' ORDER BY p.publicado_en DESC LIMIT ' . max(1, (int) $limit)
            . ' OFFSET ' . max(0, (int) $offset);

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countSearch(array $filters): int
    {
        $sql = 'SELECT COUNT(*) FROM propiedades p
                INNER JOIN colonias c ON c.id = p.colonia_id
                INNER JOIN alcaldias a ON a.id = c.alcaldia_id
                WHERE p.estado = \'activa\'';
        $params = [];

        self::applySearchFilters($sql, $filters, $params);

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT p.*, c.nombre AS colonia, a.nombre AS alcaldia, a.id AS alcaldia_id,
                    u.nombre AS vendedor_nombre, u.telefono AS vendedor_telefono, u.email AS vendedor_email
             FROM propiedades p
             INNER JOIN colonias c ON c.id = p.colonia_id
             INNER JOIN alcaldias a ON a.id = c.alcaldia_id
             INNER JOIN usuarios u ON u.id = p.vendedor_id
             WHERE p.slug = ? LIMIT 1'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT p.*, c.nombre AS colonia, a.nombre AS alcaldia, a.id AS alcaldia_id,
                    u.nombre AS vendedor_nombre, u.telefono AS vendedor_telefono
             FROM propiedades p
             INNER JOIN colonias c ON c.id = p.colonia_id
             INNER JOIN alcaldias a ON a.id = c.alcaldia_id
             INNER JOIN usuarios u ON u.id = p.vendedor_id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function byVendedor(int $vendedorId): array
    {
        $stmt = getDB()->prepare(
            'SELECT p.*, c.nombre AS colonia, a.nombre AS alcaldia
             FROM propiedades p
             INNER JOIN colonias c ON c.id = p.colonia_id
             INNER JOIN alcaldias a ON a.id = c.alcaldia_id
             WHERE p.vendedor_id = ?
             ORDER BY p.creado_en DESC'
        );
        $stmt->execute([$vendedorId]);
        return $stmt->fetchAll();
    }

    public static function todas(): array
    {
        return getDB()->query(
            'SELECT p.*, c.nombre AS colonia, a.nombre AS alcaldia,
                    u.nombre AS vendedor_nombre, u.email AS vendedor_email
             FROM propiedades p
             INNER JOIN colonias c ON c.id = p.colonia_id
             INNER JOIN alcaldias a ON a.id = c.alcaldia_id
             INNER JOIN usuarios u ON u.id = p.vendedor_id
             ORDER BY
                CASE p.estado
                    WHEN \'pendiente\' THEN 1
                    WHEN \'activa\' THEN 2
                    WHEN \'borrador\' THEN 3
                    WHEN \'rechazada\' THEN 4
                    WHEN \'rentada\' THEN 5
                    WHEN \'inactiva\' THEN 6
                    ELSE 7
                END,
                p.creado_en DESC'
        )->fetchAll();
    }

    public static function pendientes(): array
    {
        return getDB()->query(
            'SELECT p.*, c.nombre AS colonia, a.nombre AS alcaldia,
                    u.nombre AS vendedor_nombre, u.email AS vendedor_email
             FROM propiedades p
             INNER JOIN colonias c ON c.id = p.colonia_id
             INNER JOIN alcaldias a ON a.id = c.alcaldia_id
             INNER JOIN usuarios u ON u.id = p.vendedor_id
             WHERE p.estado = \'pendiente\'
             ORDER BY p.creado_en ASC'
        )->fetchAll();
    }

    public static function create(array $data): int
    {
        $slug = self::uniqueSlug($data['titulo']);

        $stmt = getDB()->prepare(
            'INSERT INTO propiedades
             (vendedor_id, colonia_id, titulo, slug, descripcion, tipo_inmueble,
              recamaras, banos, cocinas, tiene_estacionamiento, permite_mascotas,
              precio_mensual, deposito, amueblado, calle_referencia, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['vendedor_id'],
            $data['colonia_id'],
            $data['titulo'],
            $slug,
            $data['descripcion'],
            $data['tipo_inmueble'],
            max(1, (int) $data['recamaras']),
            max(1, (int) $data['banos']),
            max(1, (int) $data['cocinas']),
            (int) ($data['tiene_estacionamiento'] ?? 0),
            (int) ($data['permite_mascotas'] ?? 0),
            $data['precio_mensual'],
            null,
            (int) ($data['amueblado'] ?? 0),
            $data['calle_referencia'] ?? null,
            $data['estado'] ?? 'borrador',
        ]);

        return (int) getDB()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = getDB()->prepare(
            'UPDATE propiedades SET
                colonia_id = ?, titulo = ?, descripcion = ?, tipo_inmueble = ?,
                recamaras = ?, banos = ?, cocinas = ?,
                tiene_estacionamiento = ?, permite_mascotas = ?,
                precio_mensual = ?, deposito = ?, amueblado = ?, calle_referencia = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['colonia_id'],
            $data['titulo'],
            $data['descripcion'],
            $data['tipo_inmueble'],
            max(1, (int) $data['recamaras']),
            max(1, (int) $data['banos']),
            max(1, (int) $data['cocinas']),
            (int) ($data['tiene_estacionamiento'] ?? 0),
            (int) ($data['permite_mascotas'] ?? 0),
            $data['precio_mensual'],
            null,
            (int) ($data['amueblado'] ?? 0),
            $data['calle_referencia'] ?? null,
            $id,
        ]);
    }

    public static function updateEstado(int $id, string $estado, ?array $extra = null): void
    {
        $fields = ['estado = ?'];
        $params = [$estado];

        if ($extra !== null) {
            foreach ($extra as $key => $value) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        $params[] = $id;
        $sql = 'UPDATE propiedades SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = getDB()->prepare('DELETE FROM propiedades WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function fotos(int $propiedadId): array
    {
        $stmt = getDB()->prepare(
            'SELECT * FROM propiedad_fotos WHERE propiedad_id = ?
             ORDER BY
                CASE tipo
                    WHEN \'recamara\' THEN 1
                    WHEN \'bano\' THEN 2
                    WHEN \'cocina\' THEN 3
                    WHEN \'estacionamiento\' THEN 4
                END,
                numero ASC'
        );
        $stmt->execute([$propiedadId]);
        return $stmt->fetchAll();
    }

    public static function fotosIndexadas(int $propiedadId): array
    {
        $index = [];
        foreach (self::fotos($propiedadId) as $foto) {
            $index[self::fotoClave($foto['tipo'], (int) $foto['numero'])] = $foto;
        }
        return $index;
    }

    public static function fotoClave(string $tipo, int $numero): string
    {
        return $tipo . '-' . $numero;
    }

    public static function replaceFotoEspacio(
        int $propiedadId,
        string $tipo,
        int $numero,
        string $ruta,
        string $original
    ): void {
        $stmt = getDB()->prepare(
            'SELECT id, ruta_archivo FROM propiedad_fotos
             WHERE propiedad_id = ? AND tipo = ? AND numero = ? LIMIT 1'
        );
        $stmt->execute([$propiedadId, $tipo, $numero]);
        $existente = $stmt->fetch();

        if ($existente) {
            $rutaAnterior = UPLOAD_PATH . '/' . $existente['ruta_archivo'];
            if (is_file($rutaAnterior)) {
                unlink($rutaAnterior);
            }
            $delete = getDB()->prepare('DELETE FROM propiedad_fotos WHERE id = ?');
            $delete->execute([(int) $existente['id']]);
        }

        $orden = self::ordenParaTipo($tipo, $numero);
        $esPrincipal = $tipo === 'recamara' && $numero === 1;

        if ($esPrincipal) {
            $stmt = getDB()->prepare('UPDATE propiedad_fotos SET es_principal = 0 WHERE propiedad_id = ?');
            $stmt->execute([$propiedadId]);
        }

        $insert = getDB()->prepare(
            'INSERT INTO propiedad_fotos
             (propiedad_id, ruta_archivo, nombre_original, tipo, numero, es_principal, orden)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $propiedadId,
            $ruta,
            $original,
            $tipo,
            $numero,
            (int) $esPrincipal,
            $orden,
        ]);
    }

    private static function ordenParaTipo(string $tipo, int $numero): int
    {
        return match ($tipo) {
            'recamara' => $numero,
            'bano' => 100 + $numero,
            'cocina' => 200 + $numero,
            'estacionamiento' => 300,
            default => 400,
        };
    }

    /** @deprecated Usar replaceFotoEspacio */
    public static function addFoto(int $propiedadId, string $ruta, string $original, bool $principal = false, int $orden = 0): void
    {
        self::replaceFotoEspacio($propiedadId, 'recamara', max(1, $orden + 1), $ruta, $original);
    }

    private static function uniqueSlug(string $titulo): string
    {
        $base = slugify($titulo);
        $slug = $base;
        $i = 1;

        while (self::slugExists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private static function slugExists(string $slug): bool
    {
        $stmt = getDB()->prepare('SELECT 1 FROM propiedades WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return (bool) $stmt->fetchColumn();
    }

    public static function countAll(): int
    {
        $stmt = getDB()->query('SELECT COUNT(*) FROM propiedades');

        return (int) $stmt->fetchColumn();
    }

    public static function countByEstado(): array
    {
        $stmt = getDB()->query(
            'SELECT estado, COUNT(*) AS total FROM propiedades GROUP BY estado'
        );
        $rows = $stmt->fetchAll();
        $counts = [];

        foreach ($rows as $row) {
            $counts[$row['estado']] = (int) $row['total'];
        }

        return $counts;
    }

    public static function topVendedores(int $limit = 5): array
    {
        $limit = max(1, min(10, $limit));
        $stmt = getDB()->query(
            'SELECT u.id, u.nombre, u.apellido, COUNT(p.id) AS total_propiedades
             FROM usuarios u
             INNER JOIN propiedades p ON p.vendedor_id = u.id
             WHERE u.rol = \'vendedor\'
             GROUP BY u.id, u.nombre, u.apellido
             ORDER BY total_propiedades DESC
             LIMIT ' . $limit
        );

        return $stmt->fetchAll();
    }
}
