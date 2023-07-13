<?php

class Model {

    private function login() {
        return new ModelLogin();
    }

    private function users() {
        return new ModelUsers();
    }

    public function __get($model) {
        return $this->$model();
    }
}