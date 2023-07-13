<?php

class JWT {

    public static function encode($payload, string $hash) {

        $payload = (array) $payload;

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $header = json_encode($header);
        $header = JWT::base64url_encode($header);
         
        $payload = json_encode($payload);
        $payload = JWT::base64url_encode($payload);
         
        $signature = hash_hmac('sha256', "$header.$payload", $hash, true);
        $signature = JWT::base64url_encode($signature);
         
        return "$header.$payload.$signature";

    }

    public static function decode(string $token, string $hash) {

        $part = explode(".", $token);

        if (count($part) !== 3) {
            return false;
        }

        $header = $part[0];
        $payload = $part[1];
        $signature = $part[2];

        $valid = hash_hmac('sha256', "$header.$payload", $hash, true);
        $valid = JWT::base64url_encode($valid);

        if($signature == $valid) {
            return json_decode(JWT::base64url_decode($payload));
        }

        return false;
        
    }

    public static function base64url_encode(string $data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64url_decode(string $data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

}