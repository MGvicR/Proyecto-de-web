<?php

declare(strict_types=1);

class Usuario
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO usuarios (nombre, apellido, email, telefono, password_hash, rol)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['nombre'],
            $data['apellido'] ?? null,
            $data['email'],
            $data['telefono'] ?? null,
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['rol'] ?? 'vendedor',
        ]);

        return (int) getDB()->lastInsertId();
    }

    public static function emailExists(string $email): bool
    {
        $stmt = getDB()->prepare('SELECT 1 FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    public static function vendedoresConPropiedadesActivas(): array
    {
        $stmt = getDB()->query(
            'SELECT DISTINCT u.id, u.nombre, u.apellido
             FROM usuarios u
             INNER JOIN propiedades p ON p.vendedor_id = u.id
             WHERE u.rol = \'vendedor\'
               AND u.activo = 1
               AND p.estado = \'activa\'
             ORDER BY u.nombre ASC, u.apellido ASC'
        );

        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        $stmt = getDB()->query(
            'SELECT id, nombre, apellido, email, telefono, rol, activo, creado_en
             FROM usuarios
             ORDER BY creado_en DESC'
        );

        return $stmt->fetchAll();
    }

    public static function countByRol(): array
    {
        $stmt = getDB()->query(
            'SELECT rol, COUNT(*) AS total FROM usuarios GROUP BY rol'
        );
        $rows = $stmt->fetchAll();
        $counts = ['admin' => 0, 'vendedor' => 0, 'comprador' => 0];

        foreach ($rows as $row) {
            $counts[$row['rol']] = (int) $row['total'];
        }

        return $counts;
    }

    public static function countActivos(): int
    {
        $stmt = getDB()->query('SELECT COUNT(*) FROM usuarios WHERE activo = 1');

        return (int) $stmt->fetchColumn();
    }

    public static function countAdminsActivos(): int
    {
        $stmt = getDB()->query(
            'SELECT COUNT(*) FROM usuarios WHERE rol = \'admin\' AND activo = 1'
        );

        return (int) $stmt->fetchColumn();
    }

    public static function updateActivo(int $id, bool $activo): void
    {
        $stmt = getDB()->prepare('UPDATE usuarios SET activo = ? WHERE id = ?');
        $stmt->execute([(int) $activo, $id]);
    }

    public static function updateRol(int $id, string $rol): void
    {
        $stmt = getDB()->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
        $stmt->execute([$rol, $id]);
    }
}
