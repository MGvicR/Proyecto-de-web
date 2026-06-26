<?php

declare(strict_types=1);

class Alcaldia
{
    public static function all(): array
    {
        return getDB()
            ->query('SELECT id, nombre FROM alcaldias ORDER BY nombre')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM alcaldias WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function count(): int
    {
        return (int) getDB()->query('SELECT COUNT(*) FROM alcaldias')->fetchColumn();
    }
}
