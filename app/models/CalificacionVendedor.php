<?php

declare(strict_types=1);

class CalificacionVendedor
{
    public static function create(array $data): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO calificaciones_vendedor
             (contacto_id, vendedor_id, comprador_id, propiedad_id, estrellas, comentario)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['contacto_id'],
            $data['vendedor_id'],
            $data['comprador_id'],
            $data['propiedad_id'],
            $data['estrellas'],
            $data['comentario'] ?? null,
        ]);

        return (int) getDB()->lastInsertId();
    }

    public static function findByContacto(int $contactoId): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT * FROM calificaciones_vendedor WHERE contacto_id = ? LIMIT 1'
        );
        $stmt->execute([$contactoId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function countAll(): int
    {
        $stmt = getDB()->query('SELECT COUNT(*) FROM calificaciones_vendedor');

        return (int) $stmt->fetchColumn();
    }
}
