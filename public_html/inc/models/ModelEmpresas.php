<?php

class ModelEmpresas extends ModelRoot {

    // cria empresa
    public function create(object $values) {

        try {

            if ($endereco_id = $this->model->endereco->create($values)) {

                // criptografia secundária
                $password = md5(base64_encode(md5($values->password)));
    
                $stmt = $this->pdo->prepare("INSERT INTO empresas (nome, email, telefone, password, cnpj) VALUES (:nome, :email, :telefone, :password, :cnpj)");
                $stmt = $this->db->bindArray($stmt, [
                    'nome' => $values->nome,
                    'email' => $values->email,
                    'telefone' => $values->telefone,
                    'password' => $password,
                    'cnpj' => $values->cnpj
                ]);
                $stmt->execute();
    
                if ($stmt->rowCount() > 0) {

                    $id = $this->pdo->lastInsertId();

                    $this->createConfig($id);
                    $this->createHorariosDefault($id);
    
                    return $this->get($id);
    
                }

            } else {
                $this->view->erro('Não foi possível localizar endereço', 'end_db_model', 500, true);
            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao criar empresa', 'db_model', 500, true);
        }

        return false;

    }

    // atualiza empresa
    public function update(object $values) {

        try {

            $this->model->endereco->update($values);

            $stmt = $this->pdo->prepare("UPDATE empresas SET nome = :nome, email = :email, telefone = :telefone WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $values->empresa,
                'nome' => $values->nome,
                'email' => $values->email,
                'telefone' => $values->telefone,
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $this->get($values->id);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao atualizar empresa', 'db_model', 500, true);
        }

        return false;

    }

    // atualiza senha
    public function updatePassword(object $values) {

        try {

            // criptografia secundária
            $password = md5(base64_encode(md5($values->password)));

            $stmt = $this->pdo->prepare("UPDATE empresas SET password = :password WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, [
                'id' => $values->empresa,
                'password' => $password
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao atualizar senha', 'db_model', 500, true);
        }

        return false;

    }

    // busca empresa
    public function get(int $id) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, ['id' => $id]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $empresa = $stmt->fetch(PDO::FETCH_OBJ);

                unset($empresa->password);

                $empresa->endereco = $this->model->endereco->get($empresa->endereco_id);
                $empresa->config = $this->getConfig($empresa->id);
                $empresa->horarios = $this->getHorarios($empresa->id);

                return $empresa;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar empresa', 'db_model', 500, true);
        }

        return false;

    }

    // busca todas as empresas
    public function getAll(string $search = '') {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE nome LIKE :search OR email LIKE :search OR telefone LIKE :search OR cnpj LIKE :search");
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $empresas = $stmt->fetchAll(PDO::FETCH_OBJ);

                foreach ($empresas as $key => $empresa) {

                    unset($empresa->password);

                    $empresa->endereco = $this->model->endereco->get($empresa->endereco_id);
                    $empresa->config = $this->getConfig($empresa->id);
                    $empresa->horarios = $this->getHorarios($empresa->id);

                }

                return $empresas;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar empresas', 'db_model', 500, true);
        }

        return false;

    }

    // busca empresa por cnpj
    public function getByCnpj(string $cnpj) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE cnpj = :cnpj");
            $stmt = $this->db->bindArray($stmt, ['cnpj' => $cnpj]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $empresa = $stmt->fetch(PDO::FETCH_OBJ);

                $empresa->endereco = $this->model->endereco->get($empresa->endereco_id);

                return $empresa;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar empresa', 'db_model', 500, true);
        }

