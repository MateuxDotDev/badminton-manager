<?php
// dump and die
function dd(mixed $s): never {
  echo '<pre>';
  var_dump($s);
  echo '</pre>';
  die;
}

// dump and die versão json (mais fácil de ler pra objetos)
function jdd(mixed $s): never {
  echo '<pre>';
  echo json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  echo '</pre>';
  die;
}