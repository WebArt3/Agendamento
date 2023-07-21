<?php

class ModelEndereco extends ModelRoot {

    //-------------------------------------------------------------------
    //   ENDEREÇO 
    //-------------------------------------------------------------------

    // criar um novo endereço
    public function create(object $values) {

        try {

            $stmt = $this->pdo->prepare("INSERT INTO endereco (cep, rua, numero, bairro, cidade, uf, complemento) VALUES (:cep, :rua, :numero, :bairro, :cidade, :uf, :complemento)");
            $stmt = $this->db->bindArray($stmt, [
                'cep' => $values->cep,
                'rua' => $values->rua,
                'numero' => $values->numero,
                'bairro' => $values->bairro,
                'cidade' => $values->cidade,
                'uf' => $values->uf,
                'complemento' => $values->complemento
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->pdo->lastInsertId();

            }

            return false;

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao adicionar endereço', 'db_model', 500, true);
        }

        return false;

    }

    // atualizar um endereço
    public function update(object $values) {

        try {

            $stmt = $this->pdo->prepare("UPDATE endereco SET cep = :cep, rua = :rua, numero = :numero, bairro = :bairro, cidade = :cidade, uf = :uf, complemento = :complemento WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $values->endereco_id,
                'cep' => $values->cep,
                'rua' => $values->rua,
                'numero' => $values->numero,
                'bairro' => $values->bairro,
                'cidade' => $values->cidade,
                'uf' => $values->uf,
                'complemento' => $values->complemento
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao atualizar endereço', 'db_model', 500, true);
        }

        return false;

    }

    // deletar um endereço
    public function delete(int $id) {

        try {

            $stmt = $this->pdo->prepare("DELETE FROM endereco WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao deletar endereço', 'db_model', 500, true);
        }

        return false;

    }

    // buscar um endereço
    public function get(int $id) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM endereco WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $stmt->fetch(PDO::FETCH_OBJ);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar endereço', 'db_model', 500, true);
        }

        return false;

    }

}