        return false;

    }

    // bloqueia empresa
    public function block(int $id) {

        try {

            $stmt = $this->pdo->prepare("UPDATE empresas SET bloqueado = 0 WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, ['id' => $id]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao bloquear empresa', 'db_model', 500, true);
        }

        return false;

    }

    // desbloqueia empresa
    public function unblock(int $id) {

        try {

            $stmt = $this->pdo->prepare("UPDATE empresas SET bloqueado = 1 WHERE id = :id");
            $stmt = $this->db->bindArray($stmt, ['id' => $id]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao desbloquear empresa', 'db_model', 500, true);
        }

        return false;

    }

    /* ======================================================
    *            Configurações
    ====================================================== */

    // cria configurações default
    public function createConfig(int $empresa) {

        try {

            $stmt = $this->pdo->prepare("INSERT INTO config (empresas_id) VALUES (:empresa_id)");
            $stmt = $this->db->bindArray($stmt, ['empresa_id' => $empresa]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao criar configurações', 'db_model', 500, true);
        }

        return false;

    }

    // atualiza configurações
    public function updateConfig(object $values) {

        try {

            $stmt = $this->pdo->prepare("UPDATE config SET tempo = :tempo, quantidade = :quantidade, limite_alteracao = :limite_alteracao WHERE empresas_id = :empresa_id");
            $stmt = $this->db->bindArray($stmt, [
                'empresa_id' => $values->empresa,
                'tempo' => $values->tempo,
                'quantidade' => $values->quantidade,
                'limite_alteracao' => $values->limite_alteracao
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao atualizar configurações', 'db_model', 500, true);
        }

        return false;

    }

    // busca configurações
    public function getConfig(int $empresa) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM config WHERE empresas_id = :empresa_id");
            $stmt = $this->db->bindArray($stmt, ['empresa_id' => $empresa]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $stmt->fetch(PDO::FETCH_OBJ);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar configurações', 'db_model', 500, true);
        }

        return false;

    }

    /* ======================================================
    *            Horarios
    ====================================================== */

    // cria horarios de funcionamento default
    public function createHorariosDefault(int $empresa) {

        /*
            Dias da semana: 1 - segunda, 2 - terça, 3 - quarta, 4 - quinta, 5 - sexta, 6 - sábado, 7 - domingo
        */

        try {

            $stmt = $this->pdo->prepare(
                "INSERT INTO config_horario 
                    (empresas_id, dia_semana, hora_inicial, hora_final) 
                VALUES 
                    (:empresa_id, 1, '08:00', '18:00'),
                    (:empresa_id, 2, '08:00', '18:00'),
                    (:empresa_id, 3, '08:00', '18:00'),
                    (:empresa_id, 4, '08:00', '18:00'),
                    (:empresa_id, 5, '08:00', '18:00'),"
            );
            $stmt = $this->db->bindArray($stmt, [
                'empresa_id' => $empresa
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao criar horarios', 'db_model', 500, true);
        }

        return false;
    
    }

    // atualiza horario de funcionamento
    public function updateHorario(object $values) {

        try {

            $stmt = $this->pdo->prepare("UPDATE config_horario SET hora_inicial = :hora_inicial, hora_final = :hora_final WHERE horario_id = :horario_id");
            $stmt = $this->db->bindArray($stmt, [
                'horario_id' => $values->horario,
                'dia_semana' => $values->dia,
                'hora_inicial' => $values->hora_inicial,
                'hora_final' => $values->hora_final
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao atualizar horario', 'db_model', 500, true);
        }

        return false;

    }

    // adiciona horario de funcionamento
    public function addHorario(object $values) {

        try {

            $stmt = $this->pdo->prepare("INSERT INTO config_horario (empresas_id, dia_semana, hora_inicial, hora_final) VALUES (:empresa_id, :dia_semana, :hora_inicial, :hora_final)");
            $stmt = $this->db->bindArray($stmt, [
                'empresa_id' => $values->empresa,
                'dia_semana' => $values->dia,
                'hora_inicial' => $values->hora_inicial,
                'hora_final' => $values->hora_final
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao adicionar horario', 'db_model', 500, true);
        }

        return false;

    }

    // busca horario de funcionamento
    public function getHorario(int $horario) {

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM config_horario WHERE horario_id = :horario_id");
            $stmt = $this->db->bindArray($stmt, ['horario_id' => $horario]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $stmt->fetch(PDO::FETCH_OBJ);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar horario', 'db_model', 500, true);
        }

        return false;

    }

    // busca horarios de funcionamento
    public function getHorarios(int $empresa, int $dia = null) {

        try {

            $add = '';
            if ($dia) {
                $add = ' AND dia_semana = :dia_semana';
            }

            $stmt = $this->pdo->prepare("SELECT * FROM config_horario WHERE empresas_id = :empresa_id $add");
            $stmt = $this->db->bindArray($stmt, [
                'empresa_id' => $empresa,
                'dia_semana:optional' => $dia
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return $stmt->fetchAll(PDO::FETCH_OBJ);

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao buscar horarios', 'db_model', 500, true);
        }

        return false;

    }

    // deleta horario de funcionamento
    public function deleteHorario(int $horario) {

        try {

            $stmt = $this->pdo->prepare("DELETE FROM config_horario WHERE horario_id = :horario_id");
            $stmt = $this->db->bindArray($stmt, [
                'horario_id' => $horario
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return true;

            }

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao deletar horario', 'db_model', 500, true);
        }

        return false;

    }

}