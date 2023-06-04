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

    public function cadastrarAtletaCompeticaoCategoria(AtletaCompeticaoCategoria $acc): bool
    {
        $this->begin();
        try {
            $sql = <<<SQL
                INSERT INTO atleta_competicao_categoria (
                    atleta_id,
                    competicao_id,
                    categoria_id
                )
                VALUES (
                    :atleta_id,
                    :competicao_id,
                    :categoria_id
                )
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'atleta_id' => $acc->atletaCompeticao()->atleta()->id(),
                'competicao_id' => $acc->atletaCompeticao()->competicao()->id(),
                'categoria_id' => $acc->categoria()->id()
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