<?php

class App {

    private $post;
    private $files;
    private $get;
    private $server;
    private $auth;
    private $post_original;

    function __construct() {
        
        if(!$this->post = json_decode(file_get_contents('php://input'), true)) {
            $this->post = [];
        }

        $this->post = (Object) $this->post;

        $this->post_original = $_POST;
        $this->files = $_FILES;
        $this->get = $_GET;
        $this->server = $_SERVER;

        if (isset($this->server['HTTP_X_AUTHORIZATION'])) {
            $this->auth = str_replace("Bearer ", "", $this->server['HTTP_X_AUTHORIZATION']);
        } else {
            
            $this->auth = "";

            if (isset($this->get['token'])) {
                $this->auth = $this->get['token'];
            }
        }

    }

    public function __get($value) {
        return $this->$value;
    }

}