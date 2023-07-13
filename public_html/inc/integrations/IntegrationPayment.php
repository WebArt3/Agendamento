<?php

class IntegrationPayment extends IntegrationRoot {

    //-------------------------------------------------------------------
    //   Integração de pagamento - PaymentHub WebArt3
    //-------------------------------------------------------------------

    // cria link de pagamento
    public function link(object $pedido) {

        $request = [
            "identificador" => $pedido->id,
            "url" => "https://webart3.com",
            "amount" => number_format($pedido->price, 2, '', ''),
            "item" => $pedido->vitrine->nome,
            "parcelas" => 1,
            "nome" => $pedido->payment->nome,
            "email" => $pedido->payment->email,
            "key" => PAYMENT_KEY,
            "cep" => $pedido->payment->endereco->cep,
            "rua" => $pedido->payment->endereco->rua,
            "numero" => $pedido->payment->endereco->numero,
            "bairro" => $pedido->payment->endereco->bairro,
            "cidade" => $pedido->payment->endereco->cidade,
            "uf" => $pedido->payment->endereco->uf,
            "country" => "BR",

            "split" => [
                ["id" => ID_SPLIT, "porcentagem" => 100, "principal" => true],
                //["id" => "rp_J3vLONkh7h8Ylby4", "porcentagem" => 50, "principal" => false]
            ]

        ];

        $ch = curl_init("https://paymenthub.webart3.com/payment/createlink");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $link = json_decode($response);

        //debug
        //echo $response;
        //print_r($info);

        curl_close($ch);

        if (isset($link->url)) {

            return $link->url;

        }
            
        return false;

    }

    // pagamento com o cartão
    public function creditcard(object $pedido) {

        $pedido->payment->telefone = Validation::regexNum($pedido->payment->telefone);

        $request = [
            "identificador" => "$pedido->id",
            "amount" => number_format($pedido->price*$pedido->quant, 2, '', ''),
            "item" => $pedido->produto->nome . " - " . $pedido->quant . "x",
            "parcelas" => 1,

            "nome" => $pedido->payment->nome,
            "email" => $pedido->payment->email,
            "cpf" => $pedido->payment->cpf,
            "telefone" => $pedido->payment->telefone,

            "key" => PAYMENT_KEY,

            "cep" => $pedido->payment->endereco->cep,
            "rua" => $pedido->payment->endereco->rua,
            "numero" => $pedido->payment->endereco->numero,
            "bairro" => $pedido->payment->endereco->bairro,
            "cidade" => $pedido->payment->endereco->cidade,
            "uf" => $pedido->payment->endereco->uf,
            "country" => "BR",

            "card_number" => $pedido->payment->cartao->numero,
            "card_holder" => $pedido->payment->cartao->nome,
            "card_expiration" => $pedido->payment->cartao->validade,
            "cvv" => $pedido->payment->cartao->cvv,

            "split" => [
                ["id" => ID_SPLIT, "porcentagem" => (100-$pedido->pct_vendedor), "principal" => true],
                ["id" => $pedido->vendedor->receiver, "porcentagem" => $pedido->pct_vendedor, "principal" => false]
            ]

        ];

        $ch = curl_init("https://paymenthub.webart3.com/payment/creditcard");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $payment = json_decode($response);

        //debug
        //echo $response;
        //print_r($info);

        curl_close($ch);

        if (isset($payment->status)) {

            return ["status" => $payment->status];

        }

        return false;

    }

    // pagamento com boleto
    public function boleto(object $pedido) {

        $pedido->payment->telefone = Validation::regexNum($pedido->payment->telefone);

        $request = [
            "identificador" => "$pedido->id",
            "amount" => number_format($pedido->price*$pedido->quant, 2, '', ''),
            "item" => $pedido->produto->nome . " - " . $pedido->quant . "x",

            "nome" => $pedido->payment->nome,
            "email" => $pedido->payment->email,
            "cpf" => $pedido->payment->cpf,
            "telefone" => $pedido->payment->telefone,

            "key" => PAYMENT_KEY,

            "cep" => $pedido->payment->endereco->cep,
            "rua" => $pedido->payment->endereco->rua,
            "numero" => $pedido->payment->endereco->numero,
            "bairro" => $pedido->payment->endereco->bairro,
            "cidade" => $pedido->payment->endereco->cidade,
            "uf" => $pedido->payment->endereco->uf,
            "country" => "BR",

            "split" => [
                ["id" => ID_SPLIT, "porcentagem" => (100-$pedido->pct_vendedor), "principal" => true],
                ["id" => $pedido->vendedor->receiver, "porcentagem" => $pedido->pct_vendedor, "principal" => false]
            ],

            //"debug" => true

        ];

        $ch = curl_init("https://paymenthub.webart3.com/payment/boleto");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $boleto = json_decode($response);

        //debug
        //echo $response;
        //print_r($info);

        curl_close($ch);

        if (isset($boleto->status)) {

            unset($boleto->id);

            return $boleto;

        }

        return false;

    }

