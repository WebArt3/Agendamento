<?php

class ModelAgenda extends ModelRoot {

    // cria horario
    public function create($values) {

        try {

            $guid = guidv4();

            if ($cliente = $this->createCliente($values)) {

                $stmt = $this->pdo->prepare("INSERT INTO horarios (inicio, fim, `guid`, empresas_id, cliente_id) VALUES (:inicio, :fim, :guid, :empresas_id, :cliente_id)");
                $stmt = $this->db->bindArray($stmt, [
                    'inicio' => $values->inicio,
                    'fim' => $values->fim,
                    'guid' => $guid,
                    'empresas_id' => $values->empresa,
                    "cliente_id" => $cliente
                ]);
                $stmt->execute();
    
                if ($stmt->rowCount() > 0) {
    
                    return $this->get($guid);
    
                }
                
            } else {
                $this->view->erro('Erro ao criar cliente', 'db_model_cli', 500, true);
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

            $stmt = $this->pdo->prepare("INSERT INTO cliente (nome, telefone, cpf, endereco_id) VALUES (:nome, :telefone, :cpf, :endereco_id)");
            $stmt = $this->db->bindArray($stmt, [
                'nome' => $values->nome,
                'telefone' => $values->telefone,
                'cpf' => $values->cpf,
                'endereco_id' => $values->endereco_id
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

    // buscar horario
    public function get(string $id) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM horarios WHERE `guid` = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $horario = $stmt->fetch(PDO::FETCH_OBJ);

                $horario->empresa = $this->model->empresas->get($horario->empresas_id);
                $horario->cliente = $this->getCliente($horario->cliente_id);

                return $horario;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar horario', 'db_model', 500, true);
        }

        return false;

    }

    // buscar cliente
    public function getCliente($id) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM cliente WHERE `id` = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $id
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $cliente = $stmt->fetch(PDO::FETCH_OBJ);

                $cliente->endereco = $this->model->endereco->get($cliente->endereco_id);

                return $cliente;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar cliente', 'db_model', 500, true);
        }

        return false;

    }

    // buscar horarios
    public function getAll($values) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM horarios WHERE `inicio` >= :inicio AND `fim` <= :fim AND cancelado = 0 AND empresas_id = :empresa ORDER BY `inicio` ASC");
            $stmt = $this->db->bindArray($stmt, [
                'inicio' => $values->inicio,
                'fim' => $values->fim,
                'empresa' => $values->empresa
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $horarios = $stmt->fetchAll(PDO::FETCH_OBJ);

                foreach ($horarios as $key => $horario) {

                    $horarios[$key]->cliente = $this->getCliente($horario->cliente_id);
                    $horarios[$key]->empresa = $this->model->empresas->get($horario->empresas_id);

                }

                return $horarios;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar horarios', 'db_model', 500, true);
        }

        return false;

    }

    // verificar disponibilidade de horario
    public function check($values) {

        try {

            $stmt = $this->pdo->prepare(
                "SELECT * FROM horarios 
                WHERE 
                    (
                        (`inicio` >= :inicio AND `fim` < :fim) OR
                        (`inicio` <= :inicio AND `fim` >= :inicio) OR
                        (`inicio` <= :fim AND `fim` >= :fim)
                    ) AND
                    cancelado = 0 AND
                    empresas_id = :empresa"
            );
            $stmt = $this->db->bindArray($stmt, [
                'inicio' => $values->inicio,
                'fim' => $values->fim,
                'empresa' => $values->empresa
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $stmt->fetchAll(PDO::FETCH_OBJ);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao verificar disponibilidade de horario', 'db_model', 500, true);
        }

        return false;

    }

}