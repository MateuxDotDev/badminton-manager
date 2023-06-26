<?php

namespace App\Util\General;

use App\Tecnico\Tecnico;
use App\Util\Services\TokenService\TokenService;
use Exception;

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

    public function initFromToken(): void
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        if ($token === null) {
            return;
        }

        try {
            $decodedToken = (new TokenService())->decodeToken($token);
            if ($decodedToken->tecnico != null) {
                $this->data['tipo'] = 'tecnico';
                $this->data['tecnico'] = json_decode($decodedToken->tecnico);
            }
        } catch (Exception $ignored) {
            // Ignored
        }
    }

    public function __construct(array &$data)
    {
        $this->data = &$data;
        $this->initFromToken();
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
