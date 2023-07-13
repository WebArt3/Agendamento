<?php

class View {

    private $body;

    // Gera um erro 
	public function erro($describe, $code, ?int $status=500, ?bool $send=false){

		// configura erro

        http_response_code($status);

		$this->body = ["error" => $describe, "code" => $code];
        
        if ($send) {
            $this->show();
        }

        return $this;

	}

	// Retorna sucesso
	public function sucesso(?int $status=200, ?bool $send=false) {

        http_response_code($status);

        $this->body = ["status" => "success"];

        if ($send) {
            $this->show();
        }

        return $this;
        
    }
    
    // Retorna lista de dados
	public function send($array, ?bool $send=false) {
		
        $this->body = $array;
        
        if ($send) {
            $this->show();
        }

        return $this;
        
    }

    // prepara entrega de arquivo
    public function file($conteudo, $filename) {
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($conteudo));

        echo $conteudo;

        die;
        
    }

    // fechar janela
    public function close() {
        header('Content-Type: text/html; charset=utf-8');
        echo '<script>window.close();</script>';
        die;
    }

	// Apresenta view
	public function show() {
		
		echo json_encode($this->body);

		// para a execução
        exit();
        
    }

    // Verifica se é um erro
	public function is_erro() {
		
        if (isset($this->body["error"])) {
            return true;
        }
        
        return false;
        
    }
    
    // Verifica se é sucesso
	public function is_sucesso() {
		
        if (isset($this->body["code"]) && isset($this->body["status"])) {
            if ($this->body["code"] == 200) {
                return true;
            }
        }
        
        return false;
        
    }

    // Verifica se são dados
	public function is_send() {
		
        if (!$this->is_erro() && !$this->is_sucesso()) {
            return true;
        }
        
        return false;
        
    }

    // REtorna dados guardados
	public function get() {
		
        return $this->body;
        
    }
    
}