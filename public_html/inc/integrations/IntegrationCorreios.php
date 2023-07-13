<?php

class Correios {

    private $cepOrigem;
    private $cepDestino;
    private $peso;
    private $comprimento;
    private $altura;
    private $largura;
    private $diametro = 0;
    private $maoPropria = 'N';
    private $avisoRecebimento = 'N';
    private $codServicos = [
        'PAC' => '41106',
        'SEDEX' => '40010',
    ];

    public function setMedidas($peso, $comprimento, $altura, $largura, $diametro = 0) {
        $this->peso = $peso;
        $this->comprimento = $comprimento;
        $this->altura = $altura;
        $this->largura = $largura;
        $this->diametro = $diametro;
    }

    public function setCeps($cepOrigem, $cepDestino) {
        $this->cepOrigem = $cepOrigem;
        $this->cepDestino = $cepDestino;
    }

    public function getServico($servico) {

        if (!is_numeric($servico)) {
            if (isset($this->codServicos[$servico])) {
                $servico = $this->codServicos[$servico];
            } else {
                return false;
            }
        }
        
        return $servico;
    }

    public function calcularFrete($servico, $valorDeclarado = 0) {

        $codServico = $this->getServico($servico);

        if (!$codServico) {
            return false;
        }

        $url = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx/CalcPrecoPrazo?';
        $url .= 'nCdEmpresa=';
        $url .= '&sDsSenha=';
        $url .= '&sCepOrigem=' . $this->cepOrigem;
        $url .= '&sCepDestino=' . $this->cepDestino;
        $url .= '&nVlPeso=' . $this->peso;
        $url .= '&nCdFormato=1';
        $url .= '&nVlComprimento=' . $this->comprimento;
        $url .= '&nVlAltura=' . $this->altura;
        $url .= '&nVlLargura=' . $this->largura;
        $url .= '&nVlDiametro=' . $this->diametro;
        $url .= '&sCdMaoPropria=' . $this->maoPropria;
        $url .= '&nVlValorDeclarado=' . $valorDeclarado;
        $url .= '&sCdAvisoRecebimento=' . $this->avisoRecebimento;
        $url .= '&nCdServico=' . $codServico;
        $url .= '&nVlDiametro=0';
        $url .= '&StrRetorno=xml';
        $url .= '&nIndicaCalculo=3';

        $xml = simplexml_load_file($url);

        if (!$xml || !isset($xml->cServico)) {
            return false;
        }

        if ($xml->cServico->Erro != 0) {
            return (object) ["Erro" => $xml->cServico->MsgErro];
        }

        return (object) ["transporte" => $servico, "valor" => $xml->cServico->Valor, "prazo" => $xml->cServico->PrazoEntrega];
    }

    public function calcularFretes($valorDeclarado) {

        $fretes = [];

        foreach ($this->codServicos as $servico => $codServico) {
            $frete = $this->calcularFrete($servico, $valorDeclarado);
            if ($frete) {
                $fretes[] = $frete;
            }
        }

        return $fretes;

    }

}

class IntegrationCorreios extends IntegrationRoot {

    //-------------------------------------------------------------------
    //   Integração dos correios
    //-------------------------------------------------------------------

    // Busca valor dos fretes
    public function getFretes($valorDeclarado, $cepOrigem, $cepDestino, $peso, $comprimento, $altura, $largura, $diametro = 0) {

        $correio = new Correios();
        $correio->setCeps($cepOrigem, $cepDestino);
        $correio->setMedidas($peso, $comprimento, $altura, $largura, $diametro);
        $fretes = $correio->calcularFretes($valorDeclarado);
        
        return $fretes;

    }

    // Busca valor do frete
    public function getFrete($servico, $valorDeclarado, $cepOrigem, $cepDestino, $peso, $comprimento, $altura, $largura, $diametro = 0) {

        $correio = new Correios();
        $correio->setCeps($cepOrigem, $cepDestino);
        $correio->setMedidas($peso, $comprimento, $altura, $largura, $diametro);
        $frete = $correio->calcularFrete($servico, $valorDeclarado);
        
        return $frete;

    }
    
}