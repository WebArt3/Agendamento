<?php

class ModelUsers extends ModelRoot {

    public function get(int $id) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $dados = $stmt->fetch(PDO::FETCH_OBJ);

                unset($dados->password);

                return $dados;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar dados do usuário', 'db_model', 500, true);
        }

        return false;

    }

    public function getAdmin(int $id) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $dados = $stmt->fetch(PDO::FETCH_OBJ);

                unset($dados->password);

                return $dados;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar dados do admin', 'db_model', 500, true);
        }

        return false;

    }
    
}