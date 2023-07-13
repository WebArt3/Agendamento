<?php

class Database {
    
    //url do server
    private $servername;
    //usuario mysql
    private $username;
    //senha do usuario mysql
    private $password;
    //banco de dados a ser usado
    private $db;
    
    // Cria conexão com a db
    private $conn;

    private $view;

    function __construct() {

        $this->view = new View();
        
        $this->servername = "localhost";

        $this->username = "webart45_";
        $this->password = "##@1";
        $this->db = "webart45_";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        //ERRMODE_WARNING, ERRMODE_EXCEPTION

        try {
            $this->conn = new PDO("mysql:host={$this->servername};dbname={$this->db};charset=utf8", $this->username, $this->password);
        } catch (PDOException $erro) {
            $this->view->erro("Erro ao conectar com o banco de dados", "db_error", 500, true);
        }

    }

    public function __get($value) {

        if ($value == 'conn') {
            return $this->conn;
        } else {
            $this->view->erro("Sem permissão para acessar variável", "db_forbbiden", 500, true);
        }

    }

    public function safe($value) {

		if (is_null($value)) {
			return false;
		} elseif (empty($value)) {
			return false;
		}

        return htmlspecialchars(trim($value));
        
    }
    
    public function bindArray(PDOStatement $stmt, Array $array) : PDOStatement {
    
        //PDO::PARAM_STR
        
        foreach ($array as $key => $value) {

            $valor = $this->safe($value);
            if (strpos($key, ":json") !== false) {
                $key = str_replace(":json", "", $key);
                $valor = $value;
            }

            if (strpos($key, ":optional") !== false) {
                $key = str_replace(":optional", "", $key);
                if (is_null($value)) {
                    continue;
                }
            }

            if (strpos($key, ":") !== false) {
                $key = str_replace(":", "", $key);
            }

            $stmt->bindValue(":$key", $valor);
        }

        return $stmt;

    }

}