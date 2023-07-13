<?php

abstract class Validation {

    # valida parametros
    public static function filter($target, array $params) {

        $target = (array) $target;

        $view = new View();
        $res = [];
        
        foreach ($params as $value => $type) {

            $type == 'int' ? $type = 'integer' : false;
            
            $p = @itisreally($target[$value]);

            if ($p !== false) {

                if (is_numeric($p) && $type !== "string") {

                    $p = doubleval($p);
                    if ($type === 'integer') {
                        $p = intval($p);
                    }

                }

                $type_original = gettype($p);

                if ($type == 'string' && ($type_original == 'integer' || $type_original == 'double')) {

                    $res = array_merge($res, [$value => ((string) $p)]);

                } else {

                    if (gettype($p) == $type) {
                        
                        $res = array_merge($res, [$value => $p]);
    
                    } else {
                        $view->erro("Parâmetro $value deve ser $type", 'params_validation_type', 500, true);
                    }

                }

            } else {
                $view->erro("Parâmetro $value está vazio", 'params_validation_error', 500, true);
            }


        }

        return $res;

    }

    # valida parametros
    public static function optional($target, array $params) {

        $target = (array) $target;

        $view = new View();
        $res = [];
        
        foreach ($params as $value => $type) {

            $type == 'int' ? $type = 'integer' : false;
            
            if (isset($target[$value])) {
                $p = $target[$value];
            } else {

                $types_conversion = [
                    'string' => '',
                    'integer' => 0,
                    'double' => 0.0,
                    'boolean' => false,
                    'array' => [],
                    'object' => (object) [],
                    'null' => null,
                ];

                $p = $types_conversion[$type];

            }

            $res = array_merge($res, [$value => $p]);

        }

        return $res;

    }

    # valida imagens
    public static function validaImg(array $file) {  
        
        $nome = md5($file['name'].date('Y-m-d H:i:s'));

        // Pega a extensão
        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        //lista de extensões e mime-types permitidos
        $list_ext_mime = array(
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
        );

        //caso exista uma extensão como a do arquivo na lista
        if (array_key_exists($extensao, $list_ext_mime)) {
            //caso o mime-type do arquivo corresponda a da extensão
            if ($list_ext_mime[$extensao] == $file["type"]) {
                return [
                    'tmp_name' => $file["tmp_name"],
                    'name' => $nome.".".$extensao,
                    'true_name' => $file['name']
                ];
            }
        }

        return false;

    }

    # valida arquivos
    public static function validaArquivo(array $file) {  
        
        $nome = md5($file['name'].date('Y-m-d H:i:s'));

        // Pega a extensão
        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        //lista de extensões e mime-types permitidos
        $list_ext_mime = array(
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'wmv' => 'video/x-ms-wmv',
            'mov' => 'video/quicktime',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
        );

        //caso exista uma extensão como a do arquivo na lista
        if (array_key_exists($extensao, $list_ext_mime)) {
            //caso o mime-type do arquivo corresponda a da extensão
            if ($list_ext_mime[$extensao] == $file["type"]) {
                return [
                    'tmp_name' => $file["tmp_name"],
                    'name' => $nome.".".$extensao,
                    'true_name' => $file['name']
                ];
            }
        }

        return false;

    }

    # extrai numeros cnpj
    public static function limpaCnpj($cnpj_cpf){
        $cnpj_cpf = preg_replace('/[^0-9]/', '', $cnpj_cpf);
        return $cnpj_cpf;
    }

    # extrai numeros cpf
    public static function limpaCpf($cnpj_cpf){
        $cnpj_cpf = preg_replace('/[^0-9]/', '', $cnpj_cpf);
        return $cnpj_cpf;
    }

    # valida cpf
    public static function validaCpf($cpf) {
 
        // Extrai somente os números
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
            
        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
    
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
    
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {

            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return $cpf;
    
    }

    # regex somente numeros
    public static function regexNum($str) {
        return preg_replace('/[^0-9]/', '', $str);
    }

    # valida se o valor é um email
    public static function validaEmail(string $email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}