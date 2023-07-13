<?php

class ModelExemple extends ModelRoot {

    // cria algo
    public function create($values) {

        try {

            $stmt = $this->pdo->prepare("INSERT INTO algo (nome) VALUES (:nome)");
            $stmt = $this->db->bindArray($stmt, [
                'nome' => $values->nome,
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->get($this->pdo->lastInsertId());

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao criar algo', 'db_model', 500, true);
        }

        return false;

    }

}