<?php

class Control {

    public array $args;

    public function __construct(array $args) {
        $this->args = $args;
    }

    // private function users() {
    //     return new ControlUsers($this->args);
    // }

    private function login() {
        return new ControlLogin($this->args);
    }

    private function agenda() {
        return new ControlAgenda($this->args);
    }

    private function empresas() {
        return new ControlEmpresas($this->args);
    }

    public function __get($control) {
        return $this->$control();
    }
}