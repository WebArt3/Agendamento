<?php

class IntegrationRoot {

    public function __construct() {

        $this->view = new View();
        $this->model = new Model();

    }

}