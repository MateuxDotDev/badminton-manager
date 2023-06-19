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

        static $template = '
            <div class="d-flex flex-column">
                <div class="d-flex flex-row gap-2 fs-5">
                    <span class="{{ contem_tooltip }}" {{ title }} data-bs-toggle="tooltip">
                        {{ nome }}
                    </span>
                    {{ icone_sexo }}
                </div>
                <span>
                    {{ anos }} <small>({{ data_nascimento }})</small>
                </span>
            </div>
        ';

        $info = addslashes(htmlspecialchars(trim($atleta->informacoesAdicionais())));

        return fill_template($template, [
            'nome'            => $nome,
            'icone_sexo'      => self::iconeSexo($sexo),
            'anos'            => pluralize($idade, 'ano', 'anos'),
            'data_nascimento' => Dates::formatDayBr($dataNascimento),
            'contem_tooltip'  => empty($info) ? '' : 'contem-tooltip',
            'title'           => empty($info) ? '' : 'title="'.$info.'"',
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


    public static function campoAbbr(string $label, string $value, ?string $abbr): string
    {
        $abbr = addslashes(htmlspecialchars(trim($abbr)));
        $contemTooltip = empty($abbr) ? '' : 'contem-tooltip';
        $title         = empty($abbr) ? '' : 'title="'.$abbr.'"';

        static $template = '
            <div class="d-flex flex-column">
                <small class="text-secondary">{{ label }}</small>
                <span data-bs-toggle="tooltip" class="{{ contem_tooltip }}" {{ title }}>{{ value }}</span>
            </div>
        ';

        return fill_template($template, [
            'title'          => $title,
            'label'          => $label,
            'contem_tooltip' => $contemTooltip,
            'value'          => $value,
        ]);
    }

    public static function alerta(string $nivel, string $mensagem, array $attrMap=[])
    {
        $class = [
            'mt-3 alert alert-'.$nivel
        ];

        $attrs = [];
        foreach ($attrMap as $k => $v) {
            if ($k == 'class') {
                $class[] = $v;
            } else {
                $attrs[] = "$k='$v'";
            }
        }

        $attrs[] = 'class="'.implode(' ', $class).'"';

        $attrString = implode(' ', $attrs);

        return sprintf(
            '<div %s>%s</div>',
            $attrString,
            $mensagem
        );
    }
}