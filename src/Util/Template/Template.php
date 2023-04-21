<?php

namespace App\Util\Template;

class Template
{
    private string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function put(string $key, string $content): void
    {
        str_replace("{{ $key }}", htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $html);
    }

    public function loadScript(string $script): void {
        $scriptFile = '/assets/js/' . $script;
        $scriptUrl  = '/js/' . $script;
        $t          = filemtime($scriptFile);
        echo "<script src='$scriptUrl?t=$t'></script>";
    }

    public function loadStyle(string $style): void
    {
        $styleFile = '/assets/css/' . $style;
        $styleUrl  = '/css/' . $style;
        $t         = filemtime($styleFile);
        echo "<link rel='stylesheet' href='$styleUrl?t=$t'/>";
    }


    public function addHead(string $titulo): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>MatchPoint | {{ titulo_head }}</title>
            <link
                href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
                rel="stylesheet"
                integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
                crossorigin="anonymous"
            />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
            <?
            $this->loadStyle("style.css");
            $this->loadStyle("swal_toast.css");
            ?>
        </head>
        <body>
        <?
    }

    public function footer(): void
    {
        $footer = file_get_contents(__DIR__ . '/common/footer.html');
        $this->put('footer', $footer);
    }

    public function scripts(): void
    {
        $scripts = file_get_contents(__DIR__ . '/common/scripts.html');
        $this->put('scripts', $scripts);
        $this->put('file_time', date("Y-m-d H:i:s"));
    }

    public function naoAutorizado(): void
    {
        $naoAutorizado = file_get_contents(__DIR__ . '/common/naoAutorizado.html');
        $this->put('nao_autorizado', $naoAutorizado);
        $this->footer();
    }

    public function addAdminNav(): void
    {
        $adminNav = file_get_contents(__DIR__ . '/common/adminNav.html');
        $this->put($adminNav, 'nav_admin');
    }
}
