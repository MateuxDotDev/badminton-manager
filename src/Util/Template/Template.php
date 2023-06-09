<?php

namespace App\Util\Template;

use App\Util\Environment\Environment;
use App\Util\General\UserSession;

class Template
{
    private static function put(string $key, string $content, string $subject): string
    {
        return str_replace("{{ $key }}", htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $subject);
    }

    public static function head(string $titulo): void
    {
        $headFile = __DIR__. "/common/head.html";
        $head = file_get_contents($headFile);
        $head = self::put("titulo_head", $titulo, $head);
        $head = self::put("file_time", filemtime($headFile), $head);
        echo $head;
    }

    public static function scripts(): void
    {
        $scriptsFile = __DIR__. "/common/scripts.html";
        $scripts = file_get_contents($scriptsFile);
        echo $scripts;
    }

    public static function footer(): void
    {
        $footer = file_get_contents(__DIR__. "/common/footer.html");
        $footer = self::put('ano', date('Y'), $footer);
        $footer = self::put('base_url', Environment::getBaseUrl(), $footer);
        echo $footer;
    }

    public static function naoAutorizado(): never
    {
        self::head("Não autorizado");
        echo file_get_contents(__DIR__. "/common/unauthorized.html");
        self::scripts();
        self::footer();
        die;
    }

    public static function nav(UserSession $session): void
    {
        if      ($session->isAdmin())   self::navAdmin();
        else if ($session->isTecnico()) self::navTecnicoLogado();
        else                            self::navTecnicoNaoLogado();
    }

    public static function navAdmin(): void
    {
        echo file_get_contents(__DIR__. "/common/adminNav.html");
    }

    public static function navTecnicoLogado(): void
    {
        $nav = file_get_contents(__DIR__. "/common/tecnicoNavLogado.html");
        $nav = self::put('nome', UserSession::obj()->getTecnico()->nomeCompleto(), $nav);
        echo $nav;
    }

    public static function navTecnicoNaoLogado(): void
    {
        echo file_get_contents(__DIR__. "/common/tecnicoNavNaoLogado.html");
    }

    public static function alerta(string $mensagem, string $nivel='danger')
    {
        static $template = <<<HTML
            <main class="container">
                <div class="alert alert-%s"> %s </div>
            </main>
        HTML;
        printf($template, $nivel, $mensagem);

        self::footer();
        die();
    }
}
