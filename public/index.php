<?php

use App\Util\Template\Template;

require __DIR__ . '/../vendor/autoload.php';

Template::head('Página Inicial');
?>

<style>
    .container {
        max-width: 768px;
    }
</style>

<main>
    <header class="bg-success text-light py-5">
        <div class="container">
            <article class="row d-flex justify-content-center">
                <img style="max-width: 256px;" src="/assets/images/brand/light-full-logo.svg" alt="MatchPoint">
            </article>
            <article class="row text-center mt-5">
                <h1>Encontre facilmente duplas para seus atletas</h1>
                <h2 class="mt-3 fs-5">Com o MatchPoint, você tem mais tempo para focar no que importa!</h2>
            </article>
        </div>
    </header>

    <section class="container d-flex justify-content-center py-5 flex-column flex-md-row align-content-stretch">
        <a href="/tecnico/competicoes" class="btn btn-success me-md-5 mb-5 mb-md-0 p-3 fs-3">Ver competições</a>
        <a href="/login" class="btn btn-outline-success p-3 fs-3">Já sou cadastrado</a>
    </section>

    <section class="bg-success text-light py-5">
        <article class="container">
            <h3 class="mb-5">Conheça mais da plataforma</h3>
            <p>
                O MatchPoint é uma plataforma que visa facilitar a vida dos técnicos de badminton, permitindo que
                eles encontrem facilmente duplas para seus atletas, sem precisar ficar procurando em grupos de WhatsApp
                ou Facebook.
            </p>
        </article>
    </section>

    <section class="container py-5">
        <article class="text-end">
            <h3 class="mb-5">Mais facilidades para os técnicos</h3>
            <p>
                Aqui você pode visualizar as competições e atletas mesmo sem estar cadastrado.
                Caso não deseja realizar um cadastro, você ainda pode ter todas as funcionalidades
                do sistema através de comunicação via e-mail
            </p>
        </article>
    </section>
</main>

<?php
Template::scripts();
Template::footer();