    // pagamento com pix
    public function pix(object $pedido) {

        $pedido->payment->telefone = Validation::regexNum($pedido->payment->telefone);

        $request = [
            "identificador" => "$pedido->id",
            "amount" => number_format($pedido->price*$pedido->quant, 2, '', ''),
            "item" => $pedido->produto->nome . " - " . $pedido->quant . "x",

            "nome" => $pedido->payment->nome,
            "email" => $pedido->payment->email,
            "cpf" => $pedido->payment->cpf,
            "telefone" => $pedido->payment->telefone,

            "key" => PAYMENT_KEY,

            "cep" => $pedido->payment->endereco->cep,
            "rua" => $pedido->payment->endereco->rua,
            "numero" => $pedido->payment->endereco->numero,
            "bairro" => $pedido->payment->endereco->bairro,
            "cidade" => $pedido->payment->endereco->cidade,
            "uf" => $pedido->payment->endereco->uf,
            "country" => "BR",

            "split" => [
                ["id" => ID_SPLIT, "porcentagem" => (100-$pedido->pct_vendedor), "principal" => true],
                ["id" => $pedido->vendedor->receiver, "porcentagem" => $pedido->pct_vendedor, "principal" => false]
            ],

            //"debug" => true

        ];

        $ch = curl_init("https://paymenthub.webart3.com/payment/pix");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $pix = json_decode($response);

        //debug
        //echo $response;die;
        // print_r($info);

        curl_close($ch);

        if (isset($pix->status)) {

            unset($pix->id);

            return $pix;

        }

        return false;

    }

    // cria recebedor
    public function createReceiver(string $nome, string $email, string $cpf, string $banco, string $agencia, string $conta) {

        $agencia = explode("-", $agencia);
        $conta = explode("-", $conta);

        $request = [
            "key" => PAYMENT_KEY,
            "plataforma" => "dsjpersonalizados",

            "nome" => $nome,
            "email" => $email,
            "cpf" => $cpf,

            "banco" => $banco,
            "agencia" => $agencia[0],
            "agencia_digito" => count($agencia) > 1 ? $agencia[1] : "",
            "conta" => $conta[0],
            "conta_digito" => count($conta) > 1 ? $conta[1] : "",

            //"debug" => true
        ];

        $ch = curl_init("https://paymenthub.webart3.com/recebedor/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $receiver = json_decode($response);

        //debug
        //echo $response;
        //print_r($info);

        curl_close($ch); 

        if (isset($receiver->id)) {

            return $receiver->id;

        }

        else if (isset($receiver->error) && $receiver->code == "cpf_duplicated") {

            $this->view->erro("Não foi possível realizar cadastro", $receiver->code, 500, true);

        }

        return false;


    }

    // atualizar recebedor
    public function updateReceiver(string $id, string $nome, string $cpf, string $banco, string $agencia, string $conta) {

        $agencia = explode("-", $agencia);
        $conta = explode("-", $conta);

        $request = [
            "key" => PAYMENT_KEY,
            "id" => $id,

            "nome" => $nome,
            "cpf" => $cpf,

            "banco" => $banco,
            "agencia" => $agencia[0],
            "agencia_digito" => count($agencia) > 1 ? $agencia[1] : "",
            "conta" => $conta[0],
            "conta_digito" => count($conta) > 1 ? $conta[1] : "",

            //"debug" => true
        ];
        
        $ch = curl_init("https://paymenthub.webart3.com/recebedor/update");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $receiver = json_decode($response);

        //debug
        //echo $response;
        //print_r($info);

        curl_close($ch); 

        if (isset($receiver->id)) {

            return true;

        }

        else if (isset($receiver->error)) {

            $this->view->erro($receiver->error, $receiver->code, 500, true);

        }

        return false;


    }

    // busca dados do recebedor
    public function getReceiver(string $id) {

        $request = [
            "id" => $id
        ];

        $ch = curl_init("https://paymenthub.webart3.com/recebedor/get");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $info = (object) curl_getinfo($ch);
        $receiver = json_decode($response);

        //debug
        //echo $response;
        //print_r($info);

        curl_close($ch);

        if (isset($receiver->id)) {

            return $receiver;

        }

        return false;

    }
}