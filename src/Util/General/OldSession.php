<?php

namespace App\Util\General;

use App\Tecnico\Tecnico;

/**
 * Renomeado para OldSession porque vamos usar no lugar o objeto UserSession
 * Que é mais fácil de usar nos testes
 */
class OldSession
{
    public static function iniciar(): void
    {
        session_start();
    }

    private static function setTipo(string $tipo): void
    {
        $_SESSION['tipo'] = $tipo;
    }

    private static function isTipo(string $tipo): bool
    {
        return array_key_exists('tipo', $_SESSION) && $_SESSION['tipo'] == $tipo;
    }


    public static function setAdmin(): void
    {
        self::setTipo('admin');
    }

    public static function isAdmin(): bool
    {
        return self::isTipo('admin');
    }

    public static function destruir(): void
    {
        self::iniciar();
        unset($_SESSION['tipo']); // necessário? session_destroy já não faz isso?
        session_destroy();
    }

    public static function setTecnico(Tecnico $tecnico): void
    {
        $_SESSION['tipo']    = 'tecnico';
        $_SESSION['tecnico'] = serialize($tecnico);
    }

    public static function isTecnico(): bool
    {
        return self::isTipo('tecnico');
    }

    public static function getTecnico(): ?Tecnico
    {
        if (!self::isTipo('tecnico')) {
            return null;
        }
        return unserialize($_SESSION['tecnico']);
    }
}
