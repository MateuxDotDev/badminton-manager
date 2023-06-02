<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use PDO;
use Exception;

class AtletaCompeticaoCategoriaRepository
{

    private PDO $pdo;
    private bool $defineTransaction;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->defineTransaction = true;
    }

    public function cadastrarAtletaCompeticaoCategoria(AtletaCompeticaoCategoria $aca): bool
    {
        $this->begin();
        try {
            $sql = <<<SQL
                INSERT INTO atleta_competicao_categoria (
                    atleta_id,
                    competicao_id,
                    categoria
                )
                VALUES (
                    :atleta_id,
                    :competicao_id,
                    :categoria
                )
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'atleta_id' => $aca->atletaCompeticao()->atleta()->id(),
                'competicao_id' => $aca->atletaCompeticao()->competicao()->id(),
                'categoria' => $aca->categoria()->descricao()
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