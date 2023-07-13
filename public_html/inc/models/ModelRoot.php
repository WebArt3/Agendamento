<?php

class ModelRoot {

    public function __construct(Database $db=null) {

        $this->view = new View();
        $this->model = new Model();
        $this->app = new App();
        $this->integration = new Integration();

        if (is_null($db)) {
            $this->db = new Database();
        } else {
            $this->db = $db;
        }

        $this->pdo = $this->db->conn;

    }

}