<?php
declare(strict_types=1);

/**
 * Validación del lado del servidor (RT-05). Acumula mensajes claros para el usuario
 * y nunca expone detalles técnicos (RT-04).
 */
final class Validar
{
    private array $errores = [];

    public function texto(mixed $valor, string $etiqueta, int $max, bool $obligatorio = true): string
    {
        $t = is_string($valor) ? trim($valor) : '';

        if ($t === '') {
            if ($obligatorio) {
                $this->errores[] = "{$etiqueta} es obligatorio.";
            }
            return '';
        }

        if (mb_strlen($t) > $max) {
            $this->errores[] = "{$etiqueta} no puede tener más de {$max} caracteres.";
        }

        return $t;
    }

    /** Aplica un patrón a un texto ya recortado. Un valor vacío no se evalúa aquí. */
    public function patron(string $valor, string $regex, string $etiqueta): void
    {
        if ($valor !== '' && preg_match($regex, $valor) !== 1) {
            $this->errores[] = "{$etiqueta} tiene un formato no válido.";
        }
    }

    public function entero(mixed $valor, string $etiqueta, int $min = 1, int $max = 2147483647): int
    {
        $n = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);

        if ($n === false) {
            $this->errores[] = "{$etiqueta} no es válido.";
            return 0;
        }

        return $n;
    }

    public function dinero(mixed $valor, string $etiqueta, bool $positivo = false): string
    {
        $t = is_string($valor) ? trim($valor) : '';
        $minimo = $positivo ? 0.01 : 0.0;

        if (preg_match('/^\d{1,8}(\.\d{1,2})?$/', $t) !== 1 || (float) $t < $minimo) {
            $this->errores[] = $positivo
                ? "{$etiqueta} debe ser mayor que cero, con máximo 2 decimales."
                : "{$etiqueta} debe ser un número positivo, con máximo 2 decimales.";
            return '';
        }

        return $t;
    }

    public function fecha(mixed $valor, string $etiqueta): string
    {
        $t = is_string($valor) ? $valor : '';
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $t);

        if ($d === false || $d->format('Y-m-d') !== $t) {
            $this->errores[] = "{$etiqueta} no es una fecha válida.";
            return '';
        }

        return $t;
    }

    /** Recibe el valor de <input type="datetime-local"> y devuelve 'Y-m-d H:i:s'. */
    public function fechaHora(mixed $valor, string $etiqueta): string
    {
        $t = is_string($valor) ? $valor : '';
        $d = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $t);

        if ($d === false || $d->format('Y-m-d\TH:i') !== $t) {
            $this->errores[] = "{$etiqueta} no es válida.";
            return '';
        }

        return $d->format('Y-m-d H:i:s');
    }

    public function opcion(mixed $valor, array $permitidos, string $etiqueta): string
    {
        if (!is_string($valor) || !in_array($valor, $permitidos, true)) {
            $this->errores[] = "{$etiqueta} no es válido.";
            return '';
        }

        return $valor;
    }

    public function agregar(string $mensaje): void
    {
        $this->errores[] = $mensaje;
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function valido(): bool
    {
        return $this->errores === [];
    }

    /** Fecha de un filtro: si falta o no es válida, usa el valor por defecto. */
    public static function fechaOpcional(mixed $valor, string $porDefecto): string
    {
        $t = is_string($valor) ? $valor : '';
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $t);

        return ($d !== false && $d->format('Y-m-d') === $t) ? $t : $porDefecto;
    }
}