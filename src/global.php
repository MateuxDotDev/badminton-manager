<?php

function dump($x): void {
  echo '<pre>';
  var_dump($x);
  echo '</pre>';
}

function jdump($x): void {
  echo '<pre>';
  echo json_encode($x, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  echo '</pre>';
}

function dd(mixed $x): never {
  dump($x);
  die;
}

function jdd(mixed $x): never {
  jdump($x);
  die;
}