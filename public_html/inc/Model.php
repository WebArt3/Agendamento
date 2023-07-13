<?php

class Model {

    private function login() {
        return new ModelLogin();
    }

    private function users() {
        return new ModelUsers();
    }

    private function empresas() {
        return new ModelEmpresas();
    }

    private function endereco() {
        return new ModelEndereco();
    }

    private function agenda() {
        return new ModelAgenda();
    }

    public function __get($model) {
        return $this->$model();
    }
}