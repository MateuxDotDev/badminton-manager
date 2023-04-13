<?php

namespace App;

class Pagina {

  private readonly string $root;

  public function __construct(string $root) {
    $this->root = $root;
  }

  public function loadScript(string $script): void {
    $root = $this->root;
    $path = "$root/js/$script";
    $time = filemtime($path);
    echo "<script src='$path?t=$time'></script>";
  }

  function loadStyle(string $style): void {
    $root = $this->root;
    $path = "$root/css/$style";
    $time = filemtime($path);
    echo "<link rel='stylesheet' href='$path?t=$time'>";
  }


  public function header(string $titulo): void
  {
  ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= $titulo ?></title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
      <?
        $this->loadStyle("style.css");
        $this->loadStyle("swal_toast.css");
      ?>
    </head>
    <body>
  <?
  }

  public function footer(): void { ?>
    </body>
    </html>
    <?
  }

  public function scripts(): void
  {
  ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?
      $this->loadScript("swal_toast.js");
      $this->loadScript("alertas.js");
  }

  public function naoAutorizado(): never
  {
  ?>
    <div class="m-auto mt-5 text-center" style="width: 50%;">
      <h2>Você não está autorizado a visualizar essa página.</h2>
      <h4>Se não for redirecionado à tela de login, <a href="/">clique aqui</a>.</h4>
      <script>
        setTimeout(() => {
          location.assign('/');
        }, 3000);
      </script>
    </div>
    <?
    $this->footer();
    die;
  }
}