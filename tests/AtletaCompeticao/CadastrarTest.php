<?php

use PHPUnit\Framework\TestCase;
use App\AtletaCompeticao\Cadastrar;
use App\Admin\Competicoes\CompeticaoRepository;
use App\Admin\Competicoes\Competicao;
use App\Util\General\Dates;
use \PDO;
use \PDOStatement;

//TODO Teste unitário do cadastro de atleta numa competição

class CadastrarTest extends TestCase
{

    private Cadastrar $cadastrar;

    protected function setUp(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $pdo->method('query')
            ->willReturn($stmt);
        $competicaoRepo = new CompeticaoRepository($pdo);

        $date = Dates::parseDay('2023-11-11');
        $competicao = (new Competicao)
            ->setNome('BitLock de Badminton')
            ->setPrazo($date)
            ->setDescricao('Somente uma equipe irá sobrar nesta batalha mortal!!!');

        
    }

    //TODO Criar caso de teste para a consulta de atletas que não estão presentes na competição
    //Somente devemos retornar atletas que não estão presentes na competição e que não estão presentes em uma competição na mesma data?
}