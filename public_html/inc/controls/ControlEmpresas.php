<?php

class ControlEmpresas extends ControlRoot{

    //=============================================
    //  Controle de empresas
    //=============================================

    // cria empresa
    public function create() {

        $user = $this->control->login->checkAdmin();

        $values = Validation::filter($this->app->post, [
            'nome' => 'string',
            'email' => 'string',
            'telefone' => 'string',
            'cnpj' => 'string',
            'password' => 'string',

            'cep' => 'string',
            'rua' => 'string',
            'numero' => 'string',
            'bairro' => 'string',
            'cidade' => 'string',
            'uf' => 'string',
        ]);

        $values_optional = Validation::optional($this->app->post, [
            'complemento' => 'string',
        ]);

        $values = (Object) array_merge($values, $values_optional);

        $values->telefone = Validation::regexNum($values->telefone);
        $values->cnpj = Validation::limpaCnpj($values->cnpj);

        // valida email
        if (Validation::validaEmail($values->email)) {
            $values->email = strtolower($values->email);
        } else {
            return $this->view->erro("Email inválido", "email_error");
        }

        // valida senha
        if (strlen($values->password) < 32) {
            return $this->view->erro("Senha não criptografada", "password_error");
        }

        // valida cnpj
        if ($this->model->empresas->getByCnpj($values->cnpj)) {
            return $this->view->erro("CNPJ já cadastrado", "cnpj_error");
        }

        if ($empresa = $this->model->empresas->create($values)) {
            return $this->view->send($empresa);
        }

        return $this->view->erro("Erro ao criar empresa", "create_error", 500);

    }

    // buscar empresa
    public function get() {

        $user = $this->control->login->checkToken();

        if (isset($user->admin) && $user->admin) {
            $empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $empresa = $user->id;
        }

        if ($empresa = $this->model->empresas->get($empresa)) {
            return $this->view->send($empresa);
        }

        return $this->view->erro("Erro ao buscar empresa", "get_error", 500);

    }

    // buscar empresas
    public function getAll() {

        $user = $this->control->login->checkAdmin();

        $search = Validation::optional($this->app->post, ['search' => 'string'])["search"];

        if ($empresas = $this->model->empresas->getAll($search)) {
            return $this->view->send($empresas);
        }

        return $this->view->send([]);

    }

    // atualizar empresa
    public function update() {

        $user = $this->control->login->checkToken();

        $values = Validation::filter($this->app->post, [
            'nome' => 'string',
            'email' => 'string',
            'telefone' => 'string',

            'cep' => 'string',
            'rua' => 'string',
            'numero' => 'string',
            'bairro' => 'string',
            'cidade' => 'string',
            'uf' => 'string',
        ]);

        $values_optional = Validation::optional($this->app->post, [
            'complemento' => 'string',
        ]);

        $values = (Object) array_merge($values, $values_optional);

        $values->telefone = Validation::regexNum($values->telefone);

        if (isset($user->admin) && $user->admin) {
            $values->empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $values->empresa = $user->id;
        }

        // valida email
        if (Validation::validaEmail($values->email)) {
            $values->email = strtolower($values->email);
        } else {
            return $this->view->erro("Email inválido", "email_error");
        }

        if ($emp =$this->model->empresas->get($values->empresa)) {

            $values->endereco_id = $emp->endereco_id;

            if ($empresa = $this->model->empresas->update($values)) {
                return $this->view->send($empresa);
            }

            return $this->view->erro("Erro ao atualizar empresa", "update_error", 500);
        }

        return $this->view->erro("Empresa não encontrada", "empresa_not_found");

    }

    // atualizar senha
    public function updatePassword() {

        $user = $this->control->login->checkAdmin();

        $values = (object) Validation::filter($this->app->post, [
            'empresa' => 'int',
            'password' => 'string',
        ]);

        if (strlen($values->password) < 32) {
            return $this->view->erro("Senha não criptografada", "password_error");
        }

        if ($empresa = $this->model->empresas->updatePassword($values)) {
            return $this->view->sucesso();
        }

        return $this->view->erro("Erro ao atualizar senha", "update_password_error", 500);

    }

    // bloquear empresa
    public function block() {

        $user = $this->control->login->checkAdmin();

        $values = (object) Validation::filter($this->app->post, [
            'empresa' => 'int'
        ]);

        if ($this->model->empresas->block($values->empresa)) {
            return $this->view->sucesso();
        }

        return $this->view->erro("Erro ao bloquear empresa", "block_error", 500);

    }

    // desbloquear empresa
    public function unblock() {

        $user = $this->control->login->checkAdmin();

        $values = (object) Validation::filter($this->app->post, [
            'empresa' => 'int'
        ]);

        if ($this->model->empresas->unblock($values->empresa)) {
            return $this->view->sucesso();
        }

        return $this->view->erro("Erro ao desbloquear empresa", "unblock_error", 500);

    }

    /* =============================
    *      Configurações
    * ============================= */

