<?php

declare(strict_types=1);

class Contacto
{
    public static function create(array $data): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO contactos (propiedad_id, vendedor_id, comprador_id, nombre_visitante, email, telefono, mensaje)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['propiedad_id'],
            $data['vendedor_id'],
            $data['comprador_id'] ?? null,
            $data['nombre_visitante'],
            $data['email'] ?? null,
            $data['telefono'],
            $data['mensaje'] ?? null,
        ]);

        return (int) getDB()->lastInsertId();
    }

    public static function byVendedor(int $vendedorId): array
    {
        $stmt = getDB()->prepare(
            'SELECT c.*, p.titulo AS propiedad_titulo
             FROM contactos c
             INNER JOIN propiedades p ON p.id = c.propiedad_id
             WHERE c.vendedor_id = ?
             ORDER BY c.creado_en DESC'
        );
        $stmt->execute([$vendedorId]);
        return $stmt->fetchAll();
    }

    public static function byComprador(int $compradorId): array
    {
        $stmt = getDB()->prepare(
            'SELECT c.*, p.titulo AS propiedad_titulo, p.slug AS propiedad_slug,
                    p.precio_mensual, col.nombre AS colonia, a.nombre AS alcaldia
             FROM contactos c
             INNER JOIN propiedades p ON p.id = c.propiedad_id
             INNER JOIN colonias col ON col.id = p.colonia_id
             INNER JOIN alcaldias a ON a.id = col.alcaldia_id
             WHERE c.comprador_id = ?
             ORDER BY c.creado_en DESC'
        );
        $stmt->execute([$compradorId]);
        return $stmt->fetchAll();
    }

    public static function findByCompradorPropiedad(int $compradorId, int $propiedadId): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT * FROM contactos
             WHERE comprador_id = ? AND propiedad_id = ?
             ORDER BY creado_en DESC
             LIMIT 1'
        );
        $stmt->execute([$compradorId, $propiedadId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findForVendedor(int $id, int $vendedorId): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT * FROM contactos WHERE id = ? AND vendedor_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $vendedorId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function marcarContestado(int $id, int $vendedorId): bool
    {
        $stmt = getDB()->prepare(
            'UPDATE contactos SET leido = 1 WHERE id = ? AND vendedor_id = ? AND leido = 0'
        );
        $stmt->execute([$id, $vendedorId]);

        return $stmt->rowCount() > 0;
    }

    public static function countAll(): int
    {
        $stmt = getDB()->query('SELECT COUNT(*) FROM contactos');

        return (int) $stmt->fetchColumn();
    }

    public static function countPendientes(): int
    {
        $stmt = getDB()->query('SELECT COUNT(*) FROM contactos WHERE leido = 0');

        return (int) $stmt->fetchColumn();
    }
}
