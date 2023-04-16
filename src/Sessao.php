<?php

namespace App;

class Sessao {

  public static function iniciar(): void {
    session_start();
  }

  public static function setAdmin(): void {
    $_SESSION['tipo'] = 'admin';
  }

  public static function isAdmin(): bool {
    return array_key_exists('tipo', $_SESSION) && $_SESSION['tipo'] === 'admin';
  }

  public static function destruir(): void {
    session_destroy();
  }
}