<?

function criarSessaoAdmin(): void {
  session_start();
  $_SESSION['tipo'] = 'admin';
}

function validaSessaoAdmin(): bool {
  session_start();
  return array_key_exists('tipo', $_SESSION) && $_SESSION['tipo'] === 'admin';
}