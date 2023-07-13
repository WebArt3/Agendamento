<?php

class ModelAgenda extends ModelRoot {

    // cria horario
    public function create($values) {

        try {

            $guid = guidv4();

            $stmt = $this->pdo->prepare("INSERT INTO horarios (inicio, fim, `guid`, empresas_id) VALUES (:inicio, :fim, :guid, :empresas_id)");
            $stmt = $this->db->bindArray($stmt, [
                'inicio' => $values->inicio,
                'fim' => $values->fim,
                'guid' => $guid,
                'empresas_id' => $values->empresas_id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $id = $this->pdo->lastInsertId();

                $this->createCliente($values);

                return $this->get($id);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao criar horario', 'db_model', 500, true);
        }

        return false;

    }

    // cria cliente
    public function createCliente($values) {

        try {

            if ($values->rua) {
                $values->endereco_id = $this->model->endereco->create($values);
            } else {
                $values->endereco_id = null;
            }

            $stmt = $this->pdo->prepare("INSERT INTO clientes (nome, telefone, cpf, endereco_id, horarios_id) VALUES (:nome, :telefone, :cpf, :endereco_id, :horarios_id)");
            $stmt = $this->db->bindArray($stmt, [
                'nome' => $values->nome,
                'telefone' => $values->telefone,
                'cpf' => $values->cpf,
                'endereco_id' => $values->endereco_id,
                'horarios_id' => $values->horarios_id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->pdo->lastInsertId();

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao criar cliente', 'db_model', 500, true);
        }

        return false;

    }

    // atualiza horario
    public function update($values) {

        try {

            $stmt = $this->pdo->prepare("UPDATE horarios SET inicio = :inicio, fim = :fim WHERE `guid` = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $values->guid,
                'inicio' => $values->inicio,
                'fim' => $values->fim
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->get($values->guid);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao atualizar horario', 'db_model', 500, true);
        }

        return false;

    }

    // solicitar cancelamento de horario
    public function cancel($values) {

        try {

            $stmt = $this->pdo->prepare("UPDATE horarios SET solicitado_cancelamento = 1 WHERE `guid` = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $values->guid
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->get($values->guid);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao solicitar cancelamento de horario', 'db_model', 500, true);
        }

        return false;

    }

    // cancelar horario
    public function cancelar($values) {

        try {

            $stmt = $this->pdo->prepare("UPDATE horarios SET cancelado = 1 WHERE `guid` = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $values->guid
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->get($values->guid);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao cancelar horario', 'db_model', 500, true);
        }

        return false;

    }

}