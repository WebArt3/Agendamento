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

        if ($cpf = Validation::validaCpf($values->cpf)) {
            $values->cpf = $cpf;
        } else {
            return $this->view->erro("CPF inválido", "invalid_cpf", 500);
        }

        if ($values->cep) {
            $values->cep = Validation::regexNum($values->cep);
        }

        // get empresa
        if ($empresa = $this->model->empresas->get($values->empresa)) {

            // verifica configurações
            if($configuracao = $this->model->empresas->getConfig($empresa->id)) {

                // verifica se horario ja passou
                if (strtotime(date("Y-m-d H:i:s")) >= strtotime($values->inicio)) {
                    return $this->view->erro("Horario não pode ser agendado", "time_traveller", 500);
                }

                // adiciona fim
                $values->fim = date("Y-m-d H:i:s", strtotime("+{$configuracao->tempo} hours", strtotime($values->inicio)));

                // verifica horarios de atendimento da empresa
                $dia_da_semana = date('N', strtotime($values->inicio));
                
                if ($atendimento = $this->model->empresas->getHorarios($values->empresa, $dia_da_semana)) {

                    foreach ($atendimento as $att_horario) {
                        $att_horario->hora_inicial = strtotime($att_horario->hora_inicial);
                        $att_horario->hora_final = strtotime($att_horario->hora_final);

                        $inicio = strtotime(date("H:i:s", strtotime($values->inicio)));
                        $fim = strtotime(date("H:i:s", strtotime($values->fim)));

                        if (!($inicio >= $att_horario->hora_inicial && $fim <= $att_horario->hora_final)) {
                            return $this->view->erro("Empresa não atende este horário", "indisponible_time", 404);
                        }

                    }

                } else {
                    return $this->view->erro("Empresa não atende este dia", "indisponible_day", 404);
                }

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

    // atualiza horario
    public function update() {

        $values = (object) Validation::filter($this->app->post, [
            'guid' => 'string',
            'inicio' => 'string',
        ]);

        // get horario
        if ($horario = $this->model->agenda->get($values->guid)) {

            // verifica se horario ja passou
            if (strtotime(date("Y-m-d H:i:s")) >= strtotime($values->inicio)) {
                return $this->view->erro("Horario não pode ser alterado", "time_traveller", 500);
            }

            // verifica configurações
            if($configuracao = $this->model->empresas->getConfig($horario->empresas_id)) {

                // permite pular verificação de dias antes ate 1 hora depois de criar o horario
                if (strtotime(date("Y-m-d H:i:s")) >= strtotime("+1 hours", strtotime($horario->data_created))) {

                    // verifica se agora é pelomenos um dia antes do horario
                    if (strtotime(date("Y-m-d H:i:s")) >= strtotime("-1 days", strtotime($horario->inicio))) {
                        return $this->view->erro("Horario não pode ser alterado", "very_recent", 500);
                    }

                }

                // adiciona fim
                $values->fim = date("Y-m-d H:i:s", strtotime("+{$configuracao->tempo} hours", strtotime($values->inicio)));

                // verifica horarios de atendimento da empresa
                $dia_da_semana = date('N', strtotime($values->inicio));

                $values->empresa = $horario->empresas_id;
                
                if ($atendimento = $this->model->empresas->getHorarios($horario->empresas_id, $dia_da_semana)) {

                    foreach ($atendimento as $att_horario) {
                        $att_horario->hora_inicial = strtotime($att_horario->hora_inicial);
                        $att_horario->hora_final = strtotime($att_horario->hora_final);

                        $inicio = strtotime(date("H:i:s", strtotime($values->inicio)));
                        $fim = strtotime(date("H:i:s", strtotime($values->fim)));

                        if (!($inicio >= $att_horario->hora_inicial && $fim <= $att_horario->hora_final)) {
                            return $this->view->erro("Empresa não atende este horário", "indisponible_time", 404);
                        }

                    }

                } else {
                    return $this->view->erro("Empresa não atende este dia", "indisponible_day", 404);
                }


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
            'guid' => 'string'
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

        if ($horarios = $this->model->agenda->getAll($values)) {
            return $this->view->send($horarios);
        }

        return $this->view->send([]);

    }

    // verifica se horario esta disponivel
    public function check() {

        $values = (Object) Validation::filter($this->app->post, [
            'inicio' => 'string',
            'empresa' => 'int',
        ]);

        // get empresa
        if ($empresa = $this->model->empresas->get($values->empresa)) {

            // verifica configurações
            if($configuracao = $this->model->empresas->getConfig($empresa->id)) {

                // adiciona fim
                $values->fim = date("Y-m-d H:i:s", strtotime("+{$configuracao->tempo} hours", strtotime($values->inicio)));

                // verifica horarios de atendimento da empresa
                $dia_da_semana = date('N', strtotime($values->inicio));
                
                if ($atendimento = $this->model->empresas->getHorarios($values->empresa, $dia_da_semana)) {

                    foreach ($atendimento as $att_horario) {
                        $att_horario->hora_inicial = strtotime($att_horario->hora_inicial);
                        $att_horario->hora_final = strtotime($att_horario->hora_final);

                        $inicio = strtotime(date("H:i:s", strtotime($values->inicio)));
                        $fim = strtotime(date("H:i:s", strtotime($values->fim)));

                        if (!($inicio >= $att_horario->hora_inicial && $fim <= $att_horario->hora_final)) {
                            return $this->view->erro("Empresa não atende este horário", "indisponible_time", 404);
                        }

                    }

                } else {
                    return $this->view->erro("Empresa não atende este dia", "indisponible_day", 404);
                }

                // check horario
                if ($horarios = $this->model->agenda->check($values)) {

                    if (count($horarios) >= $configuracao->quantidade) {
                        return $this->view->erro("Horario não disponivel", "agenda_cheia", 404);
                    }

                }

                return $this->view->send(["mensagem" => "Horario disponivel!"]);

            }

            return $this->view->erro("Configuração não encontrada para empresa", "not_found_config", 404);

        }

        return $this->view->erro("Empresa não encontrada", "not_found_empresa", 404);

    }

    // solicita cancelamento de horario
    public function requestCancel() {

        $values = (Object) Validation::filter($this->app->post, [
            'guid' => 'string',
        ]);

        // get horario
        if ($horario = $this->model->agenda->get($values->guid)) {

            // verifica se horario ja passou
            if (strtotime(date("Y-m-d H:i:s")) >= strtotime($horario->inicio)) {
                return $this->view->erro("Horario não pode ser cancelado", "time_traveller", 500);
            }

            // verifica se agora é pelomenos um dia antes do horario
            if (strtotime(date("Y-m-d H:i:s")) >= strtotime("-1 days", strtotime($horario->inicio))) {
                return $this->view->erro("Horario não pode ser cancelado", "very_recent", 500);
            }

            // verifica se horario ja foi cancelado
            if ($horario->cancelado) {
                return $this->view->erro("Horario já cancelado", "already_cancelled", 500);
            }

            // cancela horario
            if ($horario = $this->model->agenda->requestCancel($values)) {
                return $this->view->send($horario);
            }
    
            return $this->view->erro("Erro ao cancelar horario", "cancel_error", 500);

        }

        return $this->view->erro("Horario não encontrado", "not_found", 404);

    }

    // cancela horario
    public function cancel() {

        $user = $this->control->login->checkToken();

        $values = (Object) Validation::filter($this->app->post, [
            'guid' => 'string',
        ]);

        if (isset($user->admin)) {
            $values->empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $values->empresa = $user->id;
        }

        // get horario
        if ($horario = $this->model->agenda->get($values->guid)) {

            // verifica se horario ja passou
            if (strtotime(date("Y-m-d H:i:s")) >= strtotime($horario->inicio)) {
                return $this->view->erro("Horario não pode ser cancelado", "time_traveller", 500);
            }

            // verifica se horario ja foi cancelado
            if ($horario->cancelado) {
                return $this->view->erro("Horario já cancelado", "already_cancelled", 500);
            }

            // cancela horario
            if ($horario = $this->model->agenda->cancel($values)) {
                return $this->view->send($horario);
            }
    
            return $this->view->erro("Erro ao cancelar horario", "cancel_error", 500);

        }

        return $this->view->erro("Horario não encontrado", "not_found", 404);

    }

}