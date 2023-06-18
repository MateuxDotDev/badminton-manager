<?php

namespace App\Util\General;

use App\Tecnico\Atleta\Atleta;
use \DateTimeInterface;
use \DateTime;
use App\Tecnico\Atleta\Sexo;

class Html
{
    public static function imgAtleta(string $src, int $tamanhoPx): string
    {
        // TODO ratio ratio-1x1 deu problema, verificar se dá pra deixar sem
        $src = '/assets/images/profile/' . $src;
        $tag = sprintf(
            '<div class="" style="max-width: %dpx">
                <img class="atleta-foto img-fluid rounded-circle profile-pic" src="%s"/>
            </div>',
            $tamanhoPx,
            $src,
        );
        return $tag;
    }

    public static function iconeSexo(Sexo $sexo): string
    {
        $masc = $sexo == Sexo::MASCULINO;
        return sprintf(
            '<i class="bi bi-gender-%s text-%s" title="%s"></i>',
            $masc ? 'male' : 'female',
            $masc ? 'blue' : 'pink',
            $masc ? 'Sexo masculino' : 'Sexo feminino',
        );
    }

    public static function campoDescricaoAtleta(Atleta $atleta)
    {
        $nome           = $atleta->nomeCompleto();
        $sexo           = $atleta->sexo();
        $dataNascimento = $atleta->dataNascimento();
        $idade          = $atleta->idade();

        // TODO abbr descrição nome

        static $template = '
            <div class="d-flex flex-column">
                <div class="d-flex flex-row gap-2 fs-5">
                    {{ nome }} {{ icone_sexo }}
                </div>
                <span>
                    {{ anos }} <small>({{ data_nascimento }})</small>
                </span>
            </div>
        ';
        return fill_template($template, [
            'nome'            => $nome,
            'icone_sexo'      => self::iconeSexo($sexo),
            'anos'            => pluralize($idade, 'ano', 'anos'),
            'data_nascimento' => Dates::formatDayBr($dataNascimento),
        ]);
    }

    public static function campo(string $label, string $value): string
    {
        return sprintf('
            <div class="d-flex flex-column">
                <small class="text-secondary">%s</small>
                <span>%s</span>
            </div>',
            $label,
            $value,
        );
    }

    public static function alerta(string $nivel, string $mensagem) {
        return sprintf('<div class="mt-3 alert alert-%s">%s</div>', $nivel, $mensagem);
    }
}