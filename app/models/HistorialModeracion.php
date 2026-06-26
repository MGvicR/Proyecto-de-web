<?php

declare(strict_types=1);

class HistorialModeracion
{
    public static function registrar(
        int $propiedadId,
        int $adminId,
        string $accion,
        string $estadoAnterior,
        string $estadoNuevo,
        ?string $comentario = null
    ): void {
        $stmt = getDB()->prepare(
            'INSERT INTO historial_moderacion
             (propiedad_id, admin_id, accion, estado_anterior, estado_nuevo, comentario)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $propiedadId,
            $adminId,
            $accion,
            $estadoAnterior,
            $estadoNuevo,
            $comentario,
        ]);
    }

    public static function byPropiedad(int $propiedadId): array
    {
        $stmt = getDB()->prepare(
            'SELECT h.*, u.nombre AS admin_nombre
             FROM historial_moderacion h
             INNER JOIN usuarios u ON u.id = h.admin_id
             WHERE h.propiedad_id = ?
             ORDER BY h.creado_en DESC'
        );
        $stmt->execute([$propiedadId]);
        return $stmt->fetchAll();
    }
}
