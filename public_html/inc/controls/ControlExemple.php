<?php

class ControlExemple extends ControlRoot{

    //=============================================
    //  Controle de algo
    //=============================================

    // cria algo
    public function create() {

        $user = $this->control->login->checkToken();

        $values = (Object) Validation::filter($this->app->post, [
            'nome' => 'string'
        ]);

        if (isset($user->admin)) {
            $values->empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $values->empresa = $user->id;
        }

        if ($categoria = $this->model->exemple->create($values)) {
            return $this->view->send($categoria);
        }

        return $this->view->erro("Erro ao criar algo", "create_error", 500);

    }

    // criar algo
    public function createComFoto() {

        $user = $this->control->login->checkToken();

        $values = (Object) Validation::filter($this->app->post_original, [
            'nome' => 'string',
        ]);

        if (isset($user->admin)) {
            $values->empresa = Validation::filter($this->app->post_original, ['empresa' => 'int'])["empresa"];
        } else {
            $values->empresa = $user->id;
        }

        // verifica foto
        if (isset($this->app->files['foto'])) {
        
            if ($foto = Validation::validaImg($this->app->files['foto'])) {

                if (move_uploaded_file($foto["tmp_name"], PATH_UPLOADS.$foto["name"])) {

                    $values->foto = $foto["name"];
            
                    if ($novo = $this->model->exemple->create($values)) {
                        return $this->view->send($novo);
                    }

                    unlink(PATH_UPLOADS.$foto["name"]);
        
                    return $this->view->erro("Não foi possível criar algo", "server_error");
        
                } 
        
                return $this->view->erro("Não foi possível fazer upload dessa imagem", "image_error");

            }

            return $this->view->erro("Imagem não foi aprovada pelo sistema", "image_error");

        } else {
            $values->foto = "";
        }

        if ($novo = $this->model->exemple->create($values)) {
            return $this->view->send($novo);
        }

        return $this->view->erro("Erro ao criar produto", "create_error", 500);

    }

    // buscar algo
    public function get() {

        $user = $this->control->login->checkToken();

        $values = (Object) Validation::filter($this->app->post, [
            'id' => 'int'
        ]);

        if ($algo = $this->model->exemple->get($values->id)) {
            return $this->view->send($algo);
        }

        return $this->view->erro("Erro ao buscar algo", "get_error", 500);

    }

    // buscar algo
    public function getAll() {

        $user = $this->control->login->checkToken();

        $search = Validation::optional($this->app->post, ['search' => 'string'])["search"];

        if (isset($user->admin)) {
            $empresa = Validation::filter($this->app->post, ['empresa' => 'int'])["empresa"];
        } else {
            $empresa = $user->id;
        }

        if ($algos = $this->model->exemple->getAll($empresa, $search)) {
            return $this->view->send($algos);
        }

        return $this->view->send([]);

    }

}