    // atualizar configurações
    public function updateConfig() {

        $user = $this->control->login->checkToken();

        $values = (object) Validation::filter($this->app->post, [
            'tempo' => 'int',
            'quantidade' => 'int',
            'limite_alteracao' => 'int',
        ]);

        if (isset($user->admin) && $user->admin) {
            $values->empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $values->empresa = $user->id;
        }

        if ($empresa = $this->model->empresas->updateConfig($values)) {
            return $this->view->sucesso();
        }

        return $this->view->erro("Erro ao atualizar configuração", "update_config_error", 500);

    }

    // buscar configurações
    public function getConfig() {

        $user = $this->control->login->checkToken();

        if (isset($user->admin) && $user->admin) {
            $empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $empresa = $user->id;
        }

        if ($config = $this->model->empresas->getConfig($empresa)) {
            return $this->view->send($config);
        }

        return $this->view->erro("Erro ao buscar configuração", "get_config_error", 500);

    }

    /* =============================
    *      Horarios
    * ============================= */

    // atualizar horario
    public function updateHorario() {

        /*
            Dias da semana: 1 - segunda, 2 - terça, 3 - quarta, 4 - quinta, 5 - sexta, 6 - sábado, 7 - domingo
        */

        $user = $this->control->login->checkToken();

        $values = (object) Validation::filter($this->app->post, [
            'horario' => 'int',
            'dia' => 'int',
            'hora_inicial' => 'string',
            'hora_final' => 'string',
        ]);

        // verifica se horario pertence a empresa
        if ($horario = $this->model->empresas->getHorario($values->horario)) {
            if (!(isset($user->admin) && $user->admin) && $horario->empresas_id != $user->id) {
                return $this->view->erro("Horario não pertence a empresa", "horario_not_found");
            }
        } else {
            return $this->view->erro("Horario não encontrado", "horario_not_found");
        }

        // verifica se horario já existe ou esta em um outro intervalo no mesmo dia
        if ($horarios = $this->model->empresas->getHorarios($values->empresa, $values->dia)) {
            
            foreach ($horarios as $horario) {

                $horario->hora_inicial = strtotime($horario->hora_inicial);
                $horario->hora_final = strtotime($horario->hora_final);

                if (
                        ($values->hora_inicial >= $horario->hora_inicial && $values->hora_inicial <= $horario->hora_final) ||
                        ($values->hora_final >= $horario->hora_inicial && $values->hora_final <= $horario->hora_final) ||
                        ($values->hora_inicial <= $horario->hora_inicial && $values->hora_final >= $horario->hora_inicial)
                    ) {
                    return $this->view->erro("Horario já cadastrado", "horario_error");
                }
            }

        }

        if ($empresa = $this->model->empresas->updateHorario($values)) {
            return $this->view->send($empresa);
        }

        return $this->view->erro("Erro ao atualizar horario", "update_horario_error", 500);

    }

    // buscar horarios
    public function getHorarios() {

        $user = $this->control->login->checkToken();

        if (isset($user->admin) && $user->admin) {
            $empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $empresa = $user->id;
        }

        if ($horarios = $this->model->empresas->getHorarios($empresa)) {
            return $this->view->send($horarios);
        }

        return $this->view->send([]);

    }

    // adicionar horario
    public function addHorario() {

        $user = $this->control->login->checkToken();

        $values = (object) Validation::filter($this->app->post, [
            'dia' => 'int',
            'hora_inicial' => 'string',
            'hora_final' => 'string',
        ]);

        if (isset($user->admin) && $user->admin) {
            $values->empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $values->empresa = $user->id;
        }

        // verifica se horario já existe ou esta em um outro intervalo no mesmo dia
        if ($horarios = $this->model->empresas->getHorarios($values->empresa, $values->dia)) {
            
            foreach ($horarios as $horario) {

                $horario->hora_inicial = strtotime($horario->hora_inicial);
                $horario->hora_final = strtotime($horario->hora_final);

                if (
                        ($values->hora_inicial >= $horario->hora_inicial && $values->hora_inicial <= $horario->hora_final) ||
                        ($values->hora_final >= $horario->hora_inicial && $values->hora_final <= $horario->hora_final) ||
                        ($values->hora_inicial <= $horario->hora_inicial && $values->hora_final >= $horario->hora_inicial)
                    ) {
                    return $this->view->erro("Horario já cadastrado", "horario_error");
                }
            }

        }

        if ($empresa = $this->model->empresas->addHorario($values)) {
            return $this->view->send($empresa);
        }

        return $this->view->erro("Erro ao adicionar horario", "add_horario_error", 500);

    }

    // deletar horario
    public function deleteHorario() {

        $user = $this->control->login->checkToken();

        $values = (object) Validation::filter($this->app->post, [
            'horario' => 'int',
        ]);

        if ($empresa = $this->model->empresas->deleteHorario($values->horario)) {
            return $this->view->send($empresa);
        }

        return $this->view->erro("Erro ao deletar horario", "delete_horario_error", 500);

    }

}