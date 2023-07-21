<?php

class ControlAgenda extends ControlRoot{

    //=============================================
    //  Controle de agendamentos
    //=============================================

    // cria checkout de horario
    public function checkout() {

        $values = Validation::filter($this->app->post, [
            'inicio' => 'string',
            'empresa' => 'int',

            'nome' => 'string',
            'telefone' => 'string',
            'cpf' => 'string',
        ]);

        $values_optional = Validation::optional($this->app->post, [
            'rua' => 'string',
            'numero' => 'string',
            'bairro' => 'string',
            'cidade' => 'string',
            'uf' => 'string',
            'cep' => 'string',
            'complemento' => 'string',
        ]);

        $values = (Object) array_merge($values, $values_optional);

        // valida campos
        $values->telefone = Validation::regexNum($values->telefone);
        $values->cpf = Validation::limpaCpf($values->cpf);

        if ($values->cep) {
            $values->cep = Validation::regexNum($values->cep);
        }

        // get empresa
        if ($empresa = $this->model->empresas->get($values->empresa)) {

            // verifica configurações
            if($configuracao = $this->model->agenda->getConfig($empresa->id)) {

                // adiciona fim
                $values->fim = date("Y-m-d H:i:s", strtotime("+{$configuracao->tempo} hours", strtotime($values->inicio)));

                // check horario
                if ($horarios = $this->model->agenda->check($values)) {

                    if (count($horarios) >= $configuracao->quantidade) {
                        return $this->view->erro("Horario não disponivel", "agenda_cheia", 404);
                    }

                }

                // cria horario
                if ($horario = $this->model->agenda->create($values)) {
                    return $this->view->send($horario);
                }
        
                return $this->view->erro("Erro ao criar horario", "create_error", 500);

            }

            return $this->view->erro("Configuração não encontrada para empresa", "not_found_config", 404);

        }

        return $this->view->erro("Empresa não encontrada", "not_found_empresa", 404);
    }

    // atualiza horario (apenas se for 1 dia antes) (apenas inicio)
    public function update() {

        $values = Validation::filter($this->app->post, [
            'guid' => 'int',
            'inicio' => 'string',
        ]);

        // get horario
        if ($horario = $this->model->agenda->get($values->guid)) {

            // verifica configurações
            if($configuracao = $this->model->agenda->getConfig($horario->empresas_id)) {

                // verifica se agora é pelomenos um dia antes do horario
                if (strtotime($horario->inicio) > strtotime("-{$configuracao->limite_alteracao} day")) {
                    return $this->view->erro("Horario não pode ser alterado", "very_recent", 404);
                }

                // adiciona fim
                $values->fim = date("Y-m-d H:i:s", strtotime("+{$configuracao->tempo} hours", strtotime($values->inicio)));

                // check horario
                if ($horarios = $this->model->agenda->check($values)) {

                    if (count($horarios) >= $configuracao->quantidade) {
                        return $this->view->erro("Horario não disponivel", "agenda_cheia", 404);
                    }

                }

                if ($horario = $this->model->agenda->update($values)) {
                    return $this->view->send($horario);
                }
        
                return $this->view->erro("Erro ao atualizar horario", "update_error", 500);

            }

            return $this->view->erro("Configuração não encontrada para empresa", "not_found_config", 404);

        }

        return $this->view->erro("Horario não encontrado", "not_found", 404);

    }

    // buscar horario
    public function get() {

        $values = (Object) Validation::filter($this->app->post, [
            'guid' => 'int'
        ]);

        if ($horario = $this->model->agenda->get($values->guid)) {
            return $this->view->send($horario);
        }

        return $this->view->erro("Horario não encontrado", "not_found", 404);

    }

    // buscar horarios
    public function getAll() {

        $user = $this->control->login->checkToken();

        $values = (object) Validation::optional($this->app->post, [
            'inicio' => 'string',
            'fim' => 'string',
        ]);

        if (!$values->inicio) {
            $values->inicio = date("Y-m-d H:i:s");
        }

        if (!$values->fim) {
            $values->fim = date("Y-m-d H:i:s", strtotime("+1 month"));
        }

        if (isset($user->admin)) {
            $empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $empresa = $user->id;
        }

        $values->empresa = $empresa;

        if ($horarios = $this->model->exemple->getAll($values)) {
            return $this->view->send($horarios);
        }

        return $this->view->send([]);

    }

    // verifica se horario esta disponivel
    public function check() {

        $values = (Object) Validation::filter($this->app->post, [
            'inicio' => 'string',
            'fim' => 'string',
            'empresa' => 'int',
        ]);

        if ($horario = $this->model->agenda->check($values)) {
            return $this->view->send($horario);
        }

        return $this->view->erro("Horario não disponivel", "not_found", 404);

    }

}