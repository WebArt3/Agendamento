<?php

class ModelLogin extends ModelRoot {

    // faz login e retorna usuario
    public function login(String $email, String $password) {

        //criptografia secundaria
        $password = md5(base64_encode(md5($password)));

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE email = :email AND `password` = :password");
            $stmt = $this->db->bindArray($stmt, [
                'email' => $email,
                'password' => $password
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $user = $stmt->fetch(PDO::FETCH_OBJ);

                unset($user->password);

                return $user;

            } 

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao executar query de login', 'db_model', 500, true);
        }

        return false;

    }

    // faz login e retorna usuario admin
    public function admin(String $email, String $password) {

        //criptografia secundaria
        $password = md5(base64_encode(md5($password)));

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE email = :email AND `password` = :password");
            $stmt = $this->db->bindArray($stmt, [
                'email' => $email,
                'password' => $password
            ]);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $user = $stmt->fetch(PDO::FETCH_OBJ);

                unset($user->password);

                return $user;

            } 

        } catch (PDOException $erro) {
            $this->view->erro('Erro ao executar query de login', 'db_model', 500, true);
        }

        return false;

    }

}