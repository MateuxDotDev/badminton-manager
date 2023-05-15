<?php

namespace App\Util\Template;

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
        $scripts = self::put("file_time", filemtime($scriptsFile), $scripts);
        echo $scripts;
    }

    public static function footer(): void
    {
        $footer = file_get_contents(__DIR__. "/common/footer.html");
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

    public static function navAdmin(): void
    {
        echo file_get_contents(__DIR__. "/common/adminNav.html");
    }

    public static function navTecnico(): void
    {
        echo file_get_contents(__DIR__. "/common/tecnicoNav.html");
    }
}
