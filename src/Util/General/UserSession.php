<?php

namespace App\Util\General;

use App\Tecnico\Tecnico;

class UserSession
{
    private array $data;

    public static function obj(): UserSession
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return new self($_SESSION);
    }

    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    private function setTipo(string $tipo): void
    {
        $this->data['tipo'] = $tipo;
    }

    private function isTipo(string $tipo): bool
    {
        return array_key_exists('tipo', $this->data) && $this->data['tipo'] == $tipo;
    }

    public function setAdmin(): void
    {
        $this->setTipo('admin');
    }

    public function isAdmin(): bool
    {
        return $this->isTipo('admin');
    }

    public function setTecnico(Tecnico $tecnico): void
    {
        $this->data['tipo']    = 'tecnico';
        $this->data['tecnico'] = serialize($tecnico);
    }

    public function isTecnico(): bool
    {
        return $this->isTipo('tecnico');
    }

    public function getTecnico(): ?Tecnico
    {
        if (!$this->isTipo('tecnico')) {
            return null;
        }
        return unserialize($this->data['tecnico']);
    }

    // TODO: get metodo para pegar o tecnico via token
}
