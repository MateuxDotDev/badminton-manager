<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use PDO;
use Exception;

class AtletaCompeticaoDuplaRepository
{

    private PDO $pdo;
    private bool $defineTransaction;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->defineTransaction = true;
    }

    public function cadastrarAtletaCompeticaoDupla(AtletaCompeticaoDupla $acd)
    {
        $this->begin();
        try {

            $sql = <<<SQL
                INSERT INTO atleta_competicao_sexo_dupla (
                    atleta_id,
                    competicao_id,
                    sexo_dupla
                )
                VALUES (
                    :atleta_id,
                    :competicao_id,
                    :sexo_dupla
                )
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'atleta_id' => $acd->atletaCompeticao()->atleta()->id(),
                'competicao_id' => $acd->atletaCompeticao()->competicao()->id(),
                'sexo_dupla' => $acd->tipoDupla()->value
            ]);
            $this->commit();

            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function defineTransaction(bool $define){
        $this->defineTransaction = $define;
    }

    private function begin(){
        if($this->defineTransaction){
            $this->pdo->beginTransaction();
        }
    }

    private function commit(){
        if($this->defineTransaction){
            $this->pdo->commit();
        }
    }

    private function rollback(){
        if($this->defineTransaction){
            $this->pdo->rollback();
        }
    }
}