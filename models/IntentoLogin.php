<?php
declare(strict_types=1);

/** Limita los intentos fallidos de inicio de sesión por usuario e IP. */
final class IntentoLogin
{
    public const MAX_INTENTOS = 5;
    public const VENTANA_MINUTOS = 10;

    public static function bloqueado(string $usuario, string $ip): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM intentos_login
              WHERE usuario = :usuario AND ip = :ip
                AND fecha_hora > DATE_SUB(NOW(), INTERVAL ' . self::VENTANA_MINUTOS . ' MINUTE)'
        );
        $stmt->execute([':usuario' => $usuario, ':ip' => $ip]);

        return (int) $stmt->fetchColumn() >= self::MAX_INTENTOS;
    }

    public static function registrar(string $usuario, string $ip): void
    {
        $pdo = Database::pdo();

        $pdo->prepare('INSERT INTO intentos_login (usuario, ip, fecha_hora) VALUES (:usuario, :ip, NOW())')
            ->execute([':usuario' => $usuario, ':ip' => $ip]);

        // Mantenimiento: los intentos de más de un día ya no sirven.
        $pdo->exec('DELETE FROM intentos_login WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    }

    public static function limpiar(string $usuario, string $ip): void
    {
        Database::pdo()
            ->prepare('DELETE FROM intentos_login WHERE usuario = :usuario AND ip = :ip')
            ->execute([':usuario' => $usuario, ':ip' => $ip]);
    }
}