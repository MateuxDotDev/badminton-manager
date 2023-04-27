<?php

namespace App\Util\Template;

class Template
{
    public function put(string $key, string $content, string $subject): void
    {
        str_replace("{{ $key }}", htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $subject);
    }

    public function head(string $titulo): void
    {
        $head = file_get_contents(__DIR__. "/common/head.html");
        $this->put("titulo_head", $titulo, $head);
        $this->put("file_time", time(), $head);
        echo $head;
    }

    public function footer(): void
    {
        $footer = file_get_contents(__DIR__. "/common/footer.html");
        $this->put("file_time", time(), $footer);
        echo $footer;
    }

    public function naoAutorizado(): never
    {
        echo file_get_contents(__DIR__. "/common/unauthorized.html");
        $this->footer();
        die;
    }

    public function navAdmin(): void
    {
        echo file_get_contents(__DIR__. "/common/navAdmin.php");
    }
}
