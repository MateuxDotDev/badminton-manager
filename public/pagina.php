<?

// TODO acho que dá pra substituir esses $root por um $_SERVER['alguma coisa']

function htmlHeader($root, $titulo): void { ?>
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
    htmlLoadStyle("$root/style.css")
  ?>
</head>
<body>
<?
}

function htmlLoadScript($script): void {
  $time = filemtime($script);
  echo "<script src='$script?t=$time'></script>";
}

function htmlLoadStyle($style): void {
  $time = filemtime($style);
  echo "<link rel='stylesheet' href='$style?t=$time'>";
}

function htmlScripts($root): void {
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?
htmlLoadScript("$root/message.js");
htmlLoadScript("$root/init_tooltips.js");
}

function htmlFooter(): void { ?>
</body>
</html>
<? }

function htmlNaoAutorizado(): never { ?>
<div class="m-auto mt-5 text-center" style="width: 50%;">
  <h2>Você não está autorizado a visualizar essa página.</h2>
  <h4>Se não for redirecionado à tela de login, <a href="/login_tecnico">clique aqui</a>.</h4>
  <script>
    setTimeout(() => {
      location.assign('/login_tecnico');
    }, 3000);
  </script>
</div>
<?
htmlFooter();
die;
}