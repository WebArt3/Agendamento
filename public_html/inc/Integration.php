<?php

class Integration {

    // private function payment() {
    //     return new IntegrationPayment();
    // }

    public function __get($integration) {
        return $this->$integration();
    }
}