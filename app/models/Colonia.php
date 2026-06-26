<?php

declare(strict_types=1);

class Colonia
{
    public static function byAlcaldia(int $alcaldiaId): array
    {
        $stmt = getDB()->prepare(
            'SELECT id, nombre, codigo_postal
             FROM colonias
             WHERE alcaldia_id = ?
             ORDER BY nombre'
        );
        $stmt->execute([$alcaldiaId]);

        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT c.*, a.nombre AS alcaldia_nombre
             FROM colonias c
             INNER JOIN alcaldias a ON a.id = c.alcaldia_id
             WHERE c.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function alcaldiaIdByColonia(int $coloniaId): ?int
    {
        $stmt = getDB()->prepare('SELECT alcaldia_id FROM colonias WHERE id = ? LIMIT 1');
        $stmt->execute([$coloniaId]);
        $alcaldiaId = $stmt->fetchColumn();

        return $alcaldiaId !== false ? (int) $alcaldiaId : null;
    }

    /** @return array<int, list<array{id:int,nombre:string,codigo_postal:?string}>> */
    public static function mapByAlcaldia(): array
    {
        $stmt = getDB()->query(
            'SELECT id, alcaldia_id, nombre, codigo_postal
             FROM colonias
             ORDER BY nombre'
        );

        $map = [];

        foreach ($stmt->fetchAll() as $row) {
            $alcaldiaId = (int) $row['alcaldia_id'];
            $map[$alcaldiaId][] = [
                'id' => (int) $row['id'],
                'nombre' => $row['nombre'],
                'codigo_postal' => $row['codigo_postal'],
            ];
        }

        return $map;
    }

    public static function count(): int
    {
        return (int) getDB()->query('SELECT COUNT(*) FROM colonias')->fetchColumn();
    }
}
