<?php

namespace App\Util\Template;

class Template
{
    private string $html;

    private string $title;

    public function __construct(string $html, string $title)
    {
        $this->html = $html;
        $this->title = $title;
    }

    public function put(string $key, string $content): void
    {
        $this->html = str_replace("{{ $key }}", htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $html);
    }

    public function addHead(string $titulo): void
    {
        $head = file_get_contents(__DIR__ . '/common/head.html');
        $this->put('head', $head);
        $this->put('titulo_head', $titulo);
    }

    public function addFooter(): void
    {
        $footer = file_get_contents(__DIR__ . '/common/footer.html');
        $this->put('footer', $footer);
    }

    public function showUnauthorized(string $titulo): void
    {
        $this->html = file_get_contents(__DIR__ . '/common/naoAutorizado.html');
        $this->addHead($titulo);
        $this->addFooter();

    }

    public function addAdminNav(): void
    {
        $adminNav = file_get_contents(__DIR__ . '/common/adminNav.html');
        $this->put($adminNav, 'nav_admin');
    }

    public function render(): string
    {
        $this->addHead($this->title);
        $this->addFooter();
        $this->put('file_time', date("Y-m-d H:i:s"));
        return preg_replace($pattern, '', $this->html);
    }
